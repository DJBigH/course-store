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

        try {
            $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            if ($isEnabled === '1' && $botToken && $chatId) {
                $email = $user->email;
                $ip = $request->ip();
                $ua = $request->userAgent();

                $text = "🔑 <b>[ĐĂNG NHẬP ADMIN THÀNH CÔNG]</b>\n\n";
                $text .= "👤 <b>Tài khoản:</b> <code>{$email}</code>\n";
                $text .= "🌐 <b>Địa chỉ IP:</b> <code>{$ip}</code>\n";
                $text .= "🖥️ <b>Thiết bị:</b> {$ua}\n";
                $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                ]);
            }
        } catch (\Exception $e) {
            // Fail silently
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
        try {
            $isEnabled = \Modules\Settings\src\Models\Setting::where('key', 'telegram_bot_enabled')->value('value');
            $botToken = config('services.telegram.bot_token');
            $chatId = config('services.telegram.chat_id');

            if ($isEnabled === '1' && $botToken && $chatId) {
                $email = $request->input($this->username());
                $ip = $request->ip();

                $text = "⚠️ <b>[CẢNH BÁO ĐĂNG NHẬP ADMIN THẤT BẠI]</b>\n\n";
                $text .= "🔒 <b>Hành vi:</b> Thử truy cập trang Quản trị\n";
                $text .= "✉️ <b>Email thử:</b> <code>{$email}</code>\n";
                $text .= "🌐 <b>Địa chỉ IP:</b> <code>{$ip}</code>\n";
                $text .= "⏱️ <b>Thời gian:</b> " . now()->format('H:i:s d/m/Y');

                \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                ]);
            }
        } catch (\Exception $e) {
            // Fail silently
        }

        throw ValidationException::withMessages([
            $this->username() => ['Email hoặc mật khẩu không hợp lệ'],
        ]);
    }
}

