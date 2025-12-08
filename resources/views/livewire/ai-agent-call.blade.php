<div class="min-h-screen bg-gray-900">
    <div class="container mx-auto px-4 py-6">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 7H7v6h6V7z"></path>
                            <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="text-white">
                        <h2 class="text-2xl font-bold">AI Assistant</h2>
                        <p class="text-sm opacity-90">Ticket: {{ $virtualQueue->ticket_number }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-4 py-2 bg-white bg-opacity-20 rounded-full text-white text-sm font-medium">
                        @if($status === 'connecting')
                            <span class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Connecting...
                            </span>
                        @elseif($status === 'connected')
                            <span class="flex items-center">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                Connected
                            </span>
                        @elseif($status === 'transferring')
                            <span class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Transferring...
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Video Section -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <!-- Video Container -->
                    <div class="relative bg-black aspect-video flex items-center justify-center">
                        <!-- AI Avatar Video Placeholder -->
                        <div id="video-container" class="w-full h-full flex items-center justify-center">
                            <!-- This would be replaced with actual video stream -->
                            <div class="text-center">
                                <div class="w-48 h-48 mx-auto mb-6 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center animate-pulse">
                                    <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-white text-2xl font-bold mb-2">AI Assistant Avatar</h3>
                                <p class="text-gray-400">Speaking in {{ strtoupper($virtualQueue->selected_language) }}</p>
                            </div>
                        </div>

                        <!-- Meeting Link Badge -->
                        <div class="absolute top-4 left-4">
                            <div class="bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg text-sm">
                                <a href="{{ $meetingLink }}" target="_blank" class="flex items-center hover:text-purple-400">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Open in new window
                                </a>
                            </div>
                        </div>

                        <!-- Your Video (Small) -->
                        <div class="absolute bottom-4 right-4 w-48 h-36 bg-gray-700 rounded-lg overflow-hidden border-2 border-purple-500">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Video Controls -->
                    <div class="bg-gray-800 p-4 flex items-center justify-center space-x-4">
                        <button class="w-12 h-12 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                        </button>
                        <button class="w-12 h-12 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                        <button wire:click="endSession" class="w-12 h-12 bg-red-600 hover:bg-red-700 rounded-full flex items-center justify-center text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chat Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg h-full flex flex-col" style="height: 600px;">
                    <!-- Chat Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-4 rounded-t-lg">
                        <h3 class="text-white font-bold text-lg">Chat with AI</h3>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        @foreach($messages as $message)
                            @if($message['role'] === 'user')
                                <!-- User Message -->
                                <div class="flex justify-end">
                                    <div class="bg-purple-600 text-white rounded-lg px-4 py-2 max-w-xs">
                                        <p class="text-sm">{{ $message['content'] }}</p>
                                        <span class="text-xs opacity-75">{{ $message['timestamp'] }}</span>
                                    </div>
                                </div>
                            @elseif($message['role'] === 'ai')
                                <!-- AI Message -->
                                <div class="flex justify-start">
                                    <div class="bg-gray-200 text-gray-800 rounded-lg px-4 py-2 max-w-xs">
                                        <p class="text-sm">{{ $message['content'] }}</p>
                                        <span class="text-xs text-gray-600">{{ $message['timestamp'] }}</span>
                                    </div>
                                </div>
                            @else
                                <!-- System Message -->
                                <div class="flex justify-center">
                                    <div class="bg-yellow-100 text-yellow-800 rounded-lg px-4 py-2 text-sm">
                                        {{ $message['content'] }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Transfer Button -->
                    @if($showTransferButton && $status === 'connected')
                        <div class="p-4 bg-yellow-50 border-t border-yellow-200">
                            <button wire:click="transferToHuman" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Transfer to Human Agent
                            </button>
                        </div>
                    @endif

                    <!-- Message Input -->
                    @if($status === 'connected')
                        <div class="p-4 border-t">
                            <form wire:submit.prevent="sendMessage" class="flex space-x-2">
                                <input type="text" 
                                       wire:model="currentMessage" 
                                       placeholder="Type your message..."
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <button type="submit" 
                                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Session Info -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-gray-600 text-sm">Customer</p>
                    <p class="text-lg font-bold text-gray-800">{{ $virtualQueue->customer_name }}</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 text-sm">Language</p>
                    <p class="text-lg font-bold text-gray-800">{{ strtoupper($virtualQueue->selected_language) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 text-sm">Session ID</p>
                    <p class="text-lg font-bold text-gray-800 font-mono text-sm">{{ substr($sessionId, 0, 8) }}...</p>
                </div>
                <div class="text-center">
                    <p class="text-gray-600 text-sm">Messages</p>
                    <p class="text-lg font-bold text-gray-800">{{ count($messages) }}</p>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Initialize video call (Twilio/Agora/Daily integration would go here)
    document.addEventListener('DOMContentLoaded', function() {
        console.log('AI Agent Call initialized');
        // Video call initialization code
    });
</script>
@endpush
