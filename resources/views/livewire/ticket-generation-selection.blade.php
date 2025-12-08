<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="max-w-6xl w-full">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                Welcome to Queue Management
            </h1>
            <p class="text-xl text-gray-600">
                Please select how you would like to generate your ticket
            </p>
        </div>

        <!-- Options Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- QR Code Option -->
            <div wire:click="selectOption('qr_code')" 
                 class="bg-white rounded-2xl shadow-xl p-8 cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-2xl border-4 border-transparent hover:border-blue-500">
                <div class="text-center">
                    <div class="bg-blue-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">QR Code</h3>
                    <p class="text-gray-600 mb-4">
                        Scan QR code to generate your ticket instantly
                    </p>
                    <div class="flex items-center justify-center text-sm text-gray-500">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Quick & Easy
                    </div>
                </div>
            </div>

            <!-- Kiosk Option -->
            <div wire:click="selectOption('kiosk')" 
                 class="bg-white rounded-2xl shadow-xl p-8 cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-2xl border-4 border-transparent hover:border-green-500">
                <div class="text-center">
                    <div class="bg-green-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Kiosk</h3>
                    <p class="text-gray-600 mb-4">
                        Use self-service kiosk to get your ticket
                    </p>
                    <div class="flex items-center justify-center text-sm text-gray-500">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Self-Service
                    </div>
                </div>
            </div>

            <!-- Virtual Queue Option -->
            <div wire:click="selectOption('virtual')" 
                 class="bg-white rounded-2xl shadow-xl p-8 cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-2xl border-4 border-transparent hover:border-purple-500 {{ !$settings->isVirtualQueueEnabled() ? 'opacity-50' : '' }}">
                <div class="text-center">
                    <div class="bg-purple-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Virtual Queue</h3>
                    <p class="text-gray-600 mb-4">
                        Join virtual queue with AI or human agent
                    </p>
                    <div class="flex items-center justify-center text-sm text-gray-500">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $settings->isVirtualQueueEnabled() ? 'Remote Service' : 'Not Available' }}
                    </div>
                    @if($settings->isVirtualQueueEnabled() && $settings->isAIAgentEnabled())
                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 7H7v6h6V7z"></path>
                                <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path>
                            </svg>
                            AI Powered
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Info Section -->
        <div class="mt-12 text-center">
            <p class="text-gray-600">
                Need help? Contact our support team
            </p>
        </div>

        @if (session()->has('error'))
            <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    </div>
</div>
