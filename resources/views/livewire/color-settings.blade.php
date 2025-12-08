<div class="p-4">

{{-- Desktop Queue Screen --}}
<h2 class="text-xl font-semibold dark:text-white mb-4">{{ __('setting.Desktop Queue Screen') }}</h2>
<div class="bg-white dark:bg-gray-900 shadow rounded p-4">
    <div class="grid grid-cols-2 gap-4">
        @foreach ([
        'page_layout' => __('setting.Page Layout'),
        'categories_background_layout' => __('setting.Services Background Layout'),
        'text_layout' => __('setting.Text Layout'),
        'hover_background_layout' => __('setting.Hover Background Layout'),
        'hover_text_layout' => __('setting.Hover Text Layout'),
        'buttons_layout' => __('setting.Buttons Layout'),
        'hover_button_layout' => __('setting.Hover Button Layout')
        ] as $field => $label)
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ $label }}</label>
            <div class="flex items-center space-x-2">
                <input type="color" wire:model="{{ $field }}" class="w-10 h-10 border rounded-md cursor-pointer" />
                <input type="text" wire:model="{{ $field }}" class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-800 dark:text-white" />
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Main Theme Setting --}}
<h2 class="text-xl font-semibold dark:text-white mb-4 p-4">{{ __('setting.Main Theme Settings') }}</h2>
<div class="bg-white dark:bg-gray-900 shadow rounded p-4">
    <div class="grid grid-cols-2 gap-4">
        @foreach ([
        'theme_color' => __('setting.Theme color'),
        'font_color' => __('setting.Font Color'),
        'button_color' => __('setting.Button Color'),

        ] as $field => $label)
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ $label }}</label>
            <div class="flex items-center space-x-2">
                <input type="color" wire:model="{{ $field }}" class="w-10 h-10 border rounded-md cursor-pointer" />
                <input type="text" wire:model="{{ $field }}" class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-800 dark:text-white" />
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Mobile Queue Screen --}}
<h2 class="text-xl font-semibold dark:text-white mt-6 mb-4">{{ __('setting.Mobile Queue Screen') }}</h2>

<div class="bg-white dark:bg-gray-900 shadow rounded p-4">
    <div class="grid grid-cols-2 gap-4 mb-3">
        @foreach ([
        'mobile_page_layout' => __('setting.Mobile Page Layout'),
        'mobile_header_background_color' => __('setting.Mobile Header Background Color'),
        'mobile_heading_text_color' => __('setting.Mobile Heading Text Color'),
        'mobile_category_button_color' => __('setting.Mobile Service Button Color'),
        'mobile_button_text_color' => __('setting.Mobile Button Text Color'),
        'mobile_button_color' => __('setting.Mobile Button Color')
        ] as $field => $label)
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ $label }}</label>
            <div class="flex items-center space-x-2">
                <input type="color" wire:model="{{ $field }}" class="w-10 h-10 border rounded-md cursor-pointer" />
                <input type="text" wire:model="{{ $field }}" class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-800 dark:text-white" />
            </div>
        </div>
        @endforeach
    </div>

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
                    {{ __('setting.Settings Updated Successfully') }}
                </h4>
            </div>
        </div>
    </div>
    @endif


</div>

<h2 class="text-xl font-semibold dark:text-white mt-6 mb-4">{{ __('setting.Call Screen') }}</h2>

<div class="bg-white dark:bg-gray-900 shadow rounded p-4">
    <div class="grid grid-cols-2 gap-4 mb-3">
        @foreach ([
        'total_served_queue_color' => __('setting.Total Served Queue Color'),
        'total_served_queue_text_color' => __('setting.Total Served Queue Text Color'),
        'transfer_queue_color' => __('setting.Transfer Queue Color'),
        'transfer_queue_text_color' => __('setting.Transfer Queue Text Color'),
        'hold_queue_color' => __('setting.Hold Queue Color'),
        'hold_queue_text_color' => __('setting.Hold Queue Text Color'),
        'missed_queue_color' => __('setting.Missed Queue Color'),
        'missed_queue_text_color' => __('setting.Missed Queue Text Color')
        ] as $field => $label)
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ $label }}</label>
            <div class="flex items-center space-x-2">
                <input type="color" wire:model="{{ $field }}" class="w-10 h-10 border rounded-md cursor-pointer" />
                <input type="text" wire:model="{{ $field }}" class="w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-800 dark:text-white" />
            </div>
        </div>
        @endforeach
    </div>

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
                    {{ __('setting.Settings Updated Successfully') }}
                </h4>
            </div>
        </div>
    </div>
    @endif


</div>

 <button wire:click="save" class="px-4 mt-3 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 mt-2">
        {{ __('setting.Save') }}
    </button>
</div>


<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('hide-alert', () => {
            setTimeout(() => {
                document.getElementById('alert')?.remove();
                Livewire.emit('resetSuccessMessage'); // Reset the message in Livewire
            }, 3000);
        });
    });
</script>
