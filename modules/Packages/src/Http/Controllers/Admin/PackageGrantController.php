<?php

namespace Modules\Packages\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Packages\src\Models\Package;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Orders\src\Models\Order;
use App\Jobs\SendTelegramTeacherNotification;

class PackageGrantController extends Controller
{
    // Số ngày giáo viên có thể nhận gói trước khi link hết hạn
    const CLAIM_TTL_DAYS = 30;

    public function __construct(
        private readonly PackageLifecycleManager $packageLifecycleManager
    ) {}

    /**
     * Trang form tặng gói đặc quyền cho giáo viên.
     */
    public function index(Request $request)
    {
        $pageTitle = __('packages::admin.titles.grant');
        $packages  = Package::query()->grantable()->get();

        $teacher = null;
        if ($request->filled('teacher_id')) {
            $teacher = Teacher::query()
                ->with(['application.package', 'student'])
                ->find($request->integer('teacher_id'));
        }

        return view('packages::admin.grant', compact('pageTitle', 'packages', 'teacher'));
    }

    /**
     * Ajax: Tìm kiếm giáo viên cho Select2.
     */
    public function searchTeachers(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $teachers = Teacher::query()
            ->with(['application.package', 'student'])
            ->where('status', 'active')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('name_en', 'like', "%{$q}%")
                        ->orWhereHas('student', function ($studentQuery) use ($q) {
                            $studentQuery->where('email', 'like', "%{$q}%")
                                ->orWhere('name', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('name')
            ->limit(15)
            ->get();

        $results = $teachers->map(function (Teacher $teacher) {
            $currentPackage = $teacher->currentPackage();
            $expiresAt      = $teacher->package_expires_at?->format('d/m/Y');
            $packageInfo    = $currentPackage
                ? $currentPackage->name_locale . ($expiresAt ? " (HH: {$expiresAt})" : ' (Vĩnh viễn)')
                : __('packages::admin.grant.no_package');

            return [
                'id'         => $teacher->id,
                'text'       => $teacher->name_locale . ' (' . ($teacher->student?->email ?? 'N/A') . ')',
                'email'      => $teacher->student?->email ?? '',
                'image'      => $teacher->image ?: null,
                'package'    => $packageInfo,
                'package_id' => $currentPackage?->id,
                'expires_at' => $teacher->package_expires_at?->toIso8601String(),
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Admin xác nhận tặng gói:
     * - Tạo TeacherApplication với status = 'pending_claim'
     * - Gửi notification web + email kèm link nhận gói
     * - KHÔNG kích hoạt gói ngay, giáo viên phải tự vào trang nhận
     */
    public function grant(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'    => ['required', 'integer', 'exists:teacher,id'],
            'package_id'    => ['required', 'integer', 'exists:teacher_packages,id'],
            'conflict_mode' => ['required', 'in:override,queue'],
            'admin_note'    => ['nullable', 'string', 'max:1000'],
            'send_email'    => ['nullable', 'boolean'],
        ]);

        $sendEmail = (bool) $request->boolean('send_email', false);

        $teacher = Teacher::query()
            ->with(['application.package', 'student'])
            ->findOrFail($validated['teacher_id']);

        $package  = Package::query()->findOrFail($validated['package_id']);
        $adminId  = auth()->id();
        $adminNote = trim((string) ($validated['admin_note'] ?? ''));

        // Hủy các lần tặng đang chờ nhận trước đó (nếu có) để tránh trùng
        TeacherApplication::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'pending_claim')
            ->whereNotNull('claim_token')
            ->update(['status' => 'cancelled', 'claim_token' => null]);

        // Tạo claim token bảo mật (64 ký tự)
        $claimToken = Str::random(64);

        DB::transaction(function () use ($teacher, $package, $adminId, $adminNote, $claimToken, $validated) {
            $sourceApplication = $teacher->application;
            $locale            = $teacher->student?->preferredLocale() ?? app()->getLocale();

            TeacherApplication::query()->create([
                'student_id'                  => $teacher->student_id,
                'teacher_id'                  => $teacher->id,
                'applicant_type'              => $sourceApplication?->applicant_type ?? 'student',
                'package_id'                  => $package->id ?? null,
                'payment_method'              => null,
                'coupon_code'                 => null,
                'discount_amount'             => 0,
                // pending_claim = chờ giáo viên xác nhận nhận gói
                'status'                      => 'pending_claim',
                'full_name'                   => $sourceApplication?->full_name ?: $teacher->name,
                'display_name'                => $sourceApplication?->display_name ?: $teacher->name,
                'headline'                    => $sourceApplication?->headline,
                'bio'                         => $sourceApplication?->bio ?: $teacher->description,
                'experience_years'            => $sourceApplication?->experience_years ?: $teacher->exp,
                'specialties'                 => $sourceApplication?->specialties ?? [],
                'phone'                       => $sourceApplication?->phone ?: $teacher->student?->phone,
                'email'                       => $sourceApplication?->email ?: $teacher->student?->email,
                'locale'                      => $locale,
                'portfolio_url'               => $sourceApplication?->portfolio_url,
                'facebook_url'                => $sourceApplication?->facebook_url,
                'youtube_url'                 => $sourceApplication?->youtube_url,
                'linkedin_url'                => $sourceApplication?->linkedin_url,
                'custom_links'                => $sourceApplication?->custom_links ?? [],
                'intro_video_url'             => $sourceApplication?->intro_video_url,
                'cv_file'                     => $sourceApplication?->cv_file,
                'identity_file'               => $sourceApplication?->identity_file,
                'type'                        => 'upgrade',
                'submitted_at'                => now(),
                'reviewed_at'                 => now(),
                'reviewed_by'                 => $adminId,
                'granted_by'                  => $adminId,
                'claim_token'                 => $claimToken,
                'claim_expires_at'            => now()->addDays(self::CLAIM_TTL_DAYS),
                'admin_note'                  => $adminNote ?: null,
                // Lưu conflict_mode vào admin_note nếu chưa có ghi chú
                // để xử lý khi teacher claim
            ]);
        });

        // Lưu conflict_mode vào cache hoặc đính kèm vào application vừa tạo
        $grantApplication = TeacherApplication::query()
            ->where('teacher_id', $teacher->id)
            ->where('claim_token', $claimToken)
            ->first();

        if ($grantApplication) {
            // Lưu conflict_mode vào admin_note prefix để xử lý khi claim
            $noteContent = 'conflict_mode:' . $validated['conflict_mode'];
            if ($adminNote) {
                $noteContent .= '|note:' . $adminNote;
            }
            $grantApplication->forceFill(['admin_note' => $noteContent])->save();

            // Create Order record for tracking
            $grantApplication->orders()->create([
                'code' => 'GIFT' . strtoupper(uniqid()),
                'student_id' => $teacher->student_id,
                'total' => 0,
                'status_id' => 2, // Success
                'type' => 'teacher_upgrade',
                'payment_method' => 'gift',
                'payment_complete_date' => now(),
                'currency' => 'VND'
            ]);

            // Notify via Telegram if teacher has feature
            if ($teacher->hasTelegramFeature()) {
                $msg = "🎁 <b>BẠN CÓ QUÀ TẶNG GÓI ĐẶC QUYỀN!</b>\n\n";
                $msg .= "Quản trị viên vừa tặng cho bạn gói: <b>" . ($package->name_locale ?: $package->name) . "</b>\n";
                $msg .= "Hãy truy cập vào hệ thống để nhận quà ngay nhé!";
                
                dispatch(new SendTelegramTeacherNotification($teacher, $msg));
            }
        }

        // Log hành động admin
        activity_log(
            action: 'admin_grant_package_pending',
            subject: $teacher,
            properties: [
                'package'       => $package->name,
                'package_id'    => $package->id,
                'conflict_mode' => $validated['conflict_mode'],
                'claim_token'   => substr($claimToken, 0, 8) . '...',
                'claim_expires' => now()->addDays(self::CLAIM_TTL_DAYS)->toDateTimeString(),
                'admin_note'    => $adminNote ?: null,
            ],
            logName: 'admin_package_management',
            description: "Admin tặng gói [{$package->name}] cho giáo viên [{$teacher->name}] – đang chờ giáo viên nhận (conflict: {$validated['conflict_mode']})",
        );

        // Gửi notification web (database notification Laravel) cho giáo viên
        $this->sendWebNotification($teacher, $package, $claimToken);

        // Gửi email kèm link nhận gói chỉ khi Admin tích vào checkbox
        if ($sendEmail) {
            $this->sendClaimEmail($teacher, $package, $claimToken);
        }

        return redirect()
            ->route('teacher-packages.grant')
            ->with('msg', __('packages::admin.messages.grant_pending', [
                'teacher' => $teacher->name_locale,
                'package' => $package->name_locale,
            ]));
    }

    /**
     * Gửi in-app notification (database notification Laravel) cho giáo viên.
     */
    private function sendWebNotification(Teacher $teacher, Package $package, string $claimToken): void
    {
        $student = $teacher->student;
        if (!$student) {
            return;
        }

        $locale   = $teacher->student?->preferredLocale() ?? app()->getLocale();
        $claimUrl = route('teacher.dashboard.package.claim', ['locale' => $locale, 'token' => $claimToken]);

        try {
            $student->notify(new \Modules\Packages\src\Notifications\PackageGrantedNotification(
                teacher: $teacher,
                package: $package,
                claimUrl: $claimUrl,
            ));
        } catch (\Throwable) {
            // Không để lỗi notification crash luồng chính
        }
    }

    /**
     * Gửi email kèm link nhận gói.
     */
    private function sendClaimEmail(Teacher $teacher, Package $package, string $claimToken): void
    {
        $teacherEmail = $teacher->student?->email ?? $teacher->application?->email;
        if (!$teacherEmail) {
            return;
        }

        $locale   = $teacher->student?->preferredLocale() ?? app()->getLocale();
        $claimUrl = route('teacher.dashboard.package.claim', ['locale' => $locale, 'token' => $claimToken]);

        try {
            Mail::to($teacherEmail)
                ->locale($locale)
                ->queue(new \App\Mail\TeacherPackageGrantedMail($teacher, $package, $claimUrl, $locale));
        } catch (\Throwable) {
            // Không để lỗi mail crash luồng chính
        }
    }
}
