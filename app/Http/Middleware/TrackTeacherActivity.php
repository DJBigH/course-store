<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrackTeacherActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $student = auth('students')->user();
        $teacher = $student?->teacher;

        if ($teacher && $teacher->status === 'active') {
            $threshold = now()->subMinutes(15);

            if (
                !$teacher->last_active_at
                || $teacher->last_active_at->lt($threshold)
                || $teacher->inactive_teacher_notified_at
                || $teacher->inactive_admin_notified_at
            ) {
                $teacher->forceFill([
                    'last_active_at' => now(),
                    'inactive_teacher_notified_at' => null,
                    'inactive_admin_notified_at' => null,
                ])->save();
            }
        }

        return $response;
    }
}
