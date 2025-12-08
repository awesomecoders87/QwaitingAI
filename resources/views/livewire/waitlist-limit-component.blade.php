<div>
<div class="grid max-w-screen-md">

    <div class="py-6">
        <div class="flex lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-6 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div class="flex-1">
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Enable limit') }}</h3>
            </div>
            <div>
                <input type="checkbox" wire:model="iswaitlistlimit" <?php echo $iswaitlistlimit ? 'checked' : '' ?> >
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="grid lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-6 mb-4" style="--cols-default: 250px 8fr; --cols-lg:  250px 8fr;">
            <div>
            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">{{ __('setting.Limit') }} *</h3>
            <p class="text-gray-600 dark:text-white text-xs">{{ __('setting.The maximum number of visits allowed on the waitlist at once') }}</p>
            
            </div>
            <div class="flex gap-3">
                <input type="number" class="w-full" wire:model="waitlistlimit">
            </div>
        </div>
    </div>
   
</div>

<button class="flex items-center justify-center px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600" wire:click="saveSetting">
    
    <span class="fi-btn-label">
       {{ __('setting.Save') }}
    </span>
    </button>

</div>
