<?php

namespace Modules\Auth\src\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\User\src\Models\User;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/login';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showResetForm($token = null)
    {
        $pageTitle = 'Đặt lại mật khẩu quản trị';

        return view('auth::admin.passwords.reset', [
            'pageTitle' => $pageTitle,
            'token' => $token,
            'email' => request()->email,
        ]);
    }

    protected function guard()
    {
        return Auth::guard('web');
    }

    protected function broker()
    {
        return app('auth.password')->broker('users');
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ];
    }

    protected function setUserPassword($user, $password)
    {
        if ($user instanceof User) {
            $user->password = bcrypt($password);

            return;
        }

        parent::setUserPassword($user, $password);
    }

    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        $this->logoutAllAdminSessions($user);

        event(new PasswordReset($user));
    }

    protected function logoutAllAdminSessions($user): void
    {
        if (!Schema::hasTable('sessions')) {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->getAuthIdentifier())
            ->delete();
    }

    protected function sendResetResponse($request, $response)
    {
        return redirect()
            ->route('login')
            ->with('msg', 'Đặt lại mật khẩu thành công. Tất cả phiên đăng nhập cũ đã được đăng xuất.');
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
            ]);
    }
}
