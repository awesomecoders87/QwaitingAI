<div class="p-4">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="flex justify-between">
        <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('text.Add Counter') }}</h2>
    </div>

    <div class="p-4 md:p-5 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
        <div class="max-w-full">

            <form wire:submit.prevent="save">

                <label class="block">{{ __('text.name') }}</label>
                <input type="text" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" wire:model.defer="name">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <div>
                    <label class="block mt-2">{{ __('text.select locations') }}</label>
                    <div wire:ignore>
                    <select wire:model="counter_locations" id="locations-select" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 dark:bg-gray-900 dark:border-gray-700 dark:text-white" multiple="multiple">
                       @if(!empty($allLocations))
                        @foreach ($allLocations as $location)
                        <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                        @endforeach
                        @endif
                    </select>
</div>
                    @error('counter_locations') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <label class="block mt-4 flex items-center">
                    <input type="checkbox" wire:model.defer="show_on_display" class="mr-2">
                    {{ __('text.Active/Inactive') }}
                </label>

                <div class="mt-4 flex justify-end space-x-2">
                    <a href="{{ url('counters') }}" class="bg-red-500 text-white px-3 py-2 rounded-md flex gap-x-2 hover:bg-red-600">{{ __('text.Cancel') }}</a>
                    <button class="px-3 py-2 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2" type="submit">{{ __('text.Save') }}</button>
                </div>

            </form>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        document.addEventListener("DOMContentLoaded", function() {
            $(document).ready(function() {
                $('#locations-select').select2();
                $('#locations-select').on("change", function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('counter_locations', data);
                });
            });

             Livewire.on('created', () => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Location Created successfully.',
                    icon: 'success',
                    // confirmButtonText: 'OK'
                }).then((result) => {
                    // if (result.isConfirmed) {
                        window.location.href = '/counters'; // Refresh the page when OK is clicked
                    // }
                });
    });
        });
    </script>


</div>

