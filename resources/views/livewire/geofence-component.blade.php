<div class="mt-4">
<div class="grid max-w-screen-md rounded-lg shadow p-4 bg-white">

    <div class="py-6 border-gray-300">
        <div class="flex lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4">
            <div class="flex-1">
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Enable geofence') }}</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.Allow registration only if customer is within a certain distance of the location') }}</p>
            
            </div>
            <div>
                <input type="checkbox" wire:model="geofence" <?php echo $geofence ? 'checked' : '' ?> >
            </div>
        </div>
    </div>

    {{-- <div class="py-6 border-gray-300">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Coordinates ') }} *</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.Override the location address coordinates and set your own.') }}</p>
            
            </div>
            <div class="flex gap-3">
                <input type="number" class="w-full" wire:model="geofenceLatitude" readonly>
                <input type="number" class="w-full" wire:model="geofenceLongitude" readonly>
            </div>
        </div>
    </div> --}}
    <div class="py-6 border-b border-gray-300">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-3 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Max distance from location') }}*</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.The allowed distance from your location or coordinates.') }}</p>
            
            </div>
            <div class="flex gap-3">
                <input type="number" wire:model="geofenceMaxDistance" class="w-full">
                <select class="w-full border p-2 rounded-md text-gray-600" wire:model="geofenceMaxDistanceUnit">
                    @foreach($slots as $key=>$value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <button class="mt-4 flex items-center justify-center w-[100px] px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" wire:click="saveSetting">
    
        <span class="fi-btn-label">
        {{ __('setting.Save') }}
        </span>
    </button>
</div>



</div>
