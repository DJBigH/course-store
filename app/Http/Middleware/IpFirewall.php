<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Settings\src\Models\IpBlacklist;
use Symfony\Component\HttpFoundation\Response;

class IpFirewall
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $blacklist = \Illuminate\Support\Facades\Cache::remember('ip_blacklist', 3600, function () {
            return IpBlacklist::pluck('ip_address')->toArray();
        });

        if (in_array($ip, $blacklist)) {
            return response()->view('errors.403_firewall', ['ip' => $ip], 403);
        }

        return $next($request);
    }
}
