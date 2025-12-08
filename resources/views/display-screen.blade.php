@extends('layouts.custom-display-layout')



@section('content')
<div class="wrapper wrapper-with-header-top">



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

            @keyframes blinkColor {
                0% { color: black; }
                50% { color: red; }
                100% { color: black; }
            }
            .blink-red {
                animation: blinkColor 500ms step-end infinite;
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



    {{-- Console logs --}}
    <div id="console-log-display" style="position: fixed; bottom: 0; left: 0; width: 100%; max-height: 200px; overflow-y: auto; background: rgba(0,0,0,0.8); color: #fff; font-size: 12px; z-index: 9999; padding: 5px;display:none">
        <strong>Console Logs:</strong>
    </div>

    @php
        // Video IDs
        $videoIds = collect($videoTemplates)->pluck('yt_vid')->filter()->values()->toArray();
        $queueToDisplay = $queues['display'];
        $waitingCalls = $queues['waiting'];
        $missedCalls = $queues['missed'];
        $holdCalls = $queues['hold'];
    @endphp

    {{-- Fullscreen button --}}
    <button class="requestfullscreen" id="toggleFullBtn">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
        </svg>
    </button>



    {{-- HEADER --}}
    <header class="w-full text-gray-800 body-font main-header-display {{ (!empty($currentTemplate) && $currentTemplate->is_header_show == 1 && $currentTemplate->is_logo == 1) ? '' : 'hidden' }}">
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
            <p class="text-center full" style="font-size: 15px;padding-bottom: 5px;font-weight: 600;">
                {{ $siteData?->queue_heading_first }}
            </p>
            <p class="text-center full" style="font-size: 15px;font-weight: 600;">
                {{ $siteData?->queue_heading_second }}
            </p>
        @else
            <div class="w-full flex flex-col items-center justify-center header1">
                <p class="py-2 text-center text-[6vh] md:text-[6vh] font-semibold leading-relaxed">
                    {{ $currentTemplate->header1_text }}
                </p>
            </div>

            <div class="w-full flex flex-col items-center justify-center header2" style="background-color: {{ $currentTemplate->header2_bg ?? '#000000' }}; color: {{ $currentTemplate->header2_text_color ?? '#ffffff' }};">
                <p class="py-1 text-center text-[30px] font-medium">
                    {{ $currentTemplate->header2_text }}
                </p>
            </div>
        @endif
    </header>

    {{-- BODY --}}
    <div class="table-display-inside">
        <div id="main-display" style="height: 251px;">
            <div class="table-display table-display-new11 video-with-queue {{ $currentTemplate->template_class ?? '' }}">
   @if (!empty($currentTemplate) && $currentTemplate->template != 'locally')
                {{-- LEFT: Images & Videos --}}
                <div class="column-large">
                    @if(!empty($imageTemplates))
                        <div class="slider" id="owl-slider-display">
                            @foreach($imageTemplates as $key => $image)
                                <div class="item slide-item">
                                    <div class="slider-inner">
                                        <img src="{{ url('storage/' . $image) }}" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($videoIds))
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
                {{-- RIGHT: Queue display --}}
                <div class="no-bottom-space element-data table-screen">
                    <div class="header-top">
                        <ul class="template-header Demodemo1">
                            <li class="content-view">
                                <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                    <h2 class="yellow one" style="color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
                                        {{ $currentTemplate->token_title ?? 'Token' }}
                                    </h2>
                                    <h2 class="yellow two" style="text-align:center; color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
                                        {{ $currentTemplate->counter_title ?? 'Counter' }}
                                    </h2>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <ul class="queue-data counter-listing Demodemo1 header-added data-{{ $currentTemplate->show_queue_number ?? 6 }}" style="height: 100%">
                        @foreach($queueToDisplay as $key => $display)
                            @php
                                 $fontColor = $display['status'] == App\Models\Queue::STATUS_PROGRESS ? $currentTemplate->current_serving_fontcolor : $currentTemplate->font_color;
                                // $fontColor = '#f20707';
                                $fontWeight = $display['status'] == App\Models\Queue::STATUS_PROGRESS ? 'bold' : 'normal';
                            @endphp
                            <li class="content-view">
                                <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                    <h1 class="token-text" data-queue-id="{{$display['id'] ?? ''}}" style="color:{{$fontColor ?? '#f20707'}};font-weight:{{$fontWeight}}">
                                        {{ $currentTemplate->is_name_on_display_screen_show ? $display['name'] : $display['token'] }}
                                    </h1>
                                    <h2 class="counter-text" data-queue-id="{{$display['id'] ?? ''}}" style="color:{{$fontColor ?? '#f20707'}};font-weight:{{$fontWeight}}">
                                        {{ $display['counter'] }}
                                    </h2>
                                </div>
                            </li>
                        @endforeach
                        @for($i = 0; $i < intval($currentTemplate->show_queue_number ?? 6) - count($queueToDisplay); $i++)
                            <li class="content-view">
                                <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                    <h1 class="yellow one "></h1>
                                    <h2 class="yellow two" style="{{ $currentTemplate->template == 'default-premium' ? "border-bottom: 5px solid; color: $currentTemplate->font_color" : '' }}"></h2>
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
                    id="bottom_calls_list" class="previously-served queue_no full-width missed {{ !empty($currentTemplate) && $currentTemplate->is_powered_by == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'powered-image-show' : '' }}">

                @if($currentTemplate->is_waiting_call_show)
                    <h3 class="theme-waiting-background">
                        Waiting Queue: {{ $waitingCalls->pluck($currentTemplate->is_name_on_display_screen_show ? 'name' : 'token')->implode(', ') ?: 'N/A' }}
                    </h3>
                @endif
                @if($currentTemplate->is_skip_call_show)
                    <h3 class="theme-missed-background">
                        Missed Queue: {{ $missedCalls->pluck($currentTemplate->is_name_on_display_screen_show ? 'name' : 'token')->implode(', ') ?: 'N/A' }}
                    </h3>
                @endif
                @if($currentTemplate->is_hold_queue)
                    <h3 class="theme-hold-background">
                        Hold Queue: {{ $holdCalls->pluck($currentTemplate->is_name_on_display_screen_show ? 'name' : 'token')->implode(', ') ?: 'N/A' }}
                    </h3>
                @endif
            </div>

             <div class="footer_bottom {{ !empty($currentTemplate) && $currentTemplate->is_datetime_show == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'time-added' : '' }}  align-{{ !empty($currentTemplate) && $currentTemplate->is_datetime_show == App\Models\ScreenTemplate::STATUS_ACTIVE ? $currentTemplate->datetime_position : '' }}"
                style="background:#000;">
                <div class="time-col theme-background">
                    <?php $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); ?>
                    <div id="showClocks"
                        style="color:{{ !empty($currentTemplate) ? $currentTemplate->datetime_font_color : '' }};">
                        {{ \Carbon\Carbon::now()->format($datetimeFormat) }}
                    </div>
                </div>


             @if(!empty($currentTemplate) && $currentTemplate->is_disclaimer == 1 && $teamId != 214)
                {{-- <p class="disclaimer" style="margin:0; font-weight:500; text-align: center; color:#fff; {{ $currentTemplate->template == 'default-premium' ? 'font-size: 2vh;' : '' }}">
                    @if($currentTemplate->template != 'default-premium')
                    <marquee>
                        {{ !empty($currentTemplate->disclaimer_title)  ? $currentTemplate->disclaimer_title.' :' : ""}} {{ $currentTemplate->display_screen_disclaimer }}
                    </marquee>
                    @else
                    {{ !empty($currentTemplate->disclaimer_title)  ? $currentTemplate->disclaimer_title.' :' : ""}} {{ $currentTemplate->display_screen_disclaimer }}
                    @endif
                </p> --}}
                @endif
            </div>
        </div>

        {{-- Audio --}}
        <audio id="audio" preload="none">
            <source src="https://bbjj.qwaiting.com/voice/Ding-noise/Ding-noise.mp3" type="audio/mpeg" />
        </audio>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/cdn/owl.carousel.min.js') }}"></script>
