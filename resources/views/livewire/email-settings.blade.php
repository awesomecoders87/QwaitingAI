<div class="p-4">
    <h2 class="text-xl font-semibold text-gray-700 dark:text-white  mb-4">{{ __('setting.Email Settings') }}</h2>
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4">
    <form wire:submit.prevent="save" class="space-y-4">
        
        <div class="-mx-2.5 flex flex-wrap gap-y-5">
        <div class="w-full px-2.5 xl:w-1/2">
            <label for="from_name" class="block font-medium text-gray-700 dark:text-white ">{{ __('setting.From Name') }}</label>
            <input type="text" id="from_name" wire:model="from_name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
            @error('from_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label for="from_email" class="block font-medium text-gray-700 dark:text-white ">{{ __('setting.Email') }}</label>
            <input type="email" id="from_email" wire:model="from_email" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
            @error('from_email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label for="hostname" class="block font-medium text-gray-700 dark:text-white ">{{ __('setting.Hostname') }}</label>
            <input type="text" id="hostname" wire:model="hostname" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
            @error('hostname') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label for="port" class="block font-medium text-gray-700 dark:text-white ">{{ __('setting.Port') }}</label>
            <input type="number" id="port" wire:model="port" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
            @error('port') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label for="username" class="block font-medium text-gray-700 dark:text-white ">{{ __('setting.Username') }}</label>
            <input type="text" id="username" wire:model="username" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
            @error('username') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label for="password" class="block font-medium text-gray-700 dark:text-white ">{{ __('setting.Password') }}</label>
            <div class="relative">
            <input type="password" id="password" wire:model="password" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
            <button type="button" class="absolute inset-y-0 top_6 right-3 flex items-center text-gray-500 dark:text-gray-400"
                onclick="togglePassword('password', 'eyeOpen_current', 'eyeClosed_current')">
                <svg id="eyeOpen_current" class="fill-gray-500 dark:fill-gray-400 hidden" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M10 13.86c-2.77 0-5.14-1.73-6.08-4.16C4.86 7.27 7.23 5.54 10 5.54s5.14 1.73 6.08 4.16c-.94 2.43-3.31 4.16-6.08 4.16zM10 4.04c-3.52 0-6.5 2.27-7.58 5.42-.06.16-.06.33 0 .49 1.08 3.15 4.06 5.42 7.58 5.42s6.5-2.27 7.58-5.42c.06-.16.06-.33 0-.49C16.5 6.31 13.52 4.04 10 4.04zm-.01 3.8c-1.03 0-1.86.83-1.86 1.86s.83 1.86 1.86 1.86h.01c1.03 0 1.86-.83 1.86-1.86s-.83-1.86-1.86-1.86h-.01z">
                    </path>
                </svg>

                <svg id="eyeClosed_current" class="fill-gray-500 dark:fill-gray-400" width="20" height="20"
                    viewBox="0 0 20 20">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M4.64 3.58a.85.85 0 00-1.06 1.06l1.28 1.28C3.75 6.84 2.89 8.06 2.42 9.46a.84.84 0 000 .49c1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.45-.29 3.5-.84l1.86 1.86a.85.85 0 001.06-1.06L4.64 3.58zM12.36 13.42L10.45 11.5a2 2 0 01-1.31.06L5.92 6.98c-.88.71-1.58 1.65-2 2.72 1.08 3.15 4.06 5.42 7.58 5.42 1.26 0 2.46-.29 3.5-.84L12.36 13.42zm3.71-3.71c-.3.75-.74 1.44-1.28 2.05l1.06 1.06a7.66 7.66 0 002.7-3.78c.06-.16.06-.33 0-.49-1.08-3.15-4.06-5.42-7.58-5.42-1.26 0-2.46.14-3.5.43l1.23 1.23c.41-.09.84-.14 1.27-.14 2.77 0 5.14 1.73 6.08 4.16z">
                    </path>
                </svg>
            </button>
        </div>
            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="w-full px-2.5 xl:w-1/2">
            <label for="encryption" class="block font-medium text-gray-700 dark:text-white ">{{ __('setting.Encryption') }}</label>
            <input type="text" id="encryption" wire:model="encryption" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 dark:focus:border-brand-800">
            @error('encryption') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
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
                            {{ __('Settings Updated Successfully') }}
                        </h4>
                    </div>
                </div>
            </div>
        @endif

        <button type="submit" class="px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('setting.Save') }}</button>
 
    </form>

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
</div>
</div>
