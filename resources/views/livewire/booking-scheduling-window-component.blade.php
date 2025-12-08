<div class="mt-4">
<div class="grid max-w-screen-md p-6 md:p-6  rounded-lg shadow bg-white dark:bg-white/[0.03] dark:border-gray-700">
    <div class="grid md:grid-cols-2 gap-3">
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Min notice') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.How much advance notice you require before customers book an appointment') }}</p>
            
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="minNotice">
                    @foreach($slots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Max notice') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.How far in the future customers is allowed to book an appointment') }}</p>
            
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="maxNotice">
                    @foreach($slots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Cancel notice') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.How far in the future customers is allowed to cancel an appointment') }}</p>
            
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="cancelNotice">
                    @foreach($slots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Automatic Cancel') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.Automatically cancel bookings if the visitor does not check in within the selected time after the scheduled start time.') }}</p>
            
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="bookingAutoCancel">
                    @foreach($reminderSlots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Week Start Day') }}</h3>
            <!-- <p class="text-gray-600 dark:text-white text-xs">How far in the future customers is allowed to cancel an appointment</p> -->
            
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="weekStart">
                    @foreach($weekSlots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Requests Acceptance Mode') }}</h3>
           
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="requestMode">
                    @foreach($modeSlots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Booking Reminder') }}</h3>
           
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="bookingReminder">
                    @foreach($reminderSlots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
   {{-- <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Show Booking Category in Row') }}</h3>
           
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="showCategoryPerRow">
                    @foreach($rowSlots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div> --}}
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Booking Heading Text') }}</h3>
           
            </div>
            <div>
               <input type="text" wire:model="bookingHeadingText" class="w-full w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Convert Appointment Input Placeholder') }}</h3>
           
            </div>
            <div>
               <input type="text" wire:model="inputPlaceholder" class="w-full w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Booking Convert Label') }}</h3>
           
            </div>
            <div>
               <input type="text" wire:model="bookingConvertLabel" class="w-full w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Walk In Label') }}</h3>
           
            </div>
            <div>
               <input type="text" wire:model="walkInLabel" class="w-full w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
        </div>
    </div>
    <div class="py-6">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Appointment Label') }}</h3>
           
            </div>
            <div>
               <input type="text" wire:model="appointmentLabel" class="w-full w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
        </div>
    </div>
    <div class="py-6">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Booking Sidebar Heading') }}</h3>
           
            </div>
            <div>
               <input type="text" wire:model="bookingsidebarHeading" class="w-full w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
            </div>
        </div>
    </div>

   {{-- <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Category Time Slot') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.Enable Category Time Slot Level') }}</p>
            
            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="categoryleveltimeslot">
                    @foreach($levelSlots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div> --}}
   
    </div>

    <button class="flex items-center justify-center w-[100px] px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" wire:click="saveSetting">
        
        <span class="fi-btn-label">
           {{ __('setting.Save') }}
        </span>
        </button>
</div>

</div>
