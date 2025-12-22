<div class="wrapper h-full wrapper-with-header-top" wire:ignore.self wire:poll.60000ms.keep-alive>

    @if($locationStep)
     <div>

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
                    data-location="{{ $location->location_name }}" wire:click="$set('location', '{{ $location->id }}')">
                    <img
                        src="{{ !empty($location->location_image) ? url('storage/' . $location->location_image) : url('storage/location_images/no_image.jpg') }}"
                        alt="{{ $location->location_name }}"
                        class="w-full h-64 object-cover rounded-md mb-3" />
                    <div class="flex-grow">
                        <h3 class="text-xl font-semibold text-gray-700">{{ $location->location_name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $location->address }}</p>
                        <p class="text-sm text-gray-500 mt-1"><strong>{{ __('text.Average Waiting Time') }}: </strong> {{ \App\Models\SiteDetail::fetchWaitingTime($location->id) ?? 0}} mins</p>
                    </div>
                </div>
                @endforeach
                @endif

            </div>
        </div>
    </div>
    @endif

    @if($displayStep)
    @php

    $videoIds = [];

    foreach($videoTemplates as $videoTemplate)
    {
        $videoIds[] = $videoTemplate['yt_vid'];
    }

    @endphp
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css"  />
       <link href="{{ asset('/css/display.css') }}?v={{time()}}" rel="stylesheet" />
        <style>
            .table-screen .content-view h1,
            .table-screen .content-view h2,
            .counter-listing .content-view h2,
            .counter-listing .content-view h1{
                font-size: {{ !empty($currentTemplate) ? $currentTemplate->font_size . 'vh !important' : '' }}
            }
            .theme-background{background:{{ $currentTemplate?->datetime_bg_color . ' !important' ?? '' }}}
            .theme-background:hover, .add-btn.theme-color:hover, .add-btn.theme-color:focus, .btn:hover, .paginate_button:hover, .ui-datepicker-calendar .ui-state-active, #atabs .nav-tabs > li > a, .form-inner input[type="submit"]:hover{
                background:{{ $currentTemplate?->datetime_bg_color . ' !important' ?? '' }};
                border-color:{{ $currentTemplate?->datetime_bg_color . ' !important' ?? '' }};
                opacity:0.8;
            }
            .theme-hold-background:hover{
                background:{{ $currentTemplate?->hold_queue_bg . ' !important' ?? '' }};
                opacity:0.8;
            }
            .theme-hold-background{ background:{{ $currentTemplate?->hold_queue_bg . ' !important' ?? '' }} }

        </style>
    @endpush

    <button class="requestfullscreen" id="toggleFullBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
        </svg>
    </button>

    <div class="table-display-inside">
       <div id="main-display">
    <div class="table-display table-display-new11 {{ !empty($currentTemplate) ? $currentTemplate->template_class : '' }}">
        <div class="no-bottom-space element-data table-screen">

            <!-- ✅ TABLE -->
            <table class="w-full  h-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 px-4 py-2 text-blue-600 text-lg font-bold">
                           <h2 class="yellow one text-5xl">
                                        {{ !empty($currentTemplate) ? $currentTemplate->token_title : __('text.Token') }}
                                    </h2>
                        </th>
                        <th class="border border-gray-300 px-4 py-2 text-blue-600 text-lg font-bold">
                            <h2 class="yellow two text-5xl" style="text-align:center;">
                                        {{ !empty($currentTemplate) ? $currentTemplate->counter_title : __('text.Counter') }}
                                    </h2>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($queueToDisplay as $key => $display)
                        @php
                            $isInProgress =
                                $display->status == App\Models\Queue::STATUS_PROGRESS &&
                                !empty($display->called_datetime);

                            $inActiveColor =
                                !empty($currentTemplate) && !empty($currentTemplate->font_color)
                                    ? $currentTemplate->font_color
                                    : '';

                            $activeTicketColor =
                                $isInProgress &&
                                !empty($currentTemplate) &&
                                !empty($currentTemplate->current_serving_fontcolor)
                                    ? $currentTemplate->current_serving_fontcolor . ' !important'
                                    : $inActiveColor;
                        @endphp

                        <tr wire:key="ticket_{{$key}}">
                            <td class="border border-gray-300 px-4 py-2 text-5xl font-semibold {{ $isInProgress ? 'red-color' : '' }}"
                                style="color:{{ $activeTicketColor }}">
                                T1 {{ $display->start_acronym . '' . $display->token }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2 text-5xl font-semibold {{ $isInProgress ? 'red-color' : '' }}"
                                style="color:{{ $activeTicketColor }}">
                                T2 {{ $display->counter?->name }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- ✅ EMPTY ROWS TO FILL UP TO 6 -->
                    @for($i = 0; $i < intval(6 - count($queueToDisplay)); $i++)
                        <tr>
                            <td class="border border-gray-300 px-4 py-2">&nbsp;</td>
                            <td class="border border-gray-300 px-4 py-2">&nbsp;</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>


        <div id="footer">
            <div
                class="previously-served queue_no full-width missed  {{ !empty($currentTemplate) && $currentTemplate->is_powered_by == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'powered-image-show' : '' }}">


                {{-- <h3 class="theme-background">
                  {{ !empty($currentTemplate) ? $currentTemplate->missed_queue : __('text.Missed Queue') }} : <span>


                    @forelse($missedCalls as $index => $missedCall)

                    @foreach($missedCall['queue_storages'] as $storageIndex => $queueStorage)
                        <a href="javascript:void(0)" wire:key="missed_{{$storageIndex}}" >
                          {{ $missedCall['token'] }}
                        </a>
                        @if (!$loop->last) , @endif
                    @endforeach
                @empty
                    N/A
                @endforelse


                    </span>
                </h3> --}}



                {{-- <h3 class="theme-hold-background">
                    {{ !empty($currentTemplate) ? $currentTemplate->hold_queue : __('text.Hold Queue') }} : <span>

                        @forelse($holdCalls as $index => $holdCall)
                            <a href="javascript:void(0)" wire:key="hold_{{$index}}">{{ $holdCall['token'] }}</a>
                            @if (!$loop->last)
                                ,
                            @endif
                        @empty
                            N/A
                        @endforelse

                    </span>
                </h3> --}}



                <div class="powered-image">
                    <div class="image"
                        style="background-image:url({{ asset('storage/' . $currentTemplate?->powered_image) }});">
                    </div>
                </div>
            </div>
            {{-- <div class="footer_bottom {{ !empty($currentTemplate) && $currentTemplate->is_datetime_show == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'time-added' : '' }}  align-{{ !empty($currentTemplate) && $currentTemplate->is_datetime_show == App\Models\ScreenTemplate::STATUS_ACTIVE ? $currentTemplate->datetime_position : '' }}"
                style="background:#000;">
                <div class="time-col theme-background"
                    style="">
                    <script>
                        function getFormattedDate() {
                            const options = {
                                weekday: 'short',
                                month: 'short',
                                day: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false
                            };
                            return new Date().toLocaleString('en-US', options);
                        }

                        function updateClock() {
                            document.getElementById("showClock").innerHTML = getFormattedDate();
                        }

                        // Call updateClock every second to update the time
                        setInterval(updateClock, 1000);
                    </script>
                    <div id="showClock"
                        style="color:{{ !empty($currentTemplate) ? $currentTemplate->datetime_font_color : '' }};">
                    </div>

                </div>
                <p class="disclaimer" style="margin:0; font-weight:500; text-align: center; color:#fff;">
                    <marquee>
                        {{ __('text.Disclaimer: Queue number may not be called in sequence. If your number have been missed, please re-queue for a new number. Thank you') }}.
                    </marquee>
                </p>
            </div> --}}
        </div>

    </div>
    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" ></script>
    <script src="{{ asset('/js/display.js?v='.time()) }}"></script>
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=Gc5DXRcK"></script>
    <script src="https://www.youtube.com/iframe_api"></script>

        <script>
        Livewire.on('announcement-display', (response) => {

        let speech = response[0].speech;
        let screenTune = response[0].screen_tune;
        let voice_lang = response[0].voice_lang;

        // Add audio element for notification sound
        let audioElement = document.createElement('audio');
        audioElement.id = 'notificationSound';
        audioElement.src = '/voices/dingdong.mp3';
        audioElement.preload = 'auto';
        audioElement.style.display = 'none';
        document.body.appendChild(audioElement);
        // Handle sound notification
        if (screenTune == 0) {
            audioElement.play().catch((err) => {
                console.error('Audio playback blocked:', err);
            });
            audioElement.addEventListener('ended', function () {
                document.body.removeChild(audioElement);
            });
        } else {
        if (typeof rvAgentPlayer !== 'undefined') {
            throw new Error('ResponsiveVoice Website Agent is already running');
        }

        var rvAgentPlayer = { version: 1 };
        var rvApiKey = 'Gc5DXRcK';
        var rvApiEndpoint = 'https://texttospeech.responsivevoice.org/v1/text:synthesize';

        if (typeof responsiveVoice === 'undefined') {
            alert('ResponsiveVoice is not loaded.');
            return;
        }

        // Extract the message from the response
        const textToSpeak = speech; // Default message if none provided

        // Check if speech synthesis is available
        // if (typeof responsiveVoice !== 'undefined') {
        //     responsiveVoice.speak(textToSpeak, "UK English Male", {rate: 1});
        // } else {
        //     alert('Speech synthesis is not supported in this browser.');
        // }

          let voiceName = "Spanish Female"; // default Spanish voice
        if (voice_lang && voice_lang.startsWith('es')) {
            voiceName = "Spanish Female";
        } else if (voice_lang && voice_lang.startsWith('vi')) {
            voiceName = "Vietnamese Female";
        } else if (voice_lang && voice_lang.startsWith('en')) {
            voiceName = "UK English Male"; // fallback to English
        }

         // Speak the text in the selected voice
        responsiveVoice.speak(textToSpeak, voiceName, { rate: 1 });
    }
    });

    // List of video IDs
  const videoIds = @json($videoIds); // Replace with your video IDs
  let currentVideoIndex = 0;

  // Load the YouTube IFrame Player API
  let player;

  function onYouTubeIframeAPIReady() {
    player = new YT.Player("player", {
      height: "50%",
      width: "100%",
      videoId: videoIds[currentVideoIndex],
      playerVars: {
            autoplay: 1, // Enable autoplay
            controls: 1, // Show player controls (1: enabled, 0: disabled)
            mute: 1, // Mute the video initially (optional)
        },
      events: {
        onStateChange: onPlayerStateChange,
      },
    });
  }

  // Function to handle video state changes
  function onPlayerStateChange(event) {
    // Check if the video has ended
    if (event.data === YT.PlayerState.ENDED) {
      playNextVideo();
    }
  }

  // Play the next video in the list
  function playNextVideo() {
    currentVideoIndex++;
    if (currentVideoIndex < videoIds.length) {
      player.loadVideoById(videoIds[currentVideoIndex]);
    } else {
        currentVideoIndex = 0;
        player.loadVideoById(videoIds[currentVideoIndex]);
    }
  }
</script>

<script src="{{asset('/js/app/call.js?v=3.1.0.0')}}"></script>

    <script>
        // var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
        //     cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
        //     encrypted: true
        // });
Pusher.logToConsole = true;
        const tenantId = {{ tenant('id') }};
        const locationId = {{ $location }};
        
        var pusher = new Pusher("{{ $reverbKey }}", {
        cluster: '', // Required by Pusher library, but empty for Reverb
        wsHost: "{{ $reverbHost }}",
        wsPort: {{ $reverbPort }},
        wssPort: {{ $reverbPort }},
        forceTLS: "{{ $reverbScheme }}" === 'https',
        enabledTransports: ['ws', 'wss'],
        encrypted: true, // Reverb doesn't need encryption for local
        disableStats: true
    });

        // var queueCall = pusher.subscribe("queue-call." + tenantId);

        // queueCall.bind('queue-call', function(data) {
        //     Livewire.dispatch('display-update', {
        //         event: data
        //     });
        // });

        var queueProgress = pusher.subscribe("queue-display." + tenantId + "." + locationId);

        queueProgress.bind('queue-display', function(data) {
            Livewire.dispatch('display-update', {
                event: data
            });
        });

        // var breakReason = pusher.subscribe("break-reason." + tenantId);

        // breakReason.bind('display-update', function(data) {});
    </script>


    @endpush
@endif

<script>
      document.addEventListener('livewire:init', () => {
       Livewire.on('refreshComponent', () => {
            window.location.reload(); // Refresh the component only, not the whole page
        });
        });
</script>
</div>
