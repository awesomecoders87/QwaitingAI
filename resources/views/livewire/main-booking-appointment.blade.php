<div>
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
        /* background-color: /<?=$background ?>/#fff !important; */
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .w-8 {
        flex: 0 0 2rem;
    }
    .container {
        display: flex;
        overflow: hidden;
        margin: auto;
    }

    .booking-sidebar {
        /* background: #4A4AFF;
        color: white; */
        padding: 20px;
        width: 40%;
        display: flex;
        flex-direction: column;
        border-right:1px solid #ddd
        /* justify-content: center; */
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
        border-radius: 10%;
        transition: 0.3s;
    }

    /* .day:hover {
        background: #d1d1f0;
    } */

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
        border-radius: 5px;
        text-align:center
    }

    .year-select {
        font-size: 14px;
        padding: 5px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .day.disabled {
    /* background-color: #eee; */
    color: #aaa;
    pointer-events: none;
    cursor: not-allowed;
    /* border: 1px solid #ddd; */
    }

    .card-active {
        @apply border-blue-600 ring-2 ring-blue-300;
    }
    /* .screen-section{
        min-height: 100vh;
        align-items: center;
        display: flex;
    } */
    @media (max-width: 767px){
      .booking-sidebar,.main-content{
        width: 100%;
        border: 0;
        padding-left: 0;
        padding-right: 0;
      }
      .service-btn{
        width: 100%;
      }
      .main{
        margin-bottom: 45px;
      }
    }
    </style>
    <div class="screen-section">
    <div class="container flex-wrap-small reverse-small">

    <div class="{{ $locationStep == false ? 'hidden' : '' }}" style="margin:auto;">

     <div
      class="p-1"
    >

       @php
      $url = request()->url();
      $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

      if ( strpos( $url, 'mobile/queue' ) !== false ) {
        $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
      }

     $logo =  App\Models\SiteDetail::viewImage($headerPage);
  @endphp
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img
          src="{{ $logo }}"
          alt="qwaiting logo"
          class="h-10"
        />
      </div>

      <!-- Heading -->
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('text.Please select location') }}</h2>
        <p class="text-gray-500 text-sm mt-1 dark:text-gray-400">{{ __('text.Choose a branch to continue') }}</p>
      </div>

      <!-- Cards -->
      <div
        id="cardContainer"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 flex-grow"
      >
       @if (empty($location) && !empty($allLocations))
        @foreach ($allLocations as $location)
        <!-- Card 1 -->
        <div
          class="location-card border border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:shadow-lg transition-all bg-white h-full flex flex-col"
          data-location="{{ $locationName }}" wire:click="$set('location', '{{ $location->id }}')"
        >
          <img
            src="{{ !empty($location->location_image) ? url('storage/' . $location->location_image) : url('storage/location_images/no_image.jpg') }}"
            alt="{{ $locationName }}"
            class="w-full h-64 object-cover rounded-md mb-3"
          />
          <div class="flex-grow">
            <h3 class="text-xl font-semibold text-gray-700 dark:text-white">{{ $location->location_name }}</h3>
            <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">{{ $location->address }}</p>
            <p class="text-sm text-gray-500 mt-1 dark:text-gray-400"><strong>{{ __('text.Average Waiting Time') }}: </strong> {{ \App\Models\SiteDetail::fetchWaitingTime($location->id) ?? 0}} mins</p>
          </div>
        </div>
        @endforeach
        @endif

    </div>
    </div>
