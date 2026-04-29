<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Settings\src\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

class SmartMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $isMaintenance = ($settings['maintenance_mode'] ?? '0') === '1';

        if ($isMaintenance) {
            $ip = $request->ip();
            $whitelist = explode(',', $settings['maintenance_whitelist_ips'] ?? '');
            $whitelist = array_map('trim', $whitelist);

            // 1. Cho phép IP trong danh sách Whitelist
            if (in_array($ip, $whitelist, true)) {
                return $next($request);
            }

            // 2. Cho phép Quản trị viên (Super Admin)
            if (auth()->check() && method_exists(auth()->user(), 'canAccessAdmin') && auth()->user()->canAccessAdmin()) {
                return $next($request);
            }

            // Kiểm tra phân vùng route
            if ($request->is('teacher/*') || $request->is('teacher')) {
                $msg = $settings['maintenance_message_teacher'] ?? 'Hệ thống giảng viên đang được nâng cấp.';
                return response()->view('teacher::errors.maintenance', ['message' => $msg], 503);
            }

            $msg = $settings['maintenance_message_' . app()->getLocale()] ?? $settings['maintenance_message'] ?? 'Hệ thống đang bảo trì định kỳ. Vui lòng quay lại sau.';
            return response()->view('errors.maintenance', ['message' => $msg], 503);
        }

        return $next($request);
    }
}
