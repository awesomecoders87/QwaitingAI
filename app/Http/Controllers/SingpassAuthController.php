<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SingpassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SingpassAuthController extends Controller
{
    protected SingpassService $singpassService;

    public function __construct(SingpassService $singpassService)
    {
        $this->singpassService = $singpassService;
    }

    /**
     * Step 1: Initiate Singpass login via PAR.
     */
    public function redirectToSingpass(Request $request)
    {
        $state = Str::random(40);
        $nonce = Str::random(40);

        $request->session()->put('singpass_state', $state);
        $request->session()->put('singpass_nonce', $nonce);

        try {
            $result = $this->singpassService->buildAuthorizationRequest($state, $nonce);

            $request->session()->put('singpass_code_verifier', $result['code_verifier']);
            $request->session()->put('singpass_dpop_pem',      $result['dpop_pem']);

            return redirect()->away($result['url']);

        } catch (\Exception $e) {
            Log::error('[Singpass] Failed to build auth URL', ['error' => $e->getMessage()]);
            return redirect()->route('tenant.login')
                ->withErrors(['login' => 'Could not connect to Singpass. Please try again or contact helpdesk.']);
        }
    }

    /**
     * Step 2: Handle the Singpass callback.
     */
    public function handleSingpassCallback(Request $request)
    {
        $state = $request->query('state');
        $code  = $request->query('code');
        $error = $request->query('error');

        if ($error || $state !== $request->session()->get('singpass_state')) {
            Log::warning('[Singpass] Callback state mismatch or upstream error', [
                'error'          => $error,
                'received_state' => $state,
                'expected_state' => $request->session()->get('singpass_state'),
            ]);
            return redirect()->route('tenant.login')
                ->withErrors(['login' => 'Singpass authentication failed or was interrupted. Please try again.']);
        }

        try {
            $codeVerifier = $request->session()->pull('singpass_code_verifier');
            $dpopPem      = $request->session()->pull('singpass_dpop_pem');

            $tokens = $this->singpassService->exchangeCodeForToken($code, $codeVerifier, $dpopPem);

            if (empty($tokens['id_token'])) {
                throw new \RuntimeException('No id_token in Singpass token response.');
            }

            $userInfo   = $this->singpassService->getUserInfo($tokens['id_token']);
            $singpassId = $userInfo['sub'] ?? null;

            Log::info('[Singpass] Login successful', ['singpass_id' => $singpassId]);

            if (empty($singpassId)) {
                throw new \RuntimeException('id_token missing sub claim.');
            }

            // Find existing user
            $user = User::where('singpass_id', $singpassId)->first();

            if ($user) {
                Log::info('[Singpass] Existing user logged in', [
                    'user_id'     => $user->id,
                    'singpass_id' => $singpassId,
                ]);
                Auth::login($user);
                $user->forceFill(['is_login' => 1, 'login_datetime' => now()])->save();

                return redirect()->intended(route('tenant.dashboard'))
                    ->with('success', 'Logged in successfully via Singpass.');
            }

            // Auto-register new user
            Log::info('[Singpass] Auto-registering new user', ['singpass_id' => $singpassId]);

            // Extract real user data from sub_attributes
            $subAttr = $userInfo['sub_attributes'] ?? [];
            $realName = $subAttr['name']            ?? 'Singpass User';
            $nric     = $subAttr['identity_number'] ?? null;

            $user = User::create([
                'name'           => $realName,
                'username'       => $nric ?? $singpassId,
                'email'          => $nric ? ($nric . '@singpass.user') : ($singpassId . '@singpass.user'),
                'phone'          => null,
                'singpass_id'    => $singpassId,
                'password'       => bcrypt(Str::random(32)),
                'role_id'        => 1,
                'is_login'       => 1,
                'login_datetime' => now(),
                'must_change_password' => 0,
            ]);

            Log::info('[Singpass] New user created', ['user_id' => $user->id]);

            Auth::login($user);

            return redirect()->intended(route('tenant.dashboard'))
                ->with('success', 'Welcome! Your account has been created via Singpass.');

        } catch (\Exception $e) {
            Log::error('[Singpass] Callback error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('tenant.login')
                ->withErrors(['login' => 'Authentication error. Please contact helpdesk.']);
        }
    }

    /**
     * JWKS endpoint for Singpass to verify client assertions.
     */
    public function jwks()
    {
        return response()->json($this->singpassService->getJwks())
            ->header('Cache-Control', 'public, max-age=3600');
    }
}