<div class="">
  @push('styles')
  <link href="{{ asset('css/main.css?v=555766777') }}" rel="stylesheet" />
  <style>
    .card-active {
      @apply border-blue-600 ring-2 ring-blue-300;
    }
  </style>
  @endpush
  <div style="right: 10px; top: 5px;" class="md:absolute m-3 px-4 md:px-0 md:my-0 md:w-15">
      <livewire:language-selector /> 
  </div>
  <div class="{{ $locationStep == false ? 'hidden' : '' }} ">
    <div
      class="bg-white p-8 shadow-xl mx-auto
             w-[90%] sm:w-[85%] md:w-[80%] lg:w-[70%] max-w-6xl
             min-h-[100vh] flex flex-col">

      @php
      $url = request()->url();
      $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

      if ( strpos( $url, 'mobile/queue' ) !== false ) {
      $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
      }

      $logo = App\Models\SiteDetail::viewImage($headerPage);
      @endphp
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img
          src="{{ $logo }}"
          alt="qwaiting logo"
          class="h-10" />
      </div>

      <!-- Heading -->
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">{{ __('text.Please select location') }}</h2>
        <p class="text-gray-500 text-sm mt-1">{{ __('text.Choose a branch to continue') }}</p>
      </div>

      <!-- Cards -->
      <div
        id="cardContainer"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 flex-grow">
        @if (empty($location) && !empty($allLocations))
        @foreach ($allLocations as $location)
        <!-- Card 1 -->
        <div
          class="location-card border border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:shadow-lg transition-all bg-white h-full flex flex-col"
          data-location="{{ $locationName }}" wire:click="$set('location', '{{ $location->id }}')">
          <img
            src="{{ !empty($location->location_image) ? url('storage/' . $location->location_image) : url('storage/location_images/no_image.jpg') }}"
            alt="{{ $locationName }}"
            class="w-full h-64 object-cover rounded-md mb-3" />
          <div class="flex-grow">
            <h3 class="text-xl font-semibold text-gray-700">{{ $location->location_name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $location->address }}</p>
          </div>
        </div>
        @endforeach
        @endif

      </div>
    </div>
  </div>

  <div class="text-center w-full {{ $firstStep == false ? 'hidden' : '' }}">
    <div class="text-4xl text-gray-900 font-bold mb-8">
      {{ $bookingHeadingText ?? __('text.What are you interested in') }}
    </div>
    <div class="flex flex-wrap justify-center gap-8 mt-6">
      <!-- Walk-In -->
      @php
      $encodedLocation = base64_encode($location);
      @endphp

      <a href="{{ url('queue/'. $encodedLocation) }}" class="selection-item bg-black-20 border-1 w-64 p-6 text-center">
        <img src="{{ url('tenancy/assets/images/walk-in.png') }}" class="mx-auto mb-4 w-20 h-20" />
        <div class="text-slate-950 text-xl font-semibold">{{ $accountSetting->walk_in_label ?? __('text.Walk-In') }}</div>
      </a>

      <!-- Appointment -->
      <a href="{{ url('/convert-to-queue') }}" class="selection-item bg-black-20 border-1 w-64 p-6 text-center text-center">
        <img src="{{ url('tenancy/assets/images/appointments-18.png') }}" class="mx-auto mb-4 w-20 h-20" />
        <div class="text-slate-950 text-xl font-semibold">{{ $accountSetting->appointment_label ?? __('text.Appointment') }}</div>
      </a>
    </div>
  </div>
</div>