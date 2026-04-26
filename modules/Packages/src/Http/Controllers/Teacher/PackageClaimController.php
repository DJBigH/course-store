<?php

namespace Modules\Packages\src\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Packages\src\Support\PackageLifecycleManager;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;

class PackageClaimController extends Controller
{
    public function __construct(
        private readonly PackageLifecycleManager $packageLifecycleManager
    ) {}

    /**
     * Hiển thị trang "Nhận quà" cho giáo viên.
     * Teacher click link trong email/notification → vào trang này xem thông tin gói → bấm Nhận.
     */
    public function show(string $token)
    {
        $application = $this->resolveValidApplication($token);

        if (!$application) {
            return view('packages::teacher.claim-invalid', [
                'pageTitle' => 'Liên kết không hợp lệ',
                'reason'    => 'expired_or_used',
            ]);
        }

        // Chỉ teacher đúng mới được xem
        $teacher = auth('students')->user()?->teacher ?? null;
        if (!$teacher || (int) $teacher->id !== (int) $application->teacher_id) {
            abort(403, 'Bạn không có quyền nhận gói này.');
        }

        $package = $application->package;

        return view('packages::teacher.claim', [
            'pageTitle'   => 'Nhận gói đặc quyền',
            'application' => $application,
            'package'     => $package,
            'teacher'     => $teacher,
            'expiresAt'   => $application->claim_expires_at,
        ]);
    }

    /**
     * Teacher xác nhận nhận gói → kích hoạt ngay.
     */
    public function claim(Request $request, string $token)
    {
        $application = $this->resolveValidApplication($token);

        if (!$application) {
            return redirect()
                ->route('teacher.dashboard.index')
                ->with('msg_danger', 'Liên kết nhận gói đã hết hạn hoặc không hợp lệ.');
        }

        $teacher = auth('students')->user()?->teacher ?? null;
        if (!$teacher || (int) $teacher->id !== (int) $application->teacher_id) {
            abort(403);
        }

        // Đọc conflict_mode từ admin_note (được lưu dạng "conflict_mode:override|note:...")
        $conflictMode = $this->extractConflictMode($application->admin_note ?? '');

        DB::transaction(function () use ($teacher, $application, $conflictMode) {
            // Đổi status thành approved để có thể kích hoạt
            $application->forceFill(['status' => 'approved'])->save();

            $teacher->loadMissing(['application.package']);

            if ($conflictMode === 'override') {
                // Ghi đè ngay bất kể gói cũ còn hạn hay không
                $startAt   = now();
                $expiresAt = $this->packageLifecycleManager->calculateExpiresAt($application->package, $startAt);

                $teacher->forceFill([
                    'application_id'     => $application->id,
                    'commission_rate'    => $application->package?->commission_rate ?? $teacher->commission_rate,
                    'package_started_at' => $startAt,
                    'package_expires_at' => $expiresAt,
                ])->save();

                $application->forceFill([
                    'status'             => 'approved',
                    'activates_at'       => $startAt,
                    'package_started_at' => $startAt,
                    'package_expires_at' => $expiresAt,
                    'activated_at'       => now(),
                    'claimed_at'         => now(),
                    'claim_token'        => null, // Vô hiệu hóa token sau khi claim
                ])->save();

                $refreshed = $teacher->fresh(['application.package']);
                $this->packageLifecycleManager->syncCouponLocks($refreshed);
                $this->packageLifecycleManager->syncCourseLocks($refreshed);

            } else {
                // Queue: dùng PackageLifecycleManager để xử lý logic queue/activate
                $packageAction = $this->packageLifecycleManager->applyApprovedChange($teacher, $application);

                $application->forceFill([
                    'claimed_at'  => now(),
                    'claim_token' => null,
                ])->save();
            }
        });

        // Log hành động teacher đã nhận gói
        activity_log(
            action: 'teacher_claimed_package',
            subject: $teacher->fresh(),
            properties: [
                'package'       => $application->package?->name,
                'package_id'    => $application->package_id,
                'conflict_mode' => $conflictMode,
            ],
            logName: 'teacher_package',
            description: "Giáo viên [{$teacher->name}] đã nhận gói đặc quyền [{$application->package?->name}]",
        );

        return redirect()
            ->route('teacher.dashboard.package.upgrade')
            ->with('msg_success', 'Chúc mừng! Bạn đã nhận thành công gói ' . ($application->package?->name_locale ?? 'đặc quyền') . '.');
    }

    /**
     * Teacher từ chối nhận gói.
     */
    public function decline(Request $request, string $token)
    {
        $application = $this->resolveValidApplication($token);

        if (!$application) {
            return redirect()->route('teacher.dashboard.index');
        }

        $teacher = auth('students')->user()?->teacher ?? null;
        if (!$teacher || (int) $teacher->id !== (int) $application->teacher_id) {
            abort(403);
        }

        DB::transaction(function () use ($application) {
            $application->forceFill([
                'status'      => 'cancelled',
                'claim_token' => null, // Vô hiệu hóa token
                'admin_note'  => ($application->admin_note ? $application->admin_note . ' | ' : '') . 'Teacher declined this gift at ' . now()->toDateTimeString(),
            ])->save();
        });

        activity_log(
            action: 'teacher_declined_package',
            subject: $teacher,
            properties: [
                'package'    => $application->package?->name,
                'package_id' => $application->package_id,
            ],
            logName: 'teacher_package',
            description: "Giáo viên [{$teacher->name}] đã từ chối nhận gói đặc quyền [{$application->package?->name}]",
        );

        return redirect()
            ->route('teacher.dashboard.index')
            ->with('msg_success', 'Bạn đã từ chối nhận gói quà tặng thành công.');
    }

    /**
     * Lấy application hợp lệ từ token (chưa dùng, chưa hết hạn, status = pending_claim).
     */
    private function resolveValidApplication(string $token): ?TeacherApplication
    {
        return TeacherApplication::query()
            ->with(['package', 'teacher'])
            ->where('claim_token', $token)
            ->where('status', 'pending_claim')
            ->where('claim_expires_at', '>', now())
            ->whereNull('claimed_at')
            ->first();
    }

    /**
     * Đọc conflict_mode từ chuỗi admin_note dạng "conflict_mode:override|note:..."
     */
    private function extractConflictMode(string $adminNote): string
    {
        if (preg_match('/conflict_mode:(override|queue)/', $adminNote, $matches)) {
            return $matches[1];
        }

        return 'queue'; // Default an toàn
    }
}
