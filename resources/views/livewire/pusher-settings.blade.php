<div class="p-4 dark:bg-gray-900 bg-transparent">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Pusher Settings') }}</h2>
    <div class="rounded-lg shadow border p-4 border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <form wire:submit.prevent="save">

            <div class="mt-6 grid grid-cols-2 gap-4">
                <!-- Custom Labels -->
                @foreach([
                    'key' => __('setting.Pusher App Key'),
                    'secret' => __('setting.Pusher Secret'),
                    'app_id' => __('setting.Pusher APP ID'),
                    'options_cluster' => __('setting.Pusher Option Cluster'),
                ] as $field => $label)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <input type="text" wire:model.defer="data.{{ $field }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 focus:ring focus:ring-indigo-200">
                     @error("data.$field")
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2">{{ __('setting.Save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.Livewire.on('pusher-settings-updated', count => {
            Swal.fire({
                title: "Success!",
                text: "Pusher Settings Updated!",
                icon: "success"
            });
        });
    });
</script>
