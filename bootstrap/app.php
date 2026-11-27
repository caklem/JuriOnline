<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Catat 403 agar mudah menelusuri laporan "Unauthorized action." dari pengguna.
        $exceptions->render(function (\Throwable $e, $request) {
            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : null;

            if ($status === 403 && !app()->runningUnitTests()) {
                \Illuminate\Support\Facades\Log::warning('403 Forbidden', [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'user_id' => $request->user()?->id,
                    'role' => $request->user()?->roleLabel(),
                    'message' => $e->getMessage(),
                ]);
            }

            return null;
        });
    })->create();
