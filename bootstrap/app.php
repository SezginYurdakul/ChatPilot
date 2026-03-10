<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Force JSON error responses for all API routes
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'unauthenticated',
                    'message' => $e->getMessage() ?: 'Unauthenticated.',
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => $e->getMessage() ?: 'The requested endpoint was not found.',
                ], 404);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => $e->getMessage() ?: 'Access denied.',
                ], 403);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'too_many_requests',
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }
        });

        // Generic HttpException (abort(403), abort(409), etc.)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = $e->getStatusCode();
                $error = match ($status) {
                    401 => 'unauthenticated',
                    403 => 'forbidden',
                    404 => 'not_found',
                    409 => 'conflict',
                    422 => 'validation_failed',
                    429 => 'too_many_requests',
                    default => $status >= 500 ? 'server_error' : 'http_error',
                };

                return response()->json([
                    'error' => $error,
                    'message' => $e->getMessage() ?: 'Request failed.',
                ], $status);
            }
        });

        // Catch-all: log 5xx errors and return clean JSON
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::error('Unhandled API exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'file' => $e->getFile().':'.$e->getLine(),
                ]);

                return response()->json([
                    'error' => 'server_error',
                    'message' => app()->isProduction()
                        ? 'An unexpected error occurred.'
                        : $e->getMessage(),
                ], 500);
            }
        });

    })->create();
