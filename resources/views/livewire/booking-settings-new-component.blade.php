<div class="p-4">
    @if($mainpage)
   <div class="main-page mx-auto">
      <style>
         .swal2-confirm{
            background:black;
         }
        div.inside-page-heading{
            display: flex !important;
            align-items: baseline;
         }
         div.inside-page-heading button{
            font-size:1rem;
         }
      </style>
      <!-- <div class="flex justify-between">
      <h3 class="fi-section-header-heading text-2xl font-semibold leading-2 text-gray-950 dark:text-white mb-4">Bookings</h3>

      </div> -->
      <div class="flex justify-between gap-6 items-center flex-wrap mb-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));">
         <h2 class="fi-section-header-heading text-xl font-semibold dark:text-white">{{ __('setting.Booking hours') }}</h2>
         <div class="flex gap-x-3">
            <div class="flex items-center">
            <label class="switch">
            <input type="checkbox" wire:click="toggle" wire:model="isEnabled">
            <span class="slider round"></span>
            </label>
            <span class="ml-3 font-medium text-gray-900 dark:text-gray-300" style="margin:10px">
            {{ $isEnabled ? __('setting.Enabled') : __('setting.Disabled') }}
            </span>
         </div>
         @if($isEnabled)
         <button class="flex items-center justify-center px-3 py-2 font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" wire:click="showEditModal">
            <span class="fi-btn-label">
             {{ __('setting.Edit hours') }}
            </span>
            </button>
            @endif
         </div>
      </div>
       @if($isEnabled)
      <div class="flex -mx-2.5 flex-wrap grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn mb-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));">
      <div class="w-full px-2.5 xl:w-1/2 mb-3">
      <div class="p-3 rounded w-full shadow col-md-6 gap-3 px-6 py-4 col-[--col-span-default] bg-white dark:bg-white/[0.03]">
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Weekly hours') }}</h3>
            <ul class="mt-3">
               @foreach($businessHours as $day)
               <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn text-gray-500"  style="--cols-default: 90px 8fr; --cols-lg: 90px 8fr;">
                  <span class="text-gray-400 dark:text-white ">{{ __('text.' . $day['day']) }}</span>
                  <span>
                     @if ($day['is_closed'] === "closed")
                     <span class="text-red-500">{{ __('setting.Closed') }}</span>
                     @else
                     {{ \Carbon\Carbon::createFromFormat('H:i', $day['start_time'])->format('h:i A') }} -
                     {{ \Carbon\Carbon::createFromFormat('H:i', $day['end_time'])->format('h:i A') }}
                     @endif
                     <!-- Show day intervals if available -->
                     @if (!empty($day['day_interval']) &&  $day['is_closed'] != "closed")
                     <ul class="mt-2 pl-4 text-sm text-gray-600">
                        @foreach($day['day_interval'] as $interval)
                        @if(!empty($interval['start_time']) && !empty($interval['end_time']))
                        <li class="flex justify-between">
                           <span>{{ \Carbon\Carbon::createFromFormat('H:i', $interval['start_time'])->format('h:i A') }} -
                           {{ \Carbon\Carbon::createFromFormat('H:i', $interval['end_time'])->format('h:i A') }}</span>
                        </li>
                        @endif
                        @endforeach
                     </ul>
                     @endif
                  </span>
               </li>
               @endforeach
            </ul>
         </div>
      </div>
      <div class="w-full px-2.5 xl:w-1/2 mb-3">
         <div class="w-full h-full p-3 rounded shadow col-md-6 gap-3 px-6 py-4 col-[--col-span-default] bg-white dark:bg-white/[0.03]">
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Hours by date') }}</h3>
            <ul class="mt-3">
               <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"><span class="text-gray-400 dark:text-white ">{{ __('setting.Weekly hours') }}</span><span></span></li>
            </ul>
            <ul class="mt-3">
               @if($customSlots)
               @foreach($customSlots as $day)
               <li class="flex gap-3 mt-3 grid grid-cols-[--cols-default] fi-fo-component-ctn"  style="--cols-default: 90px 8fr; --cols-lg: 90px 8fr;">
                  <span class="text-gray-400 dark:text-white ">{{ \Carbon\Carbon::parse($day['selected_date'])->format('M d Y') }}</span>
                  <span>
                     @if ($day['is_closed'] === "closed")
                     <span class="text-red-500">Closed</span>
                     @else
                     {{ \Carbon\Carbon::parse($day['start_time'])->format('h:i A') }} -
                     {{ \Carbon\Carbon::parse($day['end_time'])->format('h:i A') }}
                     @endif
                     <!-- Show day intervals if available -->
                     @if (!empty($day['day_interval']) && $day['is_closed'] != "closed")
                     <ul class="mt-2 pl-4 text-sm text-gray-600">
                        @foreach($day['day_interval'] as $interval)
                        @if(!empty($interval['start_time']) && !empty($interval['end_time']))
                        <li class="flex justify-between">
                           <span>{{ \Carbon\Carbon::parse($interval['start_time'])->format('h:i A') }} -
                           {{ \Carbon\Carbon::parse($interval['end_time'])->format('h:i A') }}</span>
                        </li>
                        @endif
                        @endforeach
                     </ul>
                     @endif
                  </span>
               </li>
               @endforeach
               @endif
            </ul>
         </div>
      </div>
      </div>
      @endif
      <div class="grid justify-between gap-2 mt-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(1, minmax(0, 1fr));">
         <h3 class="fi-section-header-heading text-xl font-semibold dark:text-white">{{ __('setting.General Settings') }}</h3>
         <p >{{ __('setting.Configure basic functionality around your bookings.') }}</p>
      </div>
      <ul class="mt-3 border shadow rounded border-gray-300 dark:border-gray-700">
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between cursor-pointer dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700" wire:click="showPage('availabilitySection')">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Availability') }}</h3>
               <p class="text-xs">{{ __('setting.Manage how many bookings can be made at the same time.') }}</p>
            </div>
            <div class="flex">
               <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="white-path"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg></span>
            </div>

         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between cursor-pointer  dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700" wire:click="showPage('schedulingWindow')">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Scheduling window') }}</h3>
               <p class="text-xs">{{ __('setting.Manage how far in advance customers can make a booking.') }}</p>
            </div>
            <div class="flex">
               <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="white-path"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg></span>
            </div>
         </li>
         <!-- <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between cursor-pointer" wire:click="showPage('geofence')">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">Geofence</h3>
               <p class="text-xs">Allow registration only if the customer is within a certain radius from your location.</p>
            </div>
            <div class="flex">
               <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg></span>
            </div>
         </li> -->
         <!-- <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">Booking time hold </h3>
               <p class="text-xs">Reserve the customer's selected time slot for 10 min while they are completing their registration.</p>
            </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="bookingHoldToggle" wire:model="bookingTimeHold">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li> -->
         <!-- <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between cursor-pointer" wire:click="showPage('maxBooking')">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white"> Max bookings per customer </h3>
               <p class="text-xs">Limit the number of bookings a customer can make per time period.</p>
            </div>
            <div class="flex">
               <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path></svg></span>
            </div>
         </li> -->
         <!-- <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">Booking approval </h3>
               <p class="text-xs">Customers make booking requests that must be approved by staff</p>
            </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="bookingApprovalToggle" wire:model="bookingApproval">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li> -->
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Allow customers to reschedule booking') }}</h3>
               <p class="text-xs">{{ __('setting.Customers are allowed to reschedule their booking.') }}</p>
            </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="allowRescheduleBookingToggle" wire:model="allowReschedulebooking">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Allow customers to cancel their booking') }}</h3>
               <p class="text-xs">{{ __('setting.Customers are allowed to cancel their booking.') }}</p>
            </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="allowCancelBookingToggle" wire:model="allowCancelbooking">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
         <!-- <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">Allow customers to edit their details</h3>
               <p class="text-gray-600 dark:text-white text-xs">Customers are allowed edit their details on their booking confirmation page.</p>
            </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="allowEditBookingToggle" wire:model="allowEditbooking">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li> -->
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
            <div class="">
               <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Show Convert Appointment to Queue Form') }}</h3>
            </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="convertAppointmentToQueueToggle" wire:model="convertAppointmentToQueue">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
         <div>
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Show Booking Confirmation Page') }}</h3>
         </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="bookingConfirmationPageToggle" wire:model="bookingConfirmationPage">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
         <div>
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Show Check-in QR Code') }}</h3>
         </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="checkinQrCodeToggle" wire:model="checkinQrCode">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
         <div>
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Custom Booking ID') }}</h3>
         </div>
            <div class="flex">

               <label>
                  <select  wire:change="customBookingIDToggle" wire:model="customBookingID" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">
                        <option value="default">{{ __('setting.Default') }}</option>
                        <option value="email">{{ __('setting.Email') }}</option>
                        <option value="phone">{{ __('setting.Phone') }}</option>
                  </select>

               </label>

            </div>
         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
         <div>
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Booking Convert Manually') }}</h3>
         </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="bookingConvertManuallyToggle" wire:model="bookingConvertManually">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
         <div>
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Google Calendar') }}</h3>
         </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="googleCalendarToggle" wire:model="googleCalendar">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
         <li class="bg-white flex gap-3 p-6 border-b border-gray-300 justify-items-center justify-between dark:border-gray-700 dark:bg-white/[0.03] dark:hover:bg-gray-700">
         <div>
            <h3 class="fi-section-header-heading text-base font-semibold dark:text-white">{{ __('setting.Outlook Calendar') }}</h3>
         </div>
            <div class="flex">
               <span>
               <label class="switch">
               <input type="checkbox" wire:click="outlookCalendarToggle" wire:model="outlookCalendar">
               <span class="slider round"></span>
               </label>
               </span>
            </div>
         </li>
      </ul>
      @if($showModal)
    <!-- Edit Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999 p-4" style="background: rgba(0,0,0,0.25);">

    <div class="p-5 modal-close-btn inset-0  m-auto w-full max-w-2xl bg-white-400/50 bg-white rounded-xl relative  dark:bg-gray-800 dark:border-gray-700">

    <button wire:click="showCloseModal" class="absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:right-6 sm:top-6 sm:h-11 sm:w-11">
      <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
      </svg>
    </button>

        <div>

         <div class="mb-2">
            <h3 class="text-xl font-semibold">{{ __('setting.Edit Opening Hours') }}</h3>
            <p>{{ __('setting.By weekly or by date') }}</p>
         </div>
            <div class="overflow-y-auto" style="height: calc(100% - 110px);">
            <div class="grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-6 mb-4" style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(2, minmax(0, 1fr));">
            <div class="mt-4 space-y-4 ">
                @foreach($businessHours as $index => $day)

                    <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-3">{{ __('text.' . $day['day']) }}</h3>
                        <select wire:model="businessHours.{{ $index }}.is_closed"
                                class="w-full border p-2 rounded-md border-gray-400  mt-3  dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="open">{{ __('setting.Open') }}</option>
                            <option value="closed">{{ __('setting.Closed') }}</option>
                        </select>

                            <div class="mt-3 flex gap-4">

                                <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.start_time"
                                       class="w-full border p-2 rounded-md border-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">
                                <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.end_time"
                                       class="w-full border p-2 rounded-md border-gray-400  dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">
                            </div>
                             <!-- Day Intervals -->
                             <div class="mt-3 space-y-2" wire:key="day-{{ $index }}">
                                @foreach($businessHours[$index]['day_interval'] as $slotIndex => $slot)
                                    <div class="flex gap-4 items-center"  wire:key="interval-{{ $index }}-{{ $slotIndex }}">
                                        <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.day_interval.{{ $slotIndex }}.start_time"
                                               class="w-full border p-2 rounded-md border-gray-400  dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">
                                        <input type="time" onclick="this.showPicker()" wire:model="businessHours.{{ $index }}.day_interval.{{ $slotIndex }}.end_time"
                                               class="w-full border p-2 rounded-md border-gray-400  dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">
                                        <button wire:click="removeSlot({{ $index }}, {{ $slotIndex }})"
                                                class="px-2 py-1 bg-error-500 text-white rounded hover:bg-error-600 dark:bg-white/[0.03] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">
                                            ✖
                                        </button>
                                    </div>
                                @endforeach
                                <button wire:click="addSlot({{ $index }})"
                                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-sm fi-btn-size-sm gap-1 px-2.5 py-1.5 text-sm inline-grid shadow-sm bg-custom-600 text-black hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 dark:bg-white/[0.03] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 ">
                                    + {{ __('setting.Add Time Slot') }}
                                </button>
                            </div>
                    </div>
                @endforeach
            </div>
            <div>
    <h3 class="text-lg font-semibold">Set Opening Hours</h3>

    <!-- Dynamically Display Common Time Slots -->
    <div class="mt-4 space-y-4">

            @foreach($customSlots as $slotIndex => $slot)
                <div  wire:key="days-{{ $slotIndex }}">
                   <div class="mt-3 flex gap-4">
                    <input type="date" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.selected_date" class="w-full border p-2 rounded dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">
                        <select wire:model="customSlots.{{ $slotIndex }}.is_closed"
                                class="w-full border p-2 rounded-md border-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="open">{{ __('setting.Open') }}</option>
                            <option value="closed">{{ __('setting.Closed') }}</option>
                    </select>
                    </div>
                    <div class="mt-3 flex gap-4">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.start_time" class="w-full border p-2 rounded-md border-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500" placeholder="Start Time">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.end_time" class="w-full border p-2 rounded-md border-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500" placeholder="End Time">

                    </div>    <!-- <button type="button" wire:click="removeCustomSlot({{ $slotIndex }})" class="text-red-500">Remove Slot</button> -->
                    @foreach($slot['day_interval'] as $Index => $interval)
                    <div class="mt-3 flex gap-4">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.day_interval.{{$Index}}.start_time" class="w-full border p-2 rounded-md border-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500" placeholder="Start Time">
                    <input type="time" onclick="this.showPicker()" wire:model="customSlots.{{ $slotIndex }}.day_interval.{{$Index}}.end_time" class="w-full border p-2 rounded-md border-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500" placeholder="End Time">
                    <button type="button" wire:click="removeCustomSlot({{ $slotIndex }},{{ $Index }})" class="px-2 py-1 bg-error-500 text-white rounded hover:bg-error-600">  ✖</button>
                    </div>
                    @endforeach
                    <div class="flex mt-3 gap-2">
                        <button type="button" wire:click="addCustomSlot({{ $slotIndex }})" style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);" class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">+ {{ __('setting.Add Time Slot') }}</button>
                        <button type="button" wire:click="deleteCustomSlot({{ $slotIndex }})" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg text-sm px-2.5 py-1.5">x {{ __('setting.Remove') }}</button>
                    </div>
                </div>
                    @endforeach
                    <div>
                    <button type="button" wire:click="addNextCustomSlot()" style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-sm fi-btn-size-sm gap-1 px-2.5 py-1.5 text-sm inline-grid shadow-sm bg-custom-600 text-black hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 dark:bg-white/[0.03] dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500">+ {{ __('setting.Add Next Date Slot') }}</button>
                    </div>
                </div>

            <!-- Button to Add Time Slot -->
        </div>

        </div>
