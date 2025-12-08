<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Please select location</h1>
            <p class="text-lg text-gray-600">Choose a branch to continue</p>
        </div>

        <!-- Locations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($locations as $location)
                <div wire:click="selectLocation({{ $location['id'] }})" 
                     class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 cursor-pointer overflow-hidden border border-gray-200 hover:border-purple-500">
                    
                    <!-- Location Image -->
                    <div class="h-48 bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center">
                        @if($location['image'])
                            <img src="{{ $location['image'] }}" 
                                 alt="{{ $location['name'] }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="text-center">
                                <svg class="w-20 h-20 mx-auto text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <p class="text-purple-600 font-semibold mt-2">{{ $location['name'] }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Location Details -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">{{ $location['name'] }}</h3>
                        
                        @if($location['address'])
                            <p class="text-sm text-gray-600 text-center mb-1">
                                {{ $location['address'] }}
                            </p>
                        @endif
                        
                        @if($location['city'] || $location['state'] || $location['country'])
                            <p class="text-sm text-gray-600 text-center mb-4">
                                @if($location['city']){{ $location['city'] }}, @endif
                                @if($location['state']){{ $location['state'] }}, @endif
                                @if($location['country']){{ $location['country'] }}@endif
                            </p>
                        @endif

                        <!-- Average Waiting Time -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-700">Average Waiting Time:</span>
                                <span class="ml-2 text-sm font-bold text-purple-600">
                                    @if($location['avg_wait_time'] == 0)
                                        No wait
                                    @elseif($location['avg_wait_time'] < 60)
                                        {{ $location['avg_wait_time'] }} mins
                                    @else
                                        {{ round($location['avg_wait_time'] / 60, 1) }} hrs
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Select Button -->
                        <div class="mt-4">
                            <button class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105">
                                Select Location
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Locations Available</h3>
                    <p class="text-gray-500">Please contact support for assistance.</p>
                </div>
            @endforelse
        </div>

        <!-- Footer Info -->
        <div class="mt-12 text-center">
            <p class="text-sm text-gray-500">
                Select your preferred location to join the virtual queue
            </p>
        </div>

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Public Location Selection initialized');
    });
</script>
@endpush
