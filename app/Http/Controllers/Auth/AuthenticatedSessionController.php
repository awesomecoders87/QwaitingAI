<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();

    //     $request->session()->regenerate();

    //     return redirect()->intended(route('dashboard', absolute: false));
    // }

    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt to log in
        if (!Auth::attempt($credentials)) {
            return redirect()->back()->withErrors(['email' => 'Invalid email or password']);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // If user does not have a tenant, redirect to central dashboard
        if (empty($user->team_id)) {
            return redirect()->route('dashboard');
        }

        // Get tenant domain dynamically
        $tenant = Tenancy::find($user->team_id);

        if (!$tenant) {
            return redirect()->back()->withErrors(['email' => 'Tenant not found']);
        }

        $tenantDomain = $tenant->domains()->first()->domain ?? config('tenancy.central_domains.0');

        // Generate a secure token
        $token = Crypt::encryptString(json_encode([
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(5)->timestamp, // Expiry time for security
        ]));


    // Logout from central system to prevent dual login
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

       // Redirect to tenant domain with authentication token
       return redirect()->away("http://{$tenantDomain}:8000/authenticate?token={$token}");
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
