<div class="p-4">
    <div>

        @if (session()->has('message'))
        <div class="mb-4">
            <div class="alert alert-success">{{ session('message') }}</div>
        </div>
        @endif


        <div class=" mb-4">
            <div>
                <h2 class="text-xl font-semibold dark:text-white/90 mb-4">
                    Prefer Booking List
                </h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('text.Total Bookings') }}
                    </h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $totalBookings }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('text.Checkin') }}</h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $checkinCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('text.Pending') }}</h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $pendingCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('text.Cancelled') }}</h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $cancelledCount }}</p>
                </div>
            </div>
        </div>



        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Category</label>
                <select wire:model.live="categoryId"
                    class="w-full border rounded-lg border-gray-300  px-3 py-2 text-sm dark:bg-gray-800 dark:text-white  bg-white">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Subcategory</label>
                <select wire:model.live="subCategoryId"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white  bg-white">
                    <option value="">All</option>
                    @foreach($subCategories as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Child Subcategory</label>
                <select wire:model.live="childCategoryId"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white  bg-white">
                    <option value="">All</option>
                    @foreach($childCategories as $child)
                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </select>
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">From Booking Date</label>
                <input type="date" wire:model.live="fromDate" onclick="this.showPicker()"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">To Booking Date</label>
                <input type="date" wire:model.live="toDate" onclick="this.showPicker()"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Status</label>
                <select wire:model.live="status"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white bg-white">
                    <option value="">All</option>
                    <option value="Pending">Pending</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">Interview Mode</label>
                <select wire:model.live="interviewMode"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white bg-white">
                    <option value="">All</option>
                    <option value="Face to Face">Face to Face</option>
                    <option value="Video call">Video call</option>
                </select>
            </div>
            <div>
            <button wire:click="resetFilters"
                class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow w-full">
                Reset Filters
            </button>
</div>
        </div>

        <div class="mb-4 flex mb-4">
            <div class="relative w-full lg:w-[300px]">
        <span class="pointer-events-none absolute top-1/3 left-4 -translate-y-1/4">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                    </svg>
                </span>
          
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."
                    class="dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>

        </div>


        <div
            class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
            <div class="min-w-full max-w-full overflow-x-auto">
                <table class="min-w-full table-auto">
                    <!-- table header start -->
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">

                            <th class="px-5 py-3 sm:px-6">
                                {{ $level1 }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                {{ $level2 }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                {{ $level3 }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('Ref ID')}}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                <!-- {{ $accountdetail->booking_convert_label .' Status' ??__('Is Convert')}} -->
                                 Status

                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                <!-- {{$accountdetail->booking_convert_label.' DateTime' ??__('Convert Datetime')}} -->
                                DateTime
                            </th>
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{ __('text.Email') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.name') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.contact') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.booking date') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.booking time') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.booking status') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.booking type') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.booked by') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.cancel reason') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.cancel remark') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('text.created at') }}
                            </th>
                 
                        </tr>
                    </thead>
                    <!-- table header end -->
                    <!-- table body start -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @if($bookings->count() > 0)
                        @foreach($bookings as $booking)
                        <tr>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block dark:text-white/90">

                                                {{ $booking->categories->name ?? '' }}
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
                                                class="block dark:text-white/90">
                                                {{ $booking->sub_category->name ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->child_category->name ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->refID ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->is_convert ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                <?php
                                                $bookedDate = '';
                                                if(!empty($booking->convert_datetime)){
                                                 $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); 
                                      
                                               echo $bookedDate =  \Carbon\Carbon::parse($booking->convert_datetime)->format($datetimeFormat);
                                                }
                     ?>

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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->email ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->name ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->phone ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">

                                                {{$booking->booking_date}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->start_time ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->status ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->booking_type ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->createdBy->name ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->cancel_reason ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                {{ $booking->cancel_remark ?? ''}}
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
                                                class="block text-theme-sm dark:text-white/90">
                                                <?php  $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); // Fallback to default format
                          // Return the formatted date based on the format from AccountSetting table
                          echo $created = \Carbon\Carbon::parse($booking->created_at)->format($datetimeFormat) ?? '';

                          ?>

                                            </span>

                                        </div>
                                    </div>
                                </div>
                            </td>



                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="24" class="text-center py-6">
                                <img src="{{ url('images/no-record.jpg') }}" alt="No Records Found"
                                    class="mx-auto" style="max-width:300px">
                                <p class="text-gray-500 dark:text-gray-400 mt-2">No records found.</p>
                            </td>
                        </tr>
                        @endif

                    </tbody>
                </table>

                {{ $bookings->links() }}
            </div>
        </div>

        @if ($showupdatestatus)

<div class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto z-99999" style="">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
    <div
        class="no-scrollbar relative w-full max-w-[507px] overflow-y-auto rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
        <!-- close btn -->
        
        <div class="px-2 pr-14">
            <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                Update Status of Booking
            </h4>

        </div>
        <form class="flex flex-col">
            <div class="custom-scrollbar h-[450px] overflow-y-auto px-2">

                <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-1">

                 
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Status
                        </label>
                        <select wire:model="changeStatus"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    Select
                                </option>
                                @foreach($bookingStatus as  $key=>$value)
                                <option value="{{ $key }}"
                                    class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{ $value }}
                                </option>
                                @endforeach
                            </select>
                        @error('changeStatus') <span class="text-error-500">{{ $message }}</span> @enderror
                    </div>
                  
                </div>

                <div>
                    <div class="flex items-center gap-3 px-2 mt-6 lg:justify-end">
                        <button wire:click="$set('showupdatestatus', false)" type="button"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                            Close
                        </button>
                        <button type="button"
                            class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto"
                            wire:click.prevent="updatestatus">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div id="printQueue"></div>
</div>




    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        Livewire.on('confirm-delete', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
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
                   window.location.reload();
                }
            });

        });

        Livewire.on('confirm-check-in', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You check-in ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('confirmed-check-in');
                }
            });
        });


        Livewire.on('error', data => {

        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: data.message,
            confirmButtonColor: '#d33',
        });
    });
        Livewire.on('updated', () => {
            Swal.fire({
                title: 'Success!',
                text: 'Updated successfully.',
                icon: 'success',
                // confirmButtonText: 'OK'
            })

        });
    });
    </script>



</div>