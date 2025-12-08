<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\LanguageSetting;

class SetLanguage
{
    public function handle($request, Closure $next)
    {
        $teamId = tenant('id'); // adjust if needed
        $locationId = Session::get('selectedLocation');

        if(Session::has('app_locale')){
              App::setLocale(Session::get('app_locale'));
        }
         if(!Session::has('app_locale')){
        if ($teamId && $locationId) {
            $setting = LanguageSetting::where('team_id', $teamId)
                ->where('location_id', $locationId)
                ->first();

            if ($setting && $setting->enabled_language_settings && !empty($setting->default_language)) {
                App::setLocale($setting->default_language);
                Session::put('app_locale', $setting->default_language);
            }

        }
    }

        return $next($request);
    }
}