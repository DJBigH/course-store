<?php

namespace Modules\Auth\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::ADMIN;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        $pageTitle = 'Đăng nhập quản trị';

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
        if ($user->canAccessAdmin()) {
            return null;
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            $this->username() => ['Tài khoản này không có quyền truy cập trang quản trị.'],
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => ['Email, mật khẩu hoặc quyền quản trị không hợp lệ.'],
        ]);
    }

    protected function loggedOut(Request $request)
    {
        return redirect($this->redirectTo);
    }
}
