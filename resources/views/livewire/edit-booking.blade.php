<div class="container mx-auto flex gap-4 flex-wrap">
<?php
$background ='#fffff';
$text_color ='#00000';
$category_background ='#fffff';
$buttons_background ='#fffff';

$background = $colorSetting?->page_layout ?? '#f8f8f8';
$text_size = $this->siteData?->category_text_font_size ?? 'text-6xl';
$text_color = $colorSetting?->text_layout ?? '#00000';
$category_background = $colorSetting?->categories_background_layout ?? '#fffff';
$text_color_hover = $colorSetting?->hover_text_layout ?? '#00000';
$category_background_hover = $colorSetting?->hover_background_layout ?? '#fffff';
$buttons_background = $colorSetting?->buttons_layout ?? '#00000';
$buttons_background_hover = $colorSetting?->hover_buttons_layout ?? 'rgb(99 102 241 / var(--tw-bg-opacity))';

 ?>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: <?=$background ?> !important;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
/*
    .container {
        display: flex;
        background: white;
        width: 80%;
        max-width: 1000px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin: auto;
    } */

    .booking-sidebar {
        background: #4A4AFF;
        color: white;
        padding: 20px;
        width: 40%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .booking-sidebar h2 {
        margin: 0 0 10px;
    }

    .business-hours p,
    .company-info p {
        margin: 5px 0;
        font-size: 14px;
    }

    .main-content {
        padding: 20px;
        width: 60%;
        text-align: center;
    }

    .service-btn {
        width: 80%;
        padding: 12px;
        margin: 10px auto;
        /* background: #ddd; */
        font-size: 16px;
        cursor: pointer;
        color: <?=$text_color ?> !important;
        background-color: <?=$category_background ?> !important;
        border-color: <?=$category_background ?>;
    }

    .service-btn:hover {
        /* background: #bbb; */
        color: <?=$text_color_hover ?> !important;
        background-color: <?=$category_background_hover ?> !important;
        border-color: <?=$category_background ?>;
    }

    .qr-text-color {
        color: <?=$text_color ?> !important;
    }

    .qr-code {
        margin-top: 20px;
    }

    .booking-sidebar h3 {
        margin: 10px 0 10px 0;
        font-weight: bold;
    }

    body {
        height: 100vh;
    }

    .calendar-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        /* width: 350px; */
    }

    .month-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .month-title {
        font-size: 18px;
        font-weight: bold;
    }

    .nav-btn {
        cursor: pointer;
        padding: 5px 10px;
        background: #6a5acd;
        color: white;
        border: none;
        border-radius: 5px;
        transition: 0.3s;
    }

    .nav-btn:hover {
        background: #5a4ab5;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }

    .day {
        padding: 10px;
        text-align: center;
        cursor: pointer;
        background: #fff;
        border-radius:6px;
        transition: 0.3s;
    }

    .day:hover {
        background: #d1d1f0;
    }

    .selected {
        background-color: #6a5acd !important;
        color: white;
    }

    .today {
        background-color: #ff5733 !important;
        color: white;
    }

    .header {
        font-weight: bold;
        padding: 5px;
        text-align:center
    }

    .year-select {
        font-size: 14px;
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .day.disabled {
    background-color: #eee;
    color: #aaa;
    pointer-events: none;
    cursor: not-allowed;
    border: 1px solid #ddd;
}
    </style>



    <div class="rounded-lg p-2   rounded-lg mb-3">
        <div class="text-center text-2xl text-black py-4">
            {{ __('text.booking confirmed') }}</div>
        <div class="mb-6">
            <h4 class="text-xl text-black-800 my-4 border-b pb-3">{{ __('text.booking details') }}</h4>
            <div class="my-4">
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-500">
                        {{ $accountSetting?->con_app_input_placeholder ? $accountSetting->con_app_input_placeholder : __('text.ID') . '( ' . __('text.Email') . ' )' }}
                        </div>
                    <div class="font-semibolds text-right">{{ $booking->refID ?? 'N/A' }}</div>
                </div>
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-500">{{ __('text.appointment date') }} </div>
                    <div class="font-semibolds text-right">
                        {{ $booking->booking_date ? Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="flex justify-between py-1 flex-wrap gap-3">
                    <div class="text-gray-500">{{ __('text.appointment time') }} </div>
                    <div class="font-semibolds text-right">{{ $booking->booking_time ?? 'N/A' }}</div>
                </div>
                @if (!empty($booking->category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ $level1 }} 1 </div>
                        <div class="font-semibolds text-right">{{ $booking->categories?->name ?? 'N/A' }}</div>
                    </div>
                @endif
                @if (!empty($booking->sub_category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ $level2 }} 2 </div>
                        <div class="font-semibolds text-right">{{ $booking->book_sub_category?->name ?? $booking->sub_category_id }}</div>
                    </div>
                @endif
                @if (!empty($booking->child_category_id))
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ $level3 }} 3 </div>
                        <div class="font-semibolds text-right">{{ $booking->book_child_category?->name ?? $booking->child_category_id }}</div>
                    </div>
                @endif
                <h4 class="text-xl text-black-800 my-4 border-b pb-3">{{ __('text.Contact Details') }}</h4>

                @forelse($userDetails as $key => $userD)
                    <div class="flex justify-between py-1 flex-wrap gap-3">
                        <div class="text-gray-500">{{ App\Models\FormField::viewLabel($teamId, $key) }}</div>
                        <div class="font-semibolds text-right">{{ $userD }}</div>
                    </div>
                @empty
                    No user details
                @endforelse
            </div>

        </div>

    </div>


    <div class="overflow-x-hidden flex-1">
    @if($firstpage)
        <!-- Main content area -->
        <div class="p-3 text-center">
            <h2 class="text-2xl md:text-2xl font-bold text-gray-900 text-center">Book an Appointment</h2>
            <p class="text-xl md:text-xl font-semibold text-gray-700 text-center">Select a Service</p>
            <div>
            @if($parentCategory)
            @foreach($parentCategory as $parent)
            <button type="button" class="{{ $fontSize }} {{ $borderWidth }} {{ $fontFamily }} service-btn flex justify-center items-center rounded-xl border-gray-400"
                wire:loading.class="opacity-50" wire:click.prevent="showFirstChild({{ $parent->id }})">
                @if ($siteSetting && $siteSetting->show_cat_icon == App\Models\SiteDetail::STATUS_YES &&
                !empty($parent->img))
                <img src="{{ url('storage/' . $parent->img) }}" class="w-8 md:w-10 lg:w-12 mr-4" />
                @endif
                <span>{{ $parent->name }}</span>
            </button>
            @endforeach
            @endif
            </div>
            <!-- <div class="qr-code">
                <p>Scan the QR Code to Join Queue</p>
                <img src="qr-placeholder.png" alt="QR Code" width="120">
            </div> -->
        </div>
        @endif

        @if($secondpage)
        <!-- Main content area -->
        <div class="main">
            <h2 class="text-2xl md:text-2xl font-bold text-gray-900 text-center">Book an Appointment</h2>
            <p class="text-xl md:text-xl font-semibold text-gray-700 text-center">Select a Service</p>
            @if($firstChildren)
            @foreach($firstChildren as $child)
            <button class="{{ $fontSize }} {{ $borderWidth }} {{ $fontFamily }} service-btn flex justify-center items-center"
                wire:loading.class="opacity-50" wire:click="showSecondChild({{ $child->id }})">
                @if ($siteSetting && $siteSetting->show_cat_icon == App\Models\SiteDetail::STATUS_YES &&
                !empty($child->img))
                <img src="{{ url('storage/' . $child->img) }}" class="w-8 md:w-10 lg:w-12 mr-4" />
                @endif
                <span>{{ $child->name }}</span>
            </button>
            @endforeach
            @endif
        </div>
        @endif

        @if($thirdpage)
        <!-- Main content area -->
        <div class="main">
            <h2 class="text-2xl md:text-2xl font-bold text-gray-900 text-center">Book an Appointment</h2>
            <p class="text-xl md:text-xl font-semibold text-gray-700 text-center">Select a Service</p>
            @if($secondChildren)
            @foreach($secondChildren as $subchild)
            <button class="{{ $fontSize }} {{ $borderWidth }} {{ $fontFamily }} service-btn flex justify-center items-center" wire:loading.class="opacity-50"
                wire:click="showThirdChild({{ $subchild->id }})">
                @if ($siteSetting && $siteSetting->show_cat_icon == App\Models\SiteDetail::STATUS_YES &&
                !empty($subchild->img))
                <img src="{{ url('storage/' . $subchild->img) }}" class="w-8 md:w-10 lg:w-12 mr-4" />
                @endif
                <span>{{ $subchild->name }}</span>
            </button>
            @endforeach
            @endif

        </div>
        @endif

        @if($calendarpage)
    <div class="w-full max-w-2xl mx-auto" >
        <!-- Header -->
        <h2 class="text-2xl md:text-2xl font-bold text-gray-900 text-center mb-4">Book an Appointment</h2>
        <p class="text-xl md:text-xl font-semibold text-gray-700 text-center">Select a Date and Time</p>

        <!-- Calendar Container -->
        <div class="bg-white shadow-md rounded-lg p-4" wire:ignore>
            <div class="month-header flex justify-between items-center mb-2">
                <button class="nav-btn text-lg px-2" onclick="changeMonth(-1)">◀</button>
                <div class="flex items-center space-x-2">
                    <span id="month-title" class="text-lg font-semibold"></span>
                    <select wire:model="selectedYear" class="border rounded p-1 text-sm" onchange="changeYear(this.value)" style="width:80px;">
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="nav-btn text-lg px-2" onclick="changeMonth(1)">▶</button>
            </div>

            <div id="calendar" class="grid grid-cols-7 gap-1 mt-2">
                <!-- Calendar days dynamically added here -->
            </div>
        </div>

        <!-- Available Slots Section -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Available Slots</h3>

            @if(!empty($slots) && isset($slots['start_at']) && count($slots['start_at']) > 0)
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 gap-2" style="max-height:220px;overflow-y:auto">
                    @foreach($slots['start_at'] as $key => $slot)
                        <label class="block">
                            <input type="radio" wire:model.live="appointment_time" value="{{ $slot }}" class="hidden peer">
                            <div
                                class="px-2 py-2 text-center border border-gray-300 rounded-lg cursor-pointer peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 hover:bg-blue-100 transition text-sm">
                                {{ $slot }}
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500">No slots available.</p>
            @endif
        </div>
    </div>
@endif
<!-- Start form field code and submit button -->
@if($formfieldSection)

<div  style="width:100%;margin-top:25px;">
    <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Contact Detail </h3>
<div class="flex justify-center items-center">
<form wire:submit.prevent="saveAppointmentForm" class="w-full max-w-md">

    <div class="space-y-12">
        <div class="pb-8">
            <div class="mt-5 grid grid-cols-1 gap-x-6 gap-y-1 sm:grid-cols-6 p-3">

                @foreach ($dynamicForm as $form)

                    @if (App\Models\CategoryFormField::checkFieldCategory($form['id'], $allCategories))
                        @if ($form['type'] == App\Models\FormField::TEXT_FIELD || $form['type'] == App\Models\FormField::URL_FIELD)
                            <div class="col-span-full">
                                <div class="mt-1">
                                    <!--label for="{{ $form['label'] }}"
                                        class="block mb-2 text-sm font-medium text-gray-900 ">{{ $form['label'] }}</!--label-->
                                    <div
                                        class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="{{ $form['type'] == App\Models\FormField::TEXT_FIELD ? 'text' : 'url' }}"
                                            id="{{ $form['title'] . '_' . $form['id'] }}"
                                            class="block flex-1 border-slate-400 bg-transparent py-1.5 pl-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                            placeholder="{{ $form['placeholder'] }}"
                                            wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                            @if (!empty($form['minimum_number_allowed'])) minlength="{{ $form['minimum_number_allowed'] }}" @endif
                                            @if (!empty($form['maximum_number_allowed'])) maxlength="{{ $form['maximum_number_allowed'] }}" @endif
                                            @if ($form['title'] == 'phone') onkeypress="return checkIt(event)" @endif>
                                    </div>
                                    @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($form['type'] == App\Models\FormField::PHONE_FIELD)
                            <div class="col-span-full">
                                <div class="mt-4">
                                    <div class="flex gap-2 items-center sm:max-w-md border-gray-300 rounded-md border-solid border">
                                        <!-- Country Code Dropdown -->
                                        {{-- @if ($selectedCountryCode)
                                            <!-- Show an input field with the selected country code if available -->
                                            <input type="text" id="{{ $form['title'] . '_input' }}"
                                                class="block w-1/5 rounded-md border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 pl-2 pr-2 text-gray-900"
                                                 wire:model.defer="phone_code" value="+{{ $selectedCountryCode }}"
                                                readonly>
                                            <!-- readonly to prevent editing, remove if you want it editable -->
                                        @else
                                            <!-- Show the select dropdown if no selected country code -->
                                            <select id="{{ $form['title'] . '_select' }}"
                                                class="block w-1/5 rounded-md border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 pl-2 pr-2 text-gray-900"
                                                 wire:model.defer="phone_code">
                                                @foreach ($countryCode as $code)
                                                    <option value="{{ $code }}"
                                                        @if ($phone_code == $code) selected @endif>
                                                        +{{ $code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif --}}

                                        @if ($country_phone_mode == 1)

                                                <input type="text" id="{{ $form['title'] . '_input' }}"
                                                class="block w-1/5 rounded-md border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 pl-2 pr-2 text-gray-900"
                                                wire:model.defer="phone_code" value="+{{ $selectedCountryCode }}"
                                                readonly>


                                                @else

                                                <select id="{{ $form['title'] . '_select' }}"
                                                    class="block w-1/5 rounded-md border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 pl-3 pr-2 text-gray-900"
                                                    wire:model="phone_code">
                                                    <option value=""  hidden>+Code</option>

                                                    @if(!empty($allowed_Countries))
                                                    @foreach ($allowed_Countries as $code)
                                                    <option value="{{ $code->phone_code }}"> (+{{ $code->phone_code }}) {{ $code->name }} </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                                @endif


                                        <!-- Phone Number Input Field -->
                                        <input type="number" id="{{ $form['title'] . '_' . $form['id'] }}"
                                            class="block w-4/5 flex-1 border-slate-400 bg-transparent py-1.5 pl-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 h-12 rounded-lg"
                                            placeholder="{{ $form['placeholder'] }}"
                                            @if (!empty($form['minimum_number_allowed'])) minlength="{{ $form['minimum_number_allowed'] }}" @endif
                                            @if (!empty($form['maximum_number_allowed'])) maxlength="{{ $form['maximum_number_allowed'] }}" @endif
                                            wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                    </div>
                                    <!-- Error message styling for phone number -->
                                    @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                        <div class="text-sm text-red-600 mt-2">
                                            {{ $message }}
                                        </div>
                                    @enderror


                                </div>
                            </div>
                        @elseif($form['type'] == App\Models\FormField::DATE_FIELD)
                            <div class="col-span-full">
                                <div class="mt-2">
                                    <label for="{{ $form['label'] }}"
                                        class="{{ $fontSize }} {{$fontFamily}}  text-center block mb-2  font-medium text-gray-900">{{ $form['label'] }}</label>
                                    <div
                                        class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
                                        <input id="{{ $form['title'] . '_' . $form['id'] }}"
                                        wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                            datepicker-format="yyyy-mm-dd" type="date"  onclick="this.showPicker()"
                                            datepicker-autohide placeholder="{{ $form['placeholder'] }}"
                                            class="dynamicDatePicker text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg">
                                    </div>
                                    @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($form['type'] == App\Models\FormField::SELECT_FIELD)
                            <div class="col-span-full">
                                <div class="mt-2">
                                    <label for="{{ $form['label'] }}"
                                        class="text-center block mb-2 {{ $fontSize }} {{ $fontFamily }} font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                    <div
                                        class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <select id="{{ $form['title'] . '_' . $form['id'] }}"
                                        wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                            class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
                                            <option value=""> Select an option</option>
                                            @foreach ($form['options'] as $option)
                                                <option value="{{ $option }}" class="text-color">
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($form['type'] == App\Models\FormField::NUMBER_FIELD)
                            <div class="col-span-full">
                                <div class="mt-2">
                                    <label for="{{ $form['label'] }}"
                                        class="text-center block mb-2 {{ $fontSize }} {{ $fontFamily }} font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                    <div
                                        class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input type="number"
                                            id="{{ $form['title'] . '_' . $form['id'] }}"
                                            class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                            placeholder="{{ $form['placeholder'] }}"
                                           @if (!empty($form['minimum_number_allowed'])) minlength="{{ $form['minimum_number_allowed'] }}" @endif
                                           @if (!empty($form['maximum_number_allowed'])) maxlength="{{ $form['maximum_number_allowed'] }}" @endif
                                            wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}">
                                    </div>
                                    @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @elseif($form['type'] == App\Models\FormField::TEXTAREA_FIELD)
                            <div class="col-span-full">
                                <div class="mt-2">
                                    <label for="{{ $form['label'] }}"
                                        class="text-center block mb-2 {{ $fontSize }} {{ $fontFamily }} font-medium text-gray-900 ">{{ $form['label'] }}</label>
                                    <div
                                        class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md ">
                                        <textarea id="{{ $form['title'] . '_' . $form['id'] }}" rows="4"
                                            class="block p-2.5 w-full text-blue-600 bg-gray-100 border-slate-400 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-2 h-12 @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
                                            placeholder="{{ $form['placeholder'] }}" wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                           @if (!empty($form['minimum_number_allowed'])) minlength="{{ $form['minimum_number_allowed'] }}" @endif
    @if (!empty($form['maximum_number_allowed'])) maxlength="{{ $form['maximum_number_allowed'] }}" @endif> </textarea>
                                    </div>
                                    @error('dynamicProperties.' . $form['title'] . '_' . $form['id'])
                                        <div class="text-red-500">{{ $message }}</div>
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
                                                wire:model.defer ="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                                @if (!empty($form['minimum_number_allowed'])) minlength="{{ $form['minimum_number_allowed'] }}" @endif
    @if (!empty($form['maximum_number_allowed'])) maxlength="{{ $form['maximum_number_allowed'] }}" @endif
                                                @if ($form['mandatory'] == 1) required @endif>

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
                                        <div class="text-red-500">{{ $message }}</div>
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
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        @endif
                    @endif
                @endforeach

                <div class="col-span-full flex justify-center mt-3">
                    <button
                        class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">
                        <span class="{{ $fontSize }} {{$fontFamily}}"> {{ __('text.submit') }} </span>
                        <span wire:loading wire:target='saveQueueForm' class="ml-2">
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
                </div>
            </div>
        </div>
    </div>
</form>
</div>
</div>

<div id="printQueue"></div>


<div class="flex justify-center footer-section queue-footer">
<!-- <a href="/queue"
    class="bg-white text-slate-950 hover:border-indigo-700 hover:bg-indigo-700 hover:text-white text-xl font-bolds py-2 px-12 rounded-full border-2 border-gray-800 mr-4 queue-footer-button"
    wire:loading.class="opacity-50">
    {{ __('text.home') }}
</a> -->

<button type="button" wire:click="goBackFn({{ $totalLevelCount }})"
    class="{{ $fontSize }} {{$fontFamily}}  bg-white text-slate-950 hover:border-indigo-700 hover:bg-indigo-700 hover:text-white  font-bolds py-2 px-12 rounded-full border-2 border-gray-800 {{ $totalLevelCount <= 1 ? 'hidden' : '' }} queue-footer-button"
    wire:loading.class="opacity-50">
    {{ __('text.back') }}
</button>

</div>
@endif

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     document.addEventListener("DOMContentLoaded", function () {

    Livewire.on('booking-updated', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Booking updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload(); // Refresh the page when OK is clicked
            }
        });
    });
    });

