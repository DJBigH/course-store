<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $student = auth('students')->user();

        if (!$student) {
            return redirect()->route('teacher.auth.login', ['locale' => app()->getLocale()]);
        }

        if ($student->teacher?->status === 'active') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Teacher access required.');
        }

        return redirect()->route('teacher.account.status', ['locale' => app()->getLocale()])
            ->with('msg_danger', __('teacher::auth.access.not_teacher'));
    }
}
