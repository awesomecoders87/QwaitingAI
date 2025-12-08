<div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-100 flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        
        @if($status === 'waiting')
            <!-- Waiting State -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 text-center">
                
                <!-- Animated Icon -->
                <div class="mb-8">
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full flex items-center justify-center animate-pulse">
                        <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    Please Wait
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    You're in the virtual queue for a human agent
                </p>

                <!-- Ticket Info -->
                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl p-6 mb-8">
                    <p class="text-sm opacity-90 mb-2">Your Ticket Number</p>
                    <p class="text-4xl font-bold tracking-wider">{{ $virtualQueue->ticket_number }}</p>
                </div>

                <!-- Queue Position -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 rounded-xl p-6">
                        <p class="text-gray-600 text-sm mb-2">Position in Queue</p>
                        <p class="text-4xl font-bold text-blue-600">{{ $position }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <p class="text-gray-600 text-sm mb-2">Estimated Wait</p>
                        <p class="text-4xl font-bold text-blue-600">{{ $estimatedWaitTime }}<span class="text-xl">min</span></p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
                    <h3 class="font-bold text-gray-800 mb-4">Your Information</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium text-gray-800">{{ $virtualQueue->customer_name }}</span>
                        </div>
                        @if($virtualQueue->customer_email)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium text-gray-800">{{ $virtualQueue->customer_email }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium text-gray-800">{{ $virtualQueue->customer_phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info Message -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-left">
                            <p class="text-sm text-blue-800">
                                <strong>What happens next?</strong><br>
                                An agent will be assigned to you shortly. You'll receive a notification with a meeting link via SMS/Email when it's your turn.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cancel Button -->
                <button wire:click="cancelQueue" 
                        class="text-red-600 hover:text-red-800 font-medium transition">
                    Cancel Queue
                </button>

                <!-- Auto-refresh indicator -->
                <div class="mt-6 flex items-center justify-center text-sm text-gray-500">
                    <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Auto-refreshing...
                </div>
            </div>

        @elseif($status === 'connected')
            <!-- Agent Assigned State -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 text-center">
                
                <!-- Success Icon -->
                <div class="mb-8">
                    <div class="w-32 h-32 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    Agent Assigned!
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    You've been connected with a human agent
                </p>

                <!-- Agent Info -->
                @if($assignedAgent)
                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl p-6 mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm opacity-90 mb-2">Your Agent</p>
                    <p class="text-2xl font-bold">{{ $assignedAgent->name }}</p>
                </div>
                @endif

                <!-- Meeting Link -->
                <div class="bg-gray-50 rounded-xl p-6 mb-8">
                    <p class="text-gray-600 text-sm mb-4">Meeting Link</p>
                    <div class="bg-white border-2 border-dashed border-gray-300 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600 break-all font-mono">{{ $meetingLink }}</p>
                    </div>
                    <p class="text-xs text-gray-500">A link has been sent to your phone/email</p>
                </div>

                <!-- Join Button -->
                <button wire:click="joinCall" 
                        class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold py-4 px-6 rounded-lg hover:from-blue-700 hover:to-cyan-700 transform transition-all duration-200 hover:scale-105 shadow-lg mb-4">
                    <span class="flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Join Video Call
                    </span>
                </button>

                <!-- Info -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-left">
                            <p class="text-sm text-green-800">
                                <strong>Ready to connect!</strong><br>
                                Click the button above to start your video call with the agent. Make sure your camera and microphone are enabled.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('success'))
            <div class="mt-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh position every 10 seconds when waiting
    @if($status === 'waiting')
    setInterval(function() {
        @this.call('positionUpdated');
    }, 10000);
    @endif
</script>
@endpush
