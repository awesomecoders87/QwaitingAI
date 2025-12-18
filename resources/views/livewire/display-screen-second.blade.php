<div class="wrapper wrapper-with-header-top">

    <div id="console-log-display" style="position: fixed; bottom: 0; left: 0; width: 100%; max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.8); color: #fff; font-size: 12px; z-index: 9999; padding: 5px;">
    <strong>Console Logs:</strong>
</div>

    @php
    // Build the video IDs from templates (skip empty)
    $videoIds = [];
    foreach ($videoTemplates as $videoTemplate) {
    if (!empty($videoTemplate['yt_vid'])) {
    $videoIds[] = $videoTemplate['yt_vid'];
    }
    }
    @endphp

    @push('styles')

    <link rel="stylesheet" href="{{ asset('css/cdn/owl.carousel.css') }}">
    <link href="{{ asset('/css/display.css') }}?v={{ time() }}" rel="stylesheet" />
    <style>

          @font-face {
            font-family: 'Grab Community EN v2.0 Inline';
            src: url('/tenancy/assets/fonts/GrabCommunityENv20-Inline.woff2') format('woff2'),
                url('/tenancy/assets/fonts/GrabCommunityENv20-Inline.woff') format('woff');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }


                @font-face {
            font-family: 'Grab Community Solid EN';
            src: url('/tenancy/assets/fonts/GrabCommunitySolidEN-Bold.woff2') format('woff2'),
                url('/tenancy/assets/fonts/GrabCommunitySolidEN-Bold.woff') format('woff');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }


                @font-face {
            font-family: 'Grab Community Solid EN';
            src: url('/tenancy/assets/fonts/GrabCommunitySolidEN-Medium.woff2') format('woff2'),
                url('/tenancy/assets/fonts/GrabCommunitySolidEN-Medium.woff') format('woff');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }

        .table-screen .content-view h1,
        .table-screen .content-view h2,
        .counter-listing .content-view h2,
        .counter-listing .content-view h1 {
            font-size: <?= !empty($currentTemplate) ? $currentTemplate->font_size . 'vh !important': '' ?>;
        }

        .theme-background {
            background: <?= $currentTemplate?->datetime_bg_color . ' !important' ?? '' ?>
        }

        .theme-background:hover,
        .add-btn.theme-color:hover,
        .add-btn.theme-color:focus,
        .btn:hover,
        .paginate_button:hover,
        .ui-datepicker-calendar .ui-state-active,
        #atabs .nav-tabs>li>a,
        .form-inner input[type="submit"]:hover {
            background: <?= $currentTemplate?->datetime_bg_color . ' !important' ?? '' ?>;
            border-color: <?= $currentTemplate?->datetime_bg_color . ' !important' ?? '' ?>;
            opacity: 0.8;
        }

        .theme-waiting-background:hover {
            background: <?= $currentTemplate?->waiting_queue_bg . ' !important' ?? '' ?>;
            opacity: 0.8;
        }

        .theme-waiting-background {
            background: <?= $currentTemplate?->waiting_queue_bg . ' !important' ?? '' ?>
        }

        .theme-missed-background:hover {
            background: <?= $currentTemplate?->missed_queue_bg . ' !important' ?? '' ?>;
            opacity: 0.8;
        }

       .theme-missed-background {
            background: <?=  $currentTemplate?->missed_queue_bg ? $currentTemplate->missed_queue_bg . ' !important' : 'transparent'  ?>;
            color: <?=  $currentTemplate?->missed_queue_text_color ?? '#000000'  ?>;
        }

        .theme-hold-background:hover {
            background: <?= $currentTemplate?->hold_queue_bg . ' !important' ?? '' ?>;
            opacity: 0.8;
        }

            .theme-hold-background {
            background: <?=  $currentTemplate?->hold_queue_bg ? $currentTemplate->hold_queue_bg . ' !important' : 'transparent'  ?>;
            color: <?=  $currentTemplate?->hold_queue_text_color ?? '#000000'  ?>;
            }

           .header1 {
                height: 16vh;
                background-color: <?=  $currentTemplate?->header1_bg ?? '#ffffff'  ?>;
                color: <?=  $currentTemplate?->header1_text_color ?? '#000000'  ?>;
                font-size: 6vh;
                font-family: 'Grab Community EN v2.0 Inline' !important;
            }

            .header2 {
                background-color: <?= $currentTemplate->header2_bg ?? '#ffffff' ?>;
                color: <?= $currentTemplate->header2_text_color ?? '#000000' ?>;
                font-family: 'Grab Community Solid EN' !important;
            }

            .content-display {
                display: table;
                width: 100%;
                height: 100%;
            }


    </style>

    @if ($currentTemplate->template === 'default-premium')
    <style>
        .table-display *,
        .counter-listing .content-view h2,
        .previously-served.queue_no h3 {
            font-family: 'Grab Community Solid EN';
            font-weight: 500;
        }
    </style>
