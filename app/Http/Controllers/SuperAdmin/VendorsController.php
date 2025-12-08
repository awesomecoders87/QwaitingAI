<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Mail\TenantCreated;
use Illuminate\Support\Facades\Crypt;

class VendorsController extends Controller
{
    /**
     * Display a listing of all domains (vendors).
     */
    public function index(Request $request): View
    {
        $now = Carbon::now();
        $status = $request->get('status', 'all');
        $search = $request->get('search', '');
        $startDate = $request->get('start_date', '');
        $endDate = $request->get('end_date', '');
        
        // Clean up date parameters - remove if empty or just whitespace
        if (empty(trim($startDate))) {
            $startDate = null;
        }
        if (empty(trim($endDate))) {
            $endDate = null;
        }

        $query = Domain::with(['team', 'adminUser']);

        if ($status !== 'all') {
            $query->whereHas('team');
        }

        // Filter by status
        if ($status === 'active') {
            // Active: no expired date OR expired date is more than 7 days in the future
            $query->where(function($q) use ($now) {
                $q->whereNull('expired')
                    ->orWhere('expired', '>', $now->copy()->addDays(7));
            });
        } elseif ($status === 'expired') {
            // Expired: expired date exists and is in the past
            $query->whereNotNull('expired')
                ->where('expired', '<=', $now);
        } elseif ($status === 'expiring_soon') {
            // Expiring Soon: expired date exists and is within the next 7 days or expired within the last week
            $query->whereNotNull('expired')
                ->where(function($q) use ($now) {
                    // Expires within the next 7 days
                    $q->whereBetween('expired', [$now, $now->copy()->addDays(7)])
                      // OR expired within the last week (1 week ago to now)
                      ->orWhereBetween('expired', [$now->copy()->subDays(7), $now]);
                });
        } elseif ($status === 'trial') {
            // Trial: trial_ends_at exists and is greater than today
            $query->whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', $now);
        }

        // Search functionality - search by domain, tenant ID, tenant name, or owner name
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', '%' . $search . '%')
                    ->orWhere('team_id', 'like', '%' . $search . '%')
                    ->orWhereHas('team', function($teamQuery) use ($search) {
                        $teamQuery->whereRaw("JSON_EXTRACT(data, '$.name') LIKE ?", ['%' . $search . '%']);
                    })
                    ->orWhereHas('adminUser', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%')
                            ->orWhere('address', 'like', '%' . $search . '%');
                    });
            });
        }

        // Date range filtering - only apply if both dates are provided and valid
        if (!empty($startDate) && !empty($endDate)) {
            // Both dates provided - include full end day
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        } elseif (!empty($startDate)) {
            // Only start date provided
            $start = Carbon::parse($startDate)->startOfDay();
            $query->where('created_at', '>=', $start);
        } elseif (!empty($endDate)) {
            // Only end date provided - include full end day
            $end = Carbon::parse($endDate)->endOfDay();
            $query->where('created_at', '<=', $end);
        }

        $domains = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return view('superadmin.vendors.index', [
            'domains' => $domains,
            'currentStatus' => $status,
            'searchQuery' => $search,
            'startDate' => $startDate ?? '',
            'endDate' => $endDate ?? '',
        ]);
    }

    /**
     * Show the form for creating a new vendor.
     */
    public function create(): View
    {
        return view('superadmin.vendors.create');
    }

    /**
     * Store a newly created vendor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain'       => 'required|string|max:255',
            'fullname'     => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'phone'        => 'nullable|string',
            'phone_code'   => 'nullable|string',
            'trial_days'   => 'nullable|integer|min:0|max:365',
        ]);

        try {
            $slug = Str::slug($validated['domain']);
            $domainName = $slug . '.' . env('PARENT_DOMAIN');

            // Check if domain already exists
            if (Domain::where('domain', $domainName)->exists()) {
                return back()->withErrors(['domain' => 'Domain already exists.'])->withInput();
            }

            // Generate unique username from full name
            $baseUsername = Str::slug($validated['fullname']);
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter++;
            }

            // Create Tenant
            $tenant = Tenant::create([
                'name'  => ucfirst($validated['domain']),
                'brand' => ucfirst($validated['company_name']),
            ]);

            // Set expiry date based on trial days
            $trialDays = isset($validated['trial_days']) ? (int)$validated['trial_days'] : 14;
            $expiryDate = $trialDays > 0 ? now()->addDays($trialDays) : null;

            // Create Domain
            $tenant->domains()->create([
                'domain' => $domainName,
                'expired' => $expiryDate,
				'trial_ends_at' => $expiryDate,
            ]);

            // Create Admin User
            $user = User::create([
                'name'              => $validated['fullname'],
                'username'          => $username,
                'email'             => $validated['email'],
                'phone'             => isset($validated['phone']) ? ($validated['phone_code'] ?? '') . $validated['phone'] : '',
                'is_admin'          => 1,
                'email_verified_at' => now(),
                'password'          => Hash::make('Password@123'),
                'remember_token'    => Str::random(60),
                'address'           => '',
                'timezone'          => config('app.timezone', 'UTC'),
                'language'          => 'eng',
                'country'           => '92',
                'locations'         => [],
                'sms_reminder_queue' => 1,
                'team_id'           => $tenant->id,
                'date_format'       => 'Y-m-d',
                'time_format'       => 'H:i',
                'role_id'           => 1,
                'is_login'          => 1,
                'is_active'         => 1,
            ]);

            // Assign Admin Role
            if ($adminRole = Role::where('name', 'Admin')->first()) {
                $user->roles()->attach($adminRole->id);
            }

            // Send Welcome Email
            try {
                Mail::to($validated['email'])->send(new TenantCreated(
                    ucfirst($validated['company_name']),
                    $domainName,
                    $username,
                    $validated['email'],
                    'Password@123'
                ));
            } catch (\Exception $e) {
                // Log email error but don't fail the creation
                \Log::warning('Failed to send welcome email: ' . $e->getMessage());
            }

            return redirect()->route('superadmin.vendors.index')
                ->with('success', 'Vendor created successfully! Domain: ' . $domainName);
        } catch (\Exception $e) {
            \Log::error('Vendor Creation Failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create vendor: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified vendor.
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        try {
            $domain = Domain::with(['team', 'adminUser'])->findOrFail($id);
            
            // Get admin user (if exists)
            $adminUser = $domain->adminUser && $domain->adminUser->isNotEmpty() 
                ? $domain->adminUser->first() 
                : null;
            
            // Determine status
            $now = Carbon::now();
            $expiryDate = $domain->expired ? Carbon::parse($domain->expired) : null;
            $isExpired = $expiryDate && $expiryDate->isPast();
            $isExpiringSoon = $domain->isExpiringSoon();
            $isTrial = $domain->trial_ends_at && Carbon::parse($domain->trial_ends_at)->isFuture();
            
            if ($isExpired) {
                $status = 'Expired';
                $statusClass = 'bg-red-100 text-red-800';
            } elseif ($isExpiringSoon) {
                $status = 'Expiring Soon';
                $statusClass = 'bg-yellow-100 text-yellow-800';
            } elseif ($isTrial) {
                $status = 'Trial';
                $statusClass = 'bg-blue-100 text-blue-800';
            } else {
                $status = 'Active';
                $statusClass = 'bg-green-100 text-green-800';
            }
            
            $domainData = [
                'domain' => $domain->domain,
                'team_id' => $domain->team_id,
                'created_at' => $domain->created_at->format('M d, Y H:i:s'),
                'expired' => $domain->expired ? Carbon::parse($domain->expired)->format('M d, Y') : null,
                'status' => $status,
                'status_class' => $statusClass,
            ];
            
            $ownerData = [
                'name' => $adminUser ? $adminUser->name : 'N/A',
                'email' => $adminUser ? $adminUser->email : 'N/A',
                'phone' => $adminUser ? $adminUser->phone : 'N/A',
                'address' => $adminUser ? $adminUser->address : 'N/A',
                'username' => $adminUser ? $adminUser->username : 'N/A',
            ];
            
            return response()->json([
                'success' => true,
                'domain' => $domainData,
                'owner' => $ownerData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found or error occurred.',
            ], 404);
        }
    }

    /**
     * Show the form for editing a vendor.
     */
    public function edit($id): View
    {
        $domain = Domain::with('team')->findOrFail($id);
        return view('superadmin.vendors.edit', compact('domain'));
    }

    /**
     * Update the specified vendor.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $domain = Domain::with('team')->findOrFail($id);

        $validated = $request->validate([
            'domain'       => 'required|string|max:255|unique:domains,domain,' . $id,
            'company_name' => 'required|string|max:255',
            'expired'      => 'nullable|date|after:today',
        ]);

        try {
            // Update domain
            $domain->update([
                'domain' => $validated['domain'],
                'expired' => $validated['expired'] ?? null,
            ]);

            // Update tenant brand name if it exists
            if ($domain->team) {
                $domain->team->update([
                    'brand' => $validated['company_name'],
                ]);
            }

            return redirect()->route('superadmin.vendors.index')
                ->with('success', 'Vendor updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Vendor Update Failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update vendor: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Reset password for vendor's admin user.
     */
    public function resetPassword(Request $request, $id): RedirectResponse
    {
        $domain = Domain::with('team')->findOrFail($id);
        
        if (!$domain->team) {
            return back()->withErrors(['error' => 'No tenant found for this domain.']);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Find admin user for this tenant
        $adminUser = User::where('team_id', $domain->team_id)
            ->where('is_admin', 1)
            ->first();

        if (!$adminUser) {
            return back()->withErrors(['error' => 'No admin user found for this vendor.']);
        }

        $adminUser->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password reset successfully for vendor admin.');
    }

    /**
     * Delete a vendor/domain.
     */
    public function destroy($id): RedirectResponse
    {
        $domain = Domain::findOrFail($id);
        $domainName = $domain->domain;
        $domain->delete();

        return back()->with('success', 'Vendor "' . $domainName . '" has been deleted successfully.');
    }

    /**
     * Update vendor status.
     */
    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:active,expired',
        ]);

        $domain = Domain::findOrFail($id);

        if ($request->status === 'active') {
            $domain->update(['expired' => null]);
        } else {
            $domain->update(['expired' => Carbon::now()]);
        }

        return back()->with('success', 'Vendor status updated successfully.');
    }

    /**
     * Generate auto-login link for vendor admin user.
     */
    public function generateAutoLoginLink($id)
    {
        $domain = Domain::with('team')->findOrFail($id);
        
        if (!$domain->team) {
            return response()->json(['error' => 'No tenant found for this domain.'], 404);
        }

        // Find admin user for this tenant
        $adminUser = User::where('team_id', $domain->team_id)
            ->where('is_admin', 1)
            ->first();

        if (!$adminUser) {
            return response()->json(['error' => 'No admin user found for this vendor.'], 404);
        }

        // Generate a secure token
        $token = Crypt::encryptString(json_encode([
            'user_id' => $adminUser->id,
            'expires_at' => now()->addMinutes(5)->timestamp, // Expiry time for security
        ]));

        // Generate the auto-login URL
        $autoLoginUrl = "http://{$domain->domain}:8000/authenticate?token={$token}";

        return response()->json([
            'auto_login_url' => $autoLoginUrl,
            'user_id' => $adminUser->id
        ]);
    }
}