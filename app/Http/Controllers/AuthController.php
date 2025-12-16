<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use App\Models\Addon;
use App\Models\OtpCode;
use App\Models\SiteDetail;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtp;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;
use App\Models\Counter;
use App\Models\Category;
use App\Models\Queue;
use App\Models\CustomSlot;
use App\Models\AccountSetting;
use App\Models\FormField;
use App\Models\GenerateQrCode;
use App\Models\Domain;
use App\Models\NotificationTemplate;
use App\Models\MessageTemplate;
use App\Models\WhatsappTemplate;
use App\Models\LanguageSetting;
use Illuminate\Support\Facades\Log;




class AuthController extends Controller
{
    public function login(){
        $addon = Addon::where('team_id',tenant('id'))->first();
        return view('tenant.auth.login',compact('addon'));
    }



 

public function loginstore(Request $request)
{
    // Validate input (email or username required, along with password) 
    $request->validate([
        'login' => 'required',
        'password' => 'required|min:6'
    ], [
        'login.required' => 'The email or username field is required.',
        'password.required' => 'The password field is required.',
        'password.min' => 'The password must be at least 6 characters.'
    ]);

    session()->forget('verify_otp');

    // Check if login input is an email or username
    $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Prepare credentials for authentication
    $credentials = [
        $loginType => $request->login,
        'password' => $request->password,
        'team_id' => tenant('id')
    ];

    $remember = $request->has('remember'); // Check if 'remember' checkbox is checked

    // Attempt authentication
    if (!auth()->attempt($credentials, $remember)) {
        return redirect()->back()->withErrors([
            'login' => 'The provided credentials do not match our records.'
        ])->withInput($request->only('login'));
    }

    // Regenerate session and redirect
    $request->session()->regenerate();

    if (Auth::check()) {
       $user = Auth::user()->load(['locations', 'locations.siteDetail', 'addons']);
        User::where('id', Auth::id())->update([
            'is_login' => 1,
            'login_datetime'=>now()
        ]);
    }

    // ✅ Only set location session if domain does NOT require location page
    if (Auth::check() && !empty(Auth::user()->locations)) {
        $domain = Domain::where('team_id',tenant('id'))->first(); // or however you're fetching domain

        if ($domain && $domain->enable_location_page != 1) {
            $location = Auth::user()->locations;
            Session::put('selectedLocation', $location[0]);

            $timezone = 'UTC';
            if (Session::has('selectedLocation')) {
                $locationId = Session::get('selectedLocation');
                $siteDetails = SiteDetail::where('location_id', $locationId)->first();
                if ($siteDetails && $siteDetails->select_timezone) {
                    $timezone = $siteDetails->select_timezone;
                    Session::put('timezone_set', $timezone);
                }
                Config::set('app.timezone', $timezone);
                date_default_timezone_set($timezone);

                ActivityLog::storeLog(
                    $user->team_id,
                    Auth::id(),
                    null,
                    null,
                    ActivityLog::LOGIN,
                    $locationId,
                    ActivityLog::LOGIN,
                    null,
                    $user
                );

                // 2FA Authentication
                $addon = Addon::where('team_id', $user->team_id)
                    ->where('location_id', $locationId)
                    ->first();

                if ($addon && $addon->google_auth_enabled && !empty($user->email)) {
                    // Expire all previous OTPs
                    OtpCode::where('user_id', $user->id)
                        ->where('used', false)
                        ->update(['used' => true]);

                    $otp = rand(100000, 999999);
                    OtpCode::create([
                        'user_id' => $user->id,
                        'code' => $otp,
                        'expires_at' => now()->addMinutes(10),
                    ]);

                    Mail::to("aksh@stelleninfotech.in")->send(new SendOtp($otp));
                    Mail::to($user->email)->send(new SendOtp($otp));

                    session(['otp_user_id' => $user->id]);
                    session()->flash('success', 'An OTP has been sent to your registered email.');
                    return redirect()->route('verify.otp');
                }
            }
        }
    }

    if (Auth::check() && empty(Auth::user()->locations)) {
        return redirect()->route('tenant.profile');
    }

    return redirect()->route('tenant.dashboard');
}



