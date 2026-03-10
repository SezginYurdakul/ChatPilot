<?php

use Illuminate\Auth\AuthenticationException;
use App\Services\AlertingService;
use App\Support\ApiErrorCatalog;
use App\Support\OperationalMetrics;
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
        $middleware->append(\App\Http\Middleware\AttachRequestContext::class);

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
                    'code' => ApiErrorCatalog::UNAUTHENTICATED,
                    'message' => $e->getMessage() ?: 'Unauthenticated.',
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'code' => ApiErrorCatalog::VALIDATION_FAILED,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'code' => ApiErrorCatalog::NOT_FOUND,
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'code' => ApiErrorCatalog::NOT_FOUND,
                    'message' => $e->getMessage() ?: 'The requested endpoint was not found.',
                ], 404);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'forbidden',
                    'code' => ApiErrorCatalog::FORBIDDEN,
                    'message' => $e->getMessage() ?: 'Access denied.',
                ], 403);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => 'too_many_requests',
                    'code' => ApiErrorCatalog::TOO_MANY_REQUESTS,
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
                    'code' => ApiErrorCatalog::fromErrorKey($error),
                    'message' => $e->getMessage() ?: 'Request failed.',
                ], $status);
            }
        });

        // Catch-all: log 5xx errors and return clean JSON
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $context = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'request_id' => (string) $request->attributes->get('request_id', ''),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => optional($request->user())->id,
                ];

                if (! app()->isProduction()) {
                    $context['file'] = $e->getFile().':'.$e->getLine();
                }

                Log::error('Unhandled API exception', $context);

                OperationalMetrics::increment('api_5xx');

                if (app()->bound('sentry')) {
                    app('sentry')->captureException($e);
                }

                app(AlertingService::class)->send('Unhandled API exception', [
                    'request_id' => $context['request_id'],
                    'exception' => $context['exception'],
                    'method' => $context['method'],
                    'url' => $context['url'],
                ]);

                return response()->json([
                    'error' => 'server_error',
                    'code' => ApiErrorCatalog::SERVER_ERROR,
                    'message' => app()->isProduction()
                        ? 'An unexpected error occurred.'
                        : $e->getMessage(),
                ], 500);
            }
        });

    })->create();
