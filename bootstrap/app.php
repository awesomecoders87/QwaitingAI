<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetDynamicTimezone;
use App\Http\Middleware\SetAPIDynamicTimezone;
use App\Http\Middleware\SetLanguage;
use App\Http\Middleware\Google2FAMiddleware;
use App\Http\Middleware\VerifyCsrfToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Add global middleware
        $middleware->appendToGroup('web',SetDynamicTimezone::class);
        $middleware->appendToGroup('web',SetLanguage::class);
        $middleware->appendToGroup('web', VerifyCsrfToken::class);
        $middleware->append(SetAPIDynamicTimezone::class);
        // $middleware->appendToGroup('web',Google2FAMiddleware::class);

        $middleware->alias([
            '2fa' => \App\Http\Middleware\Google2FAMiddleware::class,
            'location.exists' => \App\Http\Middleware\EnsureLocationExists::class,
            'check.qr.url' => \App\Http\Middleware\CheckQrCodeUrl::class,
            'strict.rate' => \App\Http\Middleware\StrictRateLimiter::class,
            // 'timezoneset' => \App\Http\Middleware\SetDynamicTimezone::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
