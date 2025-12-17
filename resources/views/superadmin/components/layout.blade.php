<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SuperAdmin') - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex overflow-hidden">
        <!-- Sidebar -->
        @include('superadmin.components.sidebar')
        
        <!-- Main Content -->
        <div id="main-content" class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto bg-transparent dark:bg-gray-900 transition-all duration-300 ease-in-out">
            <!-- Top Green Bar -->
            <div class="h-1 bg-green-400"></div>
            
            <!-- Top Navigation -->
            <nav class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-14 sm:h-16">
                        <!-- Left Side: Hamburger Menu + Logo -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <!-- Hamburger Menu Button (All Screens) -->
                            <button onclick="toggleSidebar()" class="p-2 rounded-md border-2 border-orange-500 text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                            
                            <!-- Logo -->
                            <div class="flex items-center flex-shrink-0">
                                <a href="{{ route('superadmin.dashboard') }}" class="flex items-center">
                                    <img src="/images/logo/superadmin/qwaiting-logo.svg" alt="Qwaiting Logo" class="h-8 sm:h-10 w-auto">
                                </a>
                            </div>
                        </div>
                        
                        <!-- Right Side: User -->
                        <div class="flex items-center gap-2 sm:gap-4">
                            <!-- User Profile -->
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-200 border-2 border-gray-300 flex items-center justify-center">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <span class="hidden sm:inline text-sm sm:text-base font-medium text-gray-700 uppercase">ADM</span>
                                
                                <!-- User Dropdown Menu -->
                                <div class="relative">
                                    <button onclick="toggleUserMenu()" class="p-1 text-gray-600 hover:text-gray-900 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                        <div class="px-4 py-2 border-b border-gray-200">
                                            <p class="text-sm font-medium text-gray-900">{{ Auth::guard('superadmin')->user()->name }}</p>
                                            <p class="text-xs text-gray-500">{{ Auth::guard('superadmin')->user()->email }}</p>
                                        </div>
                                        <a href="{{ route('superadmin.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                        <form method="POST" action="{{ route('superadmin.logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-md" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Status Message -->
                    @if (session('status'))
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-800">{{ session('status') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-md" role="alert">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">There were some errors:</h3>
                                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <script>
        let sidebarOpen = window.innerWidth >= 1024; // Open on desktop, closed on mobile by default
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebarOpen = !sidebarOpen;
            
            if (window.innerWidth >= 1024) {
                // Desktop: Collapse/Expand sidebar width
                if (sidebarOpen) {
                    sidebar.style.width = '256px';
                    sidebar.style.minWidth = '256px';
                } else {
                    sidebar.style.width = '0';
                    sidebar.style.minWidth = '0';
                }
            } else {
                // Mobile: Slide in/out
                if (sidebarOpen) {
                    sidebar.style.transform = 'translateX(0)';
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.style.transform = 'translateX(-100%)';
                    overlay.classList.add('hidden');
                }
            }
        }
        
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        }
        
        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu');
            const userButton = event.target.closest('[onclick="toggleUserMenu()"]');
            
            if (userMenu && !userMenu.contains(event.target) && !userButton) {
                userMenu.classList.add('hidden');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth >= 1024) {
                // Desktop: Show sidebar
                sidebar.style.position = 'static';
                sidebar.style.width = sidebarOpen ? '256px' : '0';
                sidebar.style.minWidth = sidebarOpen ? '256px' : '0';
                sidebar.style.transform = 'translateX(0)';
                overlay.classList.add('hidden');
            } else {
                // Mobile: Hide sidebar
                sidebar.style.position = 'fixed';
                sidebar.style.width = '256px';
                sidebar.style.minWidth = '256px';
                sidebar.style.transform = sidebarOpen ? 'translateX(0)' : 'translateX(-100%)';
                if (sidebarOpen) {
                    overlay.classList.remove('hidden');
                } else {
                    overlay.classList.add('hidden');
                }
            }
        });
        
        // Initialize sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (window.innerWidth >= 1024) {
                // Desktop: Show sidebar by default
                sidebar.style.position = 'static';
                sidebar.style.width = '256px';
                sidebar.style.minWidth = '256px';
                sidebar.style.transform = 'translateX(0)';
                sidebarOpen = true;
            } else {
                // Mobile: Hide sidebar by default
                sidebar.style.position = 'fixed';
                sidebar.style.width = '256px';
                sidebar.style.minWidth = '256px';
                sidebar.style.transform = 'translateX(-100%)';
                sidebarOpen = false;
                overlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>

