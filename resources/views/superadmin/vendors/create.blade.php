@extends('superadmin.components.layout')

@section('title', 'Create Vendor')
@section('page-title', 'Create New Vendor')

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

    <!-- Create Vendor Form -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <div class="px-8 py-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Create New Vendor</h2>
            <p class="mt-1 text-sm text-gray-600">Fill in the details below to create a new vendor domain and admin account.</p>
        </div>

        <form method="POST" action="{{ route('superadmin.vendors.store') }}" class="p-8">
            @csrf

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
                <!-- Company Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Company Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-2">Company Name <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="company_name" 
                                id="company_name" 
                                value="{{ old('company_name') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('company_name') border-red-500 @enderror"
                                required>
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="domain" class="block text-sm font-semibold text-gray-700 mb-2">Domain Name <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    name="domain" 
                                    id="domain" 
                                    value="{{ old('domain') }}"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pr-32 @error('domain') border-red-500 @enderror"
                                    placeholder="mycompany"
                                    required>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-500 pointer-events-none">.{{ env('PARENT_DOMAIN') }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Only lowercase letters, numbers, and hyphens allowed</p>
                            @error('domain')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Admin Account Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Account Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fullname" class="block text-sm font-semibold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="fullname" 
                                id="fullname" 
                                value="{{ old('fullname') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('fullname') border-red-500 @enderror"
                                required>
                            @error('fullname')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                value="{{ old('email') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                                required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone_code" class="block text-sm font-semibold text-gray-700 mb-2">Phone Code</label>
                            <input 
                                type="text" 
                                name="phone_code" 
                                id="phone_code" 
                                value="{{ old('phone_code', '+1') }}"
                                placeholder="+1"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                            <input 
                                type="text" 
                                name="phone" 
                                id="phone" 
                                value="{{ old('phone') }}"
                                placeholder="1234567890"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Trial Configuration -->
                <div class="pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Trial Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="trial_days" class="block text-sm font-semibold text-gray-700 mb-2">Trial Days</label>
                            <input 
                                type="number" 
                                name="trial_days" 
                                id="trial_days" 
                                value="{{ old('trial_days', 14) }}"
                                min="0"
                                max="365"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Set to 0 for no expiration. Default is 14 days.</p>
                        </div>
                    </div>
                </div>

                <!-- Information Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold">Default Credentials:</p>
                            <p class="mt-1">A username will be generated automatically from the full name.</p>
                            <p>Default password: <code class="bg-blue-100 px-2 py-1 rounded">Password@123</code></p>
                            <p class="mt-2">A welcome email will be sent to the admin with login credentials.</p>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Vendor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
