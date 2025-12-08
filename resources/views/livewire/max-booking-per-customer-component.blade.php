<div class="mt-4">

<div class="grid max-w-screen-md p-6 md:p-6 rounded-lg shadow bg-white">

    <div class="py-6 border-b border-gray-300">
        <div class="grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Booking limit ') }}*</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.How many simultaneous bookings your customer can have in a given time period.') }}</p>
            </div>
            <div>
                <input type="number" wire:model="calendarPaxRange">
            </div>
        </div>
    </div>

    <div class="py-6 border-b border-gray-300">
        <div class="grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Time period ') }}*</h3>
            <!-- <p class="text-gray-600 dark:text-white text-xs">The allowed distance from your location or coordinates.</p> -->
            </div>
            <div class="flex">
                <select class="w-full border p-2 rounded-md text-gray-600" wire:model="calendarPaxRangePeriod">
                    @foreach($slots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <button class="flex items-center justify-center w-[100px] px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" wire:click="saveSetting">
        <span class="fi-btn-label">
            {{ __('setting.Save') }}
        </span>
    </button>

</div>

</div>
