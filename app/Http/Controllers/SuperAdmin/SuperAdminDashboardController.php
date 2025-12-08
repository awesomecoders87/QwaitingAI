<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    /**
     * Display the superadmin dashboard.
     */
    public function index(): View
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Total Vendors (Total Domains)
        $totalVendors = Domain::count();

        // Join This Month (Domains created this month)
        $joinThisMonth = Domain::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Expired This Month (Domains that expired this month)
        $expiredThisMonth = Domain::whereNotNull('expired')
            ->whereMonth('expired', $now->month)
            ->whereYear('expired', $now->year)
            ->where('expired', '<=', $now)
            ->count();

        // Active Vendors (not expired or no expiry date)
        $activeVendors = Domain::where(function($query) use ($now) {
            $query->whereNull('expired')
                ->orWhere('expired', '>', $now);
        })->count();

        // Expired Vendors (currently expired)
        $expiredVendors = Domain::whereNotNull('expired')
            ->where('expired', '<=', $now)
            ->count();

        // Expiring Soon Vendors (expires within next 7 days or expired within last week)
        $expiringSoonVendors = Domain::whereNotNull('expired')
            ->where(function($query) use ($now) {
                // Expires within the next 7 days
                $query->whereBetween('expired', [$now, $now->copy()->addDays(7)])
                      // OR expired within the last week (1 week ago to now)
                      ->orWhereBetween('expired', [$now->copy()->subDays(7), $now]);
            })
            ->count();

        // Monthly vendor registrations for the last 6 months
        $monthlyData = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            $count = Domain::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $monthlyData[] = $count;
            $monthLabels[] = $monthStart->format('M Y');
        }

        // Vendor status distribution for pie chart
        $statusDistribution = [
            'active' => $activeVendors,
            'expiring_soon' => $expiringSoonVendors,
            'expired' => $expiredVendors,
        ];

        $stats = [
            'total_vendors' => $totalVendors,
            'join_this_month' => $joinThisMonth,
            'expired_this_month' => $expiredThisMonth,
            'active_vendors' => $activeVendors,
            'expired_vendors' => $expiredVendors,
            'expiring_soon_vendors' => $expiringSoonVendors,
        ];

        $chartData = [
            'monthly_labels' => $monthLabels,
            'monthly_data' => $monthlyData,
            'status_distribution' => $statusDistribution,
        ];

        return view('superadmin.dashboard', compact('stats', 'chartData'));
    }
}
