<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $seg = $request->segment(1);
        if (in_array($seg, ['vi', 'en', 'ko'], true)) {
            app()->setLocale($seg);
        }

        if ($e instanceof NotFoundHttpException) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return response()->view('errors.admin.404', [], 404);
            }
            if (($request->is('teacher') || $request->is('teacher/*') || $request->is('*/teacher/*')) && !$request->expectsJson()) {
                return response()->view('errors.teacher.404', [], 404);
            }
            return response()->view('errors.clients.404', [], 404);
        }

        if ($e instanceof BadRequestHttpException) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return response()->view('errors.admin.400', [], 400);
            }
            if (($request->is('teacher') || $request->is('teacher/*') || $request->is('*/teacher/*')) && !$request->expectsJson()) {
                return response()->view('errors.teacher.400', [], 400);
            }
            return response()->view('errors.clients.400', [], 400);
        }

        // Handle Teacher specific errors
        if (($request->is('teacher') || $request->is('teacher/*') || $request->is('*/teacher/*')) && !$request->expectsJson()) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return response()->view('errors.teacher.419', [], 419);
            }
            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                if (view()->exists("errors.teacher.{$status}")) {
                    return response()->view("errors.teacher.{$status}", [
                        'exception' => $e,
                        'message' => $e->getMessage()
                    ], $status);
                }
            }
            // For 500 errors in production
            if (!config('app.debug')) {
                return response()->view('errors.teacher.500', [], 500);
            }
        }

        if ($e instanceof TooManyRequestsHttpException) {
            $retryAfter = (int) ($e->getHeaders()['Retry-After'] ?? 60);
            $retryAfter = max(1, $retryAfter);

            if ($request->is('admin/forgot-password')) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => "Bạn thao tác quá nhanh. Vui lòng thử lại sau {$retryAfter} giây.",
                    ]);
            }

            if ($request->is('admin/reset-password')) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'password' => "Bạn thao tác quá nhanh. Vui lòng thử lại sau {$retryAfter} giây.",
                    ]);
            }
        }

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 403 && $request->is('admin/*')) {
            return response()->view('errors.admin.403', [
                'message' => $e->getMessage() ?: 'Bạn không có quyền truy cập khu vực này.',
            ], 403);
        }

        return parent::render($request, $e);
    }
}
