@extends('superadmin.components.layout')

@section('title', 'Vendors')
@section('page-title', 'Vendors')

@section('content')
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">All Domains (Vendors)</h2>
                @if($searchQuery || $currentStatus !== 'all')
                    <span class="text-sm text-gray-600">Filtered: {{ $domains->total() }} domains</span>
                @else
                    <span class="text-sm text-gray-600">Total: {{ $domains->total() }} domains</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <!-- Export Button -->
                <div class="relative inline-block text-left">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm" id="export-menu-button" onclick="toggleExportMenu()">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                        <svg class="ml-2 -mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                    <div id="export-menu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1" role="menu">
                            <a href="{{ route('superadmin.vendors.export', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export to Excel
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <a href="{{ route('superadmin.vendors.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Vendor
                </a>
            </div>
        </div>

        <!-- Search and Date Filters -->
       <!-- Filter Box -->
<!-- Filter Box -->
<div class="mb-6 bg-white shadow rounded-lg p-6 border border-gray-200">

   <form method="GET" action="{{ route('superadmin.vendors.index') }}" id="searchForm">

    <input type="hidden" name="status" value="{{ $currentStatus }}" id="statusInput">

    <!-- ========================= -->
    <!-- ROW 1 : SEARCH + STATUS -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- Search Field -->
        <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
                <input type="text" 
                    id="searchInput"
                    name="search" 
                    value="{{ $searchQuery }}" 
                    placeholder="Search by domain, owner name..."
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    autocomplete="off">

                <div id="searchLoading" class="hidden absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Status Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status_filter" name="status"
                class="w-full px-3 py-2 border rounded-lg focus:ring-indigo-500">
                <option value="all" {{ $currentStatus === 'all' ? 'selected' : '' }}>All</option>
                <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                <option value="expiring_soon" {{ $currentStatus === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                <option value="expired" {{ $currentStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="trial" {{ $currentStatus === 'trial' ? 'selected' : '' }}>Trial</option>
            </select>
        </div>

    </div>

    <!-- ========================= -->
    <!-- ROW 2 : DATES + PAYMENT + PLAN + BUTTONS -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mt-4">

        <!-- Start Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                class="w-full px-3 py-2 border rounded-lg focus:ring-indigo-500">
        </div>

        <!-- End Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                class="w-full px-3 py-2 border rounded-lg focus:ring-indigo-500">
        </div>

        <!-- Payment Status -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
            <select name="payment_status" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>

        <!-- Plan Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Plan Type</label>
            <select name="plan_type" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Plans</option>
                <option value="monthly" {{ request('plan_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ request('plan_type') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                <option value="custom" {{ request('plan_type') === 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div>

        <!-- Apply Button -->
        <div class="flex items-end">
            <button type="submit"
                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow">
                Apply Filters
            </button>
        </div>

        <!-- Clear Button -->
        <div class="flex items-end">
            @if($searchQuery || $startDate || $endDate || $currentStatus !== 'all' || request('payment_status') || request('plan_type'))
                <a href="{{ route('superadmin.vendors.index') }}"
                   class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 shadow">
                    Clear
                </a>
            @endif
        </div>

    </div>

</form>


</div>



        <!-- Remove the old status filters section -->
        <!-- Status Filters -->
        <div class="mb-6 hidden">
            <div class="flex flex-wrap gap-2">
                @php
                    $filters = [
                        'all' => ['label' => 'All', 'color' => 'bg-indigo-600'],
                        'active' => ['label' => 'Active', 'color' => 'bg-green-600'],
                        'expiring_soon' => ['label' => 'Expiring Soon', 'color' => 'bg-yellow-600'],
                        'expired' => ['label' => 'Expired', 'color' => 'bg-red-600'],
                        'trial' => ['label' => 'Trial', 'color' => 'bg-blue-600'],
                    ];
                @endphp
                @foreach($filters as $key => $config)
                    <a href="{{ route('superadmin.vendors.index', array_merge(['status' => $key], $searchQuery ? ['search' => $searchQuery] : [], $startDate ? ['start_date' => $startDate] : [], $endDate ? ['end_date' => $endDate] : [])) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $currentStatus === $key ? $config['color'].' text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $config['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($domains->count() > 0)
            <div class="overflow-x-auto -mx-6 px-6" id="tableContainer">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                SR No
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Domain
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Owner Name
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Owner Email
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Owner Phone
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Owner Address
                            </th>
                            <!--<th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Tenant ID
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Tenant Name
                            </th>-->
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Created
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Expires
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($domains as $index => $domain)
                        @php
                            $expiryDate = $domain->expired ? \Carbon\Carbon::parse($domain->expired) : null;
                            $isExpired = $expiryDate && $expiryDate->isPast();
                            $isExpiringSoon = $domain->isExpiringSoon();
                            $isTrial = $domain->trial_ends_at && \Carbon\Carbon::parse($domain->trial_ends_at)->isFuture();
                            
                            // Determine status key for filtering
                            if ($isExpired) {
                                $statusKey = 'expired';
                            } elseif ($isExpiringSoon) {
                                $statusKey = 'expiring_soon';
                            } elseif ($isTrial) {
                                $statusKey = 'trial';
                            } else {
                                $statusKey = 'active';
                            }
                            
                            // Calculate serial number based on pagination
                            $currentPage = $domains->currentPage();
                            $perPage = $domains->perPage();
                            $serialNumber = ($currentPage - 1) * $perPage + $index + 1;
                        @endphp
                        <tr class="hover:bg-gray-50" data-domain-id="{{ $domain->id }}" data-status="{{ $statusKey }}">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $serialNumber }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                <div class="text-sm font-medium text-gray-900 max-w-xs truncate" title="{{ $domain->domain }}">{{ $domain->domain }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                @if($domain->adminUser && $domain->adminUser->isNotEmpty())
                                    <div class="max-w-xs truncate" title="{{ $domain->adminUser->first()->name }}">{{ $domain->adminUser->first()->name }}</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                @if($domain->adminUser && $domain->adminUser->isNotEmpty())
                                    <div class="max-w-xs truncate" title="{{ $domain->adminUser->first()->email ?? 'N/A' }}">{{ $domain->adminUser->first()->email ?? 'N/A' }}</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                @if($domain->adminUser && $domain->adminUser->isNotEmpty())
                                    <div class="max-w-xs truncate" title="{{ $domain->adminUser->first()->phone ?? 'N/A' }}">{{ $domain->adminUser->first()->phone ?? 'N/A' }}</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                @if($domain->adminUser && $domain->adminUser->isNotEmpty())
                                    <div class="max-w-xs truncate" title="{{ $domain->adminUser->first()->address ?? 'N/A' }}">{{ $domain->adminUser->first()->address ?? 'N/A' }}</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                           <!-- <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $domain->team_id }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                @if($domain->team)
                                    <div class="max-w-xs truncate" title="{{ $domain->team->data['name'] ?? 'N/A' }}">{{ $domain->team->data['name'] ?? 'N/A' }}</div>
                                @else
                                    <span class="text-red-500">No Tenant</span>
                                @endif
                            </td>-->
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $domain->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                @if($domain->expired)
                                    {{ $expiryDate->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">No Expiry</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($domain->expired)
                                    @if($isExpired)
                                        <span class="status-badge px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Expired
                                        </span>
                                    @else
                                        @php
                                            $expiringSoon = $domain->isExpiringSoon();
                                        @endphp
                                        @if($expiringSoon)
                                            <div class="flex flex-col">
                                                <span class="status-badge px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Expiring Soon
                                                </span>
                                            </div>
                                        @else
                                            <span class="status-badge px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @endif
                                    @endif
                                @elseif($domain->trial_ends_at && Carbon::parse($domain->trial_ends_at)->isFuture())
                                    <span class="status-badge px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Trial
                                    </span>
                                @else
                                    <span class="status-badge px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                <div class="relative inline-block text-left">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="action-menu-{{ $domain->id }}" onclick="toggleDropdown({{ $domain->id }})">
                                        Action
                                        <svg class="ml-2 -mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <div id="dropdown-menu-{{ $domain->id }}" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" style="position: absolute;">
                                        <div class="py-1" role="menu" aria-orientation="vertical">
                                            <a href="{{ route('superadmin.vendors.edit', $domain->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Edit</a>
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" onclick="viewVendorDetails({{ $domain->id }})">View Details</a>
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" onclick="changeStatus({{ $domain->id }})">Change Status</a>
                                            <a href="{{ route('superadmin.vendors.login-as', $domain->id) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            Login as Vendor
                                        </a>

                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" onclick="openResetPasswordModal({{ $domain->id }}, '{{ $domain->domain }}')">Reset Password</a>                                         
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4" id="paginationContainer">
                {{ $domains->links() }}
            </div>
        @else
            <div class="text-center py-12" id="emptyState">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No domains found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($searchQuery || $currentStatus !== 'all')
                        No domains match your search criteria. Try adjusting your filters or search term.
                    @else
                        There are no domains registered yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

{{-- Reset Password Modal --}}

<!-- jQuery UI CSS -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<!-- jQuery and jQuery UI JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize datepickers for start and end date
    $('#start_date, #end_date').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        yearRange: 'c-10:c+10',
        onSelect: function(dateText) {
            // Update the input value
            $(this).val(dateText);
        }
    });
    
    // Sync status filter dropdown with hidden input
    $('#status_filter').on('change', function() {
        $('#statusInput').val($(this).val());
        $('#searchForm').submit();
    });
    
    // Set initial value of status filter dropdown
    $('#status_filter').val($('#statusInput').val());
    
    // Sync both search inputs on page load
    const searchQuery = "{{$searchQuery}}";
    if (searchQuery) {
        if ($('#searchInput').length) $('#searchInput').val(searchQuery);
        if ($('#filter_search').length) $('#filter_search').val(searchQuery);
    }
});
</script>
<div id="resetPasswordModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Reset Password</h3>
            <button type="button" onclick="closeResetPasswordModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-sm text-gray-600 mb-4">Enter a new password for <span id="resetVendorName" class="font-medium text-gray-900"></span>.</p>
        <form id="resetPasswordForm" method="POST" class="space-y-4" onsubmit="return validatePasswords()">
            @csrf
            <div>
                <label for="newPassword" class="block text-sm font-medium text-gray-700">New Password</label>
                <div class="relative mt-1">
                    <input type="password" id="newPassword" name="password" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10" required minlength="8" placeholder="Enter new password">
                    <button type="button" onclick="toggleResetPasswordVisibility('newPassword')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="w-5 h-5 eye-slash-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <div class="relative mt-1">
                    <input type="password" id="confirmPassword" name="password_confirmation" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-10" required minlength="8" placeholder="Confirm new password">
                    <button type="button" onclick="toggleResetPasswordVisibility('confirmPassword')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="w-5 h-5 eye-slash-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Vendor Details Modal -->
<div id="vendorDetailsModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 max-h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4 flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-900">Vendor Details</h3>
            <button type="button" onclick="closeVendorDetailsModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="vendorDetailsContent" class="overflow-y-auto flex-grow">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById('dropdown-menu-' + id);
    const isHidden = dropdown.classList.contains('hidden');
    
    // Close all other dropdowns
    document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => {
        el.classList.add('hidden');
    });
    
    // Toggle current dropdown
    if (isHidden) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[id^="action-menu-"]') && !event.target.closest('[id^="dropdown-menu-"]')) {
        document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => {
            el.classList.add('hidden');
        });
    }
});

