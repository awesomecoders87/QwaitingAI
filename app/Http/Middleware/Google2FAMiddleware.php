<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Addon;

class Google2FAMiddleware
{
     public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $addon = Addon::where('team_id', Auth::user()->team_id)
                        ->where('user_id', Auth::id())
                        ->first();

            // Check if 2FA is enabled and not yet verified
            if ($addon && $addon->google_auth_enabled  && !session()->has('verify_otp') &&  !$request->routeIs('verify.otp')) {
                return redirect()->route('verify.otp');
             
            }
          
        }

        return $next($request);
    }
}