@endif

    @endpush

    <button class="requestfullscreen" id="toggleFullBtn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
        </svg>
    </button>
    <button id="manualPlayAudio" style="position: fixed; bottom: 530px; right: 30%; z-index: 9999; padding: 10px 20px; font-size: 14px;">
    ▶ Play Audio
</button>

    {{-- HEADER --}}
    <header class="w-full text-gray-800 body-font main-header-display {{ (!empty($currentTemplate) && $currentTemplate->is_header_show == App\Models\ScreenTemplate::STATUS_ACTIVE && $currentTemplate->is_logo == App\Models\ScreenTemplate::STATUS_ACTIVE) ? '' : 'hidden' }}" wire:ignore>

        @if($currentTemplate->template != 'default-premium')
        <div class="w-full p-5 flex flex-col items-center justify-center">
            @php
            $url = request()->url();
            $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;
            if (strpos($url, 'mobile/queue') !== false) {
            $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
            }
            $logo = App\Models\SiteDetail::viewImage($headerPage);
            @endphp

            @if(!empty($currentTemplate))
            @if($currentTemplate->is_logo == App\Models\ScreenTemplate::STATUS_ACTIVE)
            <img src="{{ url($logo) }}" class="w-100 h-100 max-w-44" style="display:inline-block" width="100" />
            @else
            <img src="{{ url($logo) }}" class="w-100 h-100 max-w-44" style="display:inline-block" width="100" />
            @endif
            @endif
        </div>

        <div class="header_sec flex items-center gap-3"></div>
        <p class="text-center full" style="font-size: 15px;padding-bottom: 5px;font-weight: 600;">
            {{ $siteData?->queue_heading_first }}
        </p>
        <p class="text-center full" style="font-size: 15px;font-weight: 600;">
            {{ $siteData?->queue_heading_second }}
        </p>
        @else
        <div
            class="w-full flex flex-col items-center justify-center header1">
            {{-- First queue heading (larger height) --}}
            <p class="py-2 text-center text-[6vh] md:text-[6vh] font-semibold leading-relaxed test1">
                {{ $currentTemplate->header1_text }}
            </p>
        </div>

        <div
            class="w-full flex flex-col items-center justify-center header2"
            style="background-color: {{ $currentTemplate->header2_bg ?? '#000000' }}; color: {{ $currentTemplate->header2_text_color ?? '#ffffff' }};">
            {{-- Second queue heading (smaller height) --}}
            <p class="py-1 text-center text-[30px] font-medium">
                {{ $currentTemplate->header2_text }}
            </p>
        </div>
        @endif

    </header>


    {{-- BODY --}}
    <div class="table-display-inside" wire:ignore.self>
        <div id="main-display" wire:ignore.self>
            <div class="table-display table-display-new11 {{ !empty($currentTemplate) ? $currentTemplate->template_class : '' }}">
                @if (!empty($currentTemplate) && $currentTemplate->template != 'locally')
                <div class="column-large" wire:ignore>
                    {{-- Image slider --}}
                    @if(!empty($imageTemplates))
                    <div class="slider" id="owl-slider-display">
                        @forelse($imageTemplates as $key => $image)
                        <div class="item slide-item">
                            <div class="slider-inner" wire:key="image_{{ $key }}">
                                <img src="{{ url('storage/' . $image) }}" />
                            </div>
                        </div>
                        @empty
                        @endforelse
                    </div>
                    @endif

                    {{-- Single YouTube player container (videos play sequentially) --}}
                    @if (!empty($videoIds))
                    {{-- If you want it to share space with images: use 50vh; else 100% --}}
                    <div id="player" style="width:100%; height: {{ !empty($imageTemplates) ? '50vh' : '100%' }};"></div>
                    @endif
                </div>
                @endif

                @if (!empty($currentTemplate) && $currentTemplate->template === 'locally')
                {{-- Local Video Player --}}
                <div class="column-large" wire:ignore>
                    @include('partials.local-video-player')
                </div>
                @endif

                {{-- RIGHT / QUEUE AREA --}}
                <div class="no-bottom-space element-data table-screen">
                    <div class="header-top">
                        <ul class="template-header Demodemo1">
                            <li class="content-view">
                                <div
                                    class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                    <h2 class="yellow one" style="color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
                                        {{ !empty($currentTemplate) ? $currentTemplate->token_title : __('text.Token') }}
                                    </h2>
                                    <h2 class="yellow two" style="text-align:center; color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
                                        {{ !empty($currentTemplate) ? $currentTemplate->counter_title : __('text.Counter') }}
                                    </h2>
                                </div>
                            </li>

                            <li class="content-view">
                                <div
                                    class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                    <h2 class="yellow one" style="color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
                                        {{ !empty($currentTemplate) ? $currentTemplate->token_title : __('text.Token') }}
                                    </h2>
                                    <h2 class="yellow two" style="color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
                                        {{ !empty($currentTemplate) ? $currentTemplate->counter_title : __('text.Counter') }}
                                    </h2>
                                </div>
                            </li>

                        </ul>
                    </div>

                    <ul class="queue-data counter-listing Demodemo1 header-added data-{{ !empty($currentTemplate) ? $currentTemplate->show_queue_number : '6' }}"
                            style="height: 100%">
                           @foreach($queueToDisplay as $key => $display)
                           @php
                           $fontColor = $currentTemplate->font_color;
                           $fontweight = 'normal';
                           if($display['status'] == App\Models\Queue::STATUS_PROGRESS){
                           $fontColor = $currentTemplate->current_serving_fontcolor;
                              $fontweight = 'bold';
                           }
                           @endphp
                            <li class="content-view">
                                <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                    <h1 style="color:{{$fontColor}};font-weight:{{$fontweight}}">
                                        {{ $currentTemplate->is_name_on_display_screen_show == '1' ? $display['name'] : $display['token'] }}
                                    </h1>
                                    <h2 style="color:{{$fontColor}};font-weight:{{$fontweight}}">{{ $display['counter'] }}</h2>
                                </div>
                            </li>
                            @endforeach

                            @for($i = 0; $i < intval($currentTemplate->show_queue_number) - count($queueToDisplay); $i++)
                                <li class="content-view">
                                    <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                        <h1 class="yellow one "></h1>
                                        <h2 class="yellow two"
                                            style="{{ !empty($currentTemplate) && $currentTemplate->template == 'default-premium' ? 'border-bottom: 5px solid;' : '' }} {{ $currentTemplate->template == 'default-premium' ? "color: $currentTemplate->font_color " : '' }}">
                                        </h2>

                                    </div>
                                </li>
                                @endfor
                        </ul>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div id="footer">
            <div
                class="previously-served queue_no full-width missed {{ !empty($currentTemplate) && $currentTemplate->is_powered_by == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'powered-image-show' : '' }}">

                @if(!empty($currentTemplate) && $currentTemplate->is_waiting_call_show == App\Models\ScreenTemplate::STATUS_ACTIVE)
                <h3 class="theme-waiting-background">
                    {{ $currentTemplate->waiting_queue ?? __('text.Waiting Queue') }} :
                    <span>
                        {{ $waitingCalls->isNotEmpty()
                            ? $waitingCalls->pluck($currentTemplate->is_name_on_display_screen_show == '1' ? 'name' : 'token')->implode(', ')
                            : 'N/A' }}
                    </span>
                </h3>
                @endif

                @if(!empty($currentTemplate) && $currentTemplate->is_skip_call_show == App\Models\ScreenTemplate::STATUS_ACTIVE)
                 <h3 class="theme-missed-background">
                        {{ $currentTemplate->missed_queue ?? __('text.Missed Queue') }} :
                        <span>
                            {{ $missedCalls->isNotEmpty()
                                ? $missedCalls->pluck($currentTemplate->is_name_on_display_screen_show == '1' ? 'name' : 'token')->implode(', ')
                                : 'N/A' }}
                        </span>
                    </h3>
                @endif



                @if(!empty($currentTemplate) && $currentTemplate->is_hold_queue == App\Models\ScreenTemplate::STATUS_ACTIVE && $currentTemplate->template != 'default-premium')
                 <h3 class="theme-hold-background">
                            {{ $currentTemplate->hold_queue ?? __('text.Hold Queue') }} :
                            <span>
                                {{ $holdCalls->isNotEmpty()
                                    ? $holdCalls->pluck($currentTemplate->is_name_on_display_screen_show == '1' ? 'name' : 'token')->implode(', ')
                                    : 'N/A' }}
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
                <div class="time-col theme-background">
                    <?php $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); ?>
                    <div id="showClocks" wire:poll.60s
                        style="color:{{ !empty($currentTemplate) ? $currentTemplate->datetime_font_color : '' }};">
                        {{ \Carbon\Carbon::now()->format($datetimeFormat) }}
                    </div>
                </div>

                @if(!empty($currentTemplate) && $currentTemplate->is_disclaimer == 1)
                <p class="disclaimer" style="margin:0; font-weight:500; text-align: center; color:#fff; {{ $currentTemplate->template == 'default-premium' ? 'font-size: 2vh;' : '' }}">
                    @if($currentTemplate->template != 'default-premium')
                    <marquee>
                        {{ !empty($currentTemplate->disclaimer_title)  ? $currentTemplate->disclaimer_title.' :' : ""}} {{ $currentTemplate->display_screen_disclaimer }}
                    </marquee>
                    @else
                    {{ !empty($currentTemplate->disclaimer_title)  ? $currentTemplate->disclaimer_title.' :' : ""}} {{ $currentTemplate->display_screen_disclaimer }}
                    @endif
                </p>
                @endif
            </div>
        </div>

