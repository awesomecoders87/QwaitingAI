<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\SuperAdminPasswordResetMail;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

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
     * Only clears superadmin session, does not affect regular user session.
     */
    public function logout(Request $request): RedirectResponse
	{
		// Logout only the superadmin guard
		// This clears the 'login_superadmin_*' session key only
		Auth::guard('superadmin')->logout();

		// Forget only superadmin-related session data
		// Do NOT call session()->regenerate() as it would create a new session ID
		// and invalidate other sessions sharing the same cookie
		$request->session()->forget([
			'superadmin_id',
			// any other superadmin session keys you are using
		]);

		// Regenerate CSRF token for security (does not affect session ID)
		$request->session()->regenerateToken();

		return redirect()->route('superadmin.login');
	}

	/**
	 * Display the password reset request form.
	 */
	public function showForgotPasswordForm(): View
	{
		return view('superadmin.auth.forgot-password');
	}

	/**
	 * Handle a password reset link request.
	 */
	public function sendPasswordResetLink(Request $request): RedirectResponse
	{
		config(['session.cookie' => 'superadmin_session']);
		
		$request->validate([
			'email' => ['required', 'email', 'exists:superadmins,email'],
		]);

		// Find superadmin by email
		$superadmin = SuperAdmin::where('email', $request->email)->first();

		if (!$superadmin) {
			return back()->withErrors(['email' => 'No SuperAdmin found with this email.']);
		}

		// Generate a unique token
		$token = Str::random(60);

		// Store token in password resets table
		DB::table('password_reset_tokens')->updateOrInsert(
			['email' => $request->email],
			['token' => Hash::make($token), 'created_at' => Carbon::now()]
		);

		// Try to send password reset email
		try {
			Mail::to($request->email)->send(new SuperAdminPasswordResetMail($superadmin, $token));
			return back()->with('status', 'Password reset link sent to your email!');
		} catch (\Exception $e) {
			// Log the error for debugging
			\Log::error('Failed to send password reset email: ' . $e->getMessage());
			
			// Still return success message to user for security (don't reveal email issues)
			// The token is already stored, so they can still reset if they have the link
			return back()->with('status', 'If the email exists in our system, a password reset link has been sent. Please check your email inbox.');
		}
	}

	/**
	 * Display the password reset form.
	 */
	public function showResetForm(Request $request, $token): View
	{
		$email = $request->query('email');

		return view('superadmin.auth.reset-password', [
			'token' => $token,
			'email' => $email
		]);
	}

	/**
	 * Handle a password reset request.
	 */
	public function resetPassword(Request $request): RedirectResponse
	{
		config(['session.cookie' => 'superadmin_session']);
		
		$request->validate([
			'email' => 'required|email|exists:superadmins,email',
			'password' => 'required|min:6|confirmed',
			'token' => 'required'
		]);

		// Check if the token exists and is valid
		$resetData = DB::table('password_reset_tokens')
			->where('email', $request->email)
			->first();

		if (!$resetData || !Hash::check($request->token, $resetData->token)) {
			return back()->withErrors(['email' => 'Invalid or expired reset token.']);
		}

		// Check if token is expired (60 minutes)
		if (Carbon::parse($resetData->created_at)->addMinutes(60)->isPast()) {
			return back()->withErrors(['email' => 'Reset token has expired. Please request a new one.']);
		}

		// Update superadmin password
		$superadmin = SuperAdmin::where('email', $request->email)->first();
		if (!$superadmin) {
			return back()->withErrors(['email' => 'No SuperAdmin found with this email.']);
		}

		$superadmin->update([
			'password' => Hash::make($request->password)
		]);

		// Delete the reset token
		DB::table('password_reset_tokens')->where('email', $request->email)->delete();

		return redirect()->route('superadmin.login')->with('status', 'Password successfully reset! You can now login with your new password.');
	}

}
