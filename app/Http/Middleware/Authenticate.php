<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     */
    protected function authenticate($request, array $guards)
    {
        if (in_array('students', $guards)) {
            $isAdmin = auth('web')->check() && auth('web')->user()->hasPermission('dashboard.view');
            $isImpersonating = session()->has('admin_impersonator');

            if ($isAdmin || $isImpersonating) {
                return;
            }
        }

        parent::authenticate($request, $guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */

    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request, !in_array('students', $guards)),
        );
    }

    protected function redirectTo(Request $request, $isAdmin = true)
    {
        if (!$request->expectsJson()) {
            if ($request->is('teacher') || $request->is('teacher/*') || $request->is('*/teacher/*') || $request->routeIs('teacher.dashboard.*')) {
                return route('teacher.auth.login', ['locale' => app()->getLocale()]);
            }

            if (!$isAdmin) {
                return route('clients-login',['locale' => app()->getLocale()]);
            }
        }
        return route('login');
    }
}
