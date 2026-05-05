<?php

namespace Modules\Teacher\src\Http\Controllers\Admin;

use App\Mail\TeacherApplicationApprovedMail;
use App\Mail\TeacherApplicationRejectedMail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Modules\Students\src\Models\Student;
use Modules\Teacher\src\Models\Teacher;
use Modules\Teacher\src\Models\TeacherApplication;
use Modules\Packages\src\Support\PackageLifecycleManager;

class TeacherApplicationController extends Controller
{
    private const DEFAULT_TEACHER_AVATAR = 'resources/assets/teacher.png';

    public function __construct(
        private readonly PackageLifecycleManager $packageLifecycleManager
    ) {}

    public function index(Request $request)
    {
        $pageTitle = __('teacher::admin.titles.applications');
        $applications = TeacherApplication::query()
            ->where('type', 'new')
            ->with(['student', 'package', 'teacher', 'reviewer', 'orders'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate(12);
        
        $applications->withQueryString();

        return view('teacher::applications.lists', compact('pageTitle', 'applications'));
    }

    public function show($id)
    {
        $pageTitle = __('teacher::admin.titles.application_detail');
        $application = TeacherApplication::query()
            ->where('type', 'new')
            ->with(['student', 'package', 'teacher', 'reviewer', 'orders'])
            ->findOrFail($id);

        $history = TeacherApplication::query()
            ->where('id', '!=', $application->id)
            ->where(function($q) use ($application) {
                $q->where('email', $application->email);
                if ($application->student_id) {
                    $q->orWhere('student_id', $application->student_id);
                }
            })
            ->with(['package', 'reviewer'])
            ->latest('id')
            ->get();

        return view('teacher::applications.show', compact('pageTitle', 'application', 'history'));
    }

    public function approve(Request $request, $id)
    {
        $application = TeacherApplication::query()
            ->where('type', 'new')
            ->with(['student', 'package', 'teacher.application.package', 'teacher.student'])
            ->findOrFail($id);

        if ($application->status === 'pending_payment') {
            return back()->with('msg_danger', __('teacher::admin.messages.pending_payment_error'));
        }

        $teacher = $application->teacher;
        $displayName = $application->display_name ?: $application->full_name;
        $student = $application->student;
        $passwordSetupUrl = null;
        $accountWasCreated = false;

        if (!$student) {
            $student = Student::query()->where('email', $application->email)->first();
        }

        $mailLocale = $this->resolveMailLocale($student, $application);

        if (!$student) {
            $student = Student::query()->create([
                'name' => $application->full_name,
                'email' => $application->email,
                'phone' => $application->phone,
                'status' => 1,
                'preferred_locale' => $mailLocale,
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);
            $accountWasCreated = true;
            /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
            $broker = Password::broker('students');
            $passwordSetupUrl = URL::route('teacher.password.reset', [
                'locale' => $mailLocale,
                'token' => $broker->createToken($student),
                'email' => $student->email,
            ]);
        } elseif ($student->preferred_locale !== $mailLocale) {
            $student->forceFill(['preferred_locale' => $mailLocale])->save();
        }

        if (!$teacher) {
            $teacher = new Teacher();
        }

        $teacher->fill([
            'student_id' => $student->id,
            'name' => $displayName,
            'slug' => $this->makeUniqueSlug($displayName, $teacher->id),
            'description' => $application->bio,
            'exp' => $application->experience_years,
            'image' => $teacher->image ?: self::DEFAULT_TEACHER_AVATAR,
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        $teacher->save();

        if (!$teacher->slug) {
            $teacher->update(['slug' => $this->makeUniqueSlug($displayName, $teacher->id)]);
        }

        $application->update([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reviewed_at' => now(),
            'account_created_at' => $accountWasCreated ? now() : $application->account_created_at,
            'account_credentials_sent_at' => $passwordSetupUrl ? now() : $application->account_credentials_sent_at,
            'reviewed_by' => auth()->id(),
            'admin_note' => $request->input('admin_note'),
        ]);

        $packageAction = $this->packageLifecycleManager->applyApprovedChange($teacher, $application->fresh(['package']));
        $teacher->refresh();
        $application->refresh();

        activity_log(
            action: 'teacher_application_approved',
            subject: $teacher,
            properties: [
                'application_id' => $application->id,
                'student_id' => $student->id,
                'package' => $application->package?->name,
                'type' => $application->admin_note === 'package_upgrade' ? 'package_upgrade' : 'new_application',
                'package_action' => $packageAction,
                'package_expires_at' => optional($teacher->package_expires_at)->toDateTimeString(),
                'activates_at' => optional($application->activates_at)->toDateTimeString(),
            ],
            logName: __('teacher::admin.logs.approve_title'),
            description: $application->admin_note === 'package_upgrade'
                ? __('teacher::admin.logs.approve_upgrade_desc')
                : __('teacher::admin.logs.approve_new_desc')
        );

        Mail::to($application->email)
            ->locale($mailLocale)
            ->queue(new TeacherApplicationApprovedMail(
                $application,
                $passwordSetupUrl,
                !$accountWasCreated,
                $packageAction,
                $mailLocale
            ));

        // Notify Student via web
        if ($student) {
            $student->notify(new \App\Notifications\TeacherApplicationStatusNotification($application->fresh()));
        }

        return redirect()->route('teacher-applications.show', $application->id)
            ->with('msg', __('teacher::admin.messages.approve_success'));
    }

    public function reject(Request $request, $id)
    {
        $application = TeacherApplication::query()
            ->where('type', 'new')
            ->findOrFail($id);

        $application->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'admin_note' => trim((string) $request->input('admin_note')) ?: __('teacher::admin.messages.default_reject_note'),
        ]);

        activity_log(
            action: 'teacher_application_rejected',
            subject: $application->fresh(),
            properties: [
                'application_id' => $application->id,
                'email' => $application->email,
                'admin_note' => $application->admin_note,
            ],
            logName: __('teacher::admin.logs.reject_title') ?? 'Từ chối hồ sơ giảng viên',
            description: __('teacher::admin.logs.reject_desc') ?? 'Từ chối hồ sơ đăng ký giảng viên'
        );

        Mail::to($application->email)
            ->locale(app()->getLocale())
            ->queue(new TeacherApplicationRejectedMail($application, app()->getLocale()));

        // Notify Student via web (if exists)
        $student = $application->student ?: Student::where('email', $application->email)->first();
        if ($student) {
            $student->notify(new \App\Notifications\TeacherApplicationStatusNotification($application->fresh()));
        }

        return redirect()->route('teacher-applications.show', $application->id)
            ->with('msg', __('teacher::admin.messages.reject_success'));
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'teacher';
        $slug = $base;
        $counter = 1;

        while (Teacher::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $counter++;
            $slug = $base . '-' . $counter;
        }

        return $slug;
    }

    private function resolveMailLocale(?Student $student, TeacherApplication $application): string
    {
        $preferred = $student?->preferredLocale();
        if (in_array($preferred, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
            return $preferred;
        }

        $applicationLocale = (string) ($application->locale ?? '');
        if (in_array($applicationLocale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
            return $applicationLocale;
        }

        $currentLocale = app()->getLocale();
        if (in_array($currentLocale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
            return $currentLocale;
        }

        return config('app.locale', 'vi');
    }
}
