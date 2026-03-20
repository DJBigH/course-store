<?php

namespace App\Http\Middleware;

use App\Support\StudentTwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireStudentTwoFactorFresh
{
    public function __construct(protected StudentTwoFactorService $twoFactorService)
    {
    }

    public function handle(Request $request, Closure $next, string $mode = 'default'): Response
    {
        $student = Auth::guard('students')->user();

        if (!$student || !$student->two_factor_email_enabled) {
            return $next($request);
        }

        if (in_array($mode, ['change-password-page', 'deactivate-page'], true)) {
            $allowUntil = (int) $request->session()->get("students.two_factor.{$mode}_until", 0);

            if ($allowUntil > now()->timestamp) {
                return $next($request);
            }
        } elseif ($this->twoFactorService->hasFreshVerification($request)) {
            return $next($request);
        }

        $locale = app()->getLocale();
        $this->twoFactorService->issueChallenge(
            $student,
            StudentTwoFactorService::PURPOSE_STEP_UP,
            $locale,
            in_array($mode, ['change-password-page', 'deactivate-page'], true)
        );
        $request->session()->put('students.two_factor.intended', $request->fullUrl());
        $request->session()->put('students.two_factor.step_up_context', $mode);

        $redirectUrl = route('students.2fa.challenge', ['locale' => $locale]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('students::clients/account.two_factor.reauth_required'),
                'redirect' => $redirectUrl,
            ], 423);
        }

        return redirect($redirectUrl)->with('msg', __('students::clients/account.two_factor.reauth_required'));
    }
}
