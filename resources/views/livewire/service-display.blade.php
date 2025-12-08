<div class="h-screen bg-white">

    @if ($showlocationpage)

        <div
            class="bg-white p-8 shadow-xl mx-auto
     w-[90%] sm:w-[85%] md:w-[80%] lg:w-[70%] max-w-6xl
     min-h-[100vh] flex flex-col">

            <!-- Logo -->
            {{-- <div class="flex justify-center mb-6">
        <img src="{{ asset('storage/logo.png') }}" alt="logo" class="h-10" />
    </div> --}}

            <!-- Heading -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">{{ __('Please select location') }}</h2>
                <p class="text-gray-500 text-sm mt-1">{{ __('Choose a branch to continue') }}</p>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 flex-grow">
                @foreach ($allLocations as $loc)
                    <div class="location-card border border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:shadow-lg transition-all bg-white h-full flex flex-col"
                        wire:click="selectLocation({{ $loc->id }})">

                        <img src="{{ !empty($loc->location_image) ? url('storage/' . $loc->location_image) : url('storage/location_images/no_image.jpg') }}"
                            alt="{{ $loc->location_name }}" class="w-full h-64 object-cover rounded-md mb-3" />

                        <div class="flex-grow">
                            <h3 class="text-xl font-semibold text-gray-700">{{ $loc->location_name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $loc->address }}</p>
                            <p class="text-sm text-gray-500 mt-1">
                                <strong>Average Waiting Time: </strong>
                                {{ \App\Models\SiteDetail::fetchWaitingTime($loc->id) ?? 0 }} mins
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


    @endif
    @if ($showdisplaypage)

        <div class="table-display-inside h-screen flex flex-col" wire:poll.5s>
            <div id="display-header" class="flex justify-between border-b border-gray-400 items-center">
                <div class="display-logo p-3 w-64 h-24 text-left"><img
                        src="https://qwaiting.com/images/qwaiting-logo.svg" alt="qwaiting" class="max-h-full" /></div>
                <div class="display-head-text text-md p-3"><button class="requestfullscreen" id="toggleFullBtn"><svg
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15">
                            </path>
                        </svg>
                    </button></div>
            </div>

            <div class="grid grid-cols-3 w-full flex-1 text-center">
                @if (!empty($subcategories))
                    @foreach ($subcategories as $subcategory)
                        <div class="column flex flex-col border-r border-gray-300">
                            <div class="display-service-number text-2xl xl:text-5xl font-semibold p-3 text-indigo-600">
                                {{ $subcategory->name ?? '' }}</div>
                            <div class="display-service-list flex-1">
                                @if (!empty($subcategory->queuesSubCategoryId) && $subcategory->queuesSubCategoryId->count() > 0)
                                    @foreach ($subcategory->queuesSubCategoryId as $queue)
                                    @if($queue->status == 'Progress' && \Carbon\Carbon::parse($queue->arrives_time)->isToday())
                                        <div
                                            class="service-item border-t border-gray-300 text-md md:text-xl xl:text-4xl p-3">
                                            Token <span class="text-indigo-600 font-bold">{{ $queue->token }}</span>
                                        </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div
                                        class="service-item border-t border-gray-300 text-md md:text-xl xl:text-2xl p-3 text-gray-400">

                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>
   @endif
        <script>
            const toggleBtn = document.getElementById("toggleFullBtn");

            toggleBtn.addEventListener("click", () => {
                if (!document.fullscreenElement) {
                    // Enter fullscreen
                    document.documentElement.requestFullscreen()
                        .catch(err => console.error(`Error enabling fullscreen: ${err.message}`));
                } else {
                    // Exit fullscreen
                    document.exitFullscreen()
                        .catch(err => console.error(`Error exiting fullscreen: ${err.message}`));
                }
            });

            document.addEventListener('livewire:init', () => {
                Livewire.on('refreshComponent', () => {
                    window.location.reload(); // Refresh the component only, not the whole page
                });

    //              setInterval(() => {
    //     Livewire.dispatch('refreshComponent');
    // }, 5000);
            });


        </script>

</div>
