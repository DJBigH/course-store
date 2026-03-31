<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
class SetLocale
{
    // app/Http/Middleware/SetLocale.php
    public function handle($request, Closure $next)
    {
        $locale = $request->route('locale') ?? $request->session()->get('locale', 'vi');

        if (!in_array($locale, ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
            $locale = 'vi';
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        // ✅ quan trọng: tự động gắn locale cho route()
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
