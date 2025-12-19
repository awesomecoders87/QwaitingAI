<div class="wrapper wrapper-with-header-top">
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
        .counter-listing .content-view h1,
        span.queue-number {
            font-size: <?= !empty($currentTemplate) ? $currentTemplate->font_size . 'vh !important': '' ?>;
            line-height:1;
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
    @if ($currentTemplate->template === 'single_counter')
    <style>
         /* small extra tweaks */
    .screen-frame {
      border: 10px solid #2d3748; /* dark frame */
      border-radius: 8px;
      background-color: white;
      overflow: hidden;
    }
    .purple-edge {
      width: 36px;
      background: #6b21a8; /* purple */
      border-top-left-radius: 8px;
      border-bottom-left-radius: 8px;
    }
    /* large numeric outline feel */
    .queue-number {
      letter-spacing: -0.02em;
      line-height: 0.9;
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

    {{-- HEADER --}}
    <header class="w-full text-gray-800 body-font main-header-display"
        wire:ignore>

		{{-- ⭐ LOCATION NAME SECTION (Independent) --}}
		@if(!empty($currentTemplate) && $currentTemplate->is_location == 1)
			<div class="w-full text-center py-2"
				 style="
					  background-color: {{ $currentTemplate->location_bg ?? '#000' }};
					  color: {{ $currentTemplate->location_fontcolor ?? '#fff' }};
					  font-size: 18px;
					  font-weight: 600;
				 ">
				{{ $location_name ?? 'Location Name' }}
			</div>
		@endif


		{{-- ⭐ LOGO SECTION (Independent from location) --}}
		@if(!empty($currentTemplate) && $currentTemplate->is_header_show == App\Models\ScreenTemplate::STATUS_ACTIVE)
			<div class="w-full p-5 flex flex-col items-center justify-center 
				{{ $currentTemplate->is_logo == App\Models\ScreenTemplate::STATUS_ACTIVE ? '' : 'hidden' }}">

            @php
            $url = request()->url();
            $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;
            if (strpos($url, 'mobile/queue') !== false) {
            $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
            }
            $logo = App\Models\SiteDetail::viewImage($headerPage);
            @endphp

            <img src="{{ url($logo) }}" class="w-100 h-100 max-w-44" style="display:inline-block" width="100" />
        </div>
		@endif


		{{-- ⭐ TEXT HEADINGS (Same logic as before) --}}
		@if($currentTemplate->template != 'default-premium')
        <div class="header_sec flex items-center gap-3"></div>

			<p class="text-center full"
			   style="font-size: 15px;padding-bottom: 5px;font-weight: 600;">
            {{ $siteData?->queue_heading_first }}
        </p>

			<p class="text-center full"
			   style="font-size: 15px;font-weight: 600;">
            {{ $siteData?->queue_heading_second }}
        </p>

        @else
			<div class="w-full flex flex-col items-center justify-center header1">
            <p class="py-2 text-center text-[6vh] md:text-[6vh] font-semibold leading-relaxed test1">
                {{ $currentTemplate->header1_text }}
            </p>
        </div>

			<div class="w-full flex flex-col items-center justify-center header2"
				 style="background-color: {{ $currentTemplate->header2_bg ?? '#000000' }};
						color: {{ $currentTemplate->header2_text_color ?? '#ffffff' }};">
            <p class="py-1 text-center text-[30px] font-medium">
                {{ $currentTemplate->header2_text }}
            </p>
        </div>
        @endif



    {{-- BODY --}}
    <div class="table-display-inside" wire:ignore.self>
        <div id="main-display" wire:ignore.self>
            @if($currentTemplate->template != 'single_counter')
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
                                    <h2 class="yellow one" style="text-align:center;color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
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
                                    <h2 class="yellow one" style="text-align:center;color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
                                        {{ !empty($currentTemplate) ? $currentTemplate->token_title : __('text.Token') }}
                                    </h2>
                                    <h2 class="yellow two" style="text-align:center;color: {{ $currentTemplate->label_color_queues ?? '#000000' }};">
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
                            if($currentTemplate->display_behavior == 2) {
							$fontColor = '#000';
                            $fontweight = 'bold';
                            }else{

                            if($display['status'] == App\Models\Queue::STATUS_PROGRESS){
                                $fontColor = $currentTemplate->current_serving_fontcolor;
                                $fontweight = 'bold';
                            }
                          }

                           @endphp
                            <li class="content-view">
                                <div class="content-display h-100" style="display: table;width: 100%;height: 100%;">
                                    
									   @php
									$displayText = match($currentTemplate->is_name_on_display_screen_show) {
										1 => $display['token'],
										2 => $display['name'],
										3 => $display['name'] . ' / ' . $display['token'],
										default => '',
									};
								@endphp

							<h1 class="token-text text-center" data-queue-id="{{$display['id']}}" style="color:{{$fontColor}};font-weight:{{$fontweight}}">
								{!! $displayText !!}
                                    </h1>
                                    <h2 class="counter-text" data-queue-id="{{$display['id']}}" style="color:{{$fontColor}};font-weight:{{$fontweight}}">{{ $display['counter'] }}</h2>
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
            @else

          @php
                    // Default values
                    $fontColor   = $currentTemplate->current_serving_fontcolor;
                    $fontWeight  = 'bold';
                    $counterName = '';
                    $displayId   = '';
                    $displayText = 'No Call';

                    // Only one active queue should be displayed
                    $activeQueue = collect($queueToDisplay)
                                    ->firstWhere('status', App\Models\Queue::STATUS_PROGRESS);

                    if ($activeQueue) {
                        // Font settings
                        if ($currentTemplate->display_behavior == 2) {
                            $fontColor  = '#000';
                            $fontWeight = 'bold';
                        } else {
                            $fontColor  = $currentTemplate->current_serving_fontcolor;
                            $fontWeight = 'bold';
                        }

                        // Active queue data
                        $counterName = $activeQueue['counter'] ?? '';
                        $displayId   = $activeQueue['id'] ?? '';

                        // Determine display text format
                        $displayText = match($currentTemplate->is_name_on_display_screen_show) {
                            1 => $activeQueue['token'],
                            2 => $activeQueue['name'],
                            3 => $activeQueue['name'] . ' / ' . $activeQueue['token'],
                            default => '',
                        };
                    }
                @endphp
                        
                 <div>

                    <div class="w-full max-w-4xl flex justify-center m-auto">

                        <!-- Main content area -->
                        <div class="flex-1 p-8 sm:p-12 relative">

                        <!-- Top center text -->
                        <div class="w-full text-center">
                            <h2 class="text-3xl sm:text-4xl font-medium text-gray-900">Now Calling</h2>
                            <p class="text-blue-600 mt-2 text-lg font-semibold">Queue Number</p>
                        </div>

                        <!-- Big number -->
                        <div class="mt-8 flex items-center justify-center">
                            <span class=" token-text queue-number font-extrabold text-black drop-shadow-sm leading-none" data-queue-id="{{$displayId}}" style="color:{{$fontColor}};font-weight:{{$fontWeight}}">
                           {!! $displayText !!}
                            </span>
                        </div>
                        <div class="mt-8 flex items-center justify-center">
                            <span class="counter-text text-[2rem] sm:text-[4rem] font-extrabold text-black drop-shadow-sm leading-none text-black" data-queue-id="{{$displayId}}" style="font-weight:{{$fontWeight}}">
                          Counter: {{ $this->currentTemplate?->counters[0]->name ?? ''}}
                            </span>
                        </div>


                        </div>
                    </div>

                               
            </div>
            @endif
        </div>


        {{-- FOOTER --}}
        <div id="footer">
            <div
                class="previously-served queue_no full-width missed {{ !empty($currentTemplate) && $currentTemplate->is_powered_by == App\Models\ScreenTemplate::STATUS_ACTIVE ? 'powered-image-show' : '' }}">

 @if($currentTemplate->template == 'single_counter')
                     @if(!empty($currentTemplate) && $currentTemplate->is_skip_call_show == App\Models\ScreenTemplate::STATUS_ACTIVE)
                 <h3 class="theme-missed-background">
                        {{ $currentTemplate->missed_queue ?? __('text.Missed Queue') }} :
                        <span>
							@if ($missedCalls->isNotEmpty())
								@if ($currentTemplate->is_name_on_display_screen_show == 1)
									{{ $missedCalls->pluck('token')->implode(', ') }}
								@elseif ($currentTemplate->is_name_on_display_screen_show == 2)
									{{ $missedCalls->pluck('name')->implode(', ') }}
								@elseif ($currentTemplate->is_name_on_display_screen_show == 3)
									{{ $missedCalls->map(fn($item) => $item['token'] . ' / ' . $item['name'])->implode(', ') }}
								@else
									{{ $missedCalls->pluck('token')->implode(', ') }}
								@endif
							@else
								N/A
							@endif
                        </span>
                    </h3>
                @endif
                @endif

                @if($currentTemplate->type === "Category")
                
                @if(!empty($currentTemplate) && $currentTemplate->is_waiting_call_show == App\Models\ScreenTemplate::STATUS_ACTIVE)
                <h3 class="theme-waiting-background">
                    {{ $currentTemplate->waiting_queue ?? __('text.Waiting Queue') }} :
                    <span>
						@if ($waitingCalls->isNotEmpty())
							@if ($currentTemplate->is_name_on_display_screen_show == 1)
								{{ $waitingCalls->pluck('token')->implode(', ') }}
							@elseif ($currentTemplate->is_name_on_display_screen_show == 2)
								{{ $waitingCalls->pluck('name')->implode(', ') }}
							@elseif ($currentTemplate->is_name_on_display_screen_show == 3)
								{{ $waitingCalls->map(fn($item) => $item['name'] . ' / ' . $item['token'])->implode(', ') }}
							@else
								{{ $waitingCalls->pluck('token')->implode(', ') }}
							@endif
						@else
							N/A
						@endif
                    </span>
                </h3>
                @endif


                @if(!empty($currentTemplate) && $currentTemplate->is_skip_call_show == App\Models\ScreenTemplate::STATUS_ACTIVE)
                 <h3 class="theme-missed-background">
                        {{ $currentTemplate->missed_queue ?? __('text.Missed Queue') }} :
                        <span>
							@if ($missedCalls->isNotEmpty())
								@if ($currentTemplate->is_name_on_display_screen_show == 1)
									{{ $missedCalls->pluck('token')->implode(', ') }}
								@elseif ($currentTemplate->is_name_on_display_screen_show == 2)
									{{ $missedCalls->pluck('name')->implode(', ') }}
								@elseif ($currentTemplate->is_name_on_display_screen_show == 3)
									{{ $missedCalls->map(fn($item) => $item['token'] . ' / ' . $item['name'])->implode(', ') }}
								@else
									{{ $missedCalls->pluck('token')->implode(', ') }}
								@endif
							@else
								N/A
							@endif
                        </span>
                    </h3>
                @endif




                @if(!empty($currentTemplate) && $currentTemplate->is_hold_queue == App\Models\ScreenTemplate::STATUS_ACTIVE && $currentTemplate->template != 'default-premium')
                 <h3 class="theme-hold-background">
                            {{ $currentTemplate->hold_queue ?? __('text.Hold Queue') }} :
                            <span>
						@if ($holdCalls->isNotEmpty())
							@if ($currentTemplate->is_name_on_display_screen_show == 1)
								{{ $holdCalls->pluck('token')->implode(', ') }}
							@elseif ($currentTemplate->is_name_on_display_screen_show == 2)
								{{ $holdCalls->pluck('name')->implode(', ') }}
							@elseif ($currentTemplate->is_name_on_display_screen_show == 3)
								{{ $holdCalls->map(fn($item) => $item['token'] . ' / ' . $item['name'])->implode(', ') }}
							@else
								{{ $holdCalls->pluck('token')->implode(', ') }}
							@endif
						@else
							N/A
						@endif
                            </span>
                        </h3>
                @endif

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
                        {{ !empty($currentTemplate->disclaimer_title)  ? $currentTemplate->disclaimer_title.' :' : ""}} {{ $currentTemplate->display_screen_disclaimer ?? 'Queue numbers may be called out of sequence. Please wait for your number to be displayed.' }}
                    </marquee>
                    @else
                    {{ !empty($currentTemplate->disclaimer_title)  ? $currentTemplate->disclaimer_title.' :' : ""}} {{ $currentTemplate->display_screen_disclaimer ?? 'Queue numbers may be called out of sequence. Please wait for your number to be displayed.' }}
                    @endif
                </p>
                @endif
            </div>
        </div>
        @if($teamId ==214)
    <audio id="audio" preload="none">
  <source src="/voice/Ding-noise/Ding-noise.mp3" type="audio/mpeg" />
</audio>
@endif
    </div>


</div>

    @push('scripts')

      <script src="{{ asset('js/cdn/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('/js/display.js?v=' . time()) }}"></script>
    <script src="{{ asset('/js/responsivevoice.js?v=' . time() ) }}"></script>

    {{-- YouTube Iframe API (only once) --}}
    <script src="https://www.youtube.com/iframe_api"></script>

     <script>

// function playAudio(){
// 		$(document).ready(function(){
// 			const audio = document.querySelector("audio");
// 			if(!audio) return;
// 			const promise = audio.play();

// 			console.log('promise '+promise);

// 			if(promise !== undefined){
// 				promise.then(() => {
// 					//audio.pause();
// 				}).catch(error => {
// 					console.log('error',error)
// 				});
// 			}
// 		})


// 	}

function playAudio() {
    const audio = document.getElementById("audio");
    if (!audio) return alert("⚠️ Audio element missing!");

    audio.currentTime = 0;
    const p = audio.play();

    if (p && typeof p.then === "function") {
        p.catch(err => {
            console.log("[DING] play blocked/error:", err);
            alert("[DING] play blocked/error: " + err);
        });
    }
}


    document.addEventListener('livewire:init', () => {


    window.addEventListener('livewire:error', (event) => {
        const error = event.detail.exception; // contains the error message

        console.warn("Livewire error intercepted:", error);

        // Optional: send error to server
        Livewire.dispatch('frontend-error', { message: error });


            setTimeout(() => {
                location.reload();
            }, 1000); // 1 second delay



        // // Reload only if request timeout
        // if (error && error.includes("Request took too long")) {
        //     setTimeout(() => {
        //         location.reload();
        //     }, 1000); // 1 second delay
        // }

        // Prevent default Livewire popup
        event.preventDefault();
    });

    Livewire.on('refreshcomponent', () => {
 setTimeout(() => {
                location.reload();
            }, 1000);
    });

        });
    </script>

    {{-- ANNOUNCEMENT / TTS --}}
    <script>


        Livewire.on('announcement-display', (response) => {

if ("<?= $teamId ?>" != "214") {
            let primarySpeech = response[0].primary_speech;
            let speech = response[0].speech;
            let screenTune = response[0].screen_tune;
            let voice_lang = response[0].voice_lang;
            let dualVoice = response[0].dual;

            // ding dong sound
            let audioElement = document.createElement('audio');
            audioElement.id = 'notificationSound';
            audioElement.src = '/voices/dingdong.mp3';
            audioElement.preload = 'auto';
            audioElement.style.display = 'none';
            document.body.appendChild(audioElement);
console.log('screenTune'+audioElement);
            if (screenTune == 0) {
                audioElement.play().catch((err) => console.error('Audio playback blocked:', err));
                audioElement.addEventListener('ended', function() {
                document.body.removeChild(audioElement);
                });
            } else {
                if (typeof rvAgentPlayer !== 'undefined') {
                    console.log('ResponsiveVoice Website Agent is already running');
                    return;
                }
                var rvAgentPlayer = {
                    version: 1
                };
                if (typeof responsiveVoice === 'undefined') {
                    console.log('ResponsiveVoice is not loaded.');
                    return;
                }

                const voiceMap = {
                    'hi-IN': 'Hindi Female',
                    'fr-FR': 'French Female',
                    'es-ES': 'Spanish Female',
                    'ar-SA': 'Arabic Female',
                    'bn-IN': 'Hindi Female',
                    'vi-VN': 'Vietnamese Female',
                    'zh-CN': 'Chinese Female',
                    'en-US': 'UK English Female'
                };

                let voiceName = voiceMap[voice_lang] ?? 'UK English Female'; // fallback

                if (dualVoice) {

                    primarySpeech = primarySpeech.replaceAll("tiquete", "token");

                    console.log(primarySpeech);

                    responsiveVoice.speak(primarySpeech, 'UK English Female', {
                        rate: 1,
                        onend: function() {

                            responsiveVoice.speak(speech, voiceName, {
                                rate: 1
                            });
                        }
                    });
                } else {
                    if(voice_lang != 'vi-VN'){

                        responsiveVoice.speak(speech, voiceName, {
                            rate: 1
                        });
                    }else{
                        console.log('speech vi: '+speech);
                        console.log('voice_lang vi: '+voice_lang);
  speakVietnamese(speech);
                    }
   

                }


            }
        }else{
              playAudio();
        }
        });

    function speakVietnamese(text) {
    const msg = new SpeechSynthesisUtterance(text);
    msg.lang = "vi-VN";

    const voices = window.speechSynthesis.getVoices();
    msg.voice = voices.find(v => v.lang === "vi-VN") || null;

    window.speechSynthesis.speak(msg);
}

//          function highlightCalledTokenById(activeQueueId) {
//     if (!activeQueueId) return;

//     const allTokens = document.querySelectorAll('.queue-data .content-view');

//     allTokens.forEach(li => {
//         const tokenEl = li.querySelector('.token-text');
//         const counterEl = li.querySelector('.counter-text');
//         if (!tokenEl || !counterEl) return;

//         // Make sure both are numbers
//         if (parseInt(tokenEl.dataset.queueId) === parseInt(activeQueueId)) {
//             tokenEl.style.color = 'red';
//             counterEl.style.color = 'red';

//             setTimeout(() => {
//                 tokenEl.style.color = 'black';
//                 counterEl.style.color = 'black';
//             }, 5000);
//         } else {
//             tokenEl.style.color = 'black';
//             counterEl.style.color = 'black';
//         }
//     });
// }

     function highlightCalledTokenById(activeQueueId) {
    if (!activeQueueId) return;

    const allTokens = document.querySelectorAll('.queue-data .content-view');

    allTokens.forEach(li => {
        const tokenEl = li.querySelector('.token-text');
        const counterEl = li.querySelector('.counter-text');
        if (!tokenEl || !counterEl) return;

        const isActive = parseInt(tokenEl.dataset.queueId) === parseInt(activeQueueId);

        // Reset all to black
        tokenEl.style.color = 'black';
        counterEl.style.color = 'black';

        if (isActive) {
            let isRed = false;

            // Blink effect every 500ms
            const blinkInterval = setInterval(() => {
                isRed = !isRed;
                const color = isRed ? 'red' : 'black';
                tokenEl.style.color = color;
                counterEl.style.color = color;
            }, 500);

            // Stop blinking after 5 seconds
            setTimeout(() => {
                clearInterval(blinkInterval);
                tokenEl.style.color = 'black';
                counterEl.style.color = 'black';
            }, 5000);
        }
    });
}

                Livewire.on('highlight-color', (response) => {
                    setTimeout(() => {
                    if (response && response[0].activeid) {
                      let activeid =response[0].activeid;
                    //   console.log(activeid);
                     highlightCalledTokenById(activeid);
                    } else {
                        console.log('No active queue data');
                    }
                    }, 500);
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

    {{-- Pusher / Livewire updates --}}
    <script src="{{ asset('/js/app/call.js?v=3.1.0.0') }}"></script>
    <script>
        // IMMEDIATE TEST - This should show in console immediately
        console.log('═══════════════════════════════════════');
        console.log('🚀 DISPLAY SCREEN SCRIPT LOADED!');
        console.log('═══════════════════════════════════════');
        console.log('Pusher available:', typeof Pusher !== 'undefined');
        console.log('Livewire available:', typeof Livewire !== 'undefined');
        console.log('Reverb Key:', "{{ $reverbKey }}" ? 'SET' : 'MISSING');
        console.log('═══════════════════════════════════════');
        
        // SIMPLIFIED: Initialize immediately when script loads
        console.log('🔌 Display Screen Script loaded - Initializing Reverb...');
        
    // Check if Pusher is loaded first
    if (typeof Pusher === 'undefined') {
        console.error('❌ Pusher library not loaded! Waiting for it...');
        // Wait a bit and try again
        setTimeout(function() {
            if (typeof Pusher !== 'undefined') {
                console.log('✅ Pusher loaded, initializing now...');
                initializeReverbDisplay();
            } else {
                console.error('❌ Pusher still not loaded after wait!');
            }
        }, 1000);
    } else {
        // Pusher is already loaded, initialize immediately
        console.log('✅ Pusher already loaded');
        initializeReverbDisplay();
    }

    // Also listen for Livewire init as backup
    document.addEventListener('livewire:init', function() {
        console.log('🔄 Livewire initialized - ensuring Reverb is connected');
        if (typeof Pusher !== 'undefined' && !window.reverbDisplayInitialized) {
            initializeReverbDisplay();
        }
    });

        function initializeReverbDisplay() {
            // Prevent multiple initializations
            if (window.reverbDisplayInitialized) {
                console.log('⚠️ Reverb already initialized, skipping...');
                return;
            }
            
            // Check if Pusher is loaded
            if (typeof Pusher === 'undefined') {
                console.error('❌ Pusher library not loaded! Check if pusher.min.js is loaded.');
                return;
            }

            // Check if Reverb credentials are available
            const reverbKey = "{{ $reverbKey }}";
            const reverbHost = "{{ $reverbHost }}";
            const reverbPort = {{ $reverbPort }};
            const reverbScheme = "{{ $reverbScheme }}";
            const teamId = {{ tenant('id') }};
            const locationId = {{ $location }};

            if (!reverbKey || reverbKey === '') {
                console.error('❌ Reverb App Key is missing!');
                return;
            }

            const wsUrl = `${reverbScheme}://${reverbHost}:${reverbPort}`;
            console.log('═══════════════════════════════════════');
            console.log('🔌 REVERB CONNECTION (DISPLAY SCREEN)');
            console.log('═══════════════════════════════════════');
            console.log('📍 Server URL:', wsUrl);
            console.log('🔑 App Key:', reverbKey ? reverbKey.substring(0, 10) + '...' : 'MISSING!');
            console.log('🌐 Host:', reverbHost);
            console.log('🔌 Port:', reverbPort);
            console.log('🔒 Scheme:', reverbScheme);
            console.log('🔗 WebSocket URL:', `ws://${reverbHost}:${reverbPort}/app/${reverbKey}`);
            console.log('═══════════════════════════════════════');

            var pusher = new Pusher(reverbKey, {
                cluster: '', // Required by Pusher library, but empty for Reverb
                wsHost: reverbHost,
                wsPort: reverbPort,
                wssPort: reverbPort,
                forceTLS: reverbScheme === 'https',
                enabledTransports: ['ws', 'wss'],
                encrypted: false, // Reverb doesn't need encryption for local
                disableStats: true,
                authEndpoint: '/broadcasting/auth' // Reverb auth endpoint
            });

            // Enable Pusher logging for debugging
            Pusher.logToConsole = true;

            pusher.connection.bind('connecting', function() {
                console.log('🔄 Connecting to Reverb server (display screen)...');
            });

            pusher.connection.bind('connected', function() {
                window.reverbDisplayInitialized = true;
                console.log('═══════════════════════════════════════');
                console.log('✅ REVERB CONNECTED (DISPLAY SCREEN)!');
                console.log('📍 Connected to:', wsUrl);
                console.log('═══════════════════════════════════════');
            });

            pusher.connection.bind('error', function(err) {
                console.error('═══════════════════════════════════════');
                console.error('❌ REVERB CONNECTION ERROR (DISPLAY)!');
                console.error('📍 Failed URL:', wsUrl);
                console.error('💥 Error:', err);
                console.error('💡 Make sure Reverb server is running: php artisan reverb:start');
                console.error('═══════════════════════════════════════');
            });

            pusher.connection.bind('disconnected', function() {
                console.warn('⚠️ Reverb disconnected from display screen');
            });

            pusher.connection.bind('state_change', function(states) {
                console.log('🔄 Reverb connection state:', states.current);
            });

            var queueProgress = pusher.subscribe("queue-display." + teamId + "." + locationId);
            
            queueProgress.bind('pusher:subscription_succeeded', function() {
                console.log('✅ Successfully subscribed to queue-display channel');
            });
            
            queueProgress.bind('pusher:subscription_error', function(status) {
                console.error('❌ Failed to subscribe to queue-display channel:', status);
            });
            
            queueProgress.bind('queue-display', function(data) {
                console.log('═══════════════════════════════════════');
                console.log('📺 QUEUE-DISPLAY EVENT RECEIVED!');
                console.log('═══════════════════════════════════════');
                console.log('📦 Full event data:', data);
                console.log('📦 Queue data:', data.queue);
                console.log('📦 Queue ID:', data.queue?.id);
                console.log('📦 Queue Status:', data.queue?.status);
                console.log('═══════════════════════════════════════');
                
                if (window.Livewire) {
                    console.log('🔄 Dispatching to Livewire...');
                    Livewire.dispatch('display-update', {
                        event: data
                    });
                    console.log('✅ Dispatched to Livewire');
                } else {
                    console.error('❌ Livewire not available');
                }
            });
        }

//   document.addEventListener('livewire:init', () => {
//     Livewire.hook('request', ({ fail, respond, payload, succeed, resolve, reject, options }) => {
//         fail(async ({ status, preventDefault, retry }) => {
//             if (status === 419) {
//                 preventDefault();

//                 // Fetch a new CSRF token
//                 try {
//                     let response = await fetch('/sanctum/csrf-cookie', {
//                         credentials: 'same-origin'
//                     });

//                     if (response.ok) {
//                         // Get the new token from the meta tag
//                         let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

//                         // Update the token in the Livewire payload and in the window
//                         window.Laravel.csrfToken = token;

//                         // Retry the original request
//                         retry();
//                     } else {
//                        location.reload(true);
//                     }
//                 } catch (e) {
//                     location.reload(true);
//                 }
//             }

//             if (status === 408) {
//                   location.reload(true);
//             }

//             if (status === 401) {
//                 preventDefault();
//                 window.location.href = '/';
//             }
//         });
//     });
// });

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

    </script>
    @endpush

