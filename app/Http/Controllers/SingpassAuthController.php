<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\SingpassService;

class SingpassAuthController extends Controller
{
    protected $singpassService;

    public function __construct(SingpassService $singpassService)
    {
        $this->singpassService = $singpassService;
    }

    public function redirectToSingpass(Request $request)
    {
        $state = Str::random(40);
        $nonce = Str::random(40);
        $codeVerifier = '';

        $request->session()->put('singpass_state', $state);
        $request->session()->put('singpass_nonce', $nonce);

        // getAuthUrl now does PAR and returns /auth?request_uri=... URL
        // It also populates $codeVerifier (passed by reference)
        $authUrl = $this->singpassService->getAuthUrl($state, $nonce, $codeVerifier);

        // Store the code verifier for PKCE verification at token exchange
        $request->session()->put('singpass_code_verifier', $codeVerifier);

        return redirect()->away($authUrl);
    }

    public function handleSingpassCallback(Request $request)
    {
        $state = $request->query('state');
        $code = $request->query('code');
        $error = $request->query('error');

        if ($error || $state !== $request->session()->get('singpass_state')) {
            return redirect()->route('tenant.login')->withErrors(['login' => 'Singpass authentication failed or was interrupted. Please contact system helpdesk.']);
        }

        try {
            // Exchange code for tokens (include PKCE code_verifier)
            $codeVerifier = $request->session()->pull('singpass_code_verifier');
            $tokens = $this->singpassService->exchangeCodeForToken($code, $codeVerifier);
            
            if (!isset($tokens['id_token'])) {
                throw new \Exception('Failed to retrieve ID token from Singpass.');
            }

            // Get user information from ID Token
            $userInfo = $this->singpassService->getUserInfo($tokens['id_token']);
            $singpassId = $userInfo['sub'] ?? null;

            if (!$singpassId) {
                throw new \Exception('Invalid Singpass Identity.');
            }

            // Find user in database
            $user = User::where('singpass_id', $singpassId)->first();

            if ($user) {
                // User exists, log them in
                Auth::login($user);
                
                // Track login
                $user->is_login = 1;
                $user->login_datetime = now();
                $user->save();
                
                return redirect()->intended(route('tenant.dashboard'))->with('success', 'Logged in successfully via Singpass.');
            } else {
                // User does not exist, auto-register and log them in
                $user = new User();
                
                // Fallback details if scopes are missing from Singpass token
                $user->name = $userInfo['name'] ?? 'Singpass User';
                $user->email = $userInfo['email'] ?? ($singpassId . '@singpass.user');
                $user->phone = $userInfo['mobile_number'] ?? null;
                $user->singpass_id = $singpassId;
                
                // Set standard required fields
                $user->password = bcrypt(Str::random(16)); // Random password as they use Singpass
                $user->role_id = 3; // Basic staff/user role, adjust as needed 
                $user->is_login = 1;
                $user->login_datetime = now();
                $user->save();

                Auth::login($user);

                return redirect()->intended(route('tenant.dashboard'))->with('success', 'Account created and logged in successfully via Singpass.');
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Singpass callback error: ' . $e->getMessage());
            return redirect()->route('tenant.login')->withErrors(['login' => 'Authentication error. Please contact application helpdesk instead of Singpass.']);
        }
    }

    public function jwks()
    {
        return response()->json($this->singpassService->getJwks());
    }
}
