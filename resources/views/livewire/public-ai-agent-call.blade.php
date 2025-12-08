<div class="h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 overflow-hidden flex flex-col">
    <div class="container mx-auto px-4 py-4 flex flex-col h-full">
        
        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-5 mb-3 border border-white/20 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-pink-400 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="text-white">
                        <h2 class="text-2xl font-bold">AI Assistant</h2>
                        <p class="text-xs text-purple-200 mt-0.5">Ticket: <span class="font-semibold">{{ $virtualQueue->ticket_number }}</span></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white text-xs font-medium border border-white/30 shadow-lg">
                        @if($status === 'connecting')
                            <span class="flex items-center">
                                <svg class="animate-spin h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Connecting...
                            </span>
                        @elseif($status === 'connected')
                            <span class="flex items-center">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse shadow-lg shadow-green-400/50"></span>
                                Connected
                            </span>
                        @elseif($status === 'transferring')
                            <span class="flex items-center">
                                <svg class="animate-spin h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24">
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

        <div class="flex-1 flex flex-col min-h-0">
            
            <!-- Chat Section (Full Width) -->
            <div class="flex-1 flex flex-col min-h-0">
                <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl h-full flex flex-col border border-white/20">
                    <!-- Chat Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-5 py-3.5 rounded-t-2xl flex-shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-white font-bold text-base">Chat with AI</h3>
                                    <p class="text-white/80 text-xs">Online and ready to help</p>
                                </div>
                            </div>
                            <button wire:click="endSession" class="px-3.5 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-white text-xs font-medium transition-all duration-200 flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>End</span>
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-5 space-y-3.5 bg-gradient-to-b from-gray-50 to-gray-100/50">
                        @foreach($messages as $message)
                            @if($message['role'] === 'user')
                                <!-- User Message -->
                                <div class="flex justify-end animate-fade-in">
                                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-2xl rounded-tr-md px-4 py-2.5 max-w-md shadow-md">
                                        <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $message['content'] }}</p>
                                        <span class="text-xs opacity-75 mt-1 block">{{ $message['timestamp'] }}</span>
                                    </div>
                                </div>
                            @elseif($message['role'] === 'ai')
                                <!-- AI Message -->
                                <div class="flex justify-start animate-fade-in">
                                    <div class="flex items-start space-x-2.5 max-w-md">
                                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="bg-white text-gray-800 rounded-2xl rounded-tl-md px-4 py-2.5 shadow-md border border-gray-200">
                                            <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $message['content'] }}</p>
                                            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- System Message -->
                                <div class="flex justify-center animate-fade-in">
                                    <div class="bg-gradient-to-r from-amber-100 to-orange-100 text-amber-800 rounded-full px-4 py-1.5 text-xs font-medium shadow-sm border border-amber-200">
                                        {{ $message['content'] }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Transfer Button -->
                    @if($showTransferButton && $status === 'connected')
                        <div class="px-4 py-3 bg-gradient-to-r from-amber-50 to-orange-50 border-t border-amber-200 flex-shrink-0">
                            <button wire:click="transferToHuman" 
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                Transfer to Human Agent
                            </button>
                        </div>
                    @endif
                    
                    <!-- Start New Chat Button -->
                    @if($showNewChatButton && $status === 'connected')
                        <div class="px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50 border-t border-green-200 flex-shrink-0">
                            <button wire:click="startNewChat" 
                                    class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg hover:shadow-xl transform hover:scale-105">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Start New Chat
                            </button>
                        </div>
                    @endif

                    <!-- Message Input -->
                    @if($status === 'connected')
                        <div class="px-4 py-3.5 border-t border-gray-200 bg-white rounded-b-2xl flex-shrink-0">
                            @if($chatDisabled)
                                <!-- Disabled input with message -->
                                <div class="text-center py-3">
                                    <p class="text-sm text-gray-500 mb-2">🔒 Chat is disabled. Please click "Start New Chat" to continue.</p>
                                    <div class="flex space-x-2.5 opacity-50 pointer-events-none">
                                        <input type="text" 
                                               disabled
                                               placeholder="Type your message..."
                                               class="flex-1 px-4 py-2.5 border-2 border-gray-300 rounded-xl bg-gray-100 text-sm">
                                        <button type="button" 
                                                disabled
                                                class="bg-gray-400 text-white px-4 py-2.5 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <!-- Active input -->
                                <form wire:submit.prevent="sendMessage" class="flex space-x-2.5">
                                    <input type="text" 
                                           wire:model="currentMessage" 
                                           placeholder="Type your message..."
                                           class="flex-1 px-4 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 text-sm">
                                    <button type="submit" 
                                            class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-4 py-2.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Session Info - Compact -->
        <div class="mt-2.5 bg-white/10 backdrop-blur-lg rounded-xl shadow-lg px-4 py-2.5 border border-white/20 flex-shrink-0">
            <div class="flex items-center justify-around text-xs">
                <div class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-white font-medium">{{ $virtualQueue->customer_name }}</span>
                </div>
                <div class="w-px h-4 bg-white/20"></div>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <span class="text-white font-medium">{{ strtoupper($virtualQueue->selected_language) }}</span>
                </div>
                <div class="w-px h-4 bg-white/20"></div>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-white font-mono">{{ substr($sessionId, 0, 8) }}</span>
                </div>
                <div class="w-px h-4 bg-white/20"></div>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    <span class="text-white font-medium">{{ count($messages) }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
    
    /* Custom scrollbar for chat */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #9333ea, #ec4899);
        border-radius: 10px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #7e22ce, #db2777);
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize AI Agent Call
    document.addEventListener('DOMContentLoaded', function() {
        console.log('AI Agent Call initialized');
        
        // Auto-scroll to bottom when new messages arrive
        const messageContainer = document.querySelector('.overflow-y-auto');
        if (messageContainer) {
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }
        
        // Listen for Livewire updates
        Livewire.hook('message.processed', (message, component) => {
            setTimeout(() => {
                if (messageContainer) {
                    messageContainer.scrollTo({
                        top: messageContainer.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        });
    });
</script>

@endpush
