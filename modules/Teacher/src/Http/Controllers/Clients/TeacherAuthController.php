<?php

namespace Modules\Teacher\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Notifications\TeacherResetPasswordQueued;
use App\Support\ClientMailThrottle;
use App\Support\StudentTwoFactorService;
use App\Support\SystemMailManager;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Auth\src\Http\Requests\LoginRequest;
use Modules\Students\src\Models\Student;

class TeacherAuthController extends Controller
{
    public function __construct(
        protected StudentTwoFactorService $twoFactorService,
        protected ClientMailThrottle $mailThrottle,
        protected SystemMailManager $systemMailManager
    ) {
    }

    public function showLoginForm(Request $request, string $locale)
    {
        if ($response = $this->redirectAuthenticatedStudent($locale)) {
            return $response;
        }

        $pageTitle = __('teacher::auth.login.page_title');

        return view('teacher::clients.auth.login', compact('pageTitle'));
    }

    public function login(LoginRequest $request, string $locale)
    {
        if ($response = $this->redirectAuthenticatedStudent($locale)) {
            return $response;
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (!Auth::guard('students')->attempt($credentials, $request->remember == 1)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('msg_danger', __('teacher::auth.login.invalid_credentials'));
        }

        /** @var Student|null $student */
        $student = Auth::guard('students')->user();

        if (!$student || !$this->hasActiveTeacherAccess($student)) {
            Auth::guard('students')->logout();

            return back()
                ->withInput($request->only('email'))
                ->with('msg_danger', __('teacher::auth.access.not_teacher'));
        }

        $maxDevices = (int) setting('max_devices', config('auth.max_devices', 1));
        $activeSessions = DB::table('sessions')
            ->where('user_id', $student->id)
            ->count();

        if ($activeSessions > $maxDevices) {
            Auth::guard('students')->logout();
            abort(403, 'Tai khoan cua ban dang duoc dang nhap tren thiet bi khac.');
        }

        if ($student->preferred_locale !== $locale) {
            $student->forceFill(['preferred_locale' => $locale])->save();
        }

        if ($student->two_factor_email_enabled) {
            if (!$this->systemMailManager->isEnabled() || !$this->systemMailManager->isConfigured()) {
                Auth::guard('students')->logout();

                return back()->with('msg_danger', __('students::clients/account.two_factor.mail_disabled'));
            }

            Auth::guard('students')->logout();

            $request->session()->put('students.two_factor.pending_login_id', $student->id);
            $request->session()->put('students.two_factor.pending_remember', $request->remember == 1);
            $request->session()->put('url.intended', route('teacher.dashboard.index'));

            $this->twoFactorService->issueChallenge(
                $student,
                StudentTwoFactorService::PURPOSE_LOGIN,
                $locale,
                false
            );

            return redirect()->route('students.2fa.challenge', ['locale' => $locale])
                ->with('msg', __('students::clients/account.two_factor.login_code_sent'));
        }

        $this->twoFactorService->markRecentVerification($request);
        $this->twoFactorService->handleSuccessfulLogin($student, $request, $locale);

        return redirect()->route('teacher.dashboard.index');
    }

    public function showForgotForm(Request $request, string $locale)
    {
        if ($response = $this->redirectAuthenticatedStudent($locale)) {
            return $response;
        }

        $pageTitle = __('teacher::auth.forgot.page_title');

        return view('teacher::clients.auth.forgot', compact('pageTitle'));
    }

    public function sendResetLink(Request $request, string $locale)
    {
        if ($response = $this->redirectAuthenticatedStudent($locale)) {
            return $response;
        }

        $request->validate(['email' => 'required|email']);

        if (!$this->systemMailManager->isEnabled() || !$this->systemMailManager->isConfigured()) {
            return back()->with('msg_danger', __('auth::clients/messages.mail_disabled'));
        }

        $throttle = config('mail.throttle.forgot_password');
        $throttleKey = $this->mailThrottle->key('teacher-forgot-password', [
            $request->ip(),
            $request->input('email'),
        ]);

        if ($this->mailThrottle->tooManyAttempts($throttleKey, (int) $throttle['max_attempts'])) {
            return back()->with('msg_danger', __('auth::clients/messages.mail_throttled', [
                'seconds' => $this->mailThrottle->availableIn($throttleKey),
            ]));
        }

        $this->mailThrottle->hit($throttleKey, (int) $throttle['decay_seconds']);

        $student = Student::query()
            ->where('email', trim((string) $request->input('email')))
            ->whereHas('teacher', fn ($query) => $query->where('status', 'active'))
            ->first();

        if ($student) {
            $student->forceFill(['preferred_locale' => $locale])->save();
            $token = Password::broker('students')->createToken($student);
            $student->notify((new TeacherResetPasswordQueued($token, $locale))->locale($locale));
        }

        return back()->with('msg', __('teacher::auth.messages.reset_link_sent'));
    }

    public function showResetForm(Request $request, string $locale, string $token)
    {
        if ($response = $this->redirectAuthenticatedStudent($locale)) {
            return $response;
        }

        $pageTitle = __('teacher::auth.reset.page_title');

        return view('teacher::clients.auth.reset', compact('pageTitle', 'token'));
    }

    public function updatePassword(Request $request, string $locale)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        $email = trim((string) $request->input('email'));
        $token = trim((string) $request->input('token'));
        $password = (string) $request->input('password');

        /** @var Student|null $student */
        $student = Student::query()
            ->where('email', $email)
            ->whereHas('teacher', fn ($query) => $query->where('status', 'active'))
            ->first();

        $tokenRow = DB::table(config('auth.passwords.students.table'))
            ->where('email', $email)
            ->first();

        $isTokenValid = false;

        if ($student && $tokenRow) {
            $expiresAt = now()->parse($tokenRow->created_at)
                ->addMinutes((int) config('auth.passwords.students.expire', 10));

            $isTokenValid = $expiresAt->isFuture() && Hash::check($token, $tokenRow->token);
        }

        if (!$isTokenValid) {
            return back()->with('msg_danger', __('auth::messages.passwords.token'));
        }

        $student->forceFill([
            'password' => Hash::make($password),
            'preferred_locale' => $locale,
        ])->setRememberToken(Str::random(60));

        $student->save();
        DB::table(config('auth.passwords.students.table'))->where('email', $email)->delete();

        event(new PasswordReset($student));

        return redirect()->route('teacher.auth.login', ['locale' => $locale])
            ->with('msg', __('teacher::auth.messages.password_reset_success'));
    }

    protected function redirectAuthenticatedStudent(string $locale)
    {
        /** @var Student|null $student */
        $student = Auth::guard('students')->user();

        if (!$student) {
            return null;
        }

        if ($this->hasActiveTeacherAccess($student)) {
            return redirect()->route('teacher.dashboard.index');
        }

        $application = $student->teacherApplications()->latest('id')->first();
        $redirect = $application
            ? route('teacher.account.status', ['locale' => $locale])
            : route('teacher.portal.index', ['locale' => $locale]);

        return redirect($redirect)->with('msg_danger', __('teacher::auth.access.not_teacher'));
    }

    protected function hasActiveTeacherAccess(Student $student): bool
    {
        return $student->teacher?->status === 'active';
    }
}
