<?php

namespace Modules\Auth\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {
        $pageTitle = 'Quên mật khẩu quản trị';

        return view('auth::admin.passwords.email', compact('pageTitle'));
    }

    protected function broker()
    {
        return app('auth.password')->broker('users');
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with(
            'status',
            'Hệ thống đã gửi email đặt lại mật khẩu. Vui lòng kiểm tra hộp thư của bạn.'
        );
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Email quản trị không hợp lệ hoặc không tồn tại trong hệ thống.',
            ]);
    }
}