</div>
@if(!$locationStep && $isCustomerLogin)
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8" style="margin:auto;">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->

        <!-- Heading -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800  dark:text-white">
                @if($showOtpField)
                    {{ __('text.Verify OTP') }}
                @else
                    {{ __('text.Login with Mobile Number') }}
                @endif
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                @if($showOtpField)
                    {{ __("text.We've sent a 6-digit code to") }} {{ $mobile }}
                @else
                     {{ __('text.Enter your mobile number to receive OTP') }}
                @endif
            </p>
        </div>

        <!-- Form Container -->
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            @if(session('message'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if($showOtpField)
                <!-- OTP Form -->
                <form wire:submit.prevent="verifyOtp">
                    <div class="mb-4">
                        <label for="otp" class="block text-sm font-medium text-gray-700">
                            {{ __('text.6-digit OTP') }}
                        </label>
                        <div class="mt-1">
                            <input
                                wire:model="otp"
                                id="otp"
                                name="otp"
                                type="text"
                                inputmode="numeric"
                                pattern="\d{6}"
                                maxlength="6"
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter OTP"
                            >
                        </div>
                        @error('otp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            {{ __('text.Verify & Login') }}
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center text-sm">
                    <button
                        wire:click="$set('showOtpField', false)"
                        class="text-blue-600 hover:text-blue-500"
                    >
                       {{ __('text.Change Mobile Number') }}
                    </button>
                </div>
            @else
                <!-- Mobile Form -->
                <form wire:submit.prevent="sendOtp">
                    <div class="mb-4">
                        <label for="mobile" class="block text-sm font-medium text-gray-700">
                            {{ __('text.Mobile Number') }}
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute">

                                <select  class="block"
                                                 wire:model.defer="customer_phone_code">
                                                @foreach ($countryCode as $code)
                                                    <option value="{{ $code }}"
                                                        @if ($phone_code == $code) selected @endif>
                                                        +{{ $code }}
                                                    </option>
                                                @endforeach
                                            </select>
                            </div>

                            <input
                                wire:model="mobile"
                                id="mobile"
                                name="mobile"
                                type="tel"
                                inputmode="numeric"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                required
                                class="block w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="9876543210"
                            >
                        </div>
                        @error('mobile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            {{ __('text.Send OTP') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <!-- Footer Links -->
        <div class="mt-6 text-center text-sm">
            <p class="text-gray-600">
                By continuing, you agree to our
                <a href="#" class="text-blue-600 hover:text-blue-500">{{ __('text.Terms') }}</a> {{ __('text.and') }}
                <a href="#" class="text-blue-600 hover:text-blue-500">{{ __('text.Privacy Policy') }}</a>.
            </p>
        </div>
    </div>
</div>
@endif
@if(!$locationStep && !$isCustomerLogin)
    <!-- Sidebar with company info and hours -->
        <div class="booking-sidebar">
            <div class="sidebar-inner">
            <div  class="pb-4 border-b">
            @auth
            <h2 class="font-bold dark:text-white">{{ $user->name }}</h2>
            @endauth

            <p>{{ $siteSetting?->booking_sidebar_heading ?? '' }}</p>
</div>
            <h3 class="pt-4">{{ __('text.Business Hours') }}</h3>
            <div class="business-hours pb-3">
                @if($locationslots)
                @foreach($locationslots as $slot)
                @if($slot['is_closed'] == 'open')
                <p class="text-gray-600  dark:text-gray-400">{{ $slot['day'] }}: <span>{{ $slot['start_time'] .'-'. $slot['end_time'] }}<span>
                            @if(!empty($slot['day_interval']))
                            @foreach($slot['day_interval'] as $interval)
                            <span>{{ $interval['start_time'] .'-'. $interval['end_time'] }}<span>
                                    @endforeach
                                    @endif
                </p>

                @else
                <p class="text-red-600">{{ $slot['day'] }}: {{ __('text.closed') }}</p>
                @endif
                @endforeach
                @endif

            </div>
            @auth
            <h3 class="pt-4 border-t mt-3">{{ __('text.Company Info') }}</h3>
            <div class="company-info">
                <p class="flex gap-3"><span class="w-8">📍</span> {{ __('text.address') }}: {{ $location->address}}, {{ $location->city}}, {{ $location->state}}</p>
                <p class="flex gap-3"><span class="w-8">📧</span> {{ __('text.Email') }}: {{ $user->email }}</p>
                <p class="flex gap-3"><span class="w-8">📞</span> {{ __('text.Phone') }}: {{ $user->phone }}</p>
                <!-- <p>🌐 Website: www.qwaiting.com</p> -->
                <!-- <p>📅 Established: 2015</p> -->

            </div>
            @endauth
            @guest
            <h3 class="pt-4 border-t mt-3">{{ __('text.Company Info') }}</h3>
            <div class="company-info">
                <p class="flex gap-3"><span class="w-8">📍</span>  {{ __('text.address') }}: {{ $location->address}}, {{ $location->city}}, {{ $location->state}}</p>

            </div>
            @endguest
        </div>
        </div>


       @endif

        @if($firstpage && !$isCustomerLogin)
        <!-- Main content area -->
        <div class="main-content">
            <h2 class="text-2xl md:text-2xl font-bold  dark:text-white">{{ __('text.Book an Appointment') }}</h2>
            <p class="text-xl md:text-xl font-semibold text-gray-700 dark:text-gray-400  dark:text-gray-400">{{ __('text.Select a service') }}</p>
            @if($parentCategory)
            @foreach($parentCategory as $parent)
        <div class="">
           <button type="button"
                    class="{{ $fontSize }}  {{ $fontFamily }} {{ $borderWidth }} service-btn rounded-xl border-gray-400 flex flex-col justify-center items-center text-center"
                    wire:loading.class="opacity-50"
                    wire:click.prevent="showFirstChild({{ $parent->id }})">

                    @if ($siteSetting && $siteSetting->show_cat_icon == App\Models\SiteDetail::STATUS_YES && !empty($parent->img))
                        <img src="{{ url('storage/' . $parent->img) }}" class="w-8 md:w-10 lg:w-12 mb-2" />
                    @endif

                    <span class="font-semibold">
                        {{ $parent->name }}
                        {{ $parent->other_name ?? '' }}
                    </span>
 @if (!empty($parent->description))
                        <div>
                            <span class="{{ $fontSize }} {{ $fontFamily }}">{{ $parent->description ?? ''}}</span>
                        </div>
                        @endif

                </button>

                @if(!empty($parent->redirect_url))
                    <a href="{{ $parent->redirect_url }}" class="text-blue-600 text-sm underline mt-1" target="_blank">
                        {{ __('text.Click to Redirect') }}
                    </a>
                @endif
            </div>
            @endforeach
            @endif

            <!-- <div class="qr-code">
                <p>Scan the QR Code to Join Queue</p>
                <img src="qr-placeholder.png" alt="QR Code" width="120">
            </div> -->
        </div>
        @endif

        @if($secondpage && !$isCustomerLogin)
        <!-- Main content area -->
        <div class="main-content">
            <h2 class="text-2xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ __('text.Book an Appointment') }}</h2>
            <p class="text-xl md:text-xl font-semibold text-gray-700  dark:text-gray-400">{{ __('text.Select a service') }}</p>
            @if($firstChildren)
            @foreach($firstChildren as $child)
             <div class="">
            <button class="{{ $fontSize }} {{ $fontFamily }} {{ $borderWidth }} service-btn rounded-xl border-gray-400 flex flex-col justify-center items-center text-center"
                wire:loading.class="opacity-50" wire:click="showSecondChild({{ $child->id }})">
                @if ($siteSetting && $siteSetting->show_cat_icon == App\Models\SiteDetail::STATUS_YES &&
                !empty($child->img))
                <img src="{{ url('storage/' . $child->img) }}" class="w-8 md:w-10 lg:w-12 mr-4" />
                @endif
                <span>{{ $child->name }} {{ !empty($child['other_name']) ? ' - '.$child['other_name'] : ''}}</span>
            </button>
               @if (!empty($child['description']))
                        <div>
                            <span class="{{ $fontSize }} {{ $fontFamily }}">{{ $child['description'] ?? ''}}</span>
                        </div>
                        @endif
            @if(!empty($child->redirect_url))
                    <a href="{{ $child->redirect_url }}" class="text-blue-600 text-sm underline mt-1" target="_blank">
                        {{ __('text.Click to Redirect') }}
                    </a>
                @endif
        </div>
            @endforeach
            @endif
        </div>
        @endif

        @if($thirdpage && !$isCustomerLogin)
        <!-- Main content area -->
        <div class="main-content">
            <h2 class="text-2xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ __('text.Book an Appointment') }}</h2>
            <p class="text-xl md:text-xl font-semibold text-gray-700 dark:text-gray-400">{{ __('text.Select a service') }}</p>
            @if($secondChildren)
            @foreach($secondChildren as $subchild)
             <div class="">
            <button class="{{ $fontSize }}  {{ $fontFamily }} {{ $borderWidth }} service-btn rounded-xl border-gray-400 flex flex-col justify-center items-center text-center" wire:loading.class="opacity-50"
                wire:click="showThirdChild({{ $subchild->id }})">
                @if ($siteSetting && $siteSetting->show_cat_icon == App\Models\SiteDetail::STATUS_YES &&
                !empty($subchild->img))
                <img src="{{ url('storage/' . $subchild->img) }}" class="w-8 md:w-10 lg:w-12 mr-4" />
                @endif
                <span>{{ $subchild->name }} {{ !empty($subchild['other_name']) ? ' - '.$subchild['other_name'] : ''}}</span>
            </button>
                   @if (!empty($subchild['description']))
                        <div>
                            <span class="{{ $fontSize }} {{ $fontFamily }}">{{ $subchild['description'] ?? ''}}</span>
                        </div>
                        @endif
             @if(!empty($subchild->redirect_url))
                    <a href="{{ $subchild->redirect_url }}" class="text-blue-600 text-sm underline mt-1" target="_blank">
                        {{ __('text.Click to Redirect') }}
                    </a>
                @endif
        </div>
            @endforeach
            @endif

        </div>
        @endif

        @if($calendarpage && !$isCustomerLogin)
    <div class="w-full max-w-2xl mx-auto p-3 main-content" >
        <!-- Header -->
         @if ($enable_service && !empty($note))
    <div class="mt-4 mb-4 p-4 bg-yellow-100 text-yellow-800 rounded-lg shadow-sm">
        <strong>Note:</strong> {{ $note }}
    </div>
@endif

        <h2 class="text-2xl md:text-2xl font-bold text-center  dark:text-white">{{ __('text.Book an Appointment') }}</h2>
        <p class="text-xl md:text-xl font-semibold text-gray-700 text-center  dark:text-gray-400">{{ __('text.Select a Date and Time') }}</p>

        <!-- Calendar Container -->
        <div class="bg-white shadow-md rounded-lg p-4 dark:bg-white/[0.03] dark:border-gray-400 dark:text-white" wire:ignore>
            <div class="month-header flex justify-between items-center mb-2">
                <button class="nav-btn text-lg px-2" onclick="changeMonth(-1)">◀</button>
                <div class="flex items-center space-x-2">
                    <span id="month-title" class="text-lg font-semibold"></span>
                    <select wire:model="selectedYear" class="border rounded p-1 text-sm dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" onchange="changeYear(this.value)" style="width:80px;">
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
        @if($showPreferButton)
       <button type="button"  wire:click.prevent="modelPreferTimeSlot" class="flex justify-center rounded-lg bg-blue-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 mt-4">
                    {{ __('text.Select Prefer Time') }}
        </button>
        @endif
        <!-- Available Slots Section -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2 dark:text-white">{{ __('text.Available Slots') }}</h3>

            @if(!empty($slots) && isset($slots['start_at']) && count($slots['start_at']) > 0)
                <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-3 gap-2" style="max-height:224px;overflow-y:auto">
                    @foreach($slots['start_at'] as $key => $slot)
                        <label class="block">
                            <input type="radio" wire:model.live="appointment_time" value="{{ $slot }}" class="hidden peer">
                            <div
                                class="px-2 py-2 text-center border border-gray-300 rounded-lg cursor-pointer peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 hover:bg-blue-100 transition text-sm dark:hover:bg-gray-700">
                              @php
                              $start_time_show = explode('-', $slot);
                              @endphp
                                {{ $start_time_show[0] }}
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500">{{ __('text.No slots available.') }}</p>
            @endif
        </div>
    </div>
@endif
<!-- Start form field code and submit button -->
@if($formfieldSection && !$isCustomerLogin)

<div class="main-content">
    <h3 class="text-2xl md:text-2xl font-bold text-center">{{ __('text.Contact Details') }} </h3>
<div class="flex justify-center items-center">
<!-- <form wire:submit.prevent="saveAppointmentForm" class="w-full max-w-md"> -->
<div class="w-full max-w-md">

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
                                        class="flex sm:max-w-md">
                                        <input
                                            type="{{ $form['type'] == App\Models\FormField::TEXT_FIELD ? 'text' : 'url' }}"
                                            id="{{ $form['title'] . '_' . $form['id'] }}"
                                            class="text-center block flex-1 border border-slate-400 bg-transparent py-1.5 pl-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-white @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
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
                                                class="block w-1/5 rounded-md border border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 pl-2 pr-2 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                 wire:model.defer="phone_code" value="+{{ $selectedCountryCode }}"
                                                readonly>
                                            <!-- readonly to prevent editing, remove if you want it editable -->
                                        @else
                                            <!-- Show the select dropdown if no selected country code -->
                                            <select id="{{ $form['title'] . '_select' }}"
                                                class="block w-1/5 rounded-md border border-slate-400 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm py-2 pl-2 pr-2 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
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
                                            class="block w-4/5 flex-1 border-slate-400 bg-transparent py-1.5 pl-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 h-12 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-white"
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
                                        class="{{ $fontSize }} {{$fontFamily}}  text-center block mb-2  font-medium text-gray-900 dark:text-gray-400">{{ $form['label'] }}</label>
                                    <div
                                        class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
                                        <input id="{{ $form['title'] . '_' . $form['id'] }}"
                                        wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                            datepicker-format="yyyy-mm-dd" type="date"  onclick="this.showPicker()"
                                            datepicker-autohide placeholder="{{ $form['placeholder'] }}"
                                            class="dynamicDatePicker text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-white">
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
                                        class="text-center block mb-2 {{ $fontSize }} {{ $fontFamily }} font-medium text-gray-900 dark:text-gray-400">{{ $form['label'] }}</label>
                                    <div
                                        class="flex sm:max-w-md">
                                        <select id="{{ $form['title'] . '_' . $form['id'] }}"
                                        wire:model.defer="dynamicProperties.{{ $form['title'] . '_' . $form['id'] }}"
                                            class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-white @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror">
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
                                        class="text-center block mb-2 {{ $fontSize }} {{ $fontFamily }} font-medium text-gray-900 dark:text-gray-400">{{ $form['label'] }}</label>
                                    <div
                                        class="flex sm:max-w-md">
                                        <input type="number"
                                            id="{{ $form['title'] . '_' . $form['id'] }}"
                                            class="text-center block flex-1 border-slate-400 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6  h-12 rounded-lg dark:border-gray-600 dark:bg-gray-800 dark:text-white @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
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
                                        class="flex sm:max-w-md ">
                                        <textarea id="{{ $form['title'] . '_' . $form['id'] }}" rows="4"
                                            class="block p-2.5 w-full text-blue-600 bg-gray-100 border-slate-400 rounded focus:ring-blue-500 dark:focus:ring-blue-600 focus:ring-2 h-12 dark:border-gray-600 dark:bg-gray-800 dark:text-white @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
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
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:border-gray-600 dark:bg-gray-800  @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
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
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white @error('dynamicProperties.' . $form['title'] . '_' . $form['id']) is-invalid @enderror"
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
                    @if($this->paymentSetting?->enable_payment == 1 && $isFree == 1 && ($this->paymentSetting?->payment_applicable_to == 'appointment' || $this->paymentSetting?->payment_applicable_to == 'both'))
                                <button wire:click="showPaymentPage"
                                    class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">
                                    <span class="{{ $fontSize }} {{$fontFamily}}">{{ __('text.Next') }} </span>

                                </button>
                                @else
                    <button  wire:click.prevent="saveAppointmentForm"
                        class="flex justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button">
                        <span class="{{ $fontSize }} {{$fontFamily}}"> {{ __('text.submit') }} </span>
                        <span wire:loading wire:target='saveAppointmentForm' class="ml-2">
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
</div>




@endif

<div class="flex justify-center footer-section queue-footer bg-white dark:bg-gray-700">
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
@if($this->paymentStep == 1 && !$isCustomerLogin)
                       <div class="paymentStep m-auto" style="width:50%;">
                            <div class="max-w-md mx-auto p-6 bg-white rounded-2xl shadow-md space-y-4 " wire:ignore >
                            <h2 class="text-2xl font-semibold text-gray-800">{{ __('text.Payment') }}</h2>

                            <input
                                type="email"
                                wire:model.defer="email"
                                placeholder="Email"
                                required
                                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >



                            <div>
                                <label class="block text-sm text-gray-700 mb-1">{{ __('text.Card Details') }}</label>
                                <div id="card-element" class="px-4 py-3 border rounded-md shadow-sm bg-white"></div>
                            </div>

                        </div>
                        <button
                            id="pay-btn"
                            class="w-full max-w-md mx-auto bg-indigo-500 hover:bg-indigo-700 text-white font-bolds py-3 text-lg px-4 flex-1 rounded-lg queue-footer-button"
                        >
                      <span class="button-text">{{ __('text.Pay') }}</span>
    <svg
        id="pay-loader"
        class="ml-2 h-5 w-5 text-white animate-spin hidden"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
    >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
                        </button>
                        </div>
                @endif


@if($isPreferTimeModel)
<div x-data="{ open: open }" x-show="open" @open-modal.window="if ($event.detail.id === 'preferTimeModel') open = true"
    @close-modal.window="if ($event.detail.id === 'preferTimeModel') open = false" @keydown.escape.window="open = false"
    class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">

    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-800/50 "></div>

    <div @click.outside="open = false"
        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

        <!-- Modal Content -->
        <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('text.Select Prefer Time') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4">
                <form wire:submit.prevent="addPreferTime">

                   <label for="appt">{{ __('text.Select Time') }}:</label>
                        <input type="time" wire:model="preferStartTime" onclick="this.showPicker()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500">

                    <div class="flex items-center justify-end w-full gap-3 mt-8">
                        <!-- Submit Button -->
                        <button type="submit"
                            class="flex justify-center rounded-lg bg-blue-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            {{ __('text.Ok') }}
                        </button>

                        <!-- Cancel Button -->
                        <button type="button" @click="$dispatch('close-modal', {id: 'preferTimeModel'})"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                            {{ __('text.Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<div id="printQueue"></div>
    </div>


    <script src="https://js.stripe.com/v3/"></script>
<script>
     document.addEventListener('livewire:init', () => {
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
            stripe = Stripe("{{ config('services.stripe.key') }}");
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

                const { paymentMethod, error } = await stripe.createPaymentMethod('card', card);

                if (error) {
                    console.log(error.message);
                    Livewire.dispatch('stripe-payment-method', { paymentMethodId: null });
                } else {
                    Livewire.dispatch('stripe-payment-method', { paymentMethodId: paymentMethod.id });
                }
            });
        } else {
            console.warn('Pay button not found.');
        }
        }, 300); // Delay to ensure DOM is ready
    });
    });


