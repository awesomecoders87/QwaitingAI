<div class="max-w-(--breakpoint-2xl) p-4 md:p-4">
    <style>
        div.profile-location select#selectLocation{
            width: 100%;
        }
    </style>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-xl font-semibold dark:text-white/90">{{ __('text.Profile') }}</h2>
    </div>
<div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-md">
<div class="space-y-6 border-t border-gray-100 p-5 sm:p-6 dark:border-gray-400">
    <form wire:submit.prevent="updateProfile">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label>{{ __('text.name') }}</label>
                <input type="text" wire:model.defer="name" class="w-full p-2 px-3 border rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>{{ __('text.Email') }}</label>
                <input type="email" wire:model.defer="email" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>{{ __('text.contact') }}</label>
                <input type="text" wire:model.defer="phone" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('phone') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
               <div>
            <label>{{ __('text.Select Role') }}</label>
                <input
                    type="text"
                    value="{{ Auth::user()->getRoleNames()->implode(', ') }}"
                    class="w-full p-2 px-3 border rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    readonly
                    disabled
                />
            </div>
            <div>
                <label>{{ __('text.Date Format') }}</label>
                <select wire:model.defer="date_format" class="w-full p-2 px-3 border rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @foreach($dateFormats as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('date_format') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
          @if(Auth::check() && Auth::user()->hasRole('Admin'))
            <div>
                <label>{{ __('text.address') }}</label>
                <input type="text" wire:model.defer="address" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('address') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>{{ __('text.Language') }}</label>
                <select wire:model.defer="language" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">{{ __('text.Select Language') }}</option>
                    @foreach($languages as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('language') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>{{ __('text.country') }}</label>
                <select wire:model.defer="country" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @foreach($countries as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('country') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            @if(Auth::check() && Auth::user()->hasRole('Admin'))
             <div>
                <label>{{ __('text.Enable location page') }}</label>
                <select wire:model.defer="enable_location_page" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @foreach($enable_location_page_option as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('enable_location_page') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
             <div>
                <label>{{ __('text.Enable Active Users List') }}</label>
                <select wire:model.defer="enable_active_users_list" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    @foreach($enable_location_page_option as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('enable_active_users_list') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>
            @else
            <div class="profile-location">
                 <label>{{ __('text.select locations') }}</label>
         <livewire:location-selector />
        </div>
            @endif



            {{-- <div>
                <label>Timezone</label>
                <input type="text" wire:model.defer="timezone" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                @error('timezone') <span class="text-red-500">{{ $message }}</span> @enderror
            </div> --}}

            <div>
                <label class="text-class aksh">{{ __('text.SMS Reminder (Before Queue)') }}</label>
                <input type="number" wire:model.defer="sms_reminder_queue" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" min="1" max="100" />
                @error('sms_reminder_queue') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

            
            <div>
            <label>{{ __('text.Locations') }}</label>
            <div class="mt-2" wire:ignore>
                <select wire:model.defer="locations" class="w-full p-2 px-3 border  rounded-md border-gray-300 mt-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white" multiple="multiple" id="locations-select">
                    @foreach($locationsList as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
                @error('locations') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>
        </div>
        <div class="pt-4">
        <button type="submit" class="px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">{{ __('text.Save') }}</button>
        </div>
        @endif
    </form>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     document.addEventListener("DOMContentLoaded", function () {

        $(document).ready(function() {
    $('#locations-select').select2();
    $('#locations-select').on("change", function (e) {
        let data = $(this).val();
        @this.set('locations', data);
     });
});

    Livewire.on('profileUpdated', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Your profile has been updated successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload(); // Refresh the page when OK is clicked
            }
        });
    });
    });
</script>

</div>



