<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
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
            if (!$isAdmin) {
                return route('clients-login',['locale' => app()->getLocale()]);
            }
        }
        return route('login');
    }
}
