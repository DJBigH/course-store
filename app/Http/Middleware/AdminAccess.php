<?php

namespace App\Http\Middleware;

use App\Support\AdminSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function __construct(protected AdminSecurityService $securityService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isLocked') && $user->isLocked()) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Tai khoan nay da bi khoa va khong the truy cap trang quan tri.',
                ]);
        }

        if (!$user || !$user->canAccessAdmin()) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Tai khoan nay khong co quyen truy cap trang quan tri.',
                ]);
        }

        $this->securityService->touchCurrentSession($user, $request);

        return $next($request);
    }
}
