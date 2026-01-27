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
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
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
            '2fa' => \App\Http\Middleware\Google2FAMiddleware::class,
            'location.exists' => \App\Http\Middleware\EnsureLocationExists::class,
            'check.qr.url' => \App\Http\Middleware\CheckQrCodeUrl::class,
            'strict.rate' => \App\Http\Middleware\StrictRateLimiter::class,
            // 'timezoneset' => \App\Http\Middleware\SetDynamicTimezone::class,
        ]);

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('RedirectGuestsTo called for URL: ' . $request->url());
            if ($request->is('superadmin') || $request->is('superadmin/*')) {
                \Illuminate\Support\Facades\Log::info('Redirecting to superadmin login');
                return route('superadmin.login');
            }
            return route('login');
            return route('login');
        });

        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('RedirectUsersTo called for URL: ' . $request->url());

            // If checking superadmin guard (or authenticated as superadmin)
            if (Auth::guard('superadmin')->check()) {
                \Illuminate\Support\Facades\Log::info('Authenticated as superadmin, redirecting to dashboard');
                return route('superadmin.dashboard');
            }

            // Default for web guard
            \Illuminate\Support\Facades\Log::info('Authenticated as web user, redirecting to dashboard');
            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
