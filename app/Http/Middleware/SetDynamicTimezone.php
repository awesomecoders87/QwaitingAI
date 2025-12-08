<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use App\Models\SiteDetail;

class SetDynamicTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = 'UTC';
       // Check if 'location' is set in session
         if (Session::has('selectedLocation')) {
            $locationId = Session::get('selectedLocation');
            $siteDetails = SiteDetail::where('location_id', $locationId)->first();
            if ($siteDetails && $siteDetails->select_timezone) {
                $timezone = $siteDetails->select_timezone;

                Session::put('timezone_set', $timezone);
                
            }
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
           
        }

        return $next($request);
    }
}
