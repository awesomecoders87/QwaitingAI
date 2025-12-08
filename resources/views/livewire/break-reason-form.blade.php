<div class="p-4">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-semibold">{{ $breakReasonId ? __('text.Edit Break Reason') : __('text.Add Break Reason') }}</h2>
    </div>

    <div class="bg-white  dark:bg-white/[0.03] shadow-md rounded-lg p-6">
        <div class="max-w-full overflow-x-auto">
            <form wire:submit.prevent="save">

                {{-- Reason --}}
                <label class="block mb-1 text-sm font-medium">{{ __('text.reason') }}</label>
                <input type="text"
                       wire:model.defer="reason"
                       maxlength="255"
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10  h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:text-white/90 dark:placeholder:text-white/30  dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                @error('reason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                {{-- Break Time --}}
                <label class="block mt-4 mb-1 text-sm font-medium">{{ __('text.Break Time') }}</label>
                <select wire:model.defer="break_time"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800  dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    <option value="">-- Select --</option>
                    <option value="10">10 {{ __('text.minutes') }}</option>
                    <option value="15">15 {{ __('text.minutes') }}</option>
                    <option value="30">30 {{ __('text.minutes') }}</option>
                    <option value="60">1 {{ __('text.hour') }}</option>
                </select>
                @error('break_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                {{-- Is Approved --}}
                <label class="block mt-4 mb-1 text-sm font-medium">{{ __('text.Is Auto Approved Request') }}</label>
                <div class="flex gap-6 mt-1">
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model.defer="is_approved" value="1" class="form-radio text-brand-600 mr-2">
                        <span class="text-sm text-gray-700 dark:text-white">{{ __('text.yes') }}</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model.defer="is_approved" value="0" class="form-radio text-brand-600 mr-2">
                        <span class="text-sm text-gray-700 dark:text-white">{{ __('text.no') }}</span>
                    </label>
                </div>
                @error('is_approved') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                {{-- Break Locations --}}
                <label class="block mt-4 mb-1 text-sm font-medium">{{ __('text.select locations') }}</label>
                <div wire:ignore>
                    <select wire:model="break_locations"
                            id="locations-select"
                            multiple="multiple"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2  dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        @foreach ($allLocations as $location)
                            <option value="{{ $location->id }}" {{ in_array($location->id, $break_locations) ? 'selected' : '' }}>{{ $location->location_name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('break_locations') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                {{-- Buttons --}}
                <div class="mt-6 flex justify-end space-x-2">
                    <a href="{{ url('/break-reason') }}"
                       class="px-3 py-2 font-medium text-white transition-colors rounded-lg bg-error-500 hover:bg-error-600">Cancel</a>

                    <button type="submit"
                            class="px-3 py-2 font-medium text-white transition-colors rounded-lg bg-brand-500 hover:bg-brand-600">
                        {{ $breakReasonId ? __('text.Update') : __('text.Create') }}
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            $('#locations-select').select2();
            $('#locations-select').on("change", function (e) {
                @this.set('break_locations', $(this).val());
            });
        });
    </script>

</div>
