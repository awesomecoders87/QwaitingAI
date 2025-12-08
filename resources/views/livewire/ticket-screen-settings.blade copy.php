<div class="p-4">
   <div class="flex items-center gap-4 mb-4">
    <a href="{{ route('tenant.ticket-generate-settings') }}" class="flex items-center text-black-600 hover:underline">
        <!-- Heroicon: Arrow Left -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        
    </a>
        <h2 class="text-xl font-semibold mb-4">{{ __('setting.Ticket Screen Settings') }}</h2>
    </div>

    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        {{ session('success') }}
    </div>
    @endif
    <div class="bg-white shadow p-6 rounded dark:bg-white/[0.03]">
    <form wire:submit.prevent="save" class="space-y-4">
        <!-- First Row -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Service Text Font Size') }}</label>
                <select wire:model="category_text_font_size" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Option') }}</option>
                    @foreach (\App\Models\SiteDetail::getFontSize() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Ticket Font Family') }}</label>
                <select wire:model="ticket_font_family" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Option') }}</option>
                    @foreach (\App\Models\SiteDetail::getFontFamily() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Second Row -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Border Size') }}</label>
                <select wire:model="category_border_size" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Option') }}</option>
                    @foreach (\App\Models\SiteDetail::getBorderSize() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Token Number Digit') }}</label>
                <select wire:model="token_digit" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    @foreach (\App\Models\SiteDetail::getTokenDigit() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Third Row -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Token Start From') }}</label>
                <input type="number" wire:model="token_start" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Service Estimate Time') }}</label>
                <input type="number" wire:model="estimate_time" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            </div>
        </div>

        <!-- Ticket Messages -->
        <div>
            <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Ticket Message 1') }}</label>
            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{QUEUE COUNT}}</code></small>
            <textarea wire:model="ticket_text" class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Ticket Message 2') }}</label>
            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{QUEUE COUNT}} @{{Waiting Time}}</code></small>
            <textarea wire:model="ticket_text_2" class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
        </div>

        <!-- Ticket Waitlist Messages -->
        <div>
            <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Ticket Waitlist Message 1') }}</label>
            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{QUEUE COUNT}}</code></small>
            <textarea wire:model="waitlist_message_first" class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Ticket Waitlist Message 2') }}</label>
            <small class="block text-xs text-gray-500">{{ __('setting.Use Keyword') }}: <code class="font-mono bg-gray-200 px-1 py-0.5 rounded">@{{Waiting Time}}</code></small>
            <textarea wire:model="waitlist_message_second" class="w-full dark:bg-dark-900 h-30 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
        </div>

        <!-- QR Code Fields -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.QR Code Tagline') }}</label>
                <input type="text" wire:model="qrcode_tagline" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.QR Code Tagline Second') }}</label>
                <input type="text" wire:model="qrcode_tagline_second" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
            </div>

      
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Calculate Estimate Waiting Time & Queue Count By Service') }}</label>
                <select wire:model="category_estimated_time" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="1">Yes</option>
              
                    <option value="0">No</option>
                   
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.To count, select a service level. Wait Time and Number in Queue') }}</label>
                <select wire:model="category_level_est" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Option') }}</option>
                    @foreach (\App\Models\SiteDetail::getCategoryLevelEstimaion() as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Time slots enable setting -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Enable Time Slot') }}</label>
                <select wire:model="enable_time_slot" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Option') }}</option>
                    @foreach($enableTimeSlots as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Service Level For Time Slot') }}</label>
                <select wire:model="category_slot_level" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select Option') }}</option>
                    @foreach($categoryLevels as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Select TimeZone') }}</label>
                <select wire:model="select_timezone" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">{{ __('setting.Select TimeZone') }}</option>
                    @foreach($listTimeZones as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Select Country Code for Phone') }}</label>
                <select wire:model="country_code" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    <option value="">+{{ __('setting.Country Code') }}</option>
                    @foreach($countryCode as $value)
                    <option value="{{ $value }}">+{{ $value }}</option>
                    @endforeach
                </select>
              </div>
       
        </div>

     <!-- Additional Fields -->
    <div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Mobile App Heading First') }}</label>
        <input type="text" wire:model="app_heading_first" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Mobile App Heading Second') }}</label>
        <input type="text" wire:model="app_heading_second" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Name label on print') }}</label>
        <input type="text" wire:model="print_name_label" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Token label on print') }}</label>
        <input type="text" wire:model="print_token_label" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Arrived label on print') }}</label>
        <input type="text" wire:model="arrived_time_label" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Confirm Button label on print') }}</label>
        <input type="text" wire:model="confirm_btn_label" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Queue Heading First') }}</label>
        <input type="text" wire:model="queue_heading_first" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Queue Heading Second') }}</label>
        <input type="text" wire:model="queue_heading_second" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Waitlist Heading First') }}</label>
        <input type="text" wire:model="waitlist_heading" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
<div class="mb-4">
    <label class="block text-sm font-medium text-gray mb-2">Background Image</label>
    <input type="file" wire:model="background_image"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />

    @error('background_image')
        <span class="text-red-600 text-sm">{{ $message }}</span>
    @enderror

    @if ($background_image)
        <div class="relative mt-2 w-fit">
            @if (is_object($background_image))
                <img src="{{ $background_image->temporaryUrl() }}" class="h-32 rounded shadow" />
            @else
                <img src="{{ Storage::url($background_image) }}" class="h-32 rounded shadow" />
            @endif
            <button type="button" wire:click="deleteBackgroundImage"
                class="absolute top-0 right-0 bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                ✕
            </button>
        </div>
    @endif
</div>

</div>
<div class="grid grid-cols-2 gap-4">
   <div class="mb-4">
    <label class="block text-sm font-medium text-gray mb-2">Background Size</label>
    <select wire:model="background_size"
        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-blue-300">
        <option value="cover">Cover</option>
        <option value="contain">Contain</option>
        <option value="auto">Auto</option>
    </select>
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray mb-2">Background Repeat</label>
    <select wire:model="background_repeat"
        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-blue-300">
        <option value="no-repeat">No Repeat</option>
        <option value="repeat">Repeat</option>
        <option value="repeat-x">Repeat X</option>
        <option value="repeat-y">Repeat Y</option>
    </select>
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray mb-2">Background Position</label>
    <select wire:model="background_position"
        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-blue-300">
        <option value="center">Center</option>
        <option value="top">Top</option>
        <option value="bottom">Bottom</option>
        <option value="left">Left</option>
        <option value="right">Right</option>
    </select>
</div>
<div class="mb-4">
    <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Print Mode') }}</label>
    <select wire:model="print_mode"
        class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-blue-300">
        <option value="default">Default</option>
        <option value="silent">Silent</option>
    </select>
</div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Submit Button Label') }}</label>
        <input type="text" wire:model="submit_btn_text" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray mb-2">{{ __('setting.Back Button Label') }}</label>
        <input type="text" wire:model="back_btn_text" class="w-full dark:bg-dark-900 h-11 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
    </div>
</div>



<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_qr_code" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.Show QR Code') }}</label>
    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_qrcode_ticket" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.Show QR Code on Ticket Preview') }}</label>
    </div>
</div>

<!-- Checkboxes -->
<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="ticket_text_enable" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.Enable Ticket Text') }}</label>
    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="show_cat_icon" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.Show Service Images') }}</label>
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="queue_form_display" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.Queue Form Display') }}</label>
    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="late_coming_feature" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.Describing a mobile ticket feature where customers can indicate that they are running late and adjust their place in a queue accordingly') }}</label>
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    {{-- <div class="flex items-center">
        <input type="checkbox" wire:model="category_estimated_time" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Calculate Estimate Waiting Time & Queue Count By Service') }}</label>

    </div> --}}
    {{-- <div class="flex items-center">
        <input type="checkbox" wire:model="counter_estimated_time" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Calculate Estimate Waiting Time & Queue Count By Counter') }}</label>

    </div> --}}
</div>
<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="user_detail" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Display user detail on the mobile ticket view') }}</label>

    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="bottom_btn_enable" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Display Bottom mobile Tickets view  Buttons') }}</label>

    </div>

</div>
<!-- enable on print -->
<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_logo_on_print" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Show Logo on the Ticket Print') }}</label>

    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_name_on_print" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Show User name on the Ticket Print') }}</label>

    </div>

</div>
<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_arrived_on_print" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Show Arrived date and time on the Ticket Print') }}</label>

    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_location_on_print" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Show Location on the Ticket Print') }}</label>

    </div>

</div>
<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_category_on_print" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Show Service on the Ticket Print') }}</label>

    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_token_on_print" class="mr-2" value="1">
        <label class="block text-sm font-medium text-gray">{{ __('setting.Show Token on the Ticket Print') }}</label>

    </div>

</div>

<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_enable_waitlist_message" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray-700">{{ __('setting.Enable mobile ticket waitlist Text') }}</label>
    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_waitlist_table" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray-700">{{ __('setting.Show mobile Waitlist table') }}</label>
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div class="flex items-center">
        <input type="checkbox" wire:model="use_staff_priority" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.Use staff priority according to third level of staff') }}</label>
    </div>
    <div class="flex items-center">
        <input type="checkbox" wire:model="is_redirect_print_page" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.enable print page redirect on kiosk screen') }}</label>
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <!-- <div class="flex items-center">
        <input type="checkbox" wire:model="ticket_mode" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('setting.enable virtual meeting on kiosk screen') }}</label>
    </div> -->
    <div class="flex items-center">
        <input type="checkbox" wire:model="disable_print" class="mr-2" value="1">
        <label class="text-sm font-medium text-gray">{{ __('Disable print') }}</label>
    </div>
</div>


<div class="py-4">
 
    @if ($successMessage)
    <div class="rounded-xl border border-success-500 bg-success-50 p-4 dark:border-success-500/30 dark:bg-success-500/15 mt-2" id="alert">
        <div class="flex items-start gap-3">
            <div class="-mt-0.5 text-success-500">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.70186 12.0001C3.70186 7.41711 7.41711 3.70186 12.0001 3.70186C16.5831 3.70186 20.2984 7.41711 20.2984 12.0001C20.2984 16.5831 16.5831 20.2984 12.0001 20.2984C7.41711 20.2984 3.70186 16.5831 3.70186 12.0001ZM12.0001 1.90186C6.423 1.90186 1.90186 6.423 1.90186 12.0001C1.90186 17.5772 6.423 22.0984 12.0001 22.0984C17.5772 22.0984 22.0984 17.5772 22.0984 12.0001C22.0984 6.423 17.5772 1.90186 12.0001 1.90186ZM15.6197 10.7395C15.9712 10.388 15.9712 9.81819 15.6197 9.46672C15.2683 9.11525 14.6984 9.11525 14.347 9.46672L11.1894 12.6243L9.6533 11.0883C9.30183 10.7368 8.73198 10.7368 8.38051 11.0883C8.02904 11.4397 8.02904 12.0096 8.38051 12.3611L10.553 14.5335C10.7217 14.7023 10.9507 14.7971 11.1894 14.7971C11.428 14.7971 11.657 14.7023 11.8257 14.5335L15.6197 10.7395Z" fill=""></path>
                </svg>
            </div>

            <div>
                <h4 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">
                    {{ __('Settings Updated Successfully') }}
                </h4>
            </div>
        </div>
    </div>
    @endif

    <button type="submit" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('setting.Save') }}</button>
    <a href="{{ route('tenant.ticket-generate-settings') }}" class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-error-500 shadow-theme-xs hover:bg-error-600">
        {{ __('setting.Cancel') }}
    </a>
</form>
</div>
</div>

<script>
    document.addEventListener('livewire:init', function() {
        Livewire.on('hide-alert', () => {
            setTimeout(() => {
                document.getElementById('alert')?.remove();
                Livewire.emit('resetSuccessMessage'); // Reset the message in Livewire
            }, 3000);
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     document.addEventListener("DOMContentLoaded", function () {

    Livewire.on('updated', () => {
        Swal.fire({
            title: 'Success!',
            text: ' Updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload(); // Refresh the page when OK is clicked
            }
        });
    });
    Livewire.on('deleted', () => {
        Swal.fire({
            title: 'Success!',
            text: ' Deleted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            // if (result.isConfirmed) {
            //     location.reload(); // Refresh the page when OK is clicked
            // }
        });
    });
    });
</script>