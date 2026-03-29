<?php

namespace Modules\Auth\src\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LimitDeviceLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('students')->check()) {

            $studentId = Auth::guard('students')->id();
            $maxDevices = (int) setting('max_devices', config('auth.max_devices', 1));

            $sessions = DB::table('sessions')
                ->where('user_id', $studentId)
                ->orderBy('last_activity', 'desc')
                ->get();

            if ($sessions->count() > $maxDevices) {

                // // 👉 CÁCH 1: Đá thiết bị cũ
                // $sessions
                //     ->slice($maxDevices)
                //     ->each(
                //         fn($s) =>
                //         DB::table('sessions')->where('id', $s->id)->delete()
                //     );

                // 👉 CÁCH 2: Chặn đăng nhập (thay cho đoạn trên)
                Auth::guard('students')->logout();
                abort(403, 'Tài khoản đã đăng nhập trên thiết bị khác');
            }
        }

        return $next($request);
    }
}
