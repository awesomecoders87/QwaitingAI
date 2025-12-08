<div class="mt-4">
<div class="grid max-w-screen-md p-6 md:p-6 rounded-lg shadow bg-white dark:bg-white/[0.03] dark:border-gray-700">

    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Slot size') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.e.g. 15 min slots will show open slots at 8:00, 8:15, 8:30 etc') }}</p>

            </div>
            <div>
                <select class="w-full border p-2 rounded-md text-gray-600  dark:bg-gray-800 dark:border-gray-600 dark:text-white" wire:model="slotPeriod">
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
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Spots per slot') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.How many customers can book each slot') }}</p>

            </div>
            <div>
                <input class="w-full border dark:border-gray-600 p-2 fi-input block py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0 ps-3 pe-3  dark:bg-gray-800 dark:border-gray-600 dark:text-white" type="number" wire:model="requestPerSlot">
            </div>
        </div>
    </div>
    <div class="py-6 border-b border-gray-300 dark:border-gray-600">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Enable time slot for booking') }}</h3>

            </div>
            <div>
            <div>

                <select wire:model="choose_time_slot" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:text-white/90 dark:placeholder:text-white/30   dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    <option value="">{{ __('setting.Select Option') }}</option>
                    @foreach($enableTimeSlots as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>

            </div>
            </div>
        </div>
    </div>
    <div class="py-6">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Customer login for booking') }}</h3>

            </div>
            <div>
            <div>

                 <label class="flex items-center space-x-2">
                <input type="checkbox" wire:model.defer="is_customer_login" class="rounded-md" {{ $is_customer_login == "1" ? "checked" : "" }}>
                <span>{{ __('setting.Enable/Disable Customer Login') }}</span>
            </label>

            </div>
            </div>
        </div>
    </div>
    <div class="py-6">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Prefer Time Button for booking') }}</h3>

            </div>
            <div>
            <div>

                 <label class="flex items-center space-x-2">
                <input type="checkbox" wire:model.defer="is_prefer_time_slot" class="rounded-md" {{ $is_prefer_time_slot == "1" ? "checked" : "" }}>
                <span>{{ __('setting.Enable/Disable Prefer Time Slot') }}</span>
            </label>

            </div>
            </div>
        </div>
    </div>
    <div class="py-6">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Assign staff according to service') }}</h3>

            </div>
            <div>
            <div>

                 <label class="flex items-center space-x-2">
                <input type="checkbox" wire:model.defer="assignedStaffId" class="rounded-md" {{ $assignedStaffId == "1" ? "checked" : "" }}>
                <span>{{ __('setting.Enable/Disable Staff Assignment by Service') }}</span>
            </label>

            </div>
            </div>
        </div>
    </div>

    <button class="flex items-center justify-center w-[100px] px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" wire:click="saveAvailability">

        <span class="fi-btn-label">
           {{ __('setting.Save') }}
        </span>
        </button>
</div>

<div x-data="{ message: '', show: false }"
     @notify.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 10000);"
     x-show="show"
     x-transition
     class="fixed top-5 right-5 bg-green-100 text-green-700 px-4 py-2 rounded-md shadow-lg">
    <span x-text="message"></span>
</div>
</div>