</div>


            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="save" class="flex items-center justify-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                     {{ __('setting.Save') }}
                </button>
                <button wire:click="showCloseModal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-950 hover:text-white">
                     {{ __('setting.Cancel') }}
                </button>
            </div>
        </div>
    </div>
      </div>
@endif
   </div>
   @elseif($availabilitySection)
       <div>
       <div class="inside-page-heading flex items-center gap-4">
        <button type="button" wire:click="showPage('mainpage')"> <svg class="fi-icon-btn-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
</svg> </button>
       <h3 class="fi-section-header-heading text-xl font-semibold dark:text-white">Bookings</h3>
      </div>
       @livewire('App\Livewire\BookingAvailabilityComponent',['teamId' => $teamId, 'locationId' => $locationId])
       </div>
   @elseif($schedulingWindow)
       <div>
       <div class="inside-page-heading flex items-center gap-4">
        <button type="button" wire:click="showPage('mainpage')"> <svg class="fi-icon-btn-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
</svg> </button>
       <h3 class="fi-section-header-heading text-xl font-semibold  dark:text-white">Scheduling Window</h3>
      </div>
       @livewire('App\Livewire\BookingSchedulingWindowComponent',['teamId' => $teamId, 'locationId' => $locationId])
       </div>
   @elseif($geofence)
       {{-- <div>
       <div class="inside-page-heading flex items-center gap-4">
        <button type="button" wire:click="showPage('mainpage')"> <svg class="fi-icon-btn-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
</svg> </button>
       <h3 class="fi-section-header-heading text-xl font-semibold  dark:text-white">Geofence</h3>
      </div>
       @livewire('App\Livewire\GeofenceComponent',['teamId' => $teamId, 'locationId' => $locationId,'slotType'=>$type])
       </div> --}}
   @elseif($maxBooking)
      {{-- <div>
       <div class="inside-page-heading flex items-center gap-4">
        <button type="button" wire:click="showPage('mainpage')"> <svg class="fi-icon-btn-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
</svg> </button>
       <h3 class="fi-section-header-heading text-xl font-semibold dark:text-white">Max bookings per customer</h3>
      </div>
           @livewire('App\Livewire\MaxBookingPerCustomerComponent',['teamId' => $teamId, 'locationId' => $locationId])
       </div> --}}
   @endif
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script>
document.addEventListener('livewire:init', () => {
 Livewire.on('saved', (response) => {
        Swal.fire({
            title: "Saved Successfully",
            text: response.message,
            icon: "success",
            allowOutsideClick: false,
        }).then(() => {
            window.location.reload();
        });
    });
Livewire.on('update', () => {

        Swal.fire({
            title: "Updated",
            text: 'Updated Booking Status',
            icon: "success",
            allowOutsideClick: false,
        }).then((result) => {
            //window.location.reload();
        });
    });
    });
   </script>
</div>
