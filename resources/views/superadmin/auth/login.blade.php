<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SuperAdmin Login - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-6xl flex rounded-2xl overflow-hidden shadow-2xl">
            <!-- Left Section - Branding -->
            <div class="hidden lg:flex lg:w-1/2 bg-gray-100 flex-col justify-center px-12 py-16">
                <div class="w-full">
                    <!-- Logo -->
                    <div class="mb-10">
                        <img src="/images/logo/superadmin/qwaiting-logo.svg" alt="Qwaiting Logo" class="h-14 w-auto">
                    </div>
                    
                    <!-- Welcome Title -->
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">Welcome to QWaitin</h1>
                    
                    <!-- Description -->
                    <p class="text-lg text-gray-600 mb-10">Efficient queue management for seamless customer experiences.</p>
                    
                    <!-- Features List -->
                    <div class="space-y-5">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 text-base">Easy appointment scheduling</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 text-base">Real-time queue tracking</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700 text-base">Seamless customer engagement</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section - Login Form -->
            <div class="w-full lg:w-1/2 bg-indigo-900 flex flex-col justify-center px-8 sm:px-12 py-12 lg:py-16">
                <div class="w-full max-w-md mx-auto">
                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-6 bg-white border-2 border-orange-500 rounded-md p-4">
                            <p class="text-sm font-medium text-gray-900">{{ session('status') }}</p>
                        </div>
                    @endif

                    <!-- Title -->
                    <h2 class="text-3xl font-bold text-white mb-8">Superadmin Login</h2>

                    <form method="POST" action="{{ route('superadmin.login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-6">
                            <label for="email" class="block font-medium text-sm text-white mb-2">Email</label>
                            <input id="email" class="block w-full px-4 py-3 rounded-md border-0 bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm sm:text-base" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Enter your email" />
                            @error('email')
                                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-6">
                            <label for="password" class="block font-medium text-sm text-white mb-2">Password</label>
                            <div class="relative">
                                <input id="password" class="block w-full px-4 py-3 pr-12 rounded-md border-0 bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm sm:text-base" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password" />
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors" aria-label="Toggle password visibility">
                                    <!-- Eye icon (visible) -->
                                    <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <!-- Eye slash icon (hidden) -->
                                    <svg id="eyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0A9.97 9.97 0 015.12 5.12m3.17 3.17L12 12m0 0l3.29 3.29M12 12l3.29-3.29m0 0a9.97 9.97 0 012.12-2.12m-3.17 3.17L12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me and Forgot Password -->
                        <div class="flex items-center justify-between mb-6">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                                <input id="remember_me" type="checkbox" class="rounded border-gray-300 bg-white text-indigo-600 focus:ring-indigo-500 w-4 h-4" name="remember">
                                <span class="ms-2 text-sm text-white">Remember me</span>
                            </label>
                            <a class="text-sm text-white hover:text-indigo-200 underline transition-colors" href="{{ route('superadmin.password.request') }}">
                                Forgot Password?
                            </a>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="w-full bg-white hover:bg-gray-50 text-indigo-900 font-semibold py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900 transition ease-in-out duration-150">
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Toggle icon visibility
                    if (type === 'text') {
                        eyeIcon.classList.add('hidden');
                        eyeSlashIcon.classList.remove('hidden');
                    } else {
                        eyeIcon.classList.remove('hidden');
                        eyeSlashIcon.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>
