<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            // ❗ NO activar para Breeze / Web login
            // EnsureFrontendRequestsAreStateful::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | 422 — Validation errors
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 403 — Authorization errors
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Forbidden',
                ], 403);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 404 — Model not found
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found',
                ], 404);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 404 — Route not found
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Endpoint not found',
                ], 404);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 500 — Fallback (Production-safe)
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => app()->isProduction()
                        ? 'Internal server error'
                        : $e->getMessage(),
                ], 500);
            }
        });
    })

    ->create();
