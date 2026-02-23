<?php

namespace Modules\Auth\src\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class VerifyController extends Controller
{
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
        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true)->with('msg', __('auth::clients/email.verify.resend.success'));
    }
}
