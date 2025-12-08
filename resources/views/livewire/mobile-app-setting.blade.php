<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Mobile Api Settings') }}</h2>
    <div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <form wire:submit.prevent="updateSetting" class="space-y-4">

            <div>
                <label for="rateLimitSec" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Requests per Second') }}</label>
                <input type="number" wire:model="rateLimitSec" id="rateLimitSec" min="1"
                    placeholder="{{ __('setting.e.g., 5') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @error('rateLimitSec') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="rateLimitMinute" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Requests per Minute') }}</label>
                <input type="number" wire:model="rateLimitMinute" id="rateLimitMinute" min="1"
                    placeholder="{{ __('setting.e.g., 100') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @error('rateLimitMinute') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="rateLimitDay" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Requests per Day') }}</label>
                <input type="number" wire:model="rateLimitDay" id="rateLimitDay" min="1"
                    placeholder="{{ __('setting.e.g., 1000') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @error('rateLimitDay') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- 
            <div>
                <label for="concurrencyLimit" class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('setting.Concurrency Limit') }}</label>
                <input type="number" wire:model="concurrencyLimit" id="concurrencyLimit" min="1"
                    placeholder="{{ __('setting.e.g., 10') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                @error('concurrencyLimit') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            --}}

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                {{ __('setting.Save') }}
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('saved', () => {
            Swal.fire({
                title: "{{ __('Settings Updated Successfully') }}",
                text: "{{ __('Success') }}",
                icon: "success",
            }).then((result) => {
                window.location.reload();
            });
        });
    });
</script>
