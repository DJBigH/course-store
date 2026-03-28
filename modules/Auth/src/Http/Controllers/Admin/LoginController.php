<?php

namespace Modules\Auth\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Support\AdminSecurityService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::ADMIN;

    public function __construct(protected AdminSecurityService $securityService)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        $pageTitle = 'Đăng nhập trang quản trị';

        return view('auth::admin.login', compact('pageTitle'));
    }

    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->get($this->username()),
            'password' => $request->get('password'),
        ];
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isLocked()) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['Tài khoản này đã bị khóa và không thể đăng nhập vào trang quản trị'],
            ]);
        }

        if (!$user->canAccessAdmin()) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['Tài khoản này không có quyền truy cập vào trang quản trị'],
            ]);
        }

        if ($user->two_factor_email_enabled) {
            Auth::guard('web')->logout();
            $request->session()->put('admin.two_factor.pending_login_id', $user->id);
            $request->session()->put('admin.two_factor.pending_remember', $request->boolean('remember'));

            $this->securityService->issueChallenge($user, app()->getLocale(), false);

            return redirect()->route('admin.2fa.challenge')
                ->with('msg', 'Đã gửi mã xác thực đăng nhập qua email quản trị');
        }

        $limitMessage = $this->securityService->finalizeSuccessfulLogin($user, $request);

        if ($limitMessage) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => [$limitMessage],
            ]);
        }

        return null;
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $this->securityService->forgetCurrentSession($user, $request);
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => ['Email hoặc mật khẩu không hợp lệ'],
        ]);
    }
}