<audio id="dingAudio" preload="auto">
  <source src="https://bbjj.qwaiting.com/voice/Ding-noise/Ding-noise.mp3" type="audio/mpeg">
</audio>

    </div>


</div>

    @push('scripts')

    <script src="{{ asset('js/cdn/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('/js/display.js?v=' . time()) }}"></script>
    <script src="{{ asset('/js/responsivevoice.js?v=' . time() ) }}"></script>
    <script src="{{ asset('/js/app/call.js?v=3.1.0.0') }}"></script>

    {{-- YouTube Iframe API (only once) --}}
    <script src="https://www.youtube.com/iframe_api"></script>

     <script>

document.addEventListener('livewire:init', () => {

    // Audio file URL
    const dingFile = "https://bbjj.qwaiting.com/voice/Ding-noise/Ding-noise.mp3";
    let dingSound = null;
    let initialized = false;

    // Initialize audio
    function initDingSound() {
        if (initialized) return;

        try {
            if (typeof BS !== "undefined") {
                // BrightSign native sound
                dingSound = new BS.sound(dingFile);
                console.log("[BrightSign] Sound initialized:", dingFile);
            } else {
                // Browser fallback
                dingSound = document.getElementById("dingAudio");
                if (!dingSound) {
                    dingSound = new Audio(dingFile);
                    dingSound.preload = "auto";
                }
                console.log("[Browser] Audio element initialized:", dingFile);
            }
            initialized = true;
        } catch (err) {
            console.error("[DingSound] Initialization failed:", err);
        }
    }

    // Play audio function
    function playDing() {
        try {
            if (!initialized) initDingSound();

            if (!dingSound) {
                console.warn("[DingSound] Audio object not found.");
                return;
            }

            if (typeof BS !== "undefined") {
                // BrightSign device
                dingSound.play();
                console.log("[BrightSign] Playing ding via BS.sound()");
            } else {
                // Browser
                dingSound.currentTime = 0;
                const playPromise = dingSound.play();
                if (playPromise && typeof playPromise.then === "function") {
                    playPromise.catch(err => {
                        console.error("[DingSound] Play blocked/error:", err);
                    });
                }
                console.log("[Browser] Playing ding via HTML5 Audio");
            }
        } catch (err) {
            console.error("[DingSound] Play failed:", err);
        }
    }

    // Manual play button
    const manualBtn = document.getElementById('manualPlayAudio');
    if (manualBtn) {
        manualBtn.addEventListener('click', playDing);
    }

    // Livewire event triggers
    if (window.Livewire) {
        // New announcement
        Livewire.on('announcement-display', () => playDing());

        // Refresh component
        Livewire.on('refreshcomponent', () => {
            setTimeout(() => location.reload(), 1000);
        });
    }

});

