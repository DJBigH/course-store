<?php

namespace Modules\Auth\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Auth\src\Http\Requests\LoginRequest;
use Modules\Students\src\Models\Student;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:students', ['except' => 'logout']);
        // $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        $pageTitle = __('auth::clients/auth.login.page_title');
        return view('auth::clients.login', compact('pageTitle'));
    }

    public function login(LoginRequest $request)
    {
        $dataLogin = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (!Auth::guard('students')->attempt($dataLogin, $request->remember == 1)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('auth::messages.login.failure'),
                    'errors' => [
                        'email' => [__('auth::messages.login.failure')],
                    ],
                ], 422);
            }

            return back()->with('msg_danger', __('auth::messages.login.failure'));
        }

        $studentId  = Auth::guard('students')->id();
        $maxDevices = config('auth.max_devices', 1);

        $activeSessions = DB::table('sessions')
            ->where('user_id', $studentId)
            ->count();

        if ($activeSessions > $maxDevices) {

            Auth::guard('students')->logout();

            abort(403, 'Tài khoản đã đăng nhập trên thiết bị khác');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('auth::clients/auth.login.success_title'),
                'redirect' => route('home', ['locale' => app()->getLocale()]),
            ]);
        }

        return redirect()->route('home', ['locale' => app()->getLocale()]);
    }


    public function logout()
    {
        Auth::guard('students')->logout();
        return redirect()->route('home', ['locale' => app()->getLocale()]);
    }

    public function showFormForgot($locale)
    {
        $pageTitle = __('auth::clients/auth.forgot.page_title');
        return view('auth::clients.forgot', compact('pageTitle'));
    }

    public function handleSendForgotLink(Request $request, $locale)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('students')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('auth::messages.password.sent.success'),
                ]);
            }

            return back()->with('msg', __('auth::messages.password.sent.success'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('auth::messages.password.sent.failure'),
                'errors' => [
                    'email' => [__('auth::messages.password.sent.failure')],
                ],
            ], 422);
        }

        return back()->with('msg_danger', __('auth::messages.password.sent.failure'));
    }

    public function showFormReset($token,$locale)
    {
        $pageTitle = __('auth::clients/auth.reset.page_title');
        return view('auth::clients.reset', compact('pageTitle', 'token'));
    }

    public function updatePassword(Request $request,$locale)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        $status = Password::broker('students')->reset(
            $request->only('email', 'password', 'confirm_password', 'token'),
            function (Student $student, string $password) {
                $student->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $student->save();

                event(new PasswordReset($student));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('auth::messages.passwords.reset.success'),
                    'redirect' => route('clients-login', ['locale' => app()->getLocale()]),
                ]);
            }

            return redirect()->route('clients-login', ['locale' => app()->getLocale()])
                ->with('msg', __('auth::messages.passwords.reset.success'));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('auth::messages.passwords.reset.failure'),
                'errors' => [
                    'email' => [__('auth::messages.' . $status)],
                ],
            ], 422);
        }

        return back()->with('msg_danger', __('auth::messages.' . $status));
    }
}
