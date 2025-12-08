<?php

namespace App\Services;

use App\Models\ColorSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    public static function get()
    {
        // Ensure a logged-in user
        if (!Auth::check()) {
            return null;
        }

        // Detect team and location
        $teamId = Auth::user()->team_id ?? tenant('id');
        $locationId = Session::get('selectedLocation');

        if (!$locationId) {
            return null; // No location selected
        }

        // Create a unique cache key
        $cacheKey = "theme_settings_{$teamId}_{$locationId}";

        // Use a non-tagging cache store (file or array)
        return Cache::store('file')->remember($cacheKey, 3600, function () use ($teamId, $locationId) {
            return ColorSetting::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->first();
        });
    }

    public static function clearCache($teamId, $locationId)
    {
        $cacheKey = "theme_settings_{$teamId}_{$locationId}";
        Cache::store('file')->forget($cacheKey);
    }
}
