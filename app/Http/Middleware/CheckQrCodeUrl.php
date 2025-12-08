<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\GenerateQrCode;
class CheckQrCodeUrl
{
   
    public function handle(Request $request, Closure $next): Response
    {   
       
        $currentUrl = url($request->path());

       // Remove scheme (http:// or https://)
        $normalizedUrl = 'https://'.preg_replace('/^https?:\/\//', '', $currentUrl);
        $teamId = tenant('id');
 
        $qrcode = GenerateQrCode::where('team_id', $teamId)
        ->where('url', 'like', "%$normalizedUrl")
        ->first();

       if(!isset($qrcode)){
       
           abort(404);
       }
  
        $domain = $request->getSchemeAndHttpHost();

        // $defaultUrl = $domain . '/mobile/queue';
      
         $storedUrl = $qrcode->url;
        
        if ($normalizedUrl != $storedUrl) {
            abort(404);
        }
        return $next($request);
    }

}