document.addEventListener('livewire:init', () => {


    Livewire.on('refreshcomponent', () => {
        setTimeout(() => {
                location.reload();
            }, 1000);
    });

        });
    </script>

    {{-- Pusher / Livewire updates --}}

    <script>
        document.addEventListener('livewire:init', () => {
            const tenantId = {{ tenant('id') }};
            const locationId = {{ $location }};

            var pusher = new Pusher("{{ $reverbKey }}", {
                wsHost: "{{ $reverbHost }}",
                wsPort: {{ $reverbPort }},
                wssPort: {{ $reverbPort }},
                forceTLS: "{{ $reverbScheme }}" === 'https',
                enabledTransports: ['ws', 'wss'],
                encrypted: true,
            });

            var queueProgress = pusher.subscribe("queue-display." + tenantId + "." + locationId);
            queueProgress.bind('queue-display', function(data) {
                Livewire.dispatch('display-update', {
                    event: data
                });
            });



        });

    </script>

    {{-- SINGLE YT PLAYER: play videoIds sequentially in loop --}}
    <script>
        const videoIds = @json($videoIds);
        let currentVideoIndex = 0;
        let player;

        // Called by the YT Iframe API
        function onYouTubeIframeAPIReady() {
            if (!videoIds.length) return;

            // If you also show images, shrink player height via CSS above
            player = new YT.Player('player', {
                width: '100%',
                videoId: videoIds[currentVideoIndex],
                playerVars: {
                    autoplay: 1,
                    controls: 1,
                    mute: 1,
                    loop: 1,
                    playlist: videoIds.join(','), // required for loop behavior
                    rel: 0,
                    modestbranding: 1
                },
                events: {
                    onStateChange: onPlayerStateChange
                }
            });
        }

        function onPlayerStateChange(event) {
            if (event.data === YT.PlayerState.ENDED) {
                playNextVideo();
            }
        }

        function playNextVideo() {
            if (!videoIds.length) return;
            currentVideoIndex = (currentVideoIndex + 1) % videoIds.length;
            player.loadVideoById(videoIds[currentVideoIndex]);
        }
    </script>

    {{-- Force reload on date change --}}
    <script>
        let currentDate = new Date().toDateString();
        setInterval(() => {
            const now = new Date().toDateString();
            if (now !== currentDate) {
                location.reload(true);
            }
        }, 60000);


        (function() {
    const logContainer = document.getElementById('console-log-display');

    if (logContainer) {
        const originalLog = console.log;
        console.log = function(...args) {
            // Call original log
            originalLog.apply(console, args);

            // Show log on page
            const msg = args.map(a => (typeof a === 'object' ? JSON.stringify(a) : a)).join(' ');
            const div = document.createElement('div');
            div.textContent = `[LOG] ${msg}`;
            logContainer.appendChild(div);
            logContainer.scrollTop = logContainer.scrollHeight;
        };

        const originalWarn = console.warn;
        console.warn = function(...args) {
            originalWarn.apply(console, args);
            const msg = args.map(a => (typeof a === 'object' ? JSON.stringify(a) : a)).join(' ');
            const div = document.createElement('div');
            div.style.color = 'yellow';
            div.textContent = `[WARN] ${msg}`;
            logContainer.appendChild(div);
            logContainer.scrollTop = logContainer.scrollHeight;
        };

        const originalError = console.error;
        console.error = function(...args) {
            originalError.apply(console, args);
            const msg = args.map(a => (typeof a === 'object' ? JSON.stringify(a) : a)).join(' ');
            const div = document.createElement('div');
            div.style.color = 'red';
            div.textContent = `[ERROR] ${msg}`;
            logContainer.appendChild(div);
            logContainer.scrollTop = logContainer.scrollHeight;
        };
    }
})();
    </script>
    @endpush

