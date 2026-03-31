<?php

namespace Modules\Auth\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Support\ClientMailThrottle;
use App\Support\SystemMailManager;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    public function __construct(
        protected ClientMailThrottle $mailThrottle,
        protected SystemMailManager $systemMailManager
    )
    {
    }

    public function index(Request $request, $locale)
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home', ['locale' => app()->getLocale()]);
        }
        $pageTitle = __('auth::clients/auth.verify.page_title');
        return view('auth::clients.verify', compact('pageTitle'));
    }

    public function resend(Request $request, $locale)
    {
        if (!$this->systemMailManager->isEnabled() || !$this->systemMailManager->isConfigured()) {
            $message = __('auth::clients/messages.mail_disabled');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'resent' => false,
                ], 503);
            }

            return back()->with('msg_danger', $message);
        }

        $throttle = config('mail.throttle.verify_resend');
        $throttleKey = $this->mailThrottle->key('verify-resend', [
            $request->ip(),
            $request->user()?->getAuthIdentifier(),
            $request->user()?->email,
        ]);

        if ($this->mailThrottle->tooManyAttempts($throttleKey, (int) $throttle['max_attempts'])) {
            $message = __('auth::clients/messages.mail_throttled', [
                'seconds' => $this->mailThrottle->availableIn($throttleKey),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'resent' => false,
                ], 429);
            }

            return back()->with('msg_danger', $message);
        }

        $this->mailThrottle->hit($throttleKey, (int) $throttle['decay_seconds']);
        $request->user()->sendEmailVerificationNotification();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('auth::clients/email.verify.resend.success'),
                'resent' => true,
            ]);
        }

        return back()->with('resent', true)->with('msg', __('auth::clients/email.verify.resend.success'));
    }
}
