<?php

use App\Http\Middleware\AuthnCheck;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append(AuthnCheck::class);
        // $middleware->append(\App\Http\Middleware\SessionDebugMiddleware::class);
        // $middleware->prepend(\App\Http\Middleware\EncryptCookies::class);
        
        // Register middleware aliases
        $middleware->alias([
            'menu.access' => \App\Http\Middleware\CheckMenuAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
