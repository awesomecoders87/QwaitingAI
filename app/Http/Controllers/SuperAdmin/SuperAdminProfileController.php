<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\SuperAdminProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class SuperAdminProfileController extends Controller
{
    /**
     * Display the superadmin's profile form.
     */
    public function edit(Request $request): View
    {
        return view('superadmin.profile.edit', [
            'superadmin' => Auth::guard('superadmin')->user(),
        ]);
    }

    /**
     * Update the superadmin's profile information.
     */
    public function update(SuperAdminProfileUpdateRequest $request): RedirectResponse
    {
        $superadmin = Auth::guard('superadmin')->user();
        
        $superadmin->fill($request->validated());

        if ($superadmin->isDirty('email')) {
            $superadmin->email_verified_at = null;
        }

        $superadmin->save();

        return Redirect::route('superadmin.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the superadmin's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $superadmin = Auth::guard('superadmin')->user();
        
        // Validate current password manually since current_password rule might not work with custom guards
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Check if current password is correct
        if (!Hash::check($validated['current_password'], $superadmin->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'], 'updatePassword');
        }

        $superadmin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}

