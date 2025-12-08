<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $status = $e->getStatusCode();

            // if ($status === 404) {
            //     Log::warning('Page not found: ' . $request->url());
            //     if ($request->expectsJson()) {
            //         return response()->json(['message' => 'Not Found'], 404);
            //     }
            //     return response()->view('errors.404', [], 404);
            // }

            if ($status === 419) {
                Log::warning('CSRF token expired: ' . $request->url());
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session Expired'], 419);
                }
                return response()->view('errors.419', [], 419);
            }

            // if ($status === 500) {
            //     Log::error('Server error: ' . $request->url());
            //     if ($request->expectsJson()) {
            //         return response()->json(['message' => 'Server Error'], 500);
            //     }
            //     return response()->view('errors.500', [], 500);
            // }
        });

        // Optional: handle any other uncaught exceptions
        $this->reportable(function (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
        });
    }
}
