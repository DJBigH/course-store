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
        if ($request->is('admin') || $request->is('admin/*')) {
            throw new AuthenticationException(
                'Unauthenticated.',
                $guards,
                $this->redirectTo($request),
            );
        }
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
