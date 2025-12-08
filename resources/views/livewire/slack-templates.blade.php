<div class="p-4">

    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ __('setting.Templates') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Template Selection -->
        <div class="bg-gray-100 dark:bg-white/[0.03] rounded-md shadow p-4">
            <h3 class="text-lg font-bold mb-2">{{ __('setting.Select Template') }}</h3>
            <div class="space-y-2">
                @foreach ($templates as $key => $template)
                <label class="flex items-center cursor-pointer">
                    <input type="radio" wire:model.live="selectedTemplate" value="{{ $key }}"
                        class="text-blue-600 focus:ring focus:ring-blue-300 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <span class="ml-2 text-gray-900 dark:text-gray-200">{{ __('text.'.$template['name']) }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Template Editor -->
        <div class="bg-gray-100  dark:bg-white/[0.03] rounded-md shadow p-4">
           <h3 class="text-lg font-bold mb-2">{{ __('text.'.$templates[$selectedTemplate]['name']) }}</h3>
         
    
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">{{ __('setting.Message') }}</label>
                <textarea wire:model="body" rows="6"
                    class="w-full p-2 border rounded-md bg-white dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 focus:ring focus:ring-blue-300">
                </textarea>
            </div>

            <!-- Enable/Disable Field -->
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">{{ __('setting.Enable/Disable') }}</label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" wire:model="status" class="text-blue-600 focus:ring focus:ring-blue-300">
                    <span class="text-gray-900 dark:text-gray-200">{{ $status ? __('setting.Enabled') : __('setting.Disabled') }}</span>
                </label>
            </div>

            <!-- Select Variable -->
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">{{ __('setting.Select Variable') }}</label>
                <select wire:model="selectedVariable"
                    class="w-full p-2 border rounded-md bg-white dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 focus:ring focus:ring-blue-300">
                    <option value="">{{ __('setting.Select Variable') }}</option>
                    @foreach ($variables as $key => $desc)
                    <option value="{{ $key }}">{{ $desc }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Append Variable Button -->
            <button wire:click="appendVariableToBody"
                class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                {{ __('setting.Append Variable') }}
            </button>

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

            <!-- Save Button -->
            <div class="mt-4 flex justify-end">
                <button wire:click="saveTemplate"
                    class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('setting.Save') }}
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('hide-alert', () => {
            setTimeout(() => {
                document.getElementById('alert')?.remove();
                Livewire.emit('resetSuccessMessage');
            }, 3000);
        });
    });
</script>