function changeStatus(id) {
    const row = document.querySelector(`tr[data-domain-id="${id}"]`);
    const currentStatus = row?.dataset.status ?? 'active';
    const isExpired = currentStatus === 'expired';
    const newStatus = isExpired ? 'active' : 'expired';
    const label = isExpired ? 'activate' : 'mark as expired';
    
    if (confirm(`Are you sure you want to ${label} this vendor?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("superadmin.vendors.update-status", ":id") }}'.replace(':id', id);
        form.innerHTML = `
            @csrf
            @method('POST')
            <input type="hidden" name="status" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function openResetPasswordModal(id, domain) {
    const modal = document.getElementById('resetPasswordModal');
    const form = document.getElementById('resetPasswordForm');
    const nameSpan = document.getElementById('resetVendorName');
    const route = '{{ route("superadmin.vendors.reset-password", ":id") }}'.replace(':id', id);

    form.action = route;
    form.reset();
    nameSpan.textContent = domain;
    modal.classList.remove('hidden');
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}

function deleteVendor(id, domain) {
    if (confirm('Are you sure you want to delete vendor "' + domain + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("superadmin.vendors.destroy", ":id") }}'.replace(':id', id);
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function toggleResetPasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    const eyeIcon = input.parentNode.querySelector('.eye-icon');
    const eyeSlashIcon = input.parentNode.querySelector('.eye-slash-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeSlashIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeSlashIcon.classList.add('hidden');
    }
}

function validatePasswords() {
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return false;
    }
    
    return true;
}

function viewVendorDetails(id) {
    // Show loading state
    const modal = document.getElementById('vendorDetailsModal');
    const content = document.getElementById('vendorDetailsContent');
    content.innerHTML = `
        <div class="flex justify-center items-center h-32">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    modal.classList.remove('hidden');

    // Fetch vendor details via AJAX
    fetch('{{ route("superadmin.vendors.show", ":id") }}'.replace(':id', id))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pr-2">
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Domain Information</h4>
                            <div class="space-y-2">
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Domain:</span>
                                    <span class="text-gray-900 break-words">${data.domain.domain}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Tenant ID:</span>
                                    <span class="text-gray-900">${data.domain.team_id}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Created:</span>
                                    <span class="text-gray-900">${data.domain.created_at}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Expires:</span>
                                    <span class="text-gray-900">${data.domain.expired || 'No Expiry'}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Status:</span>
                                    <span class="text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                            data.domain.status_class
                                        }">
                                            ${data.domain.status}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Owner Information</h4>
                            <div class="space-y-2">
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Name:</span>
                                    <span class="text-gray-900 break-words">${data.owner.name || 'N/A'}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Email:</span>
                                    <span class="text-gray-900 break-words">${data.owner.email || 'N/A'}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Phone:</span>
                                    <span class="text-gray-900 break-words">${data.owner.phone || 'N/A'}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Address:</span>
                                    <span class="text-gray-900 break-words">${data.owner.address || 'N/A'}</span>
                                </div>
                                <div class="flex">
                                    <span class="font-medium text-gray-700 w-32">Username:</span>
                                    <span class="text-gray-900 break-words">${data.owner.username || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" onclick="closeVendorDetailsModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Close</button>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="text-center py-4">
                        <p class="text-red-600">Error loading vendor details: ${data.message || 'Unknown error'}</p>
                        <div class="mt-4">
                            <button type="button" onclick="closeVendorDetailsModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Close</button>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-600">Error loading vendor details. Please try again.</p>
                    <div class="mt-4">
                        <button type="button" onclick="closeVendorDetailsModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Close</button>
                    </div>
                </div>
            `;
        });
}

