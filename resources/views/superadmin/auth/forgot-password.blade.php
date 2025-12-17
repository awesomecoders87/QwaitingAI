<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - SuperAdmin - {{ config('app.name', 'Laravel') }}</title>
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
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">Welcome to QWaiting</h1>
                    
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

            <!-- Right Section - Forgot Password Form -->
            <div class="w-full lg:w-1/2 bg-indigo-900 flex flex-col justify-center px-8 sm:px-12 py-12 lg:py-16">
                <div class="w-full max-w-md mx-auto">
                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-6 bg-white border-2 border-orange-500 rounded-md p-4">
                            <p class="text-sm font-medium text-gray-900">{{ session('status') }}</p>
                        </div>
                    @endif

                    <!-- Title -->
                    <h2 class="text-3xl font-bold text-white mb-2">Forgot Password</h2>
                    <p class="text-sm text-gray-300 mb-8">Enter your email to receive a password reset link</p>

                    <form method="POST" action="{{ route('superadmin.password.email') }}">
                        @csrf

                        <!-- Email Address -->
                        <div class="mb-6">
                            <label for="email" class="block font-medium text-sm text-white mb-2">Email</label>
                            <input id="email" class="block w-full px-4 py-3 rounded-md border-0 bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm sm:text-base" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Enter your email" />
                            @error('email')
                                <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Back to Login and Submit Button -->
                        <div class="flex items-center justify-between mb-6">
                            <a class="text-sm text-white hover:text-indigo-200 underline transition-colors" href="{{ route('superadmin.login') }}">
                                Back to login
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-white hover:bg-gray-50 text-indigo-900 font-semibold py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900 transition ease-in-out duration-150">
                            Send Reset Link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
