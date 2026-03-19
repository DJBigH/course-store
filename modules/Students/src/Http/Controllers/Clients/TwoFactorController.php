<?php

namespace Modules\Students\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Mail\AccountDeactivatedMail;
use App\Mail\StudentTwoFactorStatusMail;
use App\Support\StudentTwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Students\src\Models\Student;

class TwoFactorController extends Controller
{
    public function __construct(protected StudentTwoFactorService $twoFactorService)
    {
    }

    public function startEnable(Request $request, string $locale)
    {
        $student = Auth::guard('students')->user();

        if (!$student) {
            return redirect()->route('clients-login', ['locale' => $locale]);
        }

        if ($student->two_factor_email_enabled) {
            return redirect()
                ->route('students.account.profile', ['locale' => $locale])
                ->with('msg', __('students::clients/account.two_factor.already_enabled'));
        }

        $sent = $this->twoFactorService->issueChallenge(
            $student,
            StudentTwoFactorService::PURPOSE_ENABLE,
            $locale,
            false
        );

        return redirect()
            ->route('students.2fa.challenge', ['locale' => $locale])
            ->with(
                'msg',
                $sent
                    ? __('students::clients/account.two_factor.enable_code_sent')
                    : __('students::clients/account.two_factor.code_already_sent', [
                        'seconds' => $this->twoFactorService->secondsUntilResend($student),
                    ])
            );
    }

    public function startDisable(Request $request, string $locale)
    {
        $student = Auth::guard('students')->user();

        if (!$student) {
            return redirect()->route('clients-login', ['locale' => $locale]);
        }

        if (!$student->two_factor_email_enabled) {
            return redirect()
                ->route('students.account.profile', ['locale' => $locale])
                ->with('msg', __('students::clients/account.two_factor.already_disabled'));
        }

        $sent = $this->twoFactorService->issueChallenge(
            $student,
            StudentTwoFactorService::PURPOSE_DISABLE,
            $locale,
            false
        );

        return redirect()
            ->route('students.2fa.challenge', ['locale' => $locale])
            ->with(
                'msg',
                $sent
                    ? __('students::clients/account.two_factor.disable_code_sent')
                    : __('students::clients/account.two_factor.code_already_sent', [
                        'seconds' => $this->twoFactorService->secondsUntilResend($student),
                    ])
            );
    }

    public function startDeactivate(Request $request, string $locale)
    {
        $student = Auth::guard('students')->user();

        if (!$student) {
            return redirect()->route('clients-login', ['locale' => $locale]);
        }

        $sent = $this->twoFactorService->issueChallenge(
            $student,
            StudentTwoFactorService::PURPOSE_STEP_UP,
            $locale,
            true
        );

        $request->session()->put('students.two_factor.step_up_context', 'deactivate-submit');

        return redirect()
            ->route('students.2fa.challenge', ['locale' => $locale])
            ->with(
                'msg',
                $sent
                    ? __('students::clients/account.two_factor.deactivate_code_sent')
                    : __('students::clients/account.two_factor.code_already_sent', [
                        'seconds' => $this->twoFactorService->secondsUntilResend($student),
                    ])
            );
    }

    public function showChallenge(Request $request, string $locale)
    {
        $student = $this->resolveStudent($request);

        if (!$student) {
            return redirect()->route('clients-login', ['locale' => $locale]);
        }

        $purpose = $this->resolvePurpose($request, $student);

        if (!$purpose) {
            return $this->redirectAfterMissingPurpose($request, $locale);
        }

        $pageTitle = __('auth::clients/auth.two_factor.page_title');
        $purposeLabel = __($this->twoFactorService->purposeLabelKey($purpose));

        return view('auth::clients.two_factor', [
            'pageTitle' => $pageTitle,
            'student' => $student,
            'purpose' => $purpose,
            'purposeLabel' => $purposeLabel,
            'expiresAt' => $student->two_factor_email_code_expires_at,
            'resendAfter' => $this->twoFactorService->secondsUntilResend($student),
        ]);
    }

    public function verify(Request $request, string $locale)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $student = $this->resolveStudent($request);

        if (!$student) {
            return $this->errorResponse(
                $request,
                __('students::clients/account.two_factor.challenge_not_found'),
                $locale
            );
        }

        $purpose = $this->resolvePurpose($request, $student);

        if (!$purpose) {
            return $this->errorResponse(
                $request,
                __('students::clients/account.two_factor.challenge_not_found'),
                $locale
            );
        }

        $result = $this->twoFactorService->verifyChallenge($student, (string) $request->code);

        if ($result !== 'verified') {
            $errorMessage = match ($result) {
                'expired' => __('students::clients/account.two_factor.code_expired'),
                'invalid' => __('students::clients/account.two_factor.code_invalid'),
                default => __('students::clients/account.two_factor.challenge_not_found'),
            };

            return $this->errorResponse(
                $request,
                $errorMessage,
                $locale,
                ['code' => [$errorMessage]]
            );
        }

