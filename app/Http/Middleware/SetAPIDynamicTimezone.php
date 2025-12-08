<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use App\Models\SiteDetail;

class SetAPIDynamicTimezone
{
    public function handle($request, Closure $next)
    {
        $teamId = $request->header('X-Team-ID') ?? tenant('id'); 
        $locationId = $request->header('X-Location-ID') ?? null; 
                                      
          $timezone = SiteDetail::where('team_id',$teamId)->where('location_id',$locationId)->value('select_timezone') ?? 'UTC';

        if (in_array($timezone, timezone_identifiers_list())) {
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
