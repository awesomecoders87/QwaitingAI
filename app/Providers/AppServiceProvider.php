<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Location;
use App\Observers\LocationObserver;
use App\Models\Category;
use App\Observers\CategoryObserver;
use App\Observers\TenantObserver;
use App\Models\User;
use App\Models\Tenant;
use App\Observers\UserObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use App\Models\SiteDetail;
use App\Models\Domain;
use Illuminate\Support\Facades\View;
use App\Services\ThemeService;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

  

    public function boot(): void
    {
        // RateLimiter::for('api', function (Request $request) {

            // return  Limit::perSecond(2)->by($request->ip());
            // $path = $request->path();

            // // Exclude specific endpoints from rate limiting
            // if (in_array($path, ['api/login', 'api/selected-location'])) {
            //     return Limit::none();
            // }

            // $teamId = $request->header('X-Team-ID') ?? $request->user()?->team_id;
            // $locationId = $request->header('X-Location-ID');

            // // Return early if required headers are missing
            // if (!$teamId || !$locationId) {
            //     return [
            //         Limit::none()->by($request->ip())->response(function () {
            //             return response()->json([
            //                 'message' => 'Missing required headers: X-Team-ID or X-Location-ID',
            //             ], 400);
            //         })
            //     ];
            // }

            // // Fetch rate limit settings from DB or cache
            // $limits = Cache::remember("rate_limits_{$teamId}_{$locationId}", 60, function () use ($teamId, $locationId) {
            //     return DB::table('site_details')
            //         ->where('team_id', $teamId)
            //         ->where('location_id', $locationId)
            //         ->select([
            //             'rate_limit_sec as rate_limit_per_second',
            //             'rate_limit_minute as rate_limit_per_minute',
            //             'rate_limit_day as rate_limit_per_day',
            //             'rate_limit_by',
            //             'concurrency_limit',
            //         ])->first();
            // });

            // // Default rate limit if not found
            // if (!$limits) {
            //     return [
            //         Limit::perMinute(60)->by($request->ip())->response(function () {
            //             return response()->json([
            //                 'message' => 'Too many requests. Please try again later.',
            //                 'status' => 429,
            //             ], 429);
            //         })
            //     ];
            // }

            // $rateBy = $limits->rate_limit_by ?? 'ip';

            // $identifier = match ($rateBy) {
            //     'user' => $request->user()?->id ?? $request->ip(),
            //     'email' => $request->user()?->email ?? $request->ip(),
            //     default => $request->ip(),
            // };

            // $limitsArray = [];

            // // Per second limit
            // if ($limits->rate_limit_per_second) {
            //     $limitsArray[] = Limit::perSecond((int) $limits->rate_limit_per_second)->by("{$identifier}_sec");
            // }

            // // Per minute limit
            // if ($limits->rate_limit_per_minute) {
            //     $limitsArray[] = Limit::perMinute((int) $limits->rate_limit_per_minute)->by("{$identifier}_min");
            // }

            // // Per day limit
            // if ($limits->rate_limit_per_day) {
            //     $limitsArray[] = Limit::perDay((int) $limits->rate_limit_per_day)->by("{$identifier}_day");
            // }

            // // Concurrency limit
            // if (!empty($limits->concurrency_limit)) {
            //     $concurrency = (int) $limits->concurrency_limit;
            //     $key = "api:concurrent_limit:{$identifier}";

            //     $limitsArray[] = Limit::none()->by("{$identifier}_concurrency")->response(function () use ($key, $concurrency) {
            //         $current = Cache::increment($key);
            //         if ($current > $concurrency) {
            //             Cache::decrement($key);
            //             return response()->json([
            //                 'message' => 'Too many concurrent requests.',
            //                 'status' => 429,
            //             ], 429);
            //         }

            //         Cache::decrement($key);
            //         register_shutdown_function(function () use ($key) {
            //         });

            //         return null;
            //     });
            // }

            // // Apply consistent 429 response for all rate limits
            // foreach ($limitsArray as &$limit) {
            //     if (!$limit->responseCallback) {
            //         $limit = $limit->response(function () {
            //             return response()->json([
            //                 'message' => 'Too many requests. Please try again later.',
            //                 'status' => 429,
            //             ], 429);
            //         });
            //     }
            // }

            // return $limitsArray;
        // });

        if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }


        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');
        ini_set('session.gc_maxlifetime', 14400);

       if (Session::has('selectedLocation') ) {
        if(!Session::has('timezone_set')){
            $locationId = Session::get('selectedLocation');

            // Get timezone from sitedetails table
            $siteDetails = SiteDetail::where('location_id', $locationId)->first();

            if ($siteDetails && $siteDetails->select_timezone) {
                $timezone = $siteDetails->select_timezone;

                // Save timezone to session
                Session::put('timezone_set', $timezone);

                // Set timezone in config and PHP
                Config::set('app.timezone', $timezone);
                date_default_timezone_set($timezone);
                Carbon::setTimezone($timezone);
            }
        } elseif (Session::has('timezone_set')) {
            $timezone = Session::get('timezone_set');

            // Apply stored timezone
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
            Carbon::setTimezone($timezone);
        }
    }
        // Register model observers
        Location::observe(LocationObserver::class);
        Category::observe(CategoryObserver::class);
        Tenant::observe(TenantObserver::class);
        User::observe(UserObserver::class);

        //Theme color setting
        View::composer('*', function ($view) {
            $view->with('theme', ThemeService::get());
        });


    }

}