<script src="{{ asset('/js/display-bright.js?v=' . time()) }}"></script>
<script src="{{ asset('/js/responsivevoice.js?v=' . time() ) }}"></script>
<script src="{{ asset('/js/app/call.js?v=3.1.0.0') }}"></script>
<script src="https://www.youtube.com/iframe_api"></script>

<script>

    function playAudio() {
        const audio = document.getElementById("audio");
        if(!audio) return;
        audio.currentTime = 0;
        const p = audio.play();
        if(p && typeof p.then === 'function') p.catch(err => console.warn('Audio blocked:', err));
    }
    // document.getElementById('manualPlayAudio').addEventListener('click', playAudio);

    const videoIds = @json($videoIds);
    let currentVideoIndex = 0;
    let player;

    function onYouTubeIframeAPIReady() {
        if(!videoIds.length) return;
        player = new YT.Player('player', {
            width: '100%',
            videoId: videoIds[currentVideoIndex],
            playerVars: { autoplay: 1, controls: 1, mute: 1, loop: 1, playlist: videoIds.join(','), rel: 0, modestbranding: 1 },
            events: {
                onStateChange: function(event) {
                    if(event.data === YT.PlayerState.ENDED) {
                        currentVideoIndex = (currentVideoIndex + 1) % videoIds.length;
                        player.loadVideoById(videoIds[currentVideoIndex]);
                    }
                }
            }
        });
    }

  function updateQueueDisplay(data) {
    const ul = document.querySelector('.queue-data');
    if (!ul) return;
    ul.innerHTML = '';

    const totalSlots = {{ $currentTemplate->show_queue_number ?? 6 }};
    const emptySlotStyle = "{{ $currentTemplate->template == 'default-premium' ? 'border-bottom: 5px solid; color: ' . $currentTemplate->font_color : '' }}";

    // Actual queue items
    data.display.forEach(display => {
        const fontColor = display.status === "<?= \App\Models\Queue::STATUS_PROGRESS ?>"
            ? "<?= $currentTemplate->current_serving_fontcolor ?>"
            : "<?= $currentTemplate->font_color ?>";
        const fontWeight = display.status === "<?= \App\Models\Queue::STATUS_PROGRESS ?>" ? 'bold' : 'normal';

        const li = document.createElement('li');
        li.classList.add('content-view');
        li.innerHTML = `
            <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                <h1 class="token-text" data-queue-id="${display.id}" style="color:${fontColor}; font-weight:${fontWeight}">
                    ${display.token}
                </h1>
                <h2 class="counter-text" data-queue-id="${display.id}" style="color:${fontColor}; font-weight:${fontWeight}">
                    ${display.counter}
                </h2>
            </div>`;
        ul.appendChild(li);
    });

    // Empty slots
    const remainingSlots = totalSlots - data.display.length;
    for (let i = 0; i < remainingSlots; i++) {
        const li = document.createElement('li');
        li.classList.add('content-view');
        li.innerHTML = `
            <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                <h1 class="yellow one"></h1>
                <h2 class="yellow two" style="${emptySlotStyle}"></h2>
            </div>`;
        ul.appendChild(li);
    }

    // Footer
    const footer = document.getElementById('bottom_calls_list');
    if (footer) {
        footer.innerHTML = '';
        const isWaitingCallShow = {{ $currentTemplate->is_waiting_call_show ? 'true' : 'false' }};
        const isSkipCallShow = {{ $currentTemplate->is_skip_call_show ? 'true' : 'false' }};
        const isHoldQueueShow = {{ $currentTemplate->is_hold_queue ? 'true' : 'false' }};

        if (data.waiting.length && isWaitingCallShow)
            footer.innerHTML += `<h3 class="theme-waiting-background">Waiting Queue: ${data.waiting.map(d => "{{ $currentTemplate->is_name_on_display_screen_show }}" ? d.name : d.token).join(', ')}</h3>`;
        if (data.missed.length && isSkipCallShow)
            footer.innerHTML += `<h3 class="theme-missed-background">Missed Queue: ${data.missed.map(d => "{{ $currentTemplate->is_name_on_display_screen_show }}" ? d.name : d.token).join(', ')}</h3>`;
        if (data.hold.length && isHoldQueueShow)
            footer.innerHTML += `<h3 class="theme-hold-background">Hold Queue: ${data.hold.map(d => "{{ $currentTemplate->is_name_on_display_screen_show }}" ? d.name : d.token).join(', ')}</h3>`;
    }

    // Re-apply blinking classes after DOM refresh
    applyBlinkClasses();
}

    function refreshQueues() {
        // playAudio();

        $.ajax({
            url: "{{ route('bbj.refresh') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                location: "{{ $location }}",
                template_id: "{{ $currentTemplate->id }}"
            },
            dataType: 'json',
            success: function(data) {
                if(data.error) return console.warn('Queue refresh error:', data.error);
                console.log('updateQueueDisplay');
                updateQueueDisplay(data);
                console.log('updateQueueDisplay end ');
                // playAudio();
            },
            error: function(xhr, status, error) {
                console.error('Queue refresh failed:', xhr.responseText || error);
            }
        });
    }

    // Pusher
    var pusher = new Pusher("{{ $pusherKey }}", { cluster: "{{ $pusherCluster }}", encrypted: true });
    var queueChannel = pusher.subscribe("queue-display.{{ tenant('id') }}.{{ $location }}");
    queueChannel.bind('queue-display', function(data) { refreshQueues(); });

    var queueAudio = pusher.subscribe("display-audio.{{ tenant('id') }}.{{ $location }}");
        queueAudio.bind('display-audio', function(data) {
            playAudio();
           if({{ $currentTemplate->display_behavior  }} == 2){
             setTimeout(() => {
             if (data.queue && data.queue.id) {
                const activeQueueId = data.queue.id;
                  console.log('activeQueueId' +activeQueueId);
                highlightCalledTokenById(activeQueueId);
                  console.log('end highlightCalledTokenById');
            } else {
                console.log('No active queue data');
            }
             }, 500);
            }

        });

    // Track active blink states by queue id
    const activeBlinks = new Map(); // id -> { timeoutId, endsAt }

    function applyBlinkClasses() {
        activeBlinks.forEach((state, id) => {
            const selector = `.queue-data .token-text[data-queue-id="${id}"]`;
            const tokenEl = document.querySelector(selector);
            const counterEl = tokenEl ? tokenEl.parentElement.querySelector('.counter-text') : null;
            if (tokenEl && counterEl) {
                tokenEl.classList.add('blink-red');
                counterEl.classList.add('blink-red');
            }
        });
    }

    function stopBlinkForId(id) {
        const state = activeBlinks.get(String(id));
        if (!state) return;
        const selector = `.queue-data .token-text[data-queue-id="${id}"]`;
        const tokenEl = document.querySelector(selector);
        const counterEl = tokenEl ? tokenEl.parentElement.querySelector('.counter-text') : null;
        if (tokenEl && counterEl) {
            tokenEl.classList.remove('blink-red');
            counterEl.classList.remove('blink-red');
        }
        if (state.timeoutId) clearTimeout(state.timeoutId);
        activeBlinks.delete(String(id));
    }

    function highlightCalledTokenById(activeQueueId) {
        if (!activeQueueId) return;
        const idKey = String(activeQueueId);

        // If already blinking, extend it to 5s from now
        const existing = activeBlinks.get(idKey);
        if (existing && existing.timeoutId) {
            clearTimeout(existing.timeoutId);
        }

        const endsAt = Date.now() + 5000;
        const timeoutId = setTimeout(() => stopBlinkForId(idKey), endsAt - Date.now());
        activeBlinks.set(idKey, { timeoutId, endsAt });

        // Apply class to current DOM (will be re-applied after any DOM refresh)
        applyBlinkClasses();
    }