</script>


<script>
var currentDate = new Date();
var currentYear = currentDate.getFullYear();
var currentMonth = currentDate.getMonth();

let disabledDates = @json($disabledDate);
let weekstart = "{{ $weekStart }}";
let mindatestart = {{ $mindate }};
let maxdatestart = {{ $maxdate }};

let today = new Date();
let minDate = new Date();

if (mindatestart == 0) {
    // Allow current date
    minDate.setDate(today.getDate());
} else {
    // If mindate > 0, disable today and set minDate accordingly
    minDate.setDate(today.getDate() + (mindatestart-1));
console.log(minDate);
    // Format today's date as YYYY-MM-DD and push to disabledDates
    let yyyy = today.getFullYear();
    let mm = String(today.getMonth() + 1).padStart(2, '0');
    let dd = String(today.getDate()).padStart(2, '0');
    let formattedToday = `${yyyy}-${mm}-${dd}`;

    if (!disabledDates.includes(formattedToday)) {
        disabledDates.push(formattedToday);
    }
}

let maxDate = new Date(minDate);
maxDate.setDate(minDate.getDate() + maxdatestart);
// Set maxDate based on minDate

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
        dayDiv.classList.add("bg-white");
        dayDiv.classList.add("dark:bg-gray-600");
        dayDiv.classList.add("dark:hover:bg-gray-500");
        dayDiv.innerText = day;

        let dateStr = `${year}-${(month + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        let dateObj = new Date(dateStr);
        dateObj.setHours(0, 0, 0, 0);

        let todayDate = new Date();
        todayDate.setHours(0, 0, 0, 0);

        let isToday = dateObj.getTime() === todayDate.getTime();
        let isSelected = dateObj.getTime() === minDate.getTime();
        let isBeforeMin = dateObj < minDate;
        let isAfterMax = dateObj > maxDate;
        let isDisabledInList = disabledDates.includes(dateStr);

        let shouldDisable = (!isToday && (isBeforeMin || isAfterMax)) || isDisabledInList;

        if (shouldDisable) {
            dayDiv.classList.add("disabled");
            dayDiv.classList.add("disabled:bg-gray-50");

        } else {
            dayDiv.addEventListener("click", function () {
                document.querySelectorAll(".day").forEach(d => d.classList.remove("selected"));
                this.classList.add("selected");

                Livewire.dispatch('selected-date', {
                    'date': dateStr
                });
            });
        }

        // if (isToday) {
        //     dayDiv.classList.add("today");
        // }


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

  document.addEventListener("DOMContentLoaded", () => {
        const cards = document.querySelectorAll(".location-card");
        cards.forEach((card) => {
          card.addEventListener("click", () => {
            cards.forEach((c) => c.classList.remove("card-active"));
            card.classList.add("card-active");
          });
        });
      });

       window.addEventListener('swal:time-required', () => {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Time',
            text: 'Preferred Start Time is required!',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });
</script>
    </div>


</div>
