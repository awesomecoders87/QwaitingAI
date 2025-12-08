<div class="bg-white p-8 shadow-xl mx-auto
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
            <div
                class="location-card border border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:shadow-lg transition-all bg-white h-full flex flex-col"
                wire:click="selectLocation({{ $loc->id }})">

                <img
                    src="{{ !empty($loc->location_image) ? url('storage/' . $loc->location_image) : url('storage/location_images/no_image.jpg') }}"
                    alt="{{ $loc->location_name }}"
                    class="w-full h-64 object-cover rounded-md mb-3" />

                <div class="flex-grow">
                    <h3 class="text-xl font-semibold text-gray-700">{{ $loc->location_name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $loc->address }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        <strong>Average Waiting Time: </strong>
                       {{ $loc->siteDetail->average_waiting_time ?? 0 }} mins
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>