function closeVendorDetailsModal() {
    document.getElementById('vendorDetailsModal').classList.add('hidden');
}

// Auto-login functionality
document.addEventListener('click', function(event) {
    // Handle auto-login button click
    if (event.target.classList.contains('auto-login-btn')) {
        const domainId = event.target.getAttribute('data-domain-id');
        const domainName = event.target.getAttribute('data-domain-name');
        
        // Show loading state
        event.target.innerHTML = '<svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';
        event.target.disabled = true;
        
        // Make AJAX request to generate auto-login link
        fetch('{{ route("superadmin.vendors.auto-login-link", ":id") }}'.replace(':id', domainId))
            .then(response => response.json())
            .then(data => {
                if (data.auto_login_url) {
                    // Show the auto-login link container
                    const container = document.getElementById('auto-login-container-' + domainId);
                    const urlInput = document.getElementById('auto-login-url-' + domainId);
                    const openLink = document.getElementById('open-link-' + domainId);
                    
                    urlInput.value = data.auto_login_url;
                    openLink.href = data.auto_login_url;
                    container.classList.remove('hidden');
                    
                    // Automatically copy the link to clipboard
                    urlInput.select();
                    document.execCommand('copy');
                    
                    // Show feedback to user that link was copied
                    const originalBtnText = event.target.innerHTML;
                    event.target.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Link Copied!';
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        event.target.innerHTML = 'Regenerate Link';
                        event.target.disabled = false;
                    }, 2000);
                } else {
                    alert('Error generating auto-login link: ' + (data.error || 'Unknown error'));
                    event.target.innerHTML = 'Generate Link';
                    event.target.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error generating auto-login link');
                event.target.innerHTML = 'Generate Link';
                event.target.disabled = false;
            });
    }
    
    // Handle copy link button click
    if (event.target.classList.contains('copy-link-btn')) {
        const domainId = event.target.getAttribute('data-domain-id');
        const urlInput = document.getElementById('auto-login-url-' + domainId);
        
        urlInput.select();
        document.execCommand('copy');
        
        // Show feedback
        const originalText = event.target.innerHTML;
        event.target.innerHTML = '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Copied!';
        
        setTimeout(() => {
            event.target.innerHTML = originalText;
        }, 2000);
    }
});

