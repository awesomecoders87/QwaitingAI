
<div class="wrapper wrapper-with-header-top">

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
            /* .content-display{
                height:{{ !empty($currentTemplate) ? $currentTemplate->font_size . 'vh !important' : '100%' }}
            } */
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
            .theme-waiting-background:hover{
                background:{{ $currentTemplate?->waiting_queue_bg . ' !important' ?? '' }};
                opacity:0.8;
            }
            .theme-waiting-background{ background:{{ $currentTemplate?->waiting_queue_bg . ' !important' ?? '' }} }

            .theme-missed-background:hover{
                background:{{ $currentTemplate?->missed_queue_bg . ' !important' ?? '' }};
                opacity:0.8;
            }
            .theme-missed-background{ background:{{ $currentTemplate?->missed_queue_bg . ' !important' ?? '' }} }

            .theme-hold-background:hover{
                background:{{ $currentTemplate?->hold_queue_bg . ' !important' ?? '' }};
                opacity:0.8;
            }
            .theme-hold-background{ background:{{ $currentTemplate?->hold_queue_bg . ' !important' ?? '' }} }
            /* #owl-slider-display{
                display :none;
            } */
             
        </style>
    @endpush

    <button class="requestfullscreen" id="toggleFullBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
        </svg>
    </button>


    <header class="text-gray-800 body-font main-header-display  {{(!empty($currentTemplate) && $currentTemplate->is_header_show == App\Models\ScreenTemplate::STATUS_INACTIVE) ?'hidden':''}}" wire:ignore>
        <div class="container mx-auto  flex-wrap p-5 flex-col md:flex-row items-center justify-center">
          <div class="full text-center">
         @php 
              $url = request()->url();
              $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

              if ( strpos( $url, 'mobile/queue' ) !== false ) {
                $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
              }

          $logo =  App\Models\SiteDetail::viewImage($headerPage);
          @endphp


            @if(!empty($currentTemplate) )
                    @if( $currentTemplate->is_logo == App\Models\ScreenTemplate::STATUS_ACTIVE)
                    <img src="{{ url($logo)}}" class="w-100 h-100 max-w-44" style="display:inline-block" width="100"/>
                    
                    @else 
               <img src="{{ url($logo)}}" class="w-100 h-100 max-w-44" style="display:inline-block" width="100"/>
                  @endif
              @endif
            </div>    
           <div class="header_sec flex  items-center gap-3">
            </div wire:poll.500ms>
            <p class="text-center full" style="font-size: 18px;padding-top: 10px;font-weight: 600;">{{ $siteData?->queue_heading_first }}</p>
            <p class="text-center full" style="font-size: 18px;padding-top: 10px;font-weight: 600;">{{ $siteData?->queue_heading_second }}</p>
        </div>
      
    </header>   

    
    <div class="table-display-inside" wire:ignore.self>
        <div id="main-display"  wire:ignore.self>
            <div
                class="table-display table-display-new11 {{ !empty($currentTemplate) ? $currentTemplate->template_class : '' }}">
                <div class="column-large" wire:ignore>
                    @if(!empty($imageTemplates))
                    <div class="slider" id="owl-slider-display">
                        @forelse($imageTemplates as $key=> $image)
                       
                            <div class="item slide-item">
                         <div class="slider-inner" wire:key="image_{{ $key }}">
                                    <img src="{{ url('storage/' . $image) }}" />
                                </div>
                            </div>

                            @empty
                            @endforelse
                        </div>
                       @endif 
                     @if (!empty($videoIds) && count($videoIds) > 0)
                        @foreach($videoIds as $videoId)
                        <iframe  frameborder="0" allowfullscreen="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" title="Reception video" width="100%" height="100%" src="https://www.youtube.com/embed/{{ $videoId }}?autoplay=1&amp;controls=1&amp;mute=1&amp;enablejsapi=1&amp;loop=1&amp;playlist={{ $videoId }}"></iframe>
                            @endforeach
                  
                    @endif
                </div>
                <div class="no-bottom-space element-data table-screen">
                    <div class="header-top">
                        <ul class="template-header Demodemo1">
                            <li class="content-view">
                                <div class="content-display">
                                    <h2 class="yellow one ">
                                        {{ !empty($currentTemplate) ? $currentTemplate->token_title : __('text.Token') }}
                                    </h2>
                                    <h2 class="yellow two " style="text-align:center;">
                                        {{ !empty($currentTemplate) ? $currentTemplate->counter_title : __('text.Counter') }}
                                    </h2>
                                </div>
                            </li>
                            <li class="content-view">
                                <div class="content-display">
                                    <h2 class="yellow one ">
                                        {{ !empty($currentTemplate) ? $currentTemplate->token_title : __('text.Token') }}
                                    </h2>
                                    <h2 class="yellow two " style="text-align:center;">
                                        {{ !empty($currentTemplate) ? $currentTemplate->counter_title : __('text.Counter') }}
                                    </h2>
                                </div>
                            </li>
                        </ul>

                    </div>


                    <ul class="queue-data  counter-listing Demodemo1 header-added  data-{{ !empty($currentTemplate) ? $currentTemplate->show_queue_number : '6' }}"
                        style="height: 41px;">
         
                        @foreach ($queueToDisplay as $key=> $display)
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
                            <li class="content-view" wire:key="ticket_{{$key}}">
                                <div class="content-display">

                                    <h1 class="yellow one {{ $isInProgress ? ' red-color' : '' }}"
                                        style="color:{{ $activeTicketColor }}  ">
                                        {{ $currentTemplate->is_name_on_display_screen_show == '1' ? $display->name : $display->start_acronym . '' . $display->token }}
                                    </h1>
                                    <h2 class="yellow two {{ $isInProgress ? ' red-color' : '' }}"
                                        style="color:{{ $activeTicketColor }}  ">
                                        {{ $display->counter?->name }}
                                    </h2>
                                </div>
                            </li>
                        @endforeach
                       @for($i = 0; $i < intval($currentTemplate->show_queue_number) - count($queueToDisplay); $i++)
                       <li class="content-view">
                            <div class="content-display">
                                <h1 class="yellow one "></h1>
                                <h2 class="yellow two "></h2>
                            </div>
                        </li>
                        @endfor
                    </ul>
                </div>
            </div>
        </div>

        <div id="footer" >
            <div
                class="previously-served queue_no full-width missed  {{ !empty($currentTemplate) && $currentTemplate->is_powered_by == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'powered-image-show' : '' }}">

                @if(!empty($currentTemplate)  && $currentTemplate->is_waiting_call_show == App\Models\ScreenTemplate::STATUS_ACTIVE )

                <h3 class="theme-waiting-background">
                  {{ !empty($currentTemplate) ? $currentTemplate->waiting_queue : __('text.Waiting Queue') }} : <span>

                    @forelse($waitingCalls as $index => $waitingCall)
                        <a href="javascript:void(0)" wire:key="waiting_{{$index}}" >
                          {{ $currentTemplate->is_name_on_display_screen_show == '1' && isset($waitingCall['name']) ? $waitingCall['name'] : $waitingCall['start_acronym'] . '-' . $waitingCall['token'] }}
                        </a>
                        @if (!$loop->last) , @endif
                @empty
                    N/A
                @endforelse
                      

                    </span>
                </h3>
                @endif

                @if(!empty($currentTemplate)  && $currentTemplate->is_skip_call_show == App\Models\ScreenTemplate::STATUS_ACTIVE )

                <h3 class="theme-missed-background">
                  {{ !empty($currentTemplate) ? $currentTemplate->missed_queue : __('text.Missed Queue') }} : <span>

                    @forelse($missedCalls as $index => $missedCall)
                    
                    @foreach($missedCall['queue_storages'] as $storageIndex => $queueStorage)
                        <a href="javascript:void(0)" wire:key="missed_{{$storageIndex}}" >
                          {{ $currentTemplate->is_name_on_display_screen_show == '1' && isset($missedCall['queue_storages'][0]['name']) ? $missedCall['queue_storages'][0]['name'] : $missedCall['start_acronym'] . '-' . $missedCall['token'] }}
                        </a>
                       
                    @endforeach
                     @if (!$loop->last) , @endif
                @empty
                    N/A
                @endforelse
                      

                    </span>
                </h3>
                @endif

           @if(!empty($currentTemplate)  && $currentTemplate->is_hold_queue == App\Models\ScreenTemplate::STATUS_ACTIVE )
                <h3 class="theme-hold-background">
                    {{ !empty($currentTemplate) ? $currentTemplate->hold_queue : __('text.Hold Queue') }} : <span>

                        @forelse($holdCalls as $index => $holdCall)
                            <a href="javascript:void(0)" wire:key="hold_{{$index}}">{{ $currentTemplate->is_name_on_display_screen_show == '1' && isset($holdCall['name']) ? $holdCall['name'] : $holdCall['start_acronym'] . '-' . $holdCall['token'] }}</a>
                            @if (!$loop->last)
                                ,
                            @endif
                        @empty
                            N/A
                        @endforelse

                    </span>
                </h3>
                @endif
             

                <div class="powered-image">
                    <div class="image" 
                        style="background-image:url({{ url('storage/' . $currentTemplate?->powered_image) }});height:40px">
                    </div>
                </div>
            </div>
          
            <div class="footer_bottom {{ !empty($currentTemplate) && $currentTemplate->is_datetime_show == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'time-added' : '' }}  align-{{ !empty($currentTemplate) && $currentTemplate->is_datetime_show == App\Models\ScreenTemplate::STATUS_ACTIVE ? $currentTemplate->datetime_position : '' }}"
                style="background:#000;">
                <div class="time-col theme-background"
                    style="">
                    <script>
                    //     const sessionTimezone = @json(session('timezone_set', 'UTC'));

                    // function getFormattedDate() {
                    //     const options = {
                    //         weekday: 'short',
                    //         month: 'short',
                    //         day: '2-digit',
                    //         year: 'numeric',
                    //         hour: '2-digit',
                    //         minute: '2-digit',
                    //         second: '2-digit',
                    //         hour12: false,
                    //         timeZone: sessionTimezone 
                    //     };
                    //     return new Intl.DateTimeFormat('en-US', options).format(new Date());
                    // }

                    //     function updateClock() {
                    //         document.getElementById("showClock").innerHTML = getFormattedDate();
                    //     }

                       
                    //     setInterval(updateClock, 1000);
                    </script>
                     <?php  $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); ?>
                    <div id="showClocks" wire:poll.500mS
                        style="color:{{ !empty($currentTemplate) ? $currentTemplate->datetime_font_color : '' }};">{{\Carbon\Carbon::now()->format($datetimeFormat)}}
                    </div>

                </div>
                @if(!empty($currentTemplate)  &&  $currentTemplate->is_disclaimer == 1)
                <p class="disclaimer" style="margin:0; font-weight:500; text-align: center; color:#fff;">
                    <marquee>
                       Disclaimer: {{ $currentTemplate->display_screen_disclaimer }}
                    </marquee>
                </p>
                @endif
            </div>
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

         // Determine voice based on language, fallback to Spanish if language starts with 'es'
        let voiceName = "Spanish Female"; // default Spanish voice
        if (voice_lang && voice_lang.startsWith('es')) {
            voiceName = "Spanish Female";
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
  let imageTemplates = @php echo json_encode($imageTemplates); @endphp;
  let imageCount = imageTemplates.length;

  function onYouTubeIframeAPIReady() {
    const height = imageCount > 0 ? "50%" : "100%";

    player = new YT.Player("player", {
      height: height,
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
     

        var pusher = new Pusher("{{ $pusherKey }}", {
        cluster: "{{ $pusherCluster }}",
        encrypted: true
    });

        

        var queueProgress = pusher.subscribe("queue-display.{{ tenant('id') }}.{{$location}}");

        queueProgress.bind('queue-display', function(data) {
            Livewire.dispatch('display-update', {
                event: data
            });
        });

        
    </script>

   
    <!-- <script>
        
      window.addEventListener('load', adjustLayout);
        window.addEventListener('resize', adjustLayout);

        function adjustLayout() {
            const windowHeight = window.innerHeight;

            const navbar = document.querySelector('.main-header-display');
            const footer = document.querySelector('#footer');

            const navbarHeight = navbar ? navbar.offsetHeight : 0;
            const footerHeight = footer ? footer.offsetHeight : 0;

            // Apply height adjustments
            document.documentElement.style.height = '100vh';
            document.body.style.height = '100vh';

            if (navbar) {
                navbar.style.height = `${navbarHeight}px`;
            }

            const tableInside = document.querySelector('.table-display-inside');
            if (tableInside) {
                tableInside.style.height = `${windowHeight - navbarHeight}px`;
            }

            const mainDisplay = document.querySelector('#main-display');
            if (mainDisplay) {
                mainDisplay.style.height = `${windowHeight - navbarHeight - footerHeight}px`;
            }
        }
    </script> -->
     <script>

    let currentDate = new Date().toDateString();

    setInterval(() => {
        const now = new Date().toDateString();

        // If the date has changed, reload the page
        if (now !== currentDate) {
            location.reload(true); // true = force reload from server
        }
    }, 60000); // Check every 60 seconds (adjust if needed)
</script>
      @endpush
</div>
