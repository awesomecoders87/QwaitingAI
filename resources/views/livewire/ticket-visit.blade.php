<div class="container mx-auto flex justify-center items-center md:min-h-screen">

    <div class="bg-zinc-100 rounded-lg shadow-lg md:p-6 p-2 w-full max-w-xl border rounded-lg mb-3">
        {{-- <div class="flex justify-center mb-3">
      <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
        <img class="dark:hidden" src="{{ asset('images/qwaiting-logo.svg') }}" alt="Logo" height="40" width="150" />
        <img
            class="hidden dark:block"
            src="{{ asset('images/qwaiting-logo.svg') }}"
            alt="Logo" height="40" width="150" />
        </span>
    </div> --}}
    @if (!empty($queueStorage->closed_datetime))

    <div class="flex justify-center mb-4">
        <p class="text-3xl font-semibold">{{__('text.your ticket number')}} </p>
    </div>
    <div class="flex justify-center mb-4">
        <p class="text-5xl font-bold text-gray-600 ">{{ $acronym . '' . $queueDB->token }}</p>
    </div>

    <div class="text-center mb-6">
        <h1 class="text-2xl font-semibold">{{__('text.ticket closed successfully')}}</h1>
    </div>
    @elseif (empty($queueStorage->cancelled_datetime) && $currentYourTurn == false && $queueStorage->is_missed == App\Models\Queue::STATUS_NO)
    <div class="flex justify-center mb-4">
        <div class="bg-blue-100 rounded-full px-4 py-2">
            <span class="text-5xl font-bold text-blue-600">{{ $acronym . '' . $queueDB->token }}</span>
        </div>
    </div>
    <div class="text-center mb-6">
        {{-- <h1 class="text-xl font-semibold">{{__('text.thanks for waiting')}}!</h1> --}}
        {{-- <p class="text-gray-600">{{__("text.stay on this page to get notified when it's your turn")}}.</p> --}}
    </div>

    <div class="text-center mb-6">
        @if($siteDetails->ticket_text_enable)
        @php
        $text = $showTicketText;
        @endphp
        <span class="block text-sm text-gray-600">{{ $text }}</span>
        @endif
        {{-- <span class="block text-3xl font-bold">{{ $pendingCount }}</span> --}}
    </div>

    <div class="text-center mb-6">
        @if($siteDetails->ticket_text_enable)
        @php
        $text = $showTicketText_2;
        @endphp
        <span class="block text-sm text-gray-600">{{ $text }}</span>
        @endif
    </div>


    @if ($siteDetails->late_coming_feature == App\Models\Queue::STATUS_YES && $queueDB->is_arrived == App\Models\Queue::STATUS_NO )
    <div class="text-center mb-3">
        <button class="px-4 py-1 text-white bg-blue-500 rounded hover:bg-blue-700"
            wire:click="openModal">{{__("text.I'm not coming")}}</button>
    </div>

    <div class="text-center mb-3 flex justify-between gap-3">
        <button class="px-4 py-1 text-white bg-blue-500 w-full rounded hover:bg-blue-700"
            wire:click="waitTime(5)">5 {{__("text.min late")}}</button>
        <button class="px-4 py-1 text-white bg-yellow-500 w-full rounded hover:bg-yellow-700"
            wire:click="waitTime('10')">10 {{__("text.min late")}}</button>
        <button class="px-4 py-1 text-white bg-red-500 w-full rounded hover:bg-red-700"
            wire:click="waitTime('15')">15 {{__("text.min late")}}</button>

    </div>
    <div class="text-center mb-6">
        <button class="px-4 py-1 text-white bg-green-600 w-full rounded hover:bg-green-700"
            wire:click="isArrived">{{__("text.arrived")}}</button>
    </div>
    @endif


    @elseif (empty($queueStorage->cancelled_datetime) && $currentYourTurn == true)
    <div class="text-center mb-6">
        <h1 class="text-xl font-semibold">{{ App\Models\User::viewName($queueStorage->served_by) }} {{__("text.is ready to serve you at")}} {{ $queueStorage?->counter?->name }}</h1>
    </div>

    <div class="flex justify-center mb-4">
        <p class="text-xl font-semibold">{{__("text.your ticket number")}} </p>
    </div>
    <div class="flex justify-center mb-4">
        <p class="text-xl font-bold text-gray-600 ">{{ $acronym . '' . $queueDB->token }}</p>
    </div>

    <div class="flex justify-center mb-4">
        <p class="text-xl font-bold text-gray-600 ">
            {{ !empty($queueStorage->category_id) ? App\Models\Category::viewCategoryName($queueStorage->category_id) : '' }}
        </p>

        <p class="text-xl font-bold text-gray-600 ">
            {{ !empty($queueStorage->sub_category_id) ?'-> ' . App\Models\Category::viewCategoryName($queueStorage->sub_category_id)  : '' }}
        </p>
        <p class="text-xl font-bold text-gray-600 ">
            {{ !empty($queueStorage->child_category_id) ?  '-> ' .App\Models\Category::viewCategoryName($queueStorage->child_category_id) : '' }}
        </p>


    </div>
    <div class="flex justify-center mb-4">

        @if (!empty($queueStorage->transfer_id))
        <p class="text-xl font-bold text-gray-600 "> {{__("text.assigned to")}}
            {{ App\Models\Category::viewCategoryName($queueStorage->transfer_id) }}
        </p>
        @endif
    </div>



    <div class="text-center mb-6">
        <h1 class="text-2xl text-green-500 font-semibold">{{__("text.it is Your turn now")}}!</h1>
    </div>



    @elseif (!empty($queueStorage->cancelled_datetime))
    <div class="flex justify-center mb-4">

        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="size-20">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
    </div>

    <div class="flex justify-center mb-4">
        <p class="text-xl font-semibold">{{__("text.your ticket number")}} </p>
    </div>
    <div class="flex justify-center mb-4">
        <p class="text-xl font-bold text-gray-600 ">{{ $acronym . '' . $queueDB->token }}</p>
    </div>

    <div class="flex justify-center mb-4">
        <span class="text-3xl font-bold text-gray-600">{{__("text.you have left the waitlist")}}</span>
    </div>

    @elseif($queueStorage->is_missed == App\Models\Queue::STATUS_YES)
    <div class="flex justify-center mb-4">
        <p class="text-xl font-semibold">{{__("text.your queue number is missed")}} !
        </p>
    </div>
    <div class="flex justify-center mb-4">
        <p class="text-xl font-semibold">{{__("text.your ticket number")}} </p>
    </div>
    <div class="flex justify-center mb-4">
        <p class="text-xl font-bold text-gray-600 ">{{ $acronym . '' . $queueDB->token }}</p>
    </div>
    @endif


    @if ($siteDetails->user_detail == App\Models\Queue::STATUS_YES)
    <div class="mb-6">
        <div class="border-t border px-3 bg-white">
            @forelse($userDetails as $key => $userD)
            @php
            $labelKey = App\Models\FormField::viewLabel($teamId, $key);
            $locale = session('app_locale');
            $label = $labelKey;

            if ($locale !== 'en' && !empty($translations[$labelKey][$locale])) {
            $label = $translations[$labelKey][$locale];
            }
            @endphp

            <div class="flex justify-between py-2 flex-wrap gap-3">
                <div class="text-gray-600">{{ $label }}</div>
                <div class="font-semibold text-right">
                    @if (is_array($userD))
                    {{ implode(', ', $userD) }}
                    @else
                    {{ $userD }}
                    @endif
                </div>
            </div>
            @empty
            {{ __('text.no user details') }}
            @endforelse
        </div>
    </div>
    @endif

    @if ( $siteDetails->bottom_btn_enable == App\Models\Queue::STATUS_YES && empty($queueStorage->cancelled_datetime) && empty($queueStorage->closed_datetime))
    <div class="flex flex-row flex-wrap align-items-start justify-center">
        {{-- <a href="javascript:void(0)" class="block text-center text-blue-500 hover:underline mt-2">Messages</a> --}}
        @php
        $address = App\Models\Location::adminLocation($teamId,$location);
        $id = base64_encode($queueStorage->id);
        @endphp
        @if(!empty($address))
        {{-- <a class="ml-2 mt-2 px-4 py-1 text-white bg-blue-500 rounded hover:bg-blue-700" href="https://www.google.com/maps/search/{{$address}}" target="_blank">{{__("text.get directions")}}</a> --}}
        @endif
        @if($siteDetails->enable_waitlist_list == App\Models\Queue::STATUS_YES)
        <a class="ml-2 mt-2 px-4 py-1 text-white bg-gray-500 rounded hover:bg-gray-700" href="{{ url('view-waitlist/' . $location. '/' . $id) }}" target="_blank">{{__("text.view waitlist")}}</a>
        @endif
        @if (empty($queueStorage->cancelled_datetime) && $currentYourTurn == false && $enableleaveQueue)
        <a class="ml-2 mt-2 px-4 py-1 text-white bg-yellow-500 rounded hover:bg-yellow-700" href="javascript:void(0)"
            wire:click="openModal">{{__("text.leave waitlist")}}</a>
        @endif

        @if(empty($queueStorage->cancelled_datetime) && $currentYourTurn == true && $joinCall)
        <a href="{{ url('/meeting/room_' . base64_encode($queueStorage->queue_id) . '/' . base64_encode($queueStorage->queue_id)) }}" class="ml-2 mt-2 px-4 py-1 text-white bg-yellow-500 rounded hover:bg-yellow-700">{{ __('text.Join Call') }}</a>
        @endif
    </div>

    @elseif(empty($queueStorage->cancelled_datetime) && !empty($this->generatUrl) )
    <div class="space-y-4">
        <a href="{{url($this->generatUrl?->url)}}" class="block text-center text-blue-500 hover:underline">
            {{__("text.home")}}
        </a>

    </div>

    @endif
