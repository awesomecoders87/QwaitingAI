<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Domain;
use App\Models\Addon;
use App\Models\Location;
use App\Models\SiteDetail;
use App\Models\ActivityLog;
use App\Services\SingpassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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

        // ── STEP A: Validate state ─────────────────────────────────────────────
        // Log::info('[Singpass][A] Callback received', [
        //     'has_error'      => !empty($error),
        //     'error'          => $error,
        //     'state_matches'  => ($state === $request->session()->get('singpass_state')),
        // ]);

        if ($error || $state !== $request->session()->get('singpass_state')) {
            Log::warning('[Singpass][A] State mismatch or upstream error — aborting', [
                'error'          => $error,
                'received_state' => $state,
                'expected_state' => $request->session()->get('singpass_state'),
            ]);
            return redirect()->route('tenant.login')
                ->withErrors(['login' => 'Singpass authentication failed or was interrupted. Please try again.']);
        }

        try {
            // ── STEP B: Exchange code for token ────────────────────────────────
            // Log::info('[Singpass][B] Exchanging code for token');
            $codeVerifier = $request->session()->pull('singpass_code_verifier');
            $dpopPem      = $request->session()->pull('singpass_dpop_pem');

            $tokens = $this->singpassService->exchangeCodeForToken($code, $codeVerifier, $dpopPem);

            if (empty($tokens['id_token'])) {
                throw new \RuntimeException('No id_token in Singpass token response.');
            }
            // Log::info('[Singpass][B] Token exchange successful — id_token present');

            // ── STEP C: Decode/verify id_token ────────────────────────────────
            // Log::info('[Singpass][C] Decoding id_token');
            $userInfo   = $this->singpassService->getUserInfo($tokens['id_token']);
            $singpassId = $userInfo['sub'] ?? null;
            // Log::info('[Singpass][C] id_token decoded', ['singpass_id' => $singpassId]);

            if (empty($singpassId)) {
                throw new \RuntimeException('id_token missing sub claim.');
            }

            // ── STEP D: Resolve tenant ─────────────────────────────────────────
            $tenantFn   = tenant('id');
            $teamId     = $tenantFn;
            // Log::info('[Singpass][D] Tenant resolution', [
            //     'tenant_id_raw'  => $tenantFn,
            //     'team_id_used'   => $teamId,
            //     'tenant_helper'  => function_exists('tenant') ? 'exists' : 'missing',
            // ]);

            // ── STEP E: Fetch active locations for this tenant ─────────────────
            $allLocations   = Location::where('team_id', $teamId)->where('status', 1)->get();
            $allLocationIds = $allLocations->pluck('id')->toArray();
            // Log::info('[Singpass][E] Active locations fetched', [
            //     'team_id'       => $teamId,
            //     'location_count'=> count($allLocationIds),
            //     'location_ids'  => $allLocationIds,
            // ]);

            // ── STEP F: Lookup user by singpass_id + team_id ──────────────────
            // Log::info('[Singpass][F] Looking up user', [
            //     'singpass_id' => $singpassId,
            //     'team_id'     => $teamId,
            // ]);

            $user = User::where('singpass_id', $singpassId)
                        ->where('team_id', $teamId)
                        ->first();

            // Log::info('[Singpass][F] Lookup result (with team_id)', [
            //     'found'   => !is_null($user),
            //     'user_id' => optional($user)->id,
            // ]);

            // ── STEP G: Fallback — find without team_id (legacy records) ──────
            if (!$user) {
                $legacyUser = User::where('singpass_id', $singpassId)->first();
                // Log::info('[Singpass][G] Legacy lookup (any team_id)', [
                //     'found'              => !is_null($legacyUser),
                //     'legacy_user_id'     => optional($legacyUser)->id,
                //     'legacy_team_id'     => optional($legacyUser)->team_id,
                //     'current_tenant_id'  => $teamId,
                // ]);

                if ($legacyUser) {
                    $legacyUser->team_id = $teamId;
                    $legacyUser->save();
                    $user = $legacyUser;
                    // Log::info('[Singpass][G] Updated legacy user with current team_id', [
                    //     'user_id'    => $user->id,
                    //     'new_team_id'=> $teamId,
                    // ]);
                }
            }

            // ── STEP H: Existing user path ─────────────────────────────────────
            if ($user) {
                // Log::info('[Singpass][H] Existing user path', [
                //     'user_id'        => $user->id,
                //     'user_team_id'   => $user->team_id,
                //     'has_admin_role' => $user->hasRole(User::ROLE_ADMIN),
                //     'locations'      => $user->locations,
                // ]);

                // Assign Admin role if missing
                if (!$user->hasRole(User::ROLE_ADMIN)) {
                    $adminRole = Role::where('name', User::ROLE_ADMIN)->first();
                    // Log::info('[Singpass][H] Admin role lookup', [
                    //     'role_found' => !is_null($adminRole),
                    //     'role_id'    => optional($adminRole)->id,
                    //     'role_name'  => optional($adminRole)->name,
                    // ]);
                    if ($adminRole) {
                        $user->assignRole($adminRole);
                        // Log::info('[Singpass][H] Admin role assigned to existing user', ['user_id' => $user->id]);
                    }
                } else {
                    Log::info('[Singpass][H] User already has Admin role — skipping assignment');
                }

                // Assign locations if missing
                if (empty($user->locations) && !empty($allLocationIds)) {
                    $user->locations = $allLocationIds;
                    $user->save();
                    // Log::info('[Singpass][H] Locations assigned to existing user', [
                    //     'user_id'   => $user->id,
                    //     'locations' => $allLocationIds,
                    // ]);
                } else {
                    Log::info('[Singpass][H] Locations already set — skipping', ['locations' => $user->locations]);
                }

                $user->forceFill(['is_login' => 1, 'login_datetime' => now()])->save();
                // Log::info('[Singpass][H] Existing user updated (is_login=1)');

            } else {
                // ── STEP I: New user path ──────────────────────────────────────
                // Log::info('[Singpass][I] No existing user found — creating new user', [
                //     'singpass_id' => $singpassId,
                //     'team_id'     => $teamId,
                // ]);

                $subAttr  = $userInfo['sub_attributes'] ?? [];
                $realName = $subAttr['name']            ?? 'Singpass User';
                $nric     = $subAttr['identity_number'] ?? null;

                // Log::info('[Singpass][I] New user data', [
                //     'name'      => $realName,
                //     'nric'      => $nric,
                //     'team_id'   => $teamId,
                //     'locations' => $allLocationIds,
                // ]);

                $user = User::create([
                    'name'                 => $realName,
                    'username'             => $nric ?? $singpassId,
                    'email'                => $nric ? ($nric . '@singpass.user') : ($singpassId . '@singpass.user'),
                    'phone'                => null,
                    'singpass_id'          => $singpassId,
                    'password'             => bcrypt(Str::random(32)),
                    'role_id'              => 1,
                    'team_id'              => $teamId,
                    'is_login'             => 1,
                    'is_admin'             => 1,
                    'is_active'            => 1,
                    'login_datetime'       => now(),
                    'must_change_password' => 0,
                    'locations'            => $allLocationIds,
                ]);

                // Log::info('[Singpass][I] New user created', [
                //     'user_id'  => $user->id,
                //     'team_id'  => $user->team_id,
                //     'locations'=> $user->locations,
                // ]);

                // Assign Admin Spatie role
                $adminRole = Role::where('name', User::ROLE_ADMIN)->first();
                // Log::info('[Singpass][I] Admin role lookup for new user', [
                //     'role_found' => !is_null($adminRole),
                //     'role_id'    => optional($adminRole)->id,
                // ]);
                if ($adminRole) {
                    $user->assignRole($adminRole);
                    Log::info('[Singpass][I] Admin role assigned to new user', ['user_id' => $user->id]);
                }
            }

            // ── STEP J: Log in the user ────────────────────────────────────────
            // Log::info('[Singpass][J] Logging in user', ['user_id' => $user->id]);
            Auth::login($user);
            $request->session()->regenerate();
            $user = $user->fresh();

            // Log::info('[Singpass][J] Auth::login done', [
            //     'auth_check'     => Auth::check(),
            //     'auth_user_id'   => Auth::id(),
            //     'user_team_id'   => $user->team_id,
            //     'user_locations' => $user->locations,
            //     'has_admin_role' => $user->hasRole(User::ROLE_ADMIN),
            // ]);

            // ── STEP K: Session setup ──────────────────────────────────────────
            $domain = Domain::where('team_id', $teamId)->first();
            // Log::info('[Singpass][K] Domain lookup', [
            //     'team_id'              => $teamId,
            //     'domain_found'         => !is_null($domain),
            //     'enable_location_page' => optional($domain)->enable_location_page,
            // ]);

            $userLocations = $user->locations;
            // Log::info('[Singpass][K] User locations for session setup', [
            //     'locations'     => $userLocations,
            //     'locations_empty'=> empty($userLocations),
            // ]);

            if (!empty($userLocations)) {
                if ($domain && $domain->enable_location_page != 1) {
                    $locationId = $userLocations[0];
                    Session::put('selectedLocation', $locationId);

                    $timezone    = 'UTC';
                    $siteDetails = SiteDetail::where('location_id', $locationId)->first();
                    if ($siteDetails && $siteDetails->select_timezone) {
                        $timezone = $siteDetails->select_timezone;
                        Session::put('timezone_set', $timezone);
                    }
                    Config::set('app.timezone', $timezone);
                    date_default_timezone_set($timezone);

                    // Log::info('[Singpass][K] selectedLocation set in session', [
                    //     'location_id' => $locationId,
                    //     'timezone'    => $timezone,
                    // ]);

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
                } else {
                    Log::info('[Singpass][K] enable_location_page=1 — skipping auto session, user will select location manually');
                }
            } else {
                Log::warning('[Singpass][K] No locations on user — cannot set session. Redirecting to profile.', [
                    'user_id' => $user->id,
                ]);
                return redirect()->route('tenant.profile')
                    ->with('success', 'Logged in via Singpass. Please configure your location.');
            }

            // ── STEP L: Final redirect ─────────────────────────────────────────
            // Log::info('[Singpass][L] Redirecting to dashboard', [
            //     'user_id'          => $user->id,
            //     'selectedLocation' => Session::get('selectedLocation'),
            // ]);

            return redirect()->route('tenant.dashboard')
                ->with('success', 'Logged in successfully via Singpass.');

        } catch (\Exception $e) {
            Log::error('[Singpass] Callback error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
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