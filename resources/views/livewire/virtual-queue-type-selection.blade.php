<div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-100 flex items-center justify-center p-4">
    <div class="max-w-5xl w-full">
        
        @if(!$showForm)
            <!-- Type Selection View -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Choose Your Virtual Queue Type
                </h1>
                <p class="text-xl text-gray-600">
                    Select how you would like to be assisted
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- AI Agent Option -->
                @if($settings->isAIAgentEnabled())
                <div wire:click="selectType('ai_agent')" 
                     class="bg-white rounded-2xl shadow-xl p-10 cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-2xl border-4 border-transparent hover:border-purple-500">
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-purple-100 to-pink-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-4">AI Agent</h3>
                        <p class="text-gray-600 mb-6 text-lg">
                            Connect with our intelligent AI assistant for instant help
                        </p>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                24/7 Availability
                            </div>
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Multi-language Support
                            </div>
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Instant Response
                            </div>
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Video Call with Avatar
                            </div>
                        </div>

                        <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 7H7v6h6V7z"></path>
                                <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path>
                            </svg>
                            Powered by AI
                        </div>
                    </div>
                </div>
                @endif

                <!-- Human Agent Option -->
                @if($settings->isHumanAgentEnabled())
                <div wire:click="selectType('human_agent')" 
                     class="bg-white rounded-2xl shadow-xl p-10 cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-2xl border-4 border-transparent hover:border-blue-500">
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-blue-100 to-cyan-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-3xl font-bold text-gray-800 mb-4">Human Agent</h3>
                        <p class="text-gray-600 mb-6 text-lg">
                            Connect with a real person for personalized assistance
                        </p>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Personal Touch
                            </div>
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Expert Assistance
                            </div>
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Complex Query Support
                            </div>
                            <div class="flex items-center justify-center text-sm text-gray-700">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Video Call with Staff
                            </div>
                        </div>

                        <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            Human Support
                        </div>
                    </div>
                </div>
                @endif

            </div>

        @else
            <!-- Customer Information Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
                <div class="mb-8">
                    <button wire:click="back" class="text-purple-600 hover:text-purple-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back
                    </button>
                </div>

                <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">
                    {{ $selectedType === 'ai_agent' ? 'AI Agent' : 'Human Agent' }} - Your Information
                </h2>

                <form wire:submit.prevent="createVirtualQueue" class="space-y-6">
                    
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" wire:model="customerName" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter your full name">
                        @error('customerName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" wire:model="customerEmail" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter your email">
                        @error('customerEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" wire:model="customerPhone" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter your phone number">
                        @error('customerPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Category *</label>
                        <select wire:model="selectedCategory" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedCategory') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Language Selection (for AI Agent) -->
                    @if($selectedType === 'ai_agent')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Language *</label>
                        <select wire:model="selectedLanguage" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            @foreach($settings->supported_languages ?? ['en'] as $lang)
                                <option value="{{ $lang }}">
                                    @switch($lang)
                                        @case('en') English @break
                                        @case('es') Spanish @break
                                        @case('fr') French @break
                                        @case('de') German @break
                                        @case('ar') Arabic @break
                                        @case('hi') Hindi @break
                                        @default {{ strtoupper($lang) }}
                                    @endswitch
                                </option>
                            @endforeach
                        </select>
                        @error('selectedLanguage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 px-6 rounded-lg hover:from-purple-700 hover:to-pink-700 transform transition-all duration-200 hover:scale-105 shadow-lg">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Start {{ $selectedType === 'ai_agent' ? 'AI' : 'Human' }} Session
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mt-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    </div>
</div>