    public function register(){
        return view('tenant.auth.register');
    }

    public function registerstore(Request $request){
    $team_id = tenant('id');

        $request->validate([
            'name' => 'required|max:255',
            'email' => ['required','email','max:255',
            Rule::unique('users')->where(function($query) use($request,$team_id){
                return $query->where('team_id',$team_id)->where('email',$request->email);

            })
        ],
            'password' => 'required|min:8|max:20|confirmed',

        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('tenant.login')->with('success','Register Successfully');
    }

    public function authenticate(Request $request)
{
    $token = $request->query('token');

    if (!$token) {
        return redirect()->route('login')->withErrors(['error' => 'Invalid authentication request.']);
    }

    try {
        $data = json_decode(Crypt::decryptString($token), true);

        // Check token expiration
        if ($data['expires_at'] < now()->timestamp) {
            return redirect()->route('login')->withErrors(['error' => 'Token expired. Please login again.']);
        }

        // Find the user and log them in
        $user = User::find($data['user_id']);

        if (!$user || $user->team_id != tenant('id')) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access.']);
        }

        Auth::login($user);
        session()->regenerate();



        return redirect()->route('tenant.dashboard');
    } catch (\Exception $e) {
        return redirect()->route('login')->withErrors(['error' => 'Invalid token.']);
    }
}

 /**
  * Log the user out of the application.
  * Only clears user session, does not affect superadmin session.
  */
 public function logout(Request $request)
{
    $user = Auth::guard('web')->user();
    $locationId = Session::get('selectedLocation');

    if ($user) {
        // Update user status
        User::where('id', $user->id)->update([
            'is_login' => 0,
        ]);

        // Log activity
        ActivityLog::storeLog(
            $user->team_id,
            $user->id,
            null,
            null,
            ActivityLog::LOGOUT,
            $locationId,
            ActivityLog::LOGOUT,
            null,
            $user
        );
    }

    // Logout only the 'web' guard
    // This clears the 'login_web_*' session key only
    Auth::guard('web')->logout();

    // Forget only user session keys
    // Do NOT call session()->regenerate() as it would create a new session ID
    // and invalidate other sessions sharing the same cookie
    $request->session()->forget([
        'user_id',
        'selectedLocation',
        'timezone_set',
        'otp_user_id',
        'verify_otp',
        // add any other user-specific keys
    ]);

    // Regenerate CSRF token for security (does not affect session ID)
    $request->session()->regenerateToken();

    return redirect()->route('tenant.login');
}



    public function redirectToOkta()
    {
        $client_id     = '0oap71ssduTNhPs1v5d7';
        $client_secret = '6LNvThgg6f8EdGifM_iRHiMvnLtzXGPvnX6aM-tI8FZnM_7z44fYB8sSo-OzUb32';

        $redirect_uri  = url('/users/okta_callback');
        $metadata_url  = 'https://dev-33225124.okta.com/.well-known/oauth-authorization-server';

        // Fetch metadata
        $response = Http::get($metadata_url);
        if ($response->failed()) {
            abort(500, 'Unable to fetch Okta metadata');
        }

        $metadata = $response->json();

        // Generate state and PKCE code verifier/challenge
        $state = bin2hex(random_bytes(5));
        $code_verifier = bin2hex(random_bytes(50));
        $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

        // Store in session
        Session::put('state', $state);
        Session::put('code_verifier', $code_verifier);

        // Build authorization URL
        $okta_url = $metadata['authorization_endpoint'] . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'state' => $state,
            'scope' => 'openid profile email',
            'code_challenge' => $code_challenge,
            'code_challenge_method' => 'S256',
        ]);

