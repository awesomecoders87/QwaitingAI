<div class="container mx-auto flex justify-center items-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <div class="flex justify-center mb-4">
            <span class="text-3xl font-bold text-blue-600">{{ $this->siteDetails->waitlist_heading ?? '' }}</span>
        </div>
        @if(isset($siteDetails) && $siteDetails->is_enable_waitlist_message == 1)
        <div class="text-center mb-6">

            <p class="text-gray-600">{{  $showTicketText }} </p>
            <p class="text-gray-600"> {{ $showTicketText_2 }}</p>

        </div>
@endif
@if(isset($siteDetails) && $siteDetails->is_waitlist_table == 1)
       <div class="mb-6">
        <!-- Header -->
        <div class="border-t border-b bg-gray-50">
            <div class="grid grid-cols-4 py-3 text-sm font-semibold text-gray-700">
                <span class="text-left px-2">{{ __('text.No.') }}</span>
                <span class="text-left">{{ __('text.service') }}</span>
                <span class="text-center">{{ __('text.Token') }}</span>
                <span class="text-right pr-2">{{ __('text.Waited') }}</span>
            </div>
        </div>

        <!-- Rows -->
        <div class="divide-y">
            @forelse($queuePening as $key => $queue)
                <div class="grid grid-cols-4 py-3 items-center text-sm hover:bg-gray-50">
                    <span class="text-left px-2 font-medium text-gray-800">
                        {{ $key + 1 }}
                    </span>

                    <span class="text-left text-gray-600 truncate">
                        {{ $queue->category->name ?? '' }}
                    </span>

                    <span class="text-center font-semibold text-gray-700">
                        {{ ($queue->start_acronym ?? '') . ($queue->token ?? 'N/A') }}
                    </span>


                    {{-- @if($siteDetails->category_estimated_time == App\Models\SiteDetail::STATUS_YES  && !empty($queue->assign_staff_id)) --}}
                        {{-- <span class="font-semibold text-nowrap">
                            {{ $queue->waiting_time }} {{ __('text.mins') }}

                        </span>
                        @else
                        <span class="font-semibold text-nowrap">
                            {{ ($key + 1) * $siteDetails?->estimate_time }} {{ __('text.mins') }}

                        </span> --}}

                        {{-- @endif --}}
                    <span class="text-right pr-2 text-gray-600 font-medium" wire:poll.60000ms>
                        @php
                            $seconds = abs(
                                now($timezone)->diffInSeconds(
                                    Carbon\Carbon::parse($queue->datetime, $timezone)
                                )
                            );
                            $minutes = floor($seconds / 60);
                        @endphp

                        @if ($minutes >= 60)
                            {{ floor($minutes / 60) }}h {{ $minutes % 60 }}m
                        @else
                            {{ $minutes }} {{ __('text.mins') }}
                        @endif
                    </span>
                </div>
            @empty
                <div class="py-4 text-center text-gray-500">
                    {{ __('text.Not found waitlist') }}
                </div>
            @endforelse
        </div>
    </div>
        @endif
    </div>

</div>
 @push('scripts')
    <script src="{{asset('/js/app/call.js?v=3.1.0.0')}}"></script>
      <script>


        const tenantId = {{ tenant('id') }};
        const locationId = {{ $location }};
        
        var pusher = new Pusher("{{ $reverbKey }}", {
        wsHost: "{{ $reverbHost }}",
        wsPort: {{ $reverbPort }},
        wssPort: {{ $reverbPort }},
        forceTLS: "{{ $reverbScheme }}" === 'https',
        enabledTransports: ['ws', 'wss'],
        encrypted: true
    });


        var queueProgress = pusher.subscribe("queue-display." + tenantId + "." + locationId);

        queueProgress.bind('queue-display', function(data) {

             window.location.reload();
            // Livewire.dispatch('display-update', {
            //     event: data
            // });
        });

        var queuesuspension = pusher.subscribe("queue-suspension." + tenantId + "." + locationId);

queuesuspension.bind('queue-suspension', function() {
   window.location.reload();
});


    </script>
    @endpush
