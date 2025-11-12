<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use KeycloakGuard\Exceptions\TokenException;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->trustProxies(at: []);
        $middleware->append(\App\Http\Middleware\TrustProxies::class);
        $middleware->appendToGroup('web', [
            \Laravel\Jetstream\Http\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        });
        if (app()->bound('sentry') and config('app.env') === 'production') {
            Integration::handles($exceptions);
        }
    })->create();
