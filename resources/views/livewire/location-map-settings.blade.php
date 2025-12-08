<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('setting.Locations') }} - Google Map API Key</h2>

    @if (session()->has('success'))
        <div class="mb-4 text-sm text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded bg-white shadow p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <form wire:submit.prevent="save">
            <div class="mb-4">
                <label class="block text-gray-700">Google Map API Key</label>
                <input type="text" wire:model.defer="google_map_key" class="w-full p-2 border rounded">
                @error('google_map_key') <span class="text-error-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="mt-4 px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">
                {{ __('setting.Save') }}
            </button>
        </form>
    </div>
</div>
