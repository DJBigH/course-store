<?php

namespace Modules\Auth\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\User\src\Models\User;

class TwoFactorController extends Controller
{
    public function __construct(protected AdminSecurityService $securityService)
    {
    }

    public function showChallenge(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        return view('auth::admin.two_factor', [
            'pageTitle' => 'Xác thực 2 lớp quản trị',
            'user' => $user,
            'expiresAt' => $user->two_factor_email_code_expires_at,
            'resendAfter' => $this->securityService->secondsUntilResend($user),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->resolveUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        $result = $this->securityService->verifyChallenge($user, (string) $request->code);

        if ($result !== 'verified') {
            $message = match ($result) {
                'expired' => 'Mã xác thực đã hết hạn.',
                'invalid' => 'Mã xác thực không chính xác.',
                default => 'Không tìm thấy yêu cầu xác thực.',
            };

            return back()->withErrors(['code' => $message])->with('msg_danger', $message);
        }

        $this->securityService->clearChallenge($user);

        $remember = (bool) $request->session()->pull('admin.two_factor.pending_remember', false);
        $request->session()->forget('admin.two_factor.pending_login_id');

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        $limitMessage = $this->securityService->finalizeSuccessfulLogin($user, $request);

        if ($limitMessage) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => $limitMessage]);
        }

        return redirect()->intended(route('admin.index'))->with('msg', 'Xác thực 2 lớp thành công.');
    }

    public function resend(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$this->securityService->canResend($user)) {
            return back()->with('msg_danger', 'Vui lòng đợi ' . $this->securityService->secondsUntilResend($user) . ' giây để gửi lại mã.');
        }

        $this->securityService->issueChallenge($user, app()->getLocale(), true);

        return back()->with('msg', 'Đã gửi lại mã xác thực qua email quản trị.');
    }

    protected function resolveUser(Request $request): ?User
    {
        $pendingLoginId = $request->session()->get('admin.two_factor.pending_login_id');

        return $pendingLoginId ? User::query()->find($pendingLoginId) : null;
    }
}
