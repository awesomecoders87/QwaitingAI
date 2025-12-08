<div class="p-4">
    @php use Illuminate\Support\Str; @endphp
    <h2 class="text-xl font-semibold  mb-4">{{ __('setting.Locations') }}</h2>

    <div class="mb-4">
<div class="mb-4 flex justify-between mb-4 gap-3 flex-wrap">
<div class="relative w-full lg:w-[300px]">
                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-2/4">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                    </svg>
                </span>
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('setting.Search') }}..."
        class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
</div>

 @can('Location')
        <a href="{{ route('tenant.add-location') }}" class="p-3 text-sm font-medium text-white transition-colors rounded-lg bg-brand-500 hover:bg-brand-600">
            <i class="ri-map-pin-2-line"></i> {{ __('setting.Add New Location') }}
        </a>
        @endcan
</div>

</div>

    <div class="overflow-hidden rounded-lg p-4 shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>

                    <tr class="border-b border-gray-300">
                            <th class="px-5 py-3 sm:px-6">
                                        {{ __('setting.Name') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                    {{ __('setting.Address') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                    {{ __('setting.Status') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                    {{ __('setting.City') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                    {{ __('setting.State') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                    {{ __('setting.Country') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                    {{ __('setting.ZIP Code') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                    {{ __('setting.Actions') }}
                            </th>
                </tr>
                </thead>
                <tbody class="dark:divide-gray-800">
                    @if(count($locations) > 0)
                    @foreach ($locations as $location)
                        <tr>
                            <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $location->location_name }}</td>
                            <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ str::limit($location->address,50) }}</td>
                            <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $location->status == 1 ? __('setting.Active') : __('setting.Deactive') }}</td>
                            <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $location->city }}</td>
                            <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $location->state }}</td>
                            <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $location->country }}</td>
                            <td class="px-5 py-3 sm:px-6 border-b border-gray-300">{{ $location->zip }}</td>
                            <td class="p-3 border-b border-gray-300">
                                <div x-data="{ openDropDown: false }" class="relative">
                                    <button @click="openDropDown = !openDropDown" class="text-gray-500 dark:text-gray-400 action-btn">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z"></path>
                                        </svg>
                                    </button>
                                    <div x-show="openDropDown" @click.outside="openDropDown = false" class="dropdown-menu shadow-theme-lg dark:bg-gray-dark fixed z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                                       <a href="{{ route('tenant.location.setting', $location->id) }}" class="block px-3 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                                            {{ __('setting.Settings') }}
                                        </a>
                                        <button
                                            wire:click="openCopySettingsModal({{ $location->id }})"
                                            class="w-full text-left px-3 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                                            {{ __('setting.Copy Settings') }}
                                        </button>
                                        <a href="{{ route('tenant.edit-location', $location->id) }}" class="block px-3 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                                            {{ __('setting.Edit') }}
                                        </a>
                                        <button wire:click="deleteconfirmation({{ $location->id }})" class="w-full text-left px-3 py-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">
                                            {{ __('setting.Delete') }}
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    @endforeach
                    @else
                        <tr>
                            <td colspan="12" class="text-center py-6">
                                <img src="{{ url('images/no-record.jpg') }}" alt="{{ __('setting.No Records Found here') }}"
                                    class="mx-auto" style="max-width: 300px">
                                <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('setting.No records found.') }}</p>
                            </td>
                        </tr>
                        @endif
                </tbody>
            </table>
        </div>
    </div>

    {{ $locations->links() }}


    <div x-data="{ open: @entangle('copySettingsModal') }">
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white dark:bg-gray-dark rounded-2xl shadow-lg p-6 w-full max-w-lg">

            <h2 class="text-lg font-bold mb-4">{{ __('setting.Copy Settings From Another Location') }}</h2>

            <form wire:submit.prevent="copySettings">

                <!-- Select source location -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('setting.Select Location') }}
                    </label>
                    <select wire:model="sourceLocationId" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                        <option value="">{{ __('setting.Choose Location') }}</option>
                        @foreach ($locations as $loc)
                        @if((int)$targetLocationId != (int)$loc->id)
                            <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <!-- Checkboxes -->
                <div class="mb-4 space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="copyTicketSettings" class="mr-2">
                        {{ __('setting.Ticket Screen, Call screen, Logo and  Other defaults Settings') }}
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="copyColorSettings" class="mr-2">
                        {{ __('setting.Color Settings') }}
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="copyServices" class="mr-2">
                        {{ __('setting.services') }}
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg border border-gray-300">
                        {{ __('setting.Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        {{ __('setting.Copy') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     document.addEventListener("DOMContentLoaded", function () {
    Livewire.on('confirmation-delete', () => {
        Swal.fire({
            title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('confirmed-delete');
            }
        });
    });
    Livewire.on('deleted', () => {
        Swal.fire({
            title: 'Success!',
            text: 'Deleted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload(); // Refresh the page when OK is clicked
            }
        });
    });

      Livewire.on('copy-success', () => {
            Swal.fire({
                title: 'Success!',
                text: ' Settings copied successfully.',
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

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.show-dropdown');

    buttons.forEach(button => {
      button.addEventListener('click', function (e) {
        e.stopPropagation();

        const dropdown = this.parentElement.querySelector('.dropdown-menu');

        // Hide other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
          if (menu !== dropdown) menu.style.display = 'none';
        });

        if (dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
        } else {
          dropdown.style.display = 'block'; // must show it first to get width

          const rect = this.getBoundingClientRect();
          dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
          dropdown.style.left = (rect.right + window.scrollX - dropdown.offsetWidth) + 'px';
        }
      });
    });

    document.addEventListener('click', function () {
      document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.style.display = 'none';
      });
    });
  });
</script>
