<div class="">
    <?php


    $background = $colorSetting?->page_layout ?? '#fffff';
    $text_size = $this->siteData?->category_text_font_size ?? 'text-6xl';
    $text_color = $colorSetting?->text_layout ?? '#00000';
    $category_background = $colorSetting?->categories_background_layout ?? '#fffff';
    $category_background_image = $this->siteData?->background_image ?? '';
    $category_background_size = $this->siteData?->background_size ?? 'cover';
    $category_background_repeat = $this->siteData?->background_repeat ?? 'no-repeat';
    $category_background_position = $this->siteData?->background_position ?? 'center';
    $text_color_hover = $colorSetting?->hover_text_layout ?? '#00000';
    $category_background_hover = $colorSetting?->hover_background_layout ?? '#fffff';
    $buttons_background = $colorSetting?->buttons_layout ?? '#00000';
    $hover_button_layout = $colorSetting?->hover_button_layout ?? '#00000';
    $buttons_background_hover = $colorSetting?->hover_buttons_layout ?? 'rgb(99 102 241 / var(--tw-bg-opacity))';

    ?>
    <style type="text/tailwindcss">


        body,
        .queue-footer {
            background-color: <?= $background ?> !important;
            <?php if (!empty($category_background_image)): ?>background-image: url('<?= asset('storage/' . $category_background_image) ?>');
            background-size: <?= $category_background_size ?> !important;
            background-repeat: <?= $category_background_repeat ?> !important;
            background-position: <?= $category_background_position ?> !important;
            <?php endif; ?>
        }

        a {
            color: #569FF7;
        }

        .text-color {
            color: <?= $text_color ?> !important;
            background-color: <?= $category_background ?> !important;
            border-color: <?= $category_background ?>;
            /* width: 48%; */
            /* margin: 10px; */


        }

        .text-color:hover {
            color: <?= $text_color_hover ?> !important;
            background-color: <?= $category_background_hover ?> !important;
            border-color: <?= $category_background ?>;

        }

        .Qr-text-color {
            color: <?= $text_color ?> !important;
        }

        .queue-footer-button {
            color: <?= $text_color ?> !important;
            background-color: <?= $buttons_background ?> !important;
            border-color: <?= $buttons_background ?>;
        }

        .queue-footer-button:hover {
            color: <?= $text_color_hover ?> !important;
            background-color: <?= $hover_button_layout ?> !important;
            border-color: <?= $hover_button_layout ?>;
        }

        label {
            text-align: justify;
            font-size: 1rem;
        }

          .card-active {
        @apply border-blue-600 ring-2 ring-blue-300;
      }
      @media (max-width:767px){
        .custom-text-size{font-size:1.8rem;text-align:left}
      }

          /* RTL support */
        .rtl label,
        .rtl option,
        .rtl input,
        .rtl textarea,
        .rtl select {
            direction: rtl;
            text-align: right;
        }
        body.queue_page_Petraonline .white_text{color:#fff}
        body.queue_page_Petraonline .Petraonline-text{display:block !important}

         @media (min-width: 768px){
          .md-flex-wrap{
            display:flex;
            flex-wrap:wrap;
            justify-content:center
          }
          .md-flex-wrap .column{
            width: 48%;
          }
        }


        @media (max-width: 991px) {
            .sm-full {
                width: 100%;
                flex:0 0 100%
            }

            .queue-footer-button {
                color: <?= $text_color ?> !important;
                background-color: <?= $buttons_background ?> !important;
                border-color: <?= $buttons_background ?>;
            }

            .queue-footer-button:hover {
                color: <?= $text_color_hover ?> !important;
                background-color: <?= $buttons_background_hover ?> !important;
                border-color: <?= $buttons_background ?>;
            }
        }
        @media (orientation: portrait) {
            .sm-full {
                width: 100%;
                flex:0 0 100%
            }
        }

        <?php if($teamId == 264){ ?>
     @media (min-width:992px){
    .queue_page_Psbacademy :not(.custom-text-size) {
        font-size: max(4px, 1.2vw);
        line-height: 1.4;
    }
    .cate_items .custom-text-size + div span{
        font-size: max(4px, 1.05vw);
        line-height: 1.4;
    }
    .queue_page_Psbacademy .custom-text-size {
        font-size: max(4px, 1.4vw);
        line-height: 1.4;
    }
    .queue_page_Psbacademy .p-5 {
        padding: 1rem;
        margin-bottom: 0rem;
    }
    .queue_page_Psbacademy .mt-4 {
        margin-top: 0.5rem;
    }
    .queue_page_Psbacademy .p-4 {
        padding: 0.5rem;
    }
    .queue_page_Psbacademy header img{
        max-height:80px;
        width:auto
    }
}
        <?php } ?>
    </style>

        @php
    $descriptionFontSize = match($fontSize) {
        'text-4xl' => 'text-3xl',
        'text-3xl' => 'text-2xl',
        'text-2xl' => 'text-xl',
        'text-xl'  => 'text-lg',
        default    => 'text-base',
    };
@endphp

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
					  <p class="text-sm text-gray-500 mt-1">
						<strong>{{ __('text.Average Waiting Time') }}: </strong> 
						{{ $location->siteDetail->estimate_time ?? 0 }} mins
					</p>
                    </div>
                </div>
                @endforeach
                @endif

            </div>
        </div>
    </div>

    <div class="container mx-auto {{ $locationStep == false ? '' : 'hidden' }}">


        @if ($errorMessage)
        <div class="mb-4 p-4 text-red-800 bg-red-100 rounded-lg">
            {{ $errorMessage }}
        </div>
        @endif

        <!-- Language Selector -->
        <div  style="right: 48px; top: 5px;" class="md:absolute m-3 px-4 md:px-0 md:my-0 md:w-15">
            <livewire:language-selector />
        </div>

        <!-- Fullscreen Button -->
        <!-- <button class="requestfullscreen hide-small" id="toggleFullBtn" style="position: absolute;right: 15px;top: 15px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
            </svg>
        </button> -->



                          @php
                        $locale = session('app_locale') ?? 'en';

                        $translatedTag1 = $translatedTag2= $translatedTag3= '';
                          if ($locale !== 'en' && !empty($translations['level1_tagline'][$locale])) {
                            $translatedTag1 = !empty($translations['level1_tagline'][$locale]) ? $translations['level1_tagline'][$locale] : $tag_line1;
                        }

                        if ($locale !== 'en' && !empty($translations['level2_tagline'][$locale])) {
                            $translatedTag2 = !empty($translations['level2_tagline']) ? $translations['level2_tagline'][$locale] : $tag_line2;
                        }

                        if ($locale !== 'en' && !empty($translations['level3_tagline'][$locale])) {
                            $translatedTag3 = !empty($translations['level3_tagline'][$locale]) ? $translations['level3_tagline'][$locale] : $tag_line3;
                        }

                        $layout_set ='flex flex-wrap';
                        if($layout_show == 'column'){
                            $layout_set ='grid grid-cols-1 md:grid-cols-2 justify-center m-auto gap-3 md-flex-wrap';
                        }else{
                            $layout_set ='grid grid-cols-1 justify-center md:w-[700px] m-auto gap-3';
                        }


                        @endphp
        <div class="overflow-x-hidden main-container selector-main-sections text-center pb-10">
            {{-- First Category --}}
            <!-- <div class="cate_items md:grid grid-cols-2 gap-4 {{ $firstStep == false ? 'hidden' : '' }} "> -->
                  @if(!empty($this->firstCategories) && $firstStep)
                {{-- <h2 class="tag_line text-3xl">{{ $tag_line1 }}</h2> --}}
                 <h2 class="tag_line text-3xl">{{ !empty($translatedTag1) ? $translatedTag1 : $tag_line1 }}</h2>
                @endif
            <div class="cate_items {{$layout_set}} {{ $firstStep == false ? 'hidden' : '' }} ">
                  @foreach ($this->firstCategories as $keyCat => $nameCate)
                @if($ticketDisabled)
                <div class="{{ $fontSize }} {{ $borderWidth }} {{ $fontFamily }} border-solid border-slate-300 rounded-lg mt-4 cursor-not-allowed p-4 cate_item text-color column sm-full disabled pointer-events-none opacity-50" title="Ticket generation is disabled" wire:model.defer="{{ $selectedCategoryId }}" style="background-color:{{$nameCate->bg_color ?? $category_background}} !important; border-color: {{$nameCate->bg_color ?? $category_background}};">
                    @else
                    <div class="{{ $fontSize }} {{ $borderWidth }} {{ $fontFamily }} border-solid border-slate-300 rounded-lg mt-4 cursor-pointer p-4 cate_item column hover:bg-indigo-500 hover:border-indigo-500 hover:text-white text-color sm-full"
                     wire:model.defer="{{ $selectedCategoryId }}"
                    wire:loading.class="opacity-50 pointer-events-none"
                     wire:target="showFirstChild"
                    wire:click="showFirstChild({{ $nameCate->id }})" style="background-color:{{$nameCate->bg_color ?? $category_background}} !important; border-color: {{$nameCate->bg_color ?? $category_background}};">
                        @endif

                        <div class="items-center md:justify-center h-full flex">
                            @if (
                            $this->siteDetails &&
                            $this->siteDetails->show_cat_icon == App\Models\SiteDetail::STATUS_YES &&
                            !empty($nameCate->img)
                            )
                            <img src="{{ url('storage/' . $nameCate->img) }}" class="w-20 md:w-15 lg:w-18 object-center mr-4" />
                            @endif

                            @php

                            $name = $nameCate->name ?? '';
                            $othername = $nameCate->other_name ?? '';

                            $translatedName = $name;
                            if ($locale !== 'en' && isset($translations[$name][$locale])) {
                            $translatedName = !empty($translations[$name][$locale]) ? $translations[$name][$locale] : $nameCate->name;
                            }

                            $otherName = '';
                            if (!empty($nameCate->other_name)) {
                            if ($locale !== 'en' && isset($translations[$name . '_other_name'][$locale])) {
                            $otherName = ' - ' . $translations[$name . '_other_name'][$locale];
                            } else {
                            $otherName = $othername;
                            }
                            }

                            $description = '';
                            if (!empty($nameCate->description)) {
                            if ($locale !== 'en' && isset($translations[$name . '_description'][$locale]) && !empty($translations[$name . '_description'][$locale])) {
                            $description = ' - ' . $translations[$name . '_description'][$locale];
                            } else {
                            $description = ' - ' . $nameCate->description;
                            }
                            }
                            @endphp
                            <div>
                                <span class="{{ $fontSize }} {{ $fontFamily }} custom-text-size">
                                    {{ $translatedName }} {{ $otherName }}
                                </span>

                                <div>
                                    <span class="{{ $descriptionFontSize }} {{ $fontFamily }}">{{ $description }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
            </div>


            @if ($is_qr_code == App\Models\GenerateQrCode::STATUC_ACTIVE && $isMobile == false)
            <div class="py-4 {{ $firstStep == false ? 'hidden' : '' }} ">
                @if(!empty($qrcode_tagline))
                <div class="flex justify-center items-center mt-2 mb-2">
                    <div class="text-4xl lg:text-2xl  w-full p-2 line-height-1 text-center text-black">
                        @if($locale !== 'en')
                        {{ isset($translations['Qrcode Tagline 1'][$locale]) ? $translations['Qrcode Tagline 1'][$locale] : $qrcode_tagline }}
                        @else
                        {{ $qrcode_tagline }}
                        @endif

                    </div>
                </div>
                @endif
                @if(!empty($qrcode_tagline_second))
                <div class="flex justify-center items-center mt-2 mb-2">
                    <div class="text-xl  w-full p-2 line-height-1 text-center text-black">
                        @if($locale !== 'en')
                        {{ isset($translations['Qrcode Tagline 2'][$locale]) ? $translations['Qrcode Tagline 2'][$locale] : $qrcode_tagline_second }}
                        @else
                        {{ $qrcode_tagline_second }}
                        @endif
                    </div>
                </div>
                @endif
                @if(!empty($this->qrCodeDetails->url))
                <div class="flex justify-center items-center mt-4">
                    <span class="border-2 font-sans border-solid border-slate-300 p-3 bg-white-900">
                        {!! QrCode::size(15 * $this->qrCodeDetails->size ?? 9)->errorCorrection($this->qrCodeDetails->level_ecc ?? 'L')->generate($this->qrCodeDetails->url) !!}</span>
                </div>
                @endif


            </div>
            @endif

             @if($teamId == 107)
                <div class="flex justify-center items-center mt-4 {{ $firstStep == false ? 'hidden' : '' }} ">
                   <a href="https://members.petraonline.com/" target="_blank" class="text-black text-xl py-3 px-4 bg-white rounded-xl hover:bg-gray-400 hover:text-white">Self Service</a>
                </div>
            @endif



            {{-- Second Category --}}
            @if (!empty($this->firstChildren))
               @if($secondStep)
                <h2 class="tag_line text-3xl">{{ !empty($translatedTag2) ? $translatedTag2 : $tag_line2 }}</h2>
                {{-- <h2 class="tag_line text-3xl">{{ $translatedTag2 }}</h2> --}}
                @endif
            <div class="{{$layout_set}} {{ $secondStep == false ? 'hidden' : '' }}">
                 @foreach ($this->firstChildren as $child)
                    <div class="{{ $borderWidth }}  text-center border-solid border-slate-300 rounded-lg mt-4 cursor-pointer p-4 cate_item  column hover:bg-indigo-500 hover:border-indigo-500 hover:text-white leading-6 text-color column sm-full"
                        wire:loading.class="opacity-50 pointer-events-none"
                        wire:target="showSecondChild"
                        wire:model.defer="{{ $secondChildId }}"
                        wire:click="showSecondChild({{ $child['id'] }})">

                        <div class=" items-center justify-center flex sm-left">
                            @if (!empty($child['img']))
                            <img src="{{ url('storage/' . $child['img']) }}" class="w-10 md:w-10 lg:w-14 mr-4" />
                            @endif
                            <div class="sm-left-text">
                            <span class="{{ $fontSize }} {{ $fontFamily }} custom-text-size">{{ session('app_locale') !== 'en' ? (!empty($translations[$child['name']][session('app_locale')]) ? $translations[$child['name']][session('app_locale')] : $child['name']) : $child['name'] }}

                                @php
                                if(!empty($child['other_name']))
                                {
                                if(session('app_locale') !== 'en')
                                {
                                $otherName = ' - ' . $translations[$nameCate->name . '_other_name'][session('app_locale')];
                                }
                                else
                                {
                                $otherName = ' - ' . $child['other_name'];
                                }
                                }
                                else
                                {
                                $otherName = '';
                                }
                                @endphp

                                <span> {{ $otherName }} </span>
                                {{-- Description --}}
                                @php
                                $description = '';
                                if (!empty($child['description'])) {
                                if (session('app_locale') !== 'en') {
                                $descriptionKey = $child['name'] . '_description';
                                $description = ' - ' . ($translations[$descriptionKey][session('app_locale')] ?? $child['description']);
                                } else {
                                $description = ' - ' . $child['description'];
                                }
                                }
                                @endphp
                                <div>
                                    <span class="{{ $descriptionFontSize }} {{ $fontFamily }}">{{ $description }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
            </div>
            @endif
            {{-- Third Category --}}
            @if (!empty($this->secondChildren))
            @if($thirdStep)
                {{-- <h2 class="tag_line text-3xl">{{ $tag_line3 }}</h2> --}}
               <h2 class="tag_line text-3xl">{{ !empty($translatedTag3) ? $translatedTag3 : $tag_line3 }}</h2>
                @endif
            <div class="{{$layout_set}} {{ $thirdStep == false ? 'hidden' : '' }} ">
                 @foreach ($this->secondChildren as $subchild)
                    <div class="{{ $fontSize }} {{ $borderWidth }} {{ $fontFamily }} text-center border-solid border-slate-300 rounded-lg mt-4 cursor-pointer p-4  cate_item  column hover:bg-indigo-500 hover:border-indigo-500 hover:text-white leading-6 text-color column sm-full"
                         wire:loading.class="opacity-50 pointer-events-none"
                        wire:target="showQueueForm"
                        wire:model.defer="{{ $thirdChildId }}"
                        wire:click="showQueueForm({{  $subchild['id'] }})">
                        <div class="items-center justify-center">
                            @if (!empty($subchild['img']))
                            <img src="{{ url('storage/' . $subchild['img']) }}" class="w-10 md:w-10 lg:w-14 mr-4" />
                            @endif
                            <span class="{{ $fontSize }} {{ $fontFamily }} ">{{ session('app_locale') !== 'en' ? (!empty($translations[$subchild['name']][session('app_locale')]) ? $translations[$subchild['name']][session('app_locale')] : $subchild['name'])
            : $subchild['name'] }}

                                @php
                                if(!empty($subchild['other_name']))
                                {
                                if(session('app_locale') !== 'en')
                                {
                                $otherName = ' - ' . $translations[$nameCate->name . '_other_name'][session('app_locale')];
                                }
                                else
                                {
                                $otherName = ' - ' . $subchild['other_name'];
                                }
                                }
                                else
                                {
                                $otherName = '';
                                }
                                @endphp

                                <span>{{ $otherName }}</span>
                                {{-- Description --}}
                                @php
                                $description = '';
                                if (!empty($subchild['description'])) {
                                if (session('app_locale') !== 'en') {
                                $descriptionKey = $subchild['name'] . '_description';
                                $description = ' - ' . ($translations[$descriptionKey][session('app_locale')] ?? $subchild['description']);
                                } else {
                                $description = ' - ' . $subchild['description'];
                                }
                                }
                                @endphp
                                <div>
                                    <span class="{{ $descriptionFontSize }} {{ $fontFamily }}">{{ $description }}</span>
                                </div>

                        </div>

                    </div>

                    @endforeach
            </div>

            @endif


               <div class="flex justify-center items-center {{ $fourthStep == false ? 'hidden' : '' }}">

                    <!-- <form wire:submit.prevent="saveQueueForm" class="w-full max-w-md"> -->
                    <div class="w-full max-w-md">
                      @php
                            $padding = 'pl-3';
                            if (session('app_locale') === 'ar') {
                                $padding = 'pr-3';
                            }

                        @endphp
                        <div class="space-y-12 @if(session('app_locale') === 'ar') rtl @endif">
                            <div class="pb-8">
                                  <p class="hidden Petraonline-text text-gray-600 text-lg mt-2 text-center dark:text-white white_text">
                                        Please select why you're here today?
                                    </p>
                                <div class="mt-5 grid grid-cols-1 gap-x-6 gap-y-1 sm:grid-cols-6 p-5 rounded shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                                    @foreach ($dynamicForm as $form)
                                    @if (App\Models\CategoryFormField::checkFieldCategory($form['id'], $allCategories))
                                    @if ($form['type'] == App\Models\FormField::TEXT_FIELD || $form['type'] == App\Models\FormField::URL_FIELD)
                                    <div class="col-span-full">
                                        <div class="mt-1">
                                            <label for="{{ $form['label'] }}"
                                                class="{{ $fontSize }} {{$fontFamily}} block mb-2 text-sm font-medium text-gray-900 ">
                                                {{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? $form['label']) : $form['label'] }}
                                            </label>
                                            <div
                                                class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                <input
                                                    type="{{ $form['type'] == App\Models\FormField::TEXT_FIELD ? 'text' : 'url' }}"
                                                    id="{{ $form['title'] . '_' . $form['id'] }}"
                                                    class="block flex-1 border-slate-400 bg-transparent py-1.5 {{$padding}} text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                    placeholder="{{ session('app_locale') !== 'en' ? ($translations[$form['label'] . '_placeholders'][session('app_locale')] ?? $form['placeholder']) : $form['placeholder'] }}"
                                                    wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"

                                                    @if ($form['title']=='phone' ) onkeypress="return checkIt(event)" @endif>
                                            </div>
                                            @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-red-500 text-left">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    @elseif($form['type'] == App\Models\FormField::PHONE_FIELD)
                                    <div class="col-span-full">
                                        <div class="mt-4">
                                            <label for="{{ $form['label'] }}"
                                                class="{{ $fontSize }} {{$fontFamily}} block mb-2 text-sm font-medium text-gray-900">{{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? ($form['label'] ?? "Phone")) : $form['label'] }}</label>
                                            <div class="flex gap-2 items-center sm:max-w-md border-gray-300 rounded-md border-solid border">
                                                <!-- Country Code Dropdown -->

                                              @if ($country_phone_mode == 1)

                                                <input type="text" id="{{ $form['title'] . '_input' }}"
                                                class="block w-1/5 rounded-md border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 pl-2 pr-2 text-gray-900"
                                                wire:model.defer="phone_code"
                                                value="+{{ $selectedCountryCode }}"
                                                readonly>


                                                @else

                                                <select id="{{ $form['title'] . '_select' }}"
                                                    class="block w-1/5 rounded-md border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 {{$padding}} pr-2 text-gray-900"
                                                    wire:model="phone_code">
                                                    <option value=""  hidden>+Code</option>

                                                    @if(!empty($allowed_Countries))
                                                    @foreach ($allowed_Countries as $code)
                                                    <option value="{{ $code->phone_code }}"> (+{{ $code->phone_code }}) {{ $code->name }} </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                @endif

                                                {{-- <select id="{{ $form['title'] . '_select' }}"
                                                    class="block w-1/5 rounded-md border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 {{$padding}} pr-2 text-gray-900"
                                                    wire:model.defer="phone_code">
                                                    <option value=""  hidden>+Code</option>
                                                    @foreach ($countryCode as $code)
                                                    <option value="{{ $code }}"> +{{ $code }}</option>
                                                    @endforeach
                                                </select> --}}


                                                <!-- Phone Number Input Field -->
                                                <input type="number" id="{{ $form['title'] . '_' . $form['id'] }}"
                                                    class="block w-4/5 flex-1 border-slate-400 bg-transparent py-1.5 {{$padding}} text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 h-12 rounded-lg"
                                                    placeholder="{{ session('app_locale') !== 'en' ? ($translations[$form['label'] . '_placeholders'][session('app_locale')] ?? $form['placeholder']) : $form['placeholder'] }}" {{-- max="{{ $form['maximum_number_allowed'] }}"
                                                    min="{{ $form['minimum_number_allowed'] }}" --}}
                                                     wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                     @if($teamId == 304)
                                                    wire:blur="fetchAgentName('{{ $form['title'] . '_' . $form['id'] }}')"
                                                    @endif
                                                    >
                                                       @if($teamId == 304)
                                                    <!-- Loader for fetchAgentName -->
<span wire:loading wire:target="fetchAgentName('{{ $form['title'] . '_' . $form['id'] }}')">
    <svg class="inline w-5 h-5 text-gray-500 animate-spin ml-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
</span>
@endif
                                            </div>
                                            <!-- Error message styling for phone number -->
                                            @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-sm text-red-600 mt-2 text-left">
                                                {{ $message }}
                                            </div>
                                            @enderror


                                        </div>
                                    </div>
                                    @elseif($form['type'] == App\Models\FormField::DATE_FIELD)
                                    <div class="col-span-full">
                                        <div class="mt-2">
                                            <label for="{{ $form['label'] }}"
                                                class="{{ $fontSize }} {{$fontFamily}} block mb-2 text-sm font-medium text-gray-900 ">{{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? $form['label']) : $form['label'] }}</label>
                                            <div
                                                class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
                                                <input id="{{ $form['title'] . '_' . $form['id'] }}"
                                                    wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                    datepicker-format="yyyy-mm-dd" type="date" onclick="this.showPicker()"
                                                    datepicker-autohide placeholder="{{ session('app_locale') !== 'en' ? ($translations[$form['label'] . '_placeholders'][session('app_locale')] ?? $form['placeholder']) : $form['placeholder'] }}"
                                                    class="dynamicDatePicker text-center block flex-1 border-slate-400 bg-transparent py-1.5 {{$padding}} text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg">
                                            </div>
                                            @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-red-500 text-left">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    @elseif($form['type'] == App\Models\FormField::SELECT_FIELD)
                                    <div class="col-span-full">
                                        <div class="mt-2">
                                            <label for="{{ $form['label'] }}"
                                                class="{{ $fontSize }} {{$fontFamily}} block mb-2 text-sm font-medium text-gray-900 ">{{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? $form['label']) : $form['label'] }}</label>

                                               @if ($form['is_multiple_options'] == 1)

                                    <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md" wire:ignore>
                                        <select class="multiSelect2 w-full" multiple data-attr="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                                    @foreach ($form['options'] as $option)
                                                        <option value="{{ $option }}">{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                    </div>

                                        @else
                                            <!-- Single select dropdown -->
                                             <div
                                                class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                <select id="{{ $form['title'] . '_' . $form['id'] }}"
                                                    wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                    class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 {{$padding}} text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
                                                    <option value=""> {{ __('text.Select an option') }}</option>
                                                    @foreach ($form['options'] as $option)
                                                    <option value="{{ $option }}" class="text-color">
                                                        {{ session('app_locale') !== 'en' ? ($translations[$option][session('app_locale')] ?? $option) : $option }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                            @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-red-500 text-left">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    @elseif($form['type'] == App\Models\FormField::NUMBER_FIELD)
                                    <div class="col-span-full">
                                        <div class="mt-2">
                                            <label for="{{ $form['label'] }}"
                                                class="{{ $fontSize }} {{$fontFamily}} block mb-2 text-sm font-medium text-gray-900 ">{{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? $form['label']) : $form['label'] }}</label>
                                            <div
                                                class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                <input type="number"
                                                    id="{{ $form['title'] . '_' . $form['id'] }}"
                                                    class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 {{$padding}} text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                    placeholder="{{ session('app_locale') !== 'en' ? ($translations[$form['label'] . '_placeholders'][session('app_locale')] ?? $form['placeholder']) : $form['placeholder'] }}"

                                                    wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                            </div>
                                            @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-red-500 text-left">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    @elseif($form['type'] == App\Models\FormField::TEXTAREA_FIELD)
                                    <div class="col-span-full">
                                        <div class="mt-2">
                                            <label for="{{ $form['label'] }}"
                                                class="{{ $fontSize }} {{$fontFamily}} block mb-2 text-sm font-medium text-gray-900 ">{{ session('app_locale') !== 'en' ? ($translations[$form['label']][session('app_locale')] ?? $form['label']) : $form['label'] }}</label>
                                            <div
                                                class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md ">
                                                <textarea id="{{ $form['title'] . '_' . $form['id'] }}" rows="4"
                                                    class="block p-2.5 w-full text-blue-600 bg-gray-100 border-slate-400 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-2 h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                    placeholder="{{ session('app_locale') !== 'en' ? ($translations[$form['label'] . '_placeholders'][session('app_locale')] ?? $form['placeholder']) : $form['placeholder'] }}" wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                    minlength="{{ $form['minimum_number_allowed'] }}" maxlength="{{ $form['maximum_number_allowed'] }}"> </textarea>
                                            </div>
                                            @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-red-500 text-left">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    @elseif($form['type'] == App\Models\FormField::POLICY_FIELD)
                                    <div class="col-span-full">
                                        <div class="mt-2">
                                            <!-- <label for="{{ $form['label'] }}"
                                                    class="text-center block mb-2 text-sm font-medium text-gray-900 ">{{ $form['label'] }}</label> -->

                                            @if ($form['policy'] == 'Text')
                                            <div class="flex items-center mb-4">
                                                <input type="checkbox" value=""
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                                    id="{{ $form['title'] . '_' . $form['id'] }}"
                                                    wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                    minlength="{{ $form['minimum_number_allowed'] }}"
                                                    maxlength="{{ $form['maximum_number_allowed'] }}"
                                                    @if ($form['mandatory']==1) required @endif>

                                                <label for="{{ $form['title'] . '_' . $form['id'] }}"
                                                    class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-750">{!! html_entity_decode($form['policy_content']) !!}</label>
                                            </div>
                                            @else
                                            <label for="{{ $form['title'] . '_' . $form['id'] }}"
                                                class="ms-2 text-sm font-medium font_bold text-gray-900 dark:text-gray-750">
                                                <a href="{!! $form['policy_url'] !!}"> {!! $form['policy_url'] !!}
                                                </a></label>
                                            @endif

                                            @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                            <div class="text-red-500 text-left">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    @elseif($form['type'] == App\Models\FormField::CHECKBOX_FIELD)
                                    <div class="flex items-center mb-4">
                                        <input id="{{ $form['title'] . '_' . $form['id'] }}" type="checkbox"
                                            value=""
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                            wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                        <label for="{{ $form['title'] . '_' . $form['id'] }}"
                                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-750">{{ $form['title'] }}</label>
                                    </div>

                                    @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                    <div class="text-red-500 text-left">{{ $message }}</div>
                                    @enderror
                                    @endif
                                    @endif
                                    @endforeach


                                      @if($enable_doc_file_field && $this->siteDetails?->queue_form_display == App\Models\SiteDetail::STATUS_YES)
                                   <div class="col-span-full">
                                        <div class="mt-2">
                                            <label for="docFile"
                                                class="{{ $fontSize }} {{$fontFamily}} block mb-2 text-sm font-medium text-gray-900">
                                                {{ $doc_file_label }}
                                            </label>

                                            <div
                                                class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                                <input type="file"
                                                    id="docFile"
                                                    wire:model.defer="docFile"
                                                    class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 {{$padding}} text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 h-12 rounded-lg" required>
                                            </div>

                                            @error('docFile')
                                            <div class="text-red-500 text-left mt-1">{{ $message }}</div>
                                            @enderror

                                            @if ($docFile)
                                                <p class="text-green-600 mt-2">{{ $doc_file_label ?? 'Uploaded File' }}: {{ $docFile->getClientOriginalName() }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-2" wire:loading wire:target="docFile">
                                        <progress max="100" value="50" class="w-full"></progress>
                                        <p class="text-sm text-blue-600">Uploading file...</p>
                                    </div>
                                    @endif

                                    <div class="col-span-full flex justify-center mt-3">
                                        @if($paymentSetting?->enable_payment == 1 && $isFree == 1 && ($paymentSetting?->payment_applicable_to == 'walkin' || $paymentSetting?->payment_applicable_to == 'both') && !empty($paymentSetting->api_key) && !empty($paymentSetting->api_secret))
                                        <button wire:click="showPaymentPage"
                                            class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">
                                            <span class="{{ $fontSize }} {{$fontFamily}}">{{ __('text.Next') }} </span>

                                        </button>
                                        @else
                                        {{-- <button wire:click.prevent="saveQueueForm"
                                        class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">

                                        <span class="{{ $fontSize }} {{ $fontFamily }}">
                                        @if(!empty($this->siteDetails->submit_btn_text))
                                        {{ session('app_locale') !== 'en' ? ($translations['Submit Button Label'][session('app_locale')] ?? $this->siteDetails?->submit_btn_text) : $this->siteDetails?->submit_btn_text }}
                                        @else
                                        {{ __('text.Submit') }}
                                        @endif
                                        </span>

                                        <!-- Loader shown only while saveQueueForm is running -->
                                        <span wire:loading wire:target="saveQueueForm" class="ml-2">
                                            <svg aria-hidden="true"
                                                class="inline w-6 h-6 text-gray-200 animate-spin dark:text-gray-600 fill-gray-600 dark:fill-gray-300"
                                                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                    fill="currentFill" />
                                            </svg>
                                        </span>

                                        </button> --}}

                                        <button
                                            wire:click.prevent="saveQueueForm"
                                            wire:loading.attr="disabled"
                                            wire:target="docFile, saveQueueForm"
                                            class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">

                                            <span class="{{ $fontSize }} {{ $fontFamily }}">
                                                @if(!empty($this->siteDetails->submit_btn_text))
                                                {{ session('app_locale') !== 'en' ? ($translations['Submit Button Label'][session('app_locale')] ?? $this->siteDetails?->submit_btn_text) : $this->siteDetails?->submit_btn_text }}
                                                @else
                                                {{ __('text.Submit') }}
                                                @endif
                                            </span>

                                            <!-- Loader shown only while saveQueueForm is running -->
                                            <span wire:loading wire:target="saveQueueForm" class="ml-2">
                                                <svg aria-hidden="true"
                                                    class="inline w-6 h-6 text-gray-200 animate-spin dark:text-gray-600 fill-gray-600 dark:fill-gray-300"
                                                    viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                                        fill="currentColor" />
                                                    <path
                                                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                                        fill="currentFill" />
                                                </svg>
                                            </span>

                                        </button>


                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @if($this->paymentStep == 1)
            <div class="paymentStep">
                <div class="max-w-md mx-auto p-6 bg-white rounded-2xl shadow-md space-y-4 " wire:ignore>
                    <h2 class="text-2xl font-semibold text-gray-800">{{ __('text.Payment') }}</h2>

                    <input
                        type="email"
                        wire:model.defer="email"
                        placeholder="Email"
                        required
                        class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <div>
                        <label class="block text-sm text-gray-700 mb-1">{{ __('text.Card Details') }}</label>
                        <div id="card-element" class="px-4 py-3 border rounded-md shadow-sm bg-white"></div>
                    </div>

                </div>
                <button
                    id="pay-btn"
                    class="w-full max-w-md mx-auto bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">
                    <span class="button-text">{{ __('text.Pay') }}</span>
                    <svg
                        id="pay-loader"
                        class="ml-2 h-5 w-5 text-white animate-spin hidden"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </button>
            </div>
            @endif

            <div id="printQueue"></div>


            <div class="flex justify-center footer-section queue-footer">

                  {{-- Home Button --}}
            <button type="button" wire:click="goBackFn(2)"
                class="{{ $fontSize }} {{ $fontFamily }} bg-white text-slate-950 hover:border-green-700 hover:bg-green-700 hover:text-white font-bolds py-2 px-12 rounded-full border-2 border-gray-800 queue-footer-button {{ $totalLevelCount <= 1 ? 'hidden' : '' }} mr-2"
                wire:loading.class="opacity-50">
                @if (!empty($this->siteDetails->home_btn_text))
                    {{ session('app_locale') !== 'en' ? $translations['Home Button Label'][session('app_locale')] ?? $this->siteDetails?->home_btn_text : $this->siteDetails?->home_btn_text }}
                @else
                    {{ __('text.Home') }}
                @endif
            </button>

                <button type="button" wire:click="goBackFn({{ $totalLevelCount }})"
                    class="{{ $fontSize }} {{$fontFamily}}  bg-white text-slate-950 hover:border-indigo-700 hover:bg-indigo-700 hover:text-white  font-bolds py-2 px-12 rounded-full border-2 border-gray-800 {{ $totalLevelCount <= 1 ? 'hidden' : '' }} queue-footer-button"
                    wire:loading.class="opacity-50">
                    @if(!empty($this->siteDetails->back_btn_text))
                    {{ session('app_locale') !== 'en' ? ($translations['Back Button Label'][session('app_locale')] ?? $this->siteDetails?->back_btn_text) : $this->siteDetails?->back_btn_text }}
                    @else
                    {{ __('text.Back') }}
                    @endif
                </button>

            </div>
        </div>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <script src="{{ asset('/js/display.js?v='.time()) }}"></script> -->
    <script>
        function checkIt(evt) {
            evt = evt || window.event;
            var charCode = (typeof evt.which == "undefined") ? evt.keyCode : evt.which;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Livewire.on('header-show', () => {
                location.reload(); // Refresh the page when OK is clicked
            });

        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            Livewire.on('checkAvailability', (data) => {
                Swal.fire({
                    title: 'Service Unavailable',
                    text: data.message || 'The service is currently not available. Please try again later.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('livewire:init', () => {

            Livewire.on('getLocation', () => {

                if (navigator.geolocation) {

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            let latitude = position.coords.latitude;
                            let longitude = position.coords.longitude;

                            Livewire.dispatch('locationCodChange', {
                                latitude: latitude,
                                longitude: longitude
                            });
                        },
                        function(error) {
                            handleLocationError(error);
                        }
                    );
                } else {
                    handleLocationError({
                        code: 'GEOPOSITION_UNSUPPORTED'
                    });
                }
            });

            function handleLocationError(error) {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        console.error("User denied the request for Geolocation.");
                        Livewire.dispatch('locationError', {
                            'error': 'User denied the request for Geolocation.'
                        });
                        break;
                    case error.POSITION_UNAVAILABLE:
                        console.error("Location information is unavailable.");
                        Livewire.dispatch('locationError', {
                            'error': 'Location information is unavailable.'
                        });
                        break;
                    case error.TIMEOUT:
                        console.error("The request to get user location timed out.");
                        Livewire.dispatch('locationError', {
                            'error': 'The request to get user location timed out.'
                        });
                        break;
                    case error.UNKNOWN_ERROR:
                        console.error("An unknown error occurred.");
                        Livewire.dispatch('locationError', {
                            'error': 'An unknown error occurred.'
                        });
                        break;
                    case 'GEOPOSITION_UNSUPPORTED':
                        console.error("Geolocation is not supported by this browser.");
                        Livewire.dispatch('locationError', {
                            'error': 'Geolocation is not supported by this browser.'
                        });
                        break;
                }
            }

            Livewire.on('deny-qr-scanning', () => {
                console.log("QR code scanning denied.");
				window.location.href = "{{ url('403-page') }}";
            });

                   Livewire.on('swal:limit-exceed', (data) => {
                let response =data[0];
            Swal.fire({
                title: response.title,
                text: response.text,
                icon: response.icon,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                showCancelButton: false
            });
        });

           Livewire.on('silent-print', (data) => {
                        console.log("Silent print event fired:", data);

                        if (!data || !data.html) {
                            console.error("No HTML received from Livewire.");
                            return;
                        }

                        var myHtml = '<html><head><meta charset=\"UTF-8\"><style>body {padding:0; margin: 0; font-family: Arial; text-align: center; } @media print { body { width: 80mm; margin: 0; } #PrintContent { width: 100%; max-width: 80mm; margin: 0 auto; } }</style></head><body><div id=\"PrintContent\" style=\"margin:0 auto;text-align:center;max-width: 80mm;\">'+data.html+'</div></body></html>';

                        const encoded = btoa(unescape(encodeURIComponent(myHtml)));
                        console.log("Encoded HTML:", encoded.substring(0, 50)); // preview first 50 chars

                        window.location.href = 'mysilentprint://html/' + encoded;
            });
        });
    </script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let stripe;
        let card;

        Livewire.on('cardElement', () => {
            setTimeout(() => {
                const cardContainer = document.getElementById('card-element');

                if (!cardContainer) {
                    console.error('Stripe card element not found.');
                    return;
                }

                // Initialize Stripe
                // stripe = Stripe("{{ config('services.stripe.key') }}");
                 stripe = Stripe("{{ $paymentSettingKey }}");
                const elements = stripe.elements();
                card = elements.create('card');

                // Mount card
                card.mount('#card-element');

                console.log('Card mounted.');

                // ✅ Attach Pay button listener here
                const payBtn = document.getElementById('pay-btn');
                const loader = document.getElementById('pay-loader');
                const buttonText = payBtn.querySelector('.button-text');
                if (payBtn) {
                    payBtn.addEventListener('click', async () => {


                        if (!stripe || !card) {
                            console.log('Card is not ready yet.');
                            return;
                        }


                        // Disable button and show loader
                        payBtn.disabled = true;
                        buttonText.textContent = 'Processing...';
                        loader.classList.remove('hidden');

                        const {
                            paymentMethod,
                            error
                        } = await stripe.createPaymentMethod('card', card);

                        if (error) {
                            console.log(error.message);
                            // Livewire.dispatch('stripe-payment-method', {
                            //     paymentMethodId: null
                            // });
                                Swal.fire({
                                title: 'Invalid payment keys',
                                text:  'Please check payment settings.',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        } else {
                            Livewire.dispatch('stripe-payment-method', {
                                paymentMethodId: paymentMethod.id
                            });
                        }
                    });
                } else {
                    console.warn('Pay button not found.');
                }
            }, 300); // Delay to ensure DOM is ready
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const cards = document.querySelectorAll(".location-card");
            cards.forEach((card) => {
                card.addEventListener("click", () => {
                    cards.forEach((c) => c.classList.remove("card-active"));
                    card.classList.add("card-active");
                });
            });
        });
    </script>

    @endpush

    @push('scripts')
<script>

    document.addEventListener('livewire:init', () => {
        let initSelect2 = () => {
        let elements = $('.multiSelect2');
        if (!elements.length) return;

        elements.each(function () {
            let el = $(this);
            let property = el.data('attr'); // example: dynamicProperties.MyField_12

            // prevent duplicate init
            if (el.hasClass('select2-initialized')) return;
            el.addClass('select2-initialized');

            el.select2({
                placeholder: "{{ __('text.Select an option') }}",
                allowClear: true
            });

            // update Livewire when changed
            el.on('change', function () {
                @this.set(property, $(this).val());
            });

            // set initial value from Livewire
            let currentValue = @this.get(property);
            if (currentValue) {
                el.val(currentValue).trigger('change');
            }
        });
    };

        // run on load
        initSelect2();

        // re-run whenever step changes
        Livewire.on('step-changed', (data) => {

                setTimeout(() => initSelect2(), 200);

        });

         Livewire.hook('morph.updated', () => {
        // ensures select2 reinit if Livewire re-renders this step
        initSelect2();
    });
    });



</script>
@endpush
</div>
