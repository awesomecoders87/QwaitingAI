<div class="p-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Templates List -->
        <div class="bg-white p-6 shadow-md rounded-lg  dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
            <h2 class="text-lg font-semibold mb-4">{{ __('text.templates') }}</h2>
            <ul class="space-y-2">
                @foreach ($templates as $key => $template)
                <li class="flex items-center space-x-2 cursor-pointer">
                    <label><input type="radio" wire:model.live="selectedTemplate" value="{{ $key }}" class="text-blue-600 focus:ring focus:ring-blue-300">
                    <span class="text-gray-900 dark:text-gray-200">{{ __('text.'.$template['name']) }}</span></label>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Notification Template Form -->
        <div class="bg-white p-6 shadow-md rounded-lg  dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
             <h3 class="text-lg font-bold mb-2">{{ __('text.'.$templates[$selectedTemplate]['name']) }}</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('text.Subject') }}</label>
                <input type="text" wire:model="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700  dark:text-white">{{ __('text.Body') }}</label>
                <textarea wire:model="body" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500  dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
            </div>

            <!-- Select Variable -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('text.Select Variable') }}</label>
                <select wire:model="selectedVariable" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-300  dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">{{ __('text.Select Variable') }}</option>
                    @foreach($variables as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Append Variable Buttons -->
            <div class="mt-4 flex space-x-2">
                <button wire:click="appendVariableToSubject" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('text.Append to Subject') }}
                </button>
                <button wire:click="appendVariableToBody" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('text.Append to Body') }}
                </button>
            </div>

            <!-- Toggle Enable/Disable -->
            <div class="mt-6 flex items-center space-x-2">
                <input type="checkbox" wire:model="templateStatus.{{ $selectedTemplate }}" class="text-blue-600 focus:ring focus:ring-blue-300" @checked($templateStatus[$selectedTemplate] == 1)>
                <span class="text-gray-700 dark:text-white">{{ $templateStatus[$selectedTemplate] == 1 ? __('text.Enabled') : __('text.Disabled') }}</span>
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
                            Settings Updated Successfully
                        </h4>
                    </div>
                </div>
            </div>
            @endif

            <!-- Save Button -->
            <div class="mt-6">
                <button wire:click="saveTemplate" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                    {{ __('text.Save') }}
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
                Livewire.emit('resetSuccessMessage'); // Reset the message in Livewire
            }, 3000);
        });
    });
</script>