</div>



</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        Livewire.on('leave-waitlist', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('cancel-from-waitlist');
                }
            });
        });

        Livewire.on('confirm-alert', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('late-save-waitlist');
                }
            });
        });
        Livewire.on('arrived-alert', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('is-arrived');
                }
            });
        });

        Livewire.on('deleted', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Deleted successfully.',
                icon: 'success',
                // confirmButtonText: 'OK'
            })

        });
        Livewire.on('event-success-call', (message) => {
            console.log(message);
            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                // confirmButtonText: 'OK'
            })

        });
    });
</script>


<script src="{{asset('/js/app/call.js?v=4.1.0.0')}}"></script>

<script>
    var pusher = new Pusher("{{ $reverbKey }}", {
        cluster: 'mt1',
        wsHost: "{{ $reverbHost }}",
        wsPort: {{ $reverbPort }},
        wssPort: {{ $reverbPort }},
        forceTLS: "{{ $reverbScheme }}" === 'https',
        enabledTransports: ['ws', 'wss'],
        encrypted: true,
        disableStats: true
    });

    console.log('🤖 Visit Pusher connected to Reverb', {
        host: '{{ $reverbHost }}',
        port: {{ $reverbPort }},
        scheme: '{{ $reverbScheme }}',
        team: {{ tenant('id') }},
        location: {{ $location }}
    });


    var queueProgress = pusher.subscribe("queue-display.{{ tenant('id') }}.{{$location}}");

    queueProgress.bind('queue-display', function(data) {
        Livewire.dispatch('visitor-update', {
            event: data
        });
    });

    var queueSuspension = pusher.subscribe("queue-suspension.{{ tenant('id') }}.{{$location}}");

    queueSuspension.bind('queue-suspension', function() {
        console.log('🔄 Visit page received queue-suspension event, reloading.');
        window.location.reload();
    });
</script>
@endpush
