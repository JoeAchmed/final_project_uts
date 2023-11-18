<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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

    // handle authentication
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['error' => 'Not Unauthenticated'], 401);
    }

    public function render($request, Throwable $exception)
    {
        // handle if API is not exist
        if ($exception instanceof NotFoundHttpException) {
            return response()->json(['message' => 'API or route not found'], 404);
        }

        // handle if there's not suitable method for the routes
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['message' => 'Method not allowed'], 405);
        }

        return parent::render($request, $exception);
    }
}