        return $this->completeVerifiedPurpose($request, $student, $purpose, $locale);
    }

    public function resend(Request $request, string $locale)
    {
        $student = $this->resolveStudent($request);

        if (!$student) {
            return $this->errorResponse(
                $request,
                __('students::clients/account.two_factor.challenge_not_found'),
                $locale
            );
        }

        $purpose = $this->resolvePurpose($request, $student);

        if (!$purpose) {
            return $this->errorResponse(
                $request,
                __('students::clients/account.two_factor.challenge_not_found'),
                $locale
            );
        }

        if (!$this->twoFactorService->canResend($student)) {
            return $this->errorResponse(
                $request,
                __('students::clients/account.two_factor.code_already_sent', [
                    'seconds' => $this->twoFactorService->secondsUntilResend($student),
                ]),
                $locale
            );
        }

        $this->twoFactorService->issueChallenge($student, $purpose, $locale, true);

        return $this->successResponse(
            $request,
            __('students::clients/account.two_factor.code_resent'),
            route('students.2fa.challenge', ['locale' => $locale])
        );
    }

    protected function completeVerifiedPurpose(Request $request, Student $student, string $purpose, string $locale)
    {
        $this->twoFactorService->clearChallenge($student);

        switch ($purpose) {
            case StudentTwoFactorService::PURPOSE_ENABLE:
                $student->forceFill([
                    'two_factor_email_enabled' => true,
                    'two_factor_email_enabled_at' => now(),
                ])->save();

                $this->twoFactorService->markRecentVerification($request);

                Mail::to($student->email)
                    ->locale($locale)
                    ->queue(new StudentTwoFactorStatusMail($student, 'enabled', $locale));

                return $this->successResponse(
                    $request,
                    __('students::clients/account.two_factor.enabled_success'),
                    route('students.account.profile', ['locale' => $locale])
                );

            case StudentTwoFactorService::PURPOSE_DISABLE:
                $student->forceFill([
                    'two_factor_email_enabled' => false,
                    'two_factor_email_enabled_at' => null,
                ])->save();

                $this->twoFactorService->forgetRecentVerification($request);

                Mail::to($student->email)
                    ->locale($locale)
                    ->queue(new StudentTwoFactorStatusMail($student, 'disabled', $locale));

                return $this->successResponse(
                    $request,
                    __('students::clients/account.two_factor.disabled_success'),
                    route('students.account.profile', ['locale' => $locale])
                );

            case StudentTwoFactorService::PURPOSE_STEP_UP:
                $stepUpContext = $request->session()->pull('students.two_factor.step_up_context');

                $this->twoFactorService->markRecentVerification($request);

                if (in_array($stepUpContext, ['change-password-page', 'deactivate-page'], true)) {
                    $request->session()->put(
                        "students.two_factor.{$stepUpContext}_until",
                        now()->addMinutes(5)->timestamp
                    );
                }

                if ($stepUpContext === 'deactivate-submit') {
                    $student->forceFill([
                        'email_verified_at' => null,
                        'two_factor_email_enabled' => false,
                        'two_factor_email_enabled_at' => null,
                    ])->save();

                    $this->twoFactorService->forgetRecentVerification($request);
                    $request->session()->put('students.deactivation.success_logout', true);

                    Mail::to($student->email)
                        ->locale($locale)
                        ->queue(new AccountDeactivatedMail($student, $locale));

                    activity_log(
                        'account_deactivated',
                        $student,
                        ['email' => $student->email],
                        'student_security',
                        __('students::clients/account.activity_log.account_deactivated_desc')
                    );

                    return $this->successResponse(
                        $request,
                        __('students::clients/account.profile.deactivate_success'),
                        route('students.account.deactivate-success', ['locale' => $locale])
                    );
                }

                $redirect = $request->session()->pull(
                    'students.two_factor.intended',
                    route('students.account.profile', ['locale' => $locale])
                );

                return $this->successResponse(
                    $request,
                    __('students::clients/account.two_factor.reauth_success'),
                    $redirect
                );

            default:
                $remember = (bool) $request->session()->pull('students.two_factor.pending_remember', false);
                $request->session()->forget('students.two_factor.pending_login_id');
                $intended = $request->session()->pull('url.intended', route('home', ['locale' => $locale]));

                Auth::guard('students')->login($student, $remember);
                $request->session()->regenerate();
                $this->twoFactorService->markRecentVerification($request);
                $this->twoFactorService->handleSuccessfulLogin($student, $request, $locale);

                return $this->successResponse(
                    $request,
                    __('students::clients/account.two_factor.login_success'),
                    $intended
                );
        }
    }

    protected function resolveStudent(Request $request): ?Student
    {
        if ($pendingLoginId = $request->session()->get('students.two_factor.pending_login_id')) {
            return Student::query()->find($pendingLoginId);
        }

        return Auth::guard('students')->user();
    }

    protected function resolvePurpose(Request $request, Student $student): ?string
    {
        if ($request->session()->has('students.two_factor.pending_login_id')) {
            return StudentTwoFactorService::PURPOSE_LOGIN;
        }

        return $student->two_factor_email_purpose;
    }

    protected function redirectAfterMissingPurpose(Request $request, string $locale)
    {
        if ($request->session()->has('students.two_factor.pending_login_id')) {
            $request->session()->forget([
                'students.two_factor.pending_login_id',
                'students.two_factor.pending_remember',
            ]);

            return redirect()->route('clients-login', ['locale' => $locale]);
        }

        return redirect()->route('students.account.profile', ['locale' => $locale]);
    }

    protected function successResponse(Request $request, string $message, string $redirect)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect)->with('msg', $message);
    }

    protected function errorResponse(
        Request $request,
        string $message,
        string $locale,
        array $errors = []
    ) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], 422);
        }

        return back()
            ->withErrors($errors ?: ['code' => $message])
            ->with('msg_danger', $message);
    }
}
