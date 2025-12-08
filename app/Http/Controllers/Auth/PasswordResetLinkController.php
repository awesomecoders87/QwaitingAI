<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User; // Ensure this matches your tenant user model
use App\Mail\TenantPasswordResetMail; // Custom mail class

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        // return view('auth.forgot-password');
        return view('tenant.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
    public function tenantstore(Request $request)
    {

        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'], // Validate email exists in tenants
        ]);

        // Find customer by email
        $user = User::where('email', $request->email)->where('team_id',tenant('id'))->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No User found with this email.']);
        }

        // Generate a unique token
        $token = Str::random(60);

        // Store token in password resets table (Ensure this table exists)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        // Send custom password reset email
        Mail::to($request->email)->send(new TenantPasswordResetMail($user, $token));
        return back()->with('status', 'Password reset link sent to your email!');
    }


   public function showResetForm(Request $request, $token)
{
    // Get email from URL
    $email = $request->query('email'); // or $request->email

    return view('tenant.auth.reset-password', [
        'token' => $token,
        'email' => $email
    ]);
}

    // Update Password
    public function updatePassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
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

        // Update user password
        $user = User::where('email', $request->email)->where('team_id', tenant('id'))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No user found with this email.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('tenant.login')->with('status', 'Password successfully reset!');
    }
}