// Live Search Functionality with AJAX
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const filterSearchInput = document.getElementById('filter_search');
const searchForm = document.getElementById('searchForm');
const searchLoading = document.getElementById('searchLoading');
const statusInput = document.getElementById('statusInput');

// Sync both search inputs
if (searchInput && filterSearchInput) {
    // Update filter search when main search changes
    searchInput.addEventListener('input', function(e) {
        filterSearchInput.value = searchInput.value;
        clearTimeout(searchTimeout);
        
        // Auto-search after 500ms of no typing
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 500);
    });
    
    // Update main search when filter search changes
    filterSearchInput.addEventListener('input', function(e) {
        searchInput.value = filterSearchInput.value;
        clearTimeout(searchTimeout);
        
        // Auto-search after 500ms of no typing
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 500);
    });
    
    // Also search on Enter key for both inputs
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();
        }
    });
    
    filterSearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();
        }
    });
} else if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        
        // Auto-search after 500ms of no typing
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 500);
    });
    
    // Also search on Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();
        }
    });
}

function performSearch() {
    const searchQuery = searchInput ? searchInput.value : (filterSearchInput ? filterSearchInput.value : '');
    const status = statusInput.value;
    const startDate = document.getElementById('start_date')?.value;
    const endDate = document.getElementById('end_date')?.value;
    
    // Show loading indicator
    if (searchLoading) {
        searchLoading.classList.remove('hidden');
    }
    
    // Build URL with parameters
    const url = new URL('{{ route("superadmin.vendors.index") }}');
    if (searchQuery) url.searchParams.append('search', searchQuery);
    if (status) url.searchParams.append('status', status);
    if (startDate) url.searchParams.append('start_date', startDate);
    if (endDate) url.searchParams.append('end_date', endDate);
    url.searchParams.append('ajax', '1');
    
    // Fetch results
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Create a temporary container
        const temp = document.createElement('div');
        temp.innerHTML = html;
        
        // Update table container
        const newTable = temp.querySelector('#tableContainer');
        const currentTable = document.getElementById('tableContainer');
        if (newTable && currentTable) {
            currentTable.innerHTML = newTable.innerHTML;
        }
        
        // Update pagination
        const newPagination = temp.querySelector('#paginationContainer');
        const currentPagination = document.getElementById('paginationContainer');
        if (newPagination && currentPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        }
        
        // Update empty state if present
        const newEmptyState = temp.querySelector('#emptyState');
        const currentEmptyState = document.getElementById('emptyState');
        if (newEmptyState) {
            if (currentTable) currentTable.classList.add('hidden');
            if (currentPagination) currentPagination.classList.add('hidden');
            if (currentEmptyState) {
                currentEmptyState.classList.remove('hidden');
                currentEmptyState.innerHTML = newEmptyState.innerHTML;
            }
        } else {
            if (currentTable) currentTable.classList.remove('hidden');
            if (currentPagination) currentPagination.classList.remove('hidden');
            if (currentEmptyState) currentEmptyState.classList.add('hidden');
        }
        
        // Update URL without reload
        window.history.pushState({}, '', url.toString().replace('&ajax=1', ''));
        
        // Show/hide clear button
        updateClearButton(searchQuery);
    })
    .catch(error => {
        console.error('Search error:', error);
    })
    .finally(() => {
        // Hide loading indicator
        if (searchLoading) {
            searchLoading.classList.add('hidden');
        }
    });
}

