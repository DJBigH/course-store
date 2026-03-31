<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();
        $permissionList = collect(explode(',', $permissions))
            ->map(fn($permission) => trim($permission))
            ->filter()
            ->values();

        if (!$user || $permissionList->isEmpty()) {
            abort(403, 'Bạn không có quyền truy cập tài nguyên này.');
        }

        $authorized = $permissionList->contains(fn($permission) => $user->hasPermission($permission));

        if (!$authorized) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return $next($request);
    }
}
