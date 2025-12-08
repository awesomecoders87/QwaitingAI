@extends('superadmin.components.layout')

@section('title', 'Edit Vendor')
@section('page-title', 'Edit Vendor')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('superadmin.vendors.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Vendors
        </a>
    </div>

    <!-- Edit Vendor Form -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <div class="px-8 py-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Edit Vendor</h2>
            <p class="mt-1 text-sm text-gray-600">Update vendor domain and company information.</p>
        </div>

        <form method="POST" action="{{ route('superadmin.vendors.update', $domain->id) }}" class="p-8">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-red-800">Please fix the following errors:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-6">
                <!-- Vendor Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Information</h3>
                    
                    <!-- Tenant ID (Read-only) -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tenant ID</label>
                        <input 
                            type="text" 
                            value="{{ $domain->team_id }}"
                            class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm cursor-not-allowed"
                            disabled
                            readonly>
                        <p class="mt-1 text-xs text-gray-500">Tenant ID cannot be modified</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-2">Company Name <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="company_name" 
                                id="company_name" 
                                value="{{ old('company_name', $domain->team->brand ?? $domain->team->name ?? '') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('company_name') border-red-500 @enderror"
                                required>
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="domain" class="block text-sm font-semibold text-gray-700 mb-2">Domain Name <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="domain" 
                                id="domain" 
                                value="{{ old('domain', $domain->domain) }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('domain') border-red-500 @enderror"
                                required>
                            <p class="mt-1 text-xs text-gray-500">Full domain name (e.g., subdomain.{{ env('PARENT_DOMAIN') }})</p>
                            @error('domain')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Expiry Configuration -->
                <div class="pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Expiry Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="expired" class="block text-sm font-semibold text-gray-700 mb-2">Expiry Date</label>
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        name="expired" 
                                        id="expired" 
                                        value="{{ old('expired', $domain->expired ? \Carbon\Carbon::parse($domain->expired)->format('Y-m-d') : '') }}"
                                        placeholder="Select future date"
                                        readonly
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 cursor-pointer bg-white @error('expired') border-red-500 @enderror">
                                </div>
                                <button 
                                    type="button" 
                                    id="clearExpiry"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                                    Clear
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Click to select a future date. Only dates from tomorrow onwards are allowed.</p>
                            @error('expired')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Current Status</label>
                            <div class="mt-2">
                                @php
                                    $expiryDate = $domain->expired ? \Carbon\Carbon::parse($domain->expired) : null;
                                    $isExpired = $expiryDate && $expiryDate->isPast();
                                @endphp
                                
                                @if($domain->expired)
                                    @if($isExpired)
                                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold bg-red-100 text-red-800">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                            Expired on {{ $expiryDate->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold bg-green-100 text-green-800">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Active until {{ $expiryDate->format('M d, Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold bg-blue-100 text-blue-800">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        No Expiration
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold">Important Notes:</p>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                <li>The Tenant ID cannot be modified to maintain data integrity</li>
                                <li>Domain changes will affect the vendor's access URL</li>
                                <li>Expiry dates can only be set to future dates</li>
                                <li>Clear the expiry date to remove expiration</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('superadmin.vendors.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-200 border border-transparent rounded-lg font-semibold text-sm text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Vendor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery UI CSS -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<!-- jQuery and jQuery UI JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize datepicker for expiry date
    $('#expired').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: '+1d', // Tomorrow onwards
        maxDate: '+10y', // Up to 10 years in the future
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        yearRange: 'c:c+10',
        beforeShowDay: function(date) {
            // Only allow dates from tomorrow onwards
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return [date > today];
        },
        onSelect: function(dateText) {
            // Update the input value
            $(this).val(dateText);
        }
    });

    // Add a clear button functionality
    $('#clearExpiry').on('click', function(e) {
        e.preventDefault();
        $('#expired').val('');
    });
});
</script>

<style>
/* Custom datepicker styling */
.ui-datepicker {
    font-family: inherit;
    font-size: 14px;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.ui-datepicker-header {
    background: #4f46e5;
    color: white;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.5rem;
}

.ui-datepicker-title {
    color: white;
    font-weight: 600;
}

.ui-datepicker-prev, 
.ui-datepicker-next {
    cursor: pointer;
}

.ui-datepicker-prev:hover, 
.ui-datepicker-next:hover {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.25rem;
}

.ui-state-default {
    text-align: center;
    padding: 0.5rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
}

.ui-state-default:hover {
    background: #e0e7ff;
    border-color: #4f46e5;
}

.ui-state-active,
.ui-state-highlight {
    background: #4f46e5 !important;
    color: white !important;
    border-color: #4f46e5 !important;
}

.ui-state-disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.ui-datepicker td {
    padding: 2px;
}

.ui-datepicker-buttonpane {
    border-top: 1px solid #e5e7eb;
    padding: 0.5rem;
}

.ui-datepicker-buttonpane button {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    background: white;
    cursor: pointer;
}

.ui-datepicker-buttonpane button:hover {
    background: #f3f4f6;
}
</style>
@endsection
