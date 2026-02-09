<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    // app/Http/Middleware/SetLocale.php
    public function handle($request, Closure $next)
    {
        $locale = $request->route('locale');

        if (! in_array($locale, ['vi', 'en'])) {
            $locale = config('app.fallback_locale', 'vi');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
