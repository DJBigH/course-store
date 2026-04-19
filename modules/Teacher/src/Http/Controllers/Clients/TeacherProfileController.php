<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordChangeNotification;
use App\Support\StudentTwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Teacher\src\Http\Requests\TeacherProfileUpdateRequest;

class TeacherProfileController extends Controller
{
    public function __construct(
        protected StudentTwoFactorService $twoFactorService
    ) {}

    public function show()
    {
        $pageTitle = __('courses::teacher/messages.profile.title');
        $pageName = $pageTitle;
        $student = Auth::guard('students')->user();
        $teacher = $student?->teacher;
        $application = $teacher?->application;

        return view('teacher::clients.dashboard.profile', compact('pageTitle', 'pageName', 'student', 'teacher', 'application'));
    }

    public function update(TeacherProfileUpdateRequest $request)
    {
        $student = Auth::guard('students')->user();
        abort_unless($student, 403);
        $teacher = $student->teacher;
        $application = $teacher?->application;
        $section = (string) $request->input('profile_section', 'all');
        $updateAccount = in_array($section, ['all', 'account'], true);
        $updateProfessional = in_array($section, ['all', 'professional'], true);
        $updateLinks = in_array($section, ['all', 'links'], true);
        $updatePassword = $section === 'password' || filled($request->input('password'));

        $oldValues = [
            'name' => (string) $student->name,
            'phone' => (string) $student->phone,
            'address' => (string) ($student->address ?? ''),
            'image' => (string) ($teacher?->image ?? ''),
            'display_name' => (string) ($application->display_name ?? ''),
            'headline' => (string) ($application->headline ?? ''),
            'bio' => (string) ($application->bio ?? ''),
            'experience_years' => $application->experience_years,
            'specialties' => $application?->specialties ?? [],
            'portfolio_url' => (string) ($application->portfolio_url ?? ''),
            'linkedin_url' => (string) ($application->linkedin_url ?? ''),
            'custom_links' => $application?->custom_links ?? [],
            'intro_video_url' => (string) ($application->intro_video_url ?? ''),
        ];

        if ($updateAccount) {
            $student->forceFill([
                'name' => (string) $request->input('name'),
                'phone' => (string) $request->input('phone'),
                'address' => (string) $request->input('address'),
            ])->save();
        }

        if ($application) {
            $applicationData = [];

            if ($updateAccount) {
                $applicationData['full_name'] = (string) $request->input('name');
                $applicationData['phone'] = (string) $request->input('phone');
            }

            if ($updateProfessional) {
                $applicationData['display_name'] = $request->filled('display_name') ? (string) $request->input('display_name') : null;
                $applicationData['headline'] = $request->filled('headline') ? (string) $request->input('headline') : null;
                $applicationData['bio'] = $request->filled('bio') ? (string) $request->input('bio') : null;
                $applicationData['experience_years'] = $request->filled('experience_years') ? (int) $request->input('experience_years') : null;
                $applicationData['specialties'] = $this->parseSpecialties($request->input('specialties'));
            }

            if ($updateLinks) {
                $applicationData['portfolio_url'] = $request->filled('portfolio_url') ? (string) $request->input('portfolio_url') : null;
                $applicationData['linkedin_url'] = $request->filled('linkedin_url') ? (string) $request->input('linkedin_url') : null;
                $applicationData['intro_video_url'] = $request->filled('intro_video_url') ? (string) $request->input('intro_video_url') : null;
                $applicationData['custom_links'] = $this->normalizeCustomLinks($request->input('custom_links', []));
            }

            if ($applicationData !== []) {
                $application->forceFill($applicationData)->save();
            }
        }

        if ($teacher) {
            $teacherData = [];

            if ($updateAccount) {
                $teacherData['image'] = $request->filled('image') ? (string) $request->input('image') : null;
            }

            if ($updateProfessional) {
                $teacherData['name'] = (string) ($request->input('display_name') ?: $request->input('name') ?: $teacher->name);
                $teacherData['description'] = $request->filled('bio') ? (string) $request->input('bio') : null;
                $teacherData['exp'] = $request->filled('experience_years') ? (int) $request->input('experience_years') : null;
            }

            if ($teacherData !== []) {
                $teacher->forceFill($teacherData)->save();
            }
        }

        activity_log(
            'profile_updated',
            $student,
            [
                'old' => $oldValues,
                'new' => [
                    'name' => (string) $student->name,
                    'phone' => (string) $student->phone,
                    'address' => (string) ($student->address ?? ''),
                    'image' => (string) ($teacher?->image ?? ''),
                    'display_name' => (string) ($application?->display_name ?? ''),
                    'headline' => (string) ($application?->headline ?? ''),
                    'bio' => (string) ($application?->bio ?? ''),
                    'experience_years' => $application?->experience_years,
                    'specialties' => $application?->specialties ?? [],
                    'portfolio_url' => (string) ($application?->portfolio_url ?? ''),
                    'linkedin_url' => (string) ($application?->linkedin_url ?? ''),
                    'custom_links' => $application?->custom_links ?? [],
                    'intro_video_url' => (string) ($application?->intro_video_url ?? ''),
                ],
                'workspace' => 'teacher',
            ],
            'student_profile',
            __('students::clients/account.activity_log.profile_updated_desc')
        );

        if (!$updatePassword || blank($request->input('password'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => __('courses::teacher/messages.profile.flash.updated'),
                ]);
            }

            return back()->with('msg_success', __('courses::teacher/messages.profile.flash.updated'));
        }

        $currentPassword = (string) $request->input('current_password');
        $newPassword = (string) $request->input('password');
        $guard = Auth::guard('students');

        if (method_exists($guard, 'logoutOtherDevices')) {
            $guard->logoutOtherDevices($currentPassword);
        } elseif (method_exists(Auth::class, 'logoutOtherDevices')) {
            Auth::logoutOtherDevices($currentPassword);
        }

        $student->forceFill([
            'password' => bcrypt($newPassword),
            'remember_token' => Str::random(60),
        ])->save();

        $student->notify(new ResetPasswordChangeNotification());

        activity_log(
            'password_changed',
            $student,
            ['email' => $student->email, 'workspace' => 'teacher'],
            'student_security',
            __('students::clients/account.activity_log.password_changed_desc')
        );

        $this->twoFactorService->forgetRecentVerification($request);
        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('teacher.auth.login', ['locale' => app()->getLocale()])
            ->with('msg_success', __('courses::teacher/messages.profile.flash.password_changed'));
    }

    private function parseSpecialties(mixed $raw): array
    {
        return collect(explode(',', (string) $raw))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizeCustomLinks(mixed $rows): array
    {
        return collect(is_array($rows) ? $rows : [])
            ->map(function ($row) {
                return [
                    'label' => trim((string) ($row['label'] ?? '')),
                    'url' => trim((string) ($row['url'] ?? '')),
                ];
            })
            ->filter(fn ($row) => $row['label'] !== '' || $row['url'] !== '')
            ->filter(fn ($row) => $row['label'] !== '' && $row['url'] !== '')
            ->values()
            ->all();
    }
}
