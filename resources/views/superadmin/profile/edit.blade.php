@extends('superadmin.components.layout')

@section('title', 'Profile')
@section('page-title', 'SuperAdmin Profile')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Profile Card -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-8">
            <!-- Header with Edit Button -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Profile Information</h2>
                    <p class="mt-1 text-sm text-gray-600">View and manage your account details</p>
                </div>
                <button 
                    id="editProfileBtn" 
                    onclick="toggleEditMode()" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span id="editBtnText">Edit Profile</span>
                </button>
            </div>

            <!-- View Mode -->
            <div id="viewMode">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border-l-4 border-indigo-500 pl-4 py-2">
                        <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Name</label>
                        <p class="text-lg text-gray-900 font-medium">{{ $superadmin->name }}</p>
                    </div>
                    <div class="border-l-4 border-indigo-500 pl-4 py-2">
                        <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</label>
                        <p class="text-lg text-gray-900 font-medium">{{ $superadmin->email }}</p>
                    </div>
                    <div class="border-l-4 border-green-500 pl-4 py-2">
                        <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                        <p class="text-lg">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $superadmin->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="4"></circle>
                                </svg>
                                {{ $superadmin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <label class="block text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Member Since</label>
                        <p class="text-lg text-gray-900 font-medium">{{ $superadmin->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Edit Mode -->
            <div id="editMode" class="hidden">
                <form method="post" action="{{ route('superadmin.profile.update') }}" class="space-y-6">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                            <input 
                                id="name" 
                                name="name" 
                                type="text" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3" 
                                value="{{ old('name', $superadmin->name) }}" 
                                required 
                                autofocus 
                                autocomplete="name" />
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg py-3" 
                                value="{{ old('email', $superadmin->email) }}" 
                                required 
                                autocomplete="username" />
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t">
                        <button 
                            type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Changes
                        </button>
                        <button 
                            type="button" 
                            onclick="toggleEditMode()" 
                            class="inline-flex items-center px-6 py-3 bg-gray-200 border border-transparent rounded-lg font-semibold text-sm text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p
                                x-data="{ show: true }"
                                x-show="show"
                                x-transition
                                x-init="setTimeout(() => show = false, 3000)"
                                class="text-sm font-semibold text-green-600 flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Saved successfully!
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Section -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Change Password</h2>
                    <p class="mt-1 text-sm text-gray-600">Ensure your account is using a long, random password to stay secure</p>
                </div>
                <button 
                    id="editPasswordBtn" 
                    onclick="togglePasswordEditMode()" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span id="passwordBtnText">Change Password</span>
                </button>
            </div>

            <!-- Password View Mode -->
            <div id="passwordViewMode">
                <div class="border-l-4 border-purple-500 pl-4 py-3 bg-gray-50 rounded-r-lg">
                    <p class="text-gray-600 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Your password is securely encrypted. Click "Change Password" to update it.
                    </p>
                </div>
            </div>

            <!-- Password Edit Mode -->
            <div id="passwordEditMode" class="hidden">
                @include('superadmin.profile.partials.update-password-form')
            </div>
        </div>
    </div>
</div>

<script>
function toggleEditMode() {
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    const editBtn = document.getElementById('editProfileBtn');
    const btnText = document.getElementById('editBtnText');
    
    if (viewMode.classList.contains('hidden')) {
        // Switch to view mode
        viewMode.classList.remove('hidden');
        editMode.classList.add('hidden');
        btnText.textContent = 'Edit Profile';
        editBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        editBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    } else {
        // Switch to edit mode
        viewMode.classList.add('hidden');
        editMode.classList.remove('hidden');
        btnText.textContent = 'View Mode';
        editBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        editBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
    }
}

function togglePasswordEditMode() {
    const viewMode = document.getElementById('passwordViewMode');
    const editMode = document.getElementById('passwordEditMode');
    const editBtn = document.getElementById('editPasswordBtn');
    const btnText = document.getElementById('passwordBtnText');
    
    if (viewMode.classList.contains('hidden')) {
        // Switch to view mode
        viewMode.classList.remove('hidden');
        editMode.classList.add('hidden');
        btnText.textContent = 'Change Password';
        editBtn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        editBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    } else {
        // Switch to edit mode
        viewMode.classList.add('hidden');
        editMode.classList.remove('hidden');
        btnText.textContent = 'Cancel';
        editBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
        editBtn.classList.add('bg-gray-600', 'hover:bg-gray-700');
    }
}
</script>
@endsection

