<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTeacherNotLocked
{
    /**
     * Kiểm tra xem tài khoản giáo viên có bị khóa hay không.
     * Nếu bị khóa, chuyển hướng đến trang thông báo bị khóa.
     */
    public function handle(Request $request, Closure $next)
    {
        $student = auth('students')->user();

        // Nếu chưa đăng nhập hoặc không phải giáo viên thì để EnsureActiveTeacher xử lý
        if (!$student || !$student->teacher) {
            return $next($request);
        }

        // Nếu bị khóa
        if ($student->teacher->is_locked) {
            // Cho phép truy cập route hiển thị thông báo khóa và các route tài chính cốt lõi
            if ($request->routeIs('teacher.dashboard.locked') || 
                $request->routeIs('teacher.dashboard.earnings') || 
                $request->routeIs('teacher.dashboard.payouts*')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tài khoản giáo viên của bạn đã bị khóa.',
                    'reason'  => $student->teacher->lock_reason
                ], 403);
            }

            return redirect()->route('teacher.dashboard.locked');
        }

        return $next($request);
    }
}
