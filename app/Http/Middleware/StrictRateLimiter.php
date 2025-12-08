<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StrictRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $teamId = $request->header('X-Team-ID') ?? $request->user()?->team_id;
        $locationId = $request->header('X-Location-ID');
        $ip = $request->ip();
    
        if (!$teamId || !$locationId) {
            return response()->json(['message' => 'Missing headers'], 400);
        }
    
        $limits = Cache::remember("rate_limits_{$teamId}_{$locationId}", 60, function () use ($teamId, $locationId) {
            return DB::table('site_details')
                ->where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->select([
                    'rate_limit_sec as perSecond',
                    'rate_limit_minute as perMinute',
                    'rate_limit_day as perDay',
                    'concurrency_limit',
                ])->first();
        });
    
        $identifier = $ip;
        $now = now();
    

        if ($limits->concurrency_limit) {
            $key = "concurrency:{$identifier}";
        
            // Ensure the key exists with a default TTL (auto-expire if a request crashes)
            Cache::add($key, 0, 60); // TTL = 60 seconds
        
            // Try to increment
            $current = Cache::increment($key);
        
            Log::info('Concurrency Check', [
                'key' => $key,
                'current' => $current,
                'limit' => $limits->concurrency_limit,
                'time' => $now->toDateTimeString(),
            ]);
        
            if ($current > $limits->concurrency_limit) {
                // Revert increment immediately
                Cache::decrement($key);
                return response()->json(['message' => 'Too many concurrent requests'], 429);
            }
        
            // Use a safer method to ensure decrement after request is completed or fails
           
        }
        // ⏱ Per-second limit (fixed)
        if ($limits->perSecond) {
            $key = "rate:sec:{$identifier}";
            Cache::add($key, 0, 1); // only adds if it doesn't exist, TTL = 1 second
            $current = Cache::increment($key);
    
            Log::info('Per-second check', [
                'key' => $key,
                'current' => $current,
                'limit' => $limits->perSecond,
                'time' => $now->toDateTimeString(),
            ]);
    
            if ($current > $limits->perSecond) {
                return response()->json(['message' => 'Too many requests/sec'], 429);
            }
        }
    
        // ⏱ Per-minute limit
        if ($limits->perMinute) {
            $key = "rate:min:{$identifier}";
            Cache::add($key, 0, 60); // TTL = 60 seconds

            $current = Cache::increment($key);

            Log::info('Per-minute check', [
                'key' => $key,
                'current' => $current,
                'limit' => $limits->perMinute,
                'time' => $now->toDateTimeString(),
            ]);
    
    
            if ($current > $limits->perMinute) {
                return response()->json(['message' => 'Too many requests/min'], 429);
            }
        }
    
        // ⏱ Per-day limit
        if ($limits->perDay) {
            $key = "rate:day:{$identifier}";
            Cache::add($key, 0, 86400); // TTL = 24 hours
            $current = Cache::increment($key);
    Log::info('Per-day check', [
                'key' => $key,
                'current' => $current,
                'limit' => $limits->perDay,
                'time' => $now->toDateTimeString(),
            ]);
            if ($current > $limits->perDay) {
                Cache::decrement($key);
                return response()->json(['message' => 'Too many requests/day'], 429);
            }
            app()->terminating(function () use ($key) {
             
                Cache::decrement($key);
            });
        }
    
        return $next($request);
    }
}