function updateClearButton(searchQuery) {
    const clearBtn = document.getElementById('clearBtn');
    const searchButton = searchForm.querySelector('button[type="submit"]');
    
    if (searchQuery && searchQuery.trim() !== '') {
        if (!clearBtn) {
            // Create clear button
            const newClearBtn = document.createElement('a');
            newClearBtn.href = '{{ route("superadmin.vendors.index") }}?status=' + statusInput.value;
            newClearBtn.id = 'clearBtn';
            newClearBtn.className = 'px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors shadow-sm';
            newClearBtn.textContent = 'Clear';
            newClearBtn.onclick = function(e) {
                e.preventDefault();
                if (searchInput) searchInput.value = '';
                if (filterSearchInput) filterSearchInput.value = '';
                performSearch();
            };
            searchButton.parentNode.insertBefore(newClearBtn, searchButton.nextSibling);
        }
    } else {
        if (clearBtn) {
            clearBtn.remove();
        }
    }
}

function toggleExportMenu() {
    const menu = document.getElementById('export-menu');
    const isHidden = menu.classList.contains('hidden');
    
    // Close all other dropdowns
    document.querySelectorAll('[id^="dropdown-menu-"]').forEach(el => {
        el.classList.add('hidden');
    });
    
    // Toggle export menu
    if (isHidden) {
        menu.classList.remove('hidden');
    } else {
        menu.classList.add('hidden');
    }
}

// Close export menu when clicking outside
document.addEventListener('click', function(event) {
    const exportMenu = document.getElementById('export-menu');
    const exportButton = document.getElementById('export-menu-button');
    
    if (exportMenu && exportButton && !exportButton.contains(event.target) && !exportMenu.contains(event.target)) {
        exportMenu.classList.add('hidden');
    }
});
</script>
@endsection