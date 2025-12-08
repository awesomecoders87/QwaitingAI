<aside class="w-64 bg-white border-r border-gray-200 text-gray-800 flex flex-col min-h-screen shadow-sm">
    <div class="flex-1 p-4">
        <!-- Logo -->
        <div class="mb-8 flex items-center justify-center py-6">
            <a href="{{ route('superadmin.dashboard') }}" class="flex items-center">
                <img src="/images/logo/superadmin/qwaiting-logo.svg" alt="Qwaiting Logo" class="h-12 w-auto">
            </a>
        </div>
        
        <!-- SuperAdmin Title -->
        <div class="mb-6 pb-4 border-b border-gray-200">
            <h2 class="text-sm font-bold text-center text-gray-500 uppercase tracking-wider">SuperAdmin Panel</h2>
        </div>
        
        <!-- Navigation -->
        <nav class="space-y-1">
            <a href="{{ route('superadmin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('superadmin.dashboard') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('superadmin.vendors.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('superadmin.vendors.*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Vendors
            </a>
            <a href="{{ route('superadmin.profile.edit') }}" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('superadmin.profile.*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Profile
            </a>
        </nav>
    </div>
</aside>

