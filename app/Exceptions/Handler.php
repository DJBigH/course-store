<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
            if ($request->is('admin/*')) {
                return response()->view('errors.admin.404', [], 404);
            }
            return response()->view('errors.clients.404', [], 404);
        }

        if ($e instanceof BadRequestHttpException) {
            if ($request->is('admin/*')) {
                return response()->view('errors.admin.400', [], 400);
            }
            return response()->view('errors.clients.400', [], 400);
        }

        return parent::render($request, $e);
    }
}