var currentDate = new Date();
var currentYear = currentDate.getFullYear();
var currentMonth = currentDate.getMonth();

let disabledDates = @json($disabledDate);
let weekstart = "{{ $weekStart }}"; // This is dynamic, can be set via PHP or Livewire
let mindatestart = {{$mindate}};
let maxdatestart = {{$maxdate}};

let today = new Date();
let minDate = new Date();
minDate.setDate(today.getDate() + mindatestart);

let maxDate = new Date(minDate);
maxDate.setDate(minDate.getDate() + maxdatestart);

function generateCalendar(month, year) {
    const calendarGrid = document.getElementById("calendar");
    let monthTitle = document.getElementById("month-title");

    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    const weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    let weekStartIndex = weekdays.indexOf(weekstart);
    let rotatedWeekdays = weekdays.slice(weekStartIndex).concat(weekdays.slice(0, weekStartIndex));

    // Set header
    monthTitle.innerText = `${monthNames[month]} ${year}`;
    calendarGrid.innerHTML = rotatedWeekdays.map(day =>
        `<div class="header">${day.substring(0, 3)}</div>`
    ).join('');

    // Days in month
    let daysInMonth = new Date(year, month + 1, 0).getDate();
    let actualFirstDay = new Date(year, month, 1).getDay(); // 0 = Sunday
    let offset = (actualFirstDay - weekStartIndex + 7) % 7;

    // Blank days before first
    for (let i = 0; i < offset; i++) {
        let emptyDiv = document.createElement("div");
        emptyDiv.classList.add("day");
        emptyDiv.style.visibility = "hidden";
        calendarGrid.appendChild(emptyDiv);
    }

    // Create each day
    for (let day = 1; day <= daysInMonth; day++) {
        let dayDiv = document.createElement("div");
        dayDiv.classList.add("day");
        dayDiv.innerText = day;

        let dateStr = `${year}-${(month + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        let dateObj = new Date(dateStr);
        dateObj.setHours(0, 0, 0, 0);

        let todayDate = new Date();
        todayDate.setHours(0, 0, 0, 0);

        let isToday = dateObj.getTime() === todayDate.getTime();
        let isBeforeMin = dateObj < minDate;
        let isAfterMax = dateObj > maxDate;
        let isDisabledInList = disabledDates.includes(dateStr);

        let shouldDisable = (!isToday && (isBeforeMin || isAfterMax)) || isDisabledInList;

        if (shouldDisable) {
            dayDiv.classList.add("disabled");
        } else {
            dayDiv.addEventListener("click", function () {
                document.querySelectorAll(".day").forEach(d => d.classList.remove("selected"));
                this.classList.add("selected");

                Livewire.dispatch('selected-date', {
                    'date': dateStr
                });
            });
        }

        if (isToday) {
            dayDiv.classList.add("today");
        }

        calendarGrid.appendChild(dayDiv);
    }
}

function changeMonth(direction) {
    currentMonth += direction;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    } else if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    Livewire.dispatch('change-month-year', {
        month: currentMonth + 1, // convert from 0-based to 1-based month
        year: currentYear
    });
    generateCalendar(currentMonth, currentYear);
}

function changeYear(year) {
    currentYear = parseInt(year);
    Livewire.dispatch('change-month-year', {
        month: currentMonth + 1,
        year: currentYear
    });
    generateCalendar(currentMonth, currentYear);
}

document.addEventListener("livewire:init", function () {
    Livewire.on('update-calendar', (data) => {
        if (data && data[0].year !== undefined && data[0].month !== undefined) {
            currentYear = parseInt(data[0].year);
            currentMonth = parseInt(data[0].month);
        }
        if (data[0].disabledDate) {
            disabledDates = data[0].disabledDate;
        }

        setTimeout(() => {
            generateCalendar(currentMonth, currentYear);
        }, 10);
    });
});
</script>
