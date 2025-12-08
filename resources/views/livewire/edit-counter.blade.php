<div class="p-6">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Edit Counter</h2>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6">
        @if (session()->has('message'))
            <div class="mb-4 text-green-500 text-sm font-semibold">
                {{ session('message') }}
            </div>
        @endif

        <div class="max-w-full overflow-x-auto">
        <form wire:submit.prevent="update">

            {{-- Name Input --}}
            <label class="block">{{ __('text.name') }}</label>
            <input type="text" wire:model.defer="name" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            {{-- Multi-Select with Select2 --}}
            <div class="mt-2" wire:ignore>
                <label class="block">{{ __('text.select locations') }}</label>
                <select wire:model="counter_locations" id="locations-select" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 dark:bg-gray-900 dark:border-gray-700 dark:text-white" multiple="multiple">
                    @foreach ($allLocations as $location)
                        <option value="{{ $location->id }}" {{ in_array($location->id, $counter_locations) ? 'selected' : '' }}>
                            {{ $location->location_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('counter_locations') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            {{-- Checkbox for Display --}}
            <label class="block mt-4 flex items-center">
                <input type="checkbox" wire:model.defer="show_on_display" class="mr-2">
                {{ __('text.Active/Inactive') }}
            </label>

            {{-- Buttons --}}
            <div class="mt-4 flex justify-end space-x-2">
            <a href="{{ url('counters') }}" class="p-2 text-sm font-medium text-white transition-colors rounded-lg bg-error-500 hover:bg-error-600">{{ __('text.Cancel') }}</a>
                <button class="p-2 text-sm font-medium text-white transition-colors rounded-lg bg-brand-500 hover:bg-brand-600" type="submit">{{ __('text.Update') }}</button>
            </div>

        </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
        document.addEventListener("DOMContentLoaded", function() {
            $(document).ready(function() {
                $('#locations-select').select2();
                $('#locations-select').on("change", function (e) { 
                    let data = $(this).val();
                    console.log(data);
                    // $wire.locations = data;
                    @this.set('counter_locations', data);
                });
            });
        });
    </script>
</div>


