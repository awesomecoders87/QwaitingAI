<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-pink-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🤖 Appointment Booking Chatbot</h1>
            <p class="text-gray-600">Book your appointment easily with our AI assistant</p>
        </div>

        <!-- Chat Container -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden" style="height: 600px;">
            <!-- Chat Header -->
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Booking Assistant</h2>
                        <p class="text-white/80 text-sm">{{ $isLoading ? 'Typing...' : 'Online' }}</p>
                    </div>
                </div>
                <button wire:click="startNewChat" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-white text-sm font-medium transition">
                    New Chat
                </button>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4" style="height: 450px;" id="messages-container">
                @foreach($messages as $index => $message)
                    @if($message['role'] === 'user')
                        <!-- User Message -->
                        <div class="flex justify-end">
                            <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-2xl px-4 py-3 max-w-md shadow-lg">
                                <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                                <span class="text-xs opacity-75 mt-1 block">{{ $message['timestamp'] }}</span>
                            </div>
                        </div>
                    @else
                        <!-- AI Message -->
                        <div class="flex justify-start">
                            <div class="bg-gray-100 text-gray-800 rounded-2xl px-4 py-3 max-w-md shadow-lg">
                                <p class="text-sm whitespace-pre-wrap">{!! nl2br(e($message['content'])) !!}</p>
                                <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach

                @if($isLoading)
                    <!-- Loading Indicator -->
                    <div class="flex justify-start">
                        <div class="bg-gray-100 text-gray-800 rounded-2xl px-4 py-3 shadow-lg">
                            <div class="flex space-x-2">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Booking Confirmation Card -->
            @if($bookingConfirmed && $bookingDetails)
                <div class="mx-6 mb-4 p-4 bg-green-50 border-2 border-green-200 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-green-800 font-bold">Booking Confirmed!</h3>
                    </div>
                    <div class="text-sm text-gray-700 space-y-1">
                        <p><strong>Service:</strong> {{ $bookingDetails['service']['name'] ?? 'N/A' }}</p>
                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($bookingDetails['date'])->format('l, F j, Y') }}</p>
                        <p><strong>Time:</strong> {{ $bookingDetails['time'] ?? 'N/A' }}</p>
                        <p><strong>Booking ID:</strong> {{ $bookingDetails['ref_id'] ?? 'N/A' }}</p>
                    </div>
                </div>
            @endif

            <!-- User Info Form (if needed) -->
            @if($showUserInfoForm)
                <div class="mx-6 mb-4 p-4 bg-blue-50 border-2 border-blue-200 rounded-lg">
                    <h3 class="text-blue-800 font-bold mb-3">Please provide your details:</h3>
                    <div class="space-y-3">
                        <input type="text" wire:model="name" placeholder="Your Name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="tel" wire:model="phone" placeholder="Phone Number" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <input type="email" wire:model="email" placeholder="Email (optional)" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <button wire:click="sendMessage" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                            Continue Booking
                        </button>
                    </div>
                </div>
            @endif

            <!-- Message Input -->
            <div class="border-t border-gray-200 p-4 bg-gray-50">
                <form wire:submit.prevent="sendMessage" class="flex space-x-3">
                    <input type="text" 
                           wire:model="currentMessage" 
                           placeholder="Type your message here..."
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           {{ $isLoading ? 'disabled' : '' }}>
                    <button type="submit" 
                            class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-lg transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ $isLoading ? 'disabled' : '' }}>
                        @if($isLoading)
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        @endif
                    </button>
                </form>
            </div>
        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>💡 <strong>Tip:</strong> You can say things like:</p>
            <p class="mt-2">"Book dental service on December 11 at 4pm" or "I need a haircut tomorrow morning"</p>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom when new message is added
        document.addEventListener('livewire:init', () => {
            Livewire.on('scroll-to-bottom', () => {
                const container = document.getElementById('messages-container');
                if (container) {
                    setTimeout(() => {
                        container.scrollTop = container.scrollHeight;
                    }, 100);
                }
            });
        });

        // Auto-scroll on mount and after messages update
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        // Scroll after Livewire updates
        document.addEventListener('livewire:init', () => {
            Livewire.hook('message.processed', () => {
                setTimeout(() => {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }, 100);
            });
        });
    </script>
</div>

