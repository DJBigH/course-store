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

        // Kiểm tra xem IP có nằm trong blacklist không
        $isBlocked = IpBlacklist::where('ip_address', $ip)->exists();

        if ($isBlocked) {
            return response()->view('errors.403_firewall', ['ip' => $ip], 403);
        }

        return $next($request);
    }
}
