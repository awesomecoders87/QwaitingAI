<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SuperAdminAuthController extends Controller
{
    /**
     * Display the superadmin login view.
     */
    public function showLoginForm(): View
    {
        return view('superadmin.auth.login');
    }

    /**
     * Handle an incoming superadmin authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
		config(['session.cookie' => 'superadmin_session']);
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Attempt to authenticate using the superadmin guard
        if (Auth::guard('superadmin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('superadmin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the superadmin out of the application.
     */
    public function logout(Request $request): RedirectResponse
	{
		// Logout only the superadmin guard
		Auth::guard('superadmin')->logout();

		// Forget only superadmin-related session data
		$request->session()->forget([
			'superadmin_id',
			// any other superadmin session keys you are using
		]);

		// Regenerate CSRF token for security
		$request->session()->regenerateToken();

		return redirect()->route('superadmin.login');
	}

}
