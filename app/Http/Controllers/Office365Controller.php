<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
Use App\Models\User;
Use App\Models\Role;
use App\Models\Addon;
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\OtpCode;
use App\Models\SiteDetail;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtp;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\Facades\Log;

class Office365Controller extends Controller
{
    public function redirect(Request $request)
    {

        $domain = Domain::where('team_id',tenant('id'))->first();
         $addon = Addon::where('team_id',tenant('id'))->first();
         if (isset($addon) && $addon->office_enabled == 1 && !empty($addon->office_client_id) && !empty($addon->office_secret) && !empty($addon->office_tenant_id) ){
        $clientId = $addon->office_client_id;
        $redirectUri = 'https://'.$domain->domain.'/office365/callback';
        $tenantId = $addon->office_tenant_id;
        $scopes = implode(' ', [
            'offline_access',
            'openid',
            'https://graph.microsoft.com/User.Read'

        ]);


        $state = base64_encode(tenant('id')); // optional back redirect


        $authUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => $scopes,
            'state' => $state,
        ]);
      $login_url_365 = str_replace('{{STATE}}',$redirectUri,$authUrl);

        return redirect($login_url_365);
    }
     return redirect()->route('tenant.login')->with('success','Office 365 Keys are missing');
    }

    public function callback(Request $request)
    {
        $teamId = tenant('id');
        $domain = Domain::where('team_id',$teamId)->first();
        $addon = Addon::where('team_id',tenant('id'))->first();
 $tenantId = $addon->office_tenant_id;
        $code = $request->get('code');
        $state = $request->get('state', '/');

        if (!$code) {
            return redirect('/login')->with('error', 'Authorization code missing.');
        }

        $tokenResponse = Http::asForm()->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'https://'.$domain->domain.'/office365/callback',
            'client_id' => $addon->office_client_id,
            'client_secret' => $addon->office_secret,
            'scope' => 'offline_access openid https://graph.microsoft.com/User.Read',
        ]);

        if (!$tokenResponse->successful()) {
            return response()->json(['error' => 'Unable to fetch access token', 'details' => $tokenResponse->body()], 500);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        // $userResponse = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . $accessToken,
        //     'Accept' => 'application/json',
        // ])->get('https://outlook.office.com/api/v2.0/Me');
        $userResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ])->get('https://graph.microsoft.com/v1.0/me');

        if (!$userResponse->successful()) {
            return response()->json(['error' => 'Failed to fetch user info', 'details' => $userResponse->body()], 500);
        }

    $graphUser = $userResponse->json();

           // Extract relevant fields
    $email = $graphUser['mail'] ?? $graphUser['userPrincipalName'] ?? null;
    $name = $graphUser['displayName'] ?? '';
    $username = $graphUser['givenName'] ?? ('user' . rand(1000, 9999));
    $phone = $graphUser['mobilePhone'] ?? '';


    // Check if user exists
    $existingUser = User::where('email', $email)->where('team_id', $teamId)->first();
// dd( [
//     $existingUser
// ]);
    if ($existingUser) {
        // Log in the user
        Auth::login($existingUser);
           // Regenerate session and redirect
    $request->session()->regenerate();
     if(Auth::check()){
          $user = Auth::user();
          User::where('id',Auth::id())->update([
            'is_login'=>1,
        ]);
    }

    if(Auth::check() && !empty(Auth::user()->locations)){

      if ($domain && $domain->enable_location_page != 1) {
        $location = Auth::user()->locations;
         Session::put('selectedLocation', $location[0]);
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
         ActivityLog::storeLog($user->team_id, Auth::id(),null, null, ActivityLog::LOGIN,  $locationId,ActivityLog::LOGIN,null,$user);
            // 2Fa Authentication


            $addon = Addon::where('team_id', Auth::user()->team_id)
                        ->where('location_id',  $locationId)
                        ->first();

            // Check if 2FA is enabled
            if ($addon && $addon->google_auth_enabled && !empty($user->email)) {
                   // ✅ Expire all previous OTPs
                OtpCode::where('user_id',  $user->id)
        ->where('used', false)
        ->update(['used' => true]);

                 $otp = rand(100000, 999999);
                  OtpCode::create([
                    'user_id' => $user->id,
                    'code' => $otp,
                    'expires_at' => now()->addMinutes(10),
                ]);

                Mail::to($user->email)->send(new SendOtp($otp));

                session(['otp_user_id' => $user->id]);
  session()->flash('success', 'An OTP has been sent to your registered email.');
                return redirect()->route('verify.otp');
            }
        }

        }


    }else{
        Auth::logout();
        return redirect('/login')->with('success', 'Admin has not verfiy your account yet.');
    }

    if(Auth::check() && empty(Auth::user()->locations)){
              return redirect()->route('tenant.profile');
    }
    return redirect()->route('tenant.dashboard');
    }else{


    // Create new user
    $newUser = User::create([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'username' => $username,
        'password' => Hash::make('Password@123'),
        'role_id' => 1, // set default if needed
        // 'unique_id' => '',
        // 'counter_id' => '',
        'locations' => [],
        // 'assign_counters' => [],
        'is_admin' => 1,
        'is_active' => 1,
        'show_next_button' => 1,
        'enable_desktop_notification' => 1,
        'enable_hold_queue' => 0,
        'team_id' => $teamId,
        'microsoft_email' => $graphUser['userPrincipalName'],
    ]);

     $role = Role::find(1);
        if ($role) {
            $newUser->assignRole($role); // must use role name
        }


    return redirect('/login')->with('success', 'Account created successfully. Admin will verfiy your account first.login creds Email '. $email.' and password = password@123');

        // You can now log in the user, or redirect them back with user data
        // return redirect($state . '?' . http_build_query(['email' => $user['EmailAddress'] ?? $user['UserPrincipalName'] ?? 'unknown']));
    }

    }
}