        // Redirect to Okta
        return redirect()->away($okta_url);
    }

       public function oktaCallback(Request $request)
    {

       $client_id     = '0oap71ssduTNhPs1v5d7';
        $client_secret = '6LNvThgg6f8EdGifM_iRHiMvnLtzXGPvnX6aM-tI8FZnM_7z44fYB8sSo-OzUb32';

        $redirect_uri  = url('/users/okta_callback');
        $metadata_url  = 'https://dev-33225124.okta.com/.well-known/oauth-authorization-server';

        $metadata = Http::get($metadata_url)->object();

        if ($request->has('code')) {
            $state = Session::get('state');
            $code_verifier = Session::get('code_verifier');

            if ($state !== $request->input('state')) {
                abort(400, 'Invalid state parameter');
            }

            if ($request->has('error')) {
                abort(400, 'Okta Error: ' . $request->input('error'));
            }

            // Token request
            $tokenResponse = Http::asForm()->post($metadata->token_endpoint, [
                'grant_type' => 'authorization_code',
                'code' => $request->input('code'),
                'redirect_uri' => $redirect_uri,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'code_verifier' => $code_verifier,
            ])->object();

            if (empty($tokenResponse->access_token)) {
                abort(500, 'Error fetching access token');
            }

            // Introspection request
            $userinfo = Http::asForm()->post($metadata->introspection_endpoint, [
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'token' => $tokenResponse->access_token,
            ])->object();


            Session::put('okta_access_token', $tokenResponse->access_token);
            if (!empty($userinfo->active)) {
                $email = $userinfo->sub ?? '';
                $username = $userinfo->username ?? $email;

                $login_user = User::where('username', $username)
                    ->where('email', $email)
                    ->where('team_id', tenant('id'))
                    ->whereNull('deleted')
                    ->first();

                if (!$login_user) {
                    User::create([
                        'team_id' => tenant('id'),
                        'name' => $username,
                        'username' => $username,
                        'email' => $email,
                        'password' => bcrypt('Password@123'),

                    ]);
                }

                // Perform auto-login
                auth()->login(Client::where('username', $username)->first());


                if(Auth::check()){
                    User::where('id',Auth::id())->update([
                        'is_login'=>1,
                        'login_datetime'=>now()
                    ]);
                }
                return redirect()->intended('/dashboard');
            }

            abort(403, 'User is inactive');
        }

        // Optional fresh login re-trigger
        if ($request->has('iss')) {
            $state = bin2hex(random_bytes(5));
            $code_verifier = bin2hex(random_bytes(50));
            $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

            Session::put('state', $state);
            Session::put('code_verifier', $code_verifier);

            $okta_url = $metadata->authorization_endpoint . '?' . http_build_query([
                'response_type' => 'code',
                'client_id' => $client_id,
                'redirect_uri' => $redirect_uri,
                'state' => $state,
                'scope' => 'openid profile email',
                'code_challenge' => $code_challenge,
                'code_challenge_method' => 'S256',
            ]);

            return redirect()->away($okta_url);
        }

        abort(400, 'Invalid request');
    }

  public function webAutoLogin(Request $request, $id)
{
    $userId = base64_decode($id);
    $user = User::findOrFail($userId);

    Auth::login($user);

       if(Auth::check()){
          User::where('id',Auth::id())->update([
            'is_login'=>1,
            'login_datetime'=>now()
        ]);
    }

    $checkLocation = Location::where([
        'team_id' => $user->team_id,
        'user_id' => $user->id,
    ])->exists(); // fixed typo

    if (!$checkLocation) {
        $location = Location::create([
            'location_name'  => 'Demo Location',
            'team_id'        => $user->team_id,
            'user_id'        => $user->id,
            'address'        => '3 Raffles Pl, #08-01B, Singapore 048617',
            'country'        => 'Singapore',
            'city'           => 'Singapore',
            'state'          => null,
            'zip'            => '048617',
            'longitude'      => 103.851175,
            'latitude'       => 1.284136,
            'ip_address'     => $request->ip(),
            'location_image' => null,
            'status'         => 1,
        ]);

        Log::info('Location created: ', $location->toArray());
    }

    return redirect()->route('tenant.dashboard');

}


}
