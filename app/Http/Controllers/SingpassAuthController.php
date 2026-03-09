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
            // FIX #2: buildAuthorizationRequest returns url + code_verifier + dpop_pem
            $result = $this->singpassService->buildAuthorizationRequest($state, $nonce);

            // Persist BOTH the code_verifier AND the DPoP PEM for use at callback
            $request->session()->put('singpass_code_verifier', $result['code_verifier']);
            $request->session()->put('singpass_dpop_pem',      $result['dpop_pem']);   // ← was missing

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

        // Validate state and check for upstream errors
        if ($error || $state !== $request->session()->get('singpass_state')) {
            Log::warning('[Singpass] Callback state mismatch or upstream error', [
                'error'           => $error,
                'received_state'  => $state,
                'expected_state'  => $request->session()->get('singpass_state'),
            ]);
            return redirect()->route('tenant.login')
                ->withErrors(['login' => 'Singpass authentication failed or was interrupted. Please contact system helpdesk.']);
        }

        try {
            // Pull session values (pull = get + delete)
            $codeVerifier = $request->session()->pull('singpass_code_verifier');
            $dpopPem      = $request->session()->pull('singpass_dpop_pem');   // ← FIX #2

            // Exchange code for tokens — pass DPoP PEM so the proof matches dpop_jkt
            $tokens = $this->singpassService->exchangeCodeForToken($code, $codeVerifier, $dpopPem);

            if (empty($tokens['id_token'])) {
                throw new \RuntimeException('No id_token in Singpass token response.');
            }

            // Decode and verify the ID token (FIX #4: signature verified inside getUserInfo)
            $userInfo   = $this->singpassService->getUserInfo($tokens['id_token']);
            $singpassId = $userInfo['sub'] ?? null;

            if (empty($singpassId)) {
                throw new \RuntimeException('id_token missing sub claim — invalid Singpass identity.');
            }

            // Find or auto-register the user
            $user = User::where('singpass_id', $singpassId)->first();

            if ($user) {
                Auth::login($user);
                $user->forceFill(['is_login' => 1, 'login_datetime' => now()])->save();

                return redirect()->intended(route('tenant.dashboard'))
                    ->with('success', 'Logged in successfully via Singpass.');
            }

            // Auto-register
            $user = User::create([
                'name'           => $userInfo['name']          ?? 'Singpass User',
                'email'          => $userInfo['email']         ?? ($singpassId . '@singpass.user'),
                'phone'          => $userInfo['mobile_number'] ?? null,
                'singpass_id'    => $singpassId,
                'password'       => bcrypt(Str::random(32)),   // Random — user authenticates via Singpass only
                'role_id'        => 3,
                'is_login'       => 1,
                'login_datetime' => now(),
            ]);

            Auth::login($user);

            return redirect()->intended(route('tenant.dashboard'))
                ->with('success', 'Account created and logged in successfully via Singpass.');

        } catch (\Exception $e) {
            Log::error('[Singpass] Callback error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('tenant.login')
                ->withErrors(['login' => 'Authentication error. Please contact application helpdesk.']);
        }
    }

    /**
     * Expose the RP's JWKS endpoint for Singpass to verify client assertions.
     * Register https://qwaiting-ai.thevistiq.com/sp/jwks in Developer Portal.
     */
    public function jwks()
    {
        return response()->json($this->singpassService->getJwks())
            ->header('Cache-Control', 'public, max-age=3600');
    }
}