//       (function() {
//     const logContainer = document.getElementById('console-log-display');

//     if (logContainer) {
//         const originalLog = console.log;
//         console.log = function(...args) {
//             // Call original log
//             originalLog.apply(console, args);

//             // Show log on page
//             const msg = args.map(a => (typeof a === 'object' ? JSON.stringify(a) : a)).join(' ');
//             const div = document.createElement('div');
//             div.textContent = `[LOG] ${msg}`;
//             logContainer.appendChild(div);
//             logContainer.scrollTop = logContainer.scrollHeight;
//         };

//         const originalWarn = console.warn;
//         console.warn = function(...args) {
//             originalWarn.apply(console, args);
//             const msg = args.map(a => (typeof a === 'object' ? JSON.stringify(a) : a)).join(' ');
//             const div = document.createElement('div');
//             div.style.color = 'yellow';
//             div.textContent = `[WARN] ${msg}`;
//             logContainer.appendChild(div);
//             logContainer.scrollTop = logContainer.scrollHeight;
//         };

//         const originalError = console.error;
//         console.error = function(...args) {
//             originalError.apply(console, args);
//             const msg = args.map(a => (typeof a === 'object' ? JSON.stringify(a) : a)).join(' ');
//             const div = document.createElement('div');
//             div.style.color = 'red';
//             div.textContent = `[ERROR] ${msg}`;
//             logContainer.appendChild(div);
//             logContainer.scrollTop = logContainer.scrollHeight;
//         };
//     }
// })();


 function updateClock() {
        const clockEl = document.getElementById('showClocks');
        if(!clockEl) return;

        const now = new Date();

        // Format date/time like Carbon (adjust format as needed)
        const options = {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            hour12: false
        };
        clockEl.textContent = now.toLocaleString('en-GB', options).replace(',', '');
    }

    // Initial update
    updateClock();

    // Update every second
    setInterval(updateClock, 1000);



</script>
@endpush
