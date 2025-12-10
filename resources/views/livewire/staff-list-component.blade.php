<div class="p-4">
    <div>

            @if (session()->has('message'))
            <div class="mb-4">
            <div class="alert alert-success">{{ session('message') }}</div>
            </div>
            @endif


        <div class="mb-4 flex justify-between mb-4">
            <div>
        <h2 class="text-xl font-semibold dark:text-white/90">
            {{ __('text.staff') }}
        </h2>
    </div>

111111111
        </div>


        <div class="mb-4 flex justify-between mb-4 gap-3 flex-wrap">

            <div class="relative w-full lg:w-[300px]">
                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/4">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z"
                            fill="" />
                    </svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('text.Search') }}..."
                    class="bg-white dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>
            <div class="flex items-center gap-x-2">
            @can('Staff Delete')
            <button id="bulkDeleteBtn"  class="danger-btn bg-red-500 text-white px-3 py-2 rounded-md flex gap-x-2 hover:bg-red-600 flex gap-x-2"><i class="ri-delete-bin-line"></i> {{ __('text.Bulk Delete') }}</button>
            @endcan


                @can('Staff Add')
                    <a href="{{ route('tenant.staff.create') }}"
                        class="primary-btn py-2 px-3 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2">
                        <i class="ri-add-circle-line"></i> {{ __('text.Add') }}
                    </a>
                @endcan

                <a href="{{ url('roles') }}" class="primary-btn text-white px-3 py-2 rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2"><i class="ri-user-add-line"></i> {{ __('text.Roles') }}</a>
            </div>

        </div>

        <div
            class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
            <div class="max-w-full overflow-x-auto">
                <table class="min-w-full table-auto">
                    <!-- table header start -->
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-200 text-gray-800 dark:text-white">
                        <th class="px-5 py-3 sm:px-6">
                                    <input type="checkbox" id="selectAll" class="cursor-pointer rounded bg-white rounded border border-gray-400 dark:border-gray-500 dark:bg-gray-700">
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                       {{ __('text.name') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                         {{ __('text.Username') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                        {{ __('text.contact') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                        {{ __('text.Email') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                        {{ __('text.role') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                        {{ __('text.location') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                        {{ __('text.created at') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                        {{ __('text.Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <!-- table header end -->
                    <!-- table body start -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @if($staffs->count() > 0)
                        @foreach($staffs as $staff)
                        <tr>
                        <td class="px-5 py-4 sm:px-6">
                        <input type="checkbox" class="staff-checkbox cursor-pointer bg-white rounded border border-gray-400 dark:border-gray-500 dark:bg-gray-700" value="{{ $staff->id }}">
                    </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $staff->name ?? ''}}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $staff->username ?? '' }}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $staff->phone ?? '' }}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $staff->email ?? '' }}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $staff->roles->first()->name ?? '' }}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                @php
												$locations = [];
												if (!empty($staff->locations)) {
													foreach ($staff->locations as $locationId) {
														if (isset($allLocations[$locationId])) {
															$locations[] = $allLocations[$locationId];
														}
													}
												}
												echo implode(', ', $locations);
											@endphp

                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                               
											{{ \Carbon\Carbon::parse($staff->created_at)->format($datetimeFormat) }}
                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 whitespace-nowrap">

                                <div class="flex items-center justify-center">
                                    <div x-data="{openDropDown: false}" class="relative">
                                        <button @click="openDropDown = !openDropDown"
                                            class="text-gray-500 dark:text-gray-400 action-btn">
                                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z"
                                                    fill="" />
                                            </svg>
                                        </button>
                                        <div x-show="openDropDown" @click.outside="openDropDown = false"
                                            class="dropdown-menu shadow-theme-lg dark:bg-gray-dark fixed top-full right-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                                            <a href="{{ route('tenant.staff.setting', ['staffId' => $staff->id]) }}">
                                                <button
                                                    class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                    {{ __('text.Setting') }}
                                                </button>

                                            </a>
                                             @can('Staff Password Change')
                                            <button wire:click="openPasswordModal({{ $staff->id }})"
                                                class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                 {{ __('text.Change Password') }}
                                            </button>
                                            @endcan
                                            @can('Staff Edit')
                                            <a href="{{ route('tenant.staff.edit', ['staffId' => $staff->id]) }}">
                                                <button
                                                    class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                    {{ __('text.Edit') }}
                                                </button>
                                            </a>
                                            @endcan
                                            @can('Staff Delete')
                                            <button wire:click="deleteStaff({{ $staff->id }})"
                                                class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                {{ __('text.Delete') }}
                                            </button>
                                            @endcan

                                        </div>
                                    </div>
                                </div>

                            </td>

                        </tr>
                        @endforeach
                        @else
                        <tr>
                           <td colspan="15" class="text-center py-3">
                              <p class="text-center"><strong>{{ __('text.No records found.') }}</strong></p>
                            </td>
                        </tr>
                        @endif

                    </tbody>
                </table>

                {{ $staffs->links() }}
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    @if ($showPasswordModal)

    <div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999" style="">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div
            class="no-scrollbar relative w-full max-w-[507px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
            <!-- close btn -->
            <button wire:click="$set('showPasswordModal', false)"
                class="transition-color absolute right-5 top-5 z-999 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:bg-gray-700 dark:bg-white/[0.05] dark:text-gray-400 dark:hover:bg-white/[0.07] dark:hover:text-gray-300">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                        fill=""></path>
                </svg>
            </button>
            <div class="px-2 pr-14">
                <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                    {{ __('text.Change Password') }}
                </h4>

            </div>
            <form class="flex flex-col">
                <div class="custom-scrollbar h-[450px] overflow-y-auto px-2">

                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-1">

                        {{-- <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Old Password
                            </label>
                            <input type="password" wire:model="oldPassword"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('oldPassword') <span class="text-danger">{{ $message }}</span> @enderror
                        </div> --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.New Password') }}
                            </label>
                            <input type="password" wire:model="newPassword"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('newPassword') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('text.Confirm Password') }}
                            </label>
                            <input type="password" wire:model="confirmPassword"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @error('confirmPassword') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                            <button wire:click="$set('showPasswordModal', false)" type="button"
                                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                                {{ __('text.Close') }}
                            </button>
                            <button type="button"
                                class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto"
                                wire:click.prevent="updatePassword">
                                {{ __('text.Save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteConfirm)
    test
    <div class="modal fade show fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999"
        tabindex="-1">
        <div class="modal-dialog relative w-full max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" wire:click="$set('showDeleteConfirm', false)">×</button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this staff?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        wire:click="$set('showDeleteConfirm', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        $(document).ready(function() {
            $('.multiple').select2();
            $('#closed_by').on("change", function(e) {
                let data = $(this).val();
                @this.set('closed_by', data);
            });

        });
    });
    </script>

<script>
    document.getElementById('selectAll').addEventListener('click', function () {
        let checkboxes = document.querySelectorAll('.staff-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    document.getElementById('bulkDeleteBtn').addEventListener('click', function () {
        let selectedIds = Array.from(document.querySelectorAll('.staff-checkbox:checked')).map(cb => cb.value);
      console.log(selectedIds);
        if (selectedIds.length > 0) {
            Livewire.dispatch('bulkDeleteStaff', { 'ids' : selectedIds });
        } else {
            Livewire.dispatch('no-record-selected');
        }
    });
</script>


</div>
