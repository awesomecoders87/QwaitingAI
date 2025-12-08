<div class="p-4">
    <div>

        @if (session()->has('message'))
        <div class="mb-4">
            <div class="alert alert-success">{{ session('message') }}</div>
        </div>
        @endif


        <div class=" mb-4">
            <div>
                <h2 class="text-xl page-title font-semibold dark:text-white/90 mb-4">
                    {{ __('report.Booking List') }}
                </h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('report.Total Bookings') }}
                    </h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $totalBookings }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('report.Checkin') }}</h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $checkinCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('report.Pending') }}</h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $pendingCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                    <h3 class="text-sm text-gray-500 dark:text-white/70 font-medium">{{ __('report.Cancelled') }}</h3>
                    <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $cancelledCount }}</p>
                </div>
            </div>
        </div>



        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-4 items-end pb-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('report.Service') }}</label>
                <select wire:model.live="categoryId"
                    class="w-full border rounded-lg border-gray-300  px-3 py-2 text-sm dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600">
                    <option value="">{{ __('report.All') }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('report.sub service') }}</label>
                <select wire:model.live="subCategoryId"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600">
                    <option value="">{{ __('report.All') }}</option>
                    @foreach($subCategories as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('report.Child Sub-service') }}</label>
                <select wire:model.live="childCategoryId"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600">
                    <option value="">All</option>
                    @foreach($childCategories as $child)
                    <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </select>
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('report.From Date') }}</label>
                <input type="date" wire:model.live="fromDate" onclick="this.showPicker()"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('report.To Date') }}</label>
                <input type="date" wire:model.live="toDate" onclick="this.showPicker()"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('report.status') }}</label>
                <select wire:model.live="status"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white bg-white">
                    <option value="">All</option>
                    <option value="Pending">{{ __('report.Pending') }}</option>
                    <option value="Confirmed">{{ __('report.Confirmed') }}</option>
                    <option value="In Progress">{{ __('report.In Progress') }}</option>
                    <option value="Completed">{{ __('report.Completed') }}</option>
                    <option value="Cancelled">{{ __('report.Cancelled') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white">{{ __('report.Interview Mode') }}</label>
                <select wire:model.live="interviewMode"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white bg-white">
                    <option value="">{{ __('report.All') }}</option>
                    <option value="Face to Face">{{ __('report.Face to Face') }}</option>
                    <option value="Video call">{{ __('report.Video Call') }}</option>
                </select>
            </div>
                  <div class="flex gap-3">
                    <button wire:click="resetFilters"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow text-center">
                        {{ __('report.Reset Filters') }}
                    </button>
                    
                </div>
        </div>

        <div class="mb-4 flex gap-3 flex-wrap justify-between">
            <div class="relative w-full lg:w-[300px]">
        <span class="pointer-events-none absolute top-1/3 left-4 -translate-y-1/4">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                    </svg>
                </span>
          
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."
                    class="dark:bg-dark-900 bg-white shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            </div>

            <div class="flex gap-3 items-center flex-wrap">
                <button wire:click="exportBookings"
                        class="flex-1 text-nowrap bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow text-center">
                         {{ __('report.Export CSV') }}
                    </button>
                    
                    <button wire:click="exportPdf"
                        class="flex-1 text-nowrap bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow text-center">
                        {{ __('report.Export PDF') }}
                    </button>

                    <!-- New actions: sample download and modal upload -->
                    <button wire:click="downloadSampleBookingCsv"
                        class="flex-1 text-nowrap bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow text-center">
                        Download sample CSV
                    </button>
                    <button wire:click="openUploadModal"
                        class="flex-1 text-nowrap bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow text-center">
                        Upload CSV
                    </button>
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

                                {{ __('report.Ref ID')}}
                            </th>
                          <th class="px-5 py-3 sm:px-6">
                                <!-- {{ $accountdetail->booking_convert_label .' Status' ??__('Is Convert')}} -->
                                {{ __('report.status')}}

                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                <!-- {{$accountdetail->booking_convert_label.' DateTime' ??__('Convert Datetime')}} -->
                               {{ __('report.Date & Time')}}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{ __('report.Email') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.name') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.contact') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Booking Date') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Booking Time') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Booking Status') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Booking Type') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Booked By') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Cancel Reason') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Cancel Remark') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.created at') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Actions') }}
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
                                             <span class="block text-theme-sm dark:text-white/90">
											@if($booking->convert_datetime)
												{{ \Carbon\Carbon::parse($booking->convert_datetime)->format($datetimeFormat) }}
											@endif
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
                                                {{ $booking->booking_time ?? ''}}
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
                                             <span class="block text-theme-sm dark:text-white/90">
												{{ \Carbon\Carbon::parse($booking->created_at)->format($datetimeFormat) }}
											</span>

                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 whitespace-nowrap">

                                <div class="flex items-center justify-center">
                                    <div x-data="{openDropDown: false}" class="relative">
                                        <button @click="openDropDown = !openDropDown"
                                            class="text-gray-500 dark:text-gray-400">
                                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z"
                                                    fill="" />
                                            </svg>
                                        </button>
                                        <div x-show="openDropDown" @click.outside="openDropDown = false"
                                            class="shadow-theme-lg dark:bg-gray-dark absolute top-full right-0 z-40 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800">
                                            @can('Booking Status Update')
                                                @if($booking->is_convert == 'No')
                                                <button wire:click="openStatusModal({{ $booking->id }})"
                                                    class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                 {{__('text.Update Status') }}
                                                </button>
                                                @endif
                                            @endcan

                                            @can('Booking Check-in')
                                            @if($booking->is_convert == App\Models\Booking::STATUS_NO && $booking->status == App\Models\Booking::STATUS_CONFIRMED && isset($accountdetail) && $accountdetail->booking_convert_manually == 1)
                                            <button wire:click="confimationforcheckin({{ $booking->id }})"
                                                class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                {{ __('report.Check-In') }}
                                            </button>
                                            @endif
                                            @endcan
                                            @can('Booking Edit')
                                            @if($booking->is_convert == App\Models\Booking::STATUS_NO)
                                            <a href="{{ route('edit-booking', ['id' => base64_encode($booking->id)]) }}" target="_blank">
                                                <button
                                                    class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                     {{ __('report.Edit') }}
                                                </button>
                                            </a>
                                            @endif
                                          @endcan
                                       @can('Booking Delete')
                                            <button wire:click="deleteBooking({{ $booking->id }})"
                                                class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                                {{ __('report.Delete') }}
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
                            <td colspan="24" class="text-center py-6">
                                <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
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
                {{__('report.Update Status of Booking') }}
            </h4>

        </div>
        <form class="flex flex-col">
            <div class="custom-scrollbar h-[450px] overflow-y-auto px-2">

                <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-1">

                 
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        {{__('report.status') }}
                        </label>
                        <select wire:model="changeStatus"
                                class="dark:bg-dark-900 z-20 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    {{__('report.Select') }}
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
                            {{__('report.Close') }}
                        </button>
                        <button type="button"
                            class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto"
                            wire:click.prevent="updatestatus">
                            {{__('report.Save') }}
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

@if($showUploadModal)
<div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" wire:click="closeUploadModal"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Upload Bookings CSV</h3>
            <button class="text-gray-500 hover:text-gray-700" wire:click="closeUploadModal">✕</button>
        </div>

        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">Select a CSV file with headers</p>

            <input type="file" wire:model="csvFile" accept=".csv,text/csv"
                class="w-full text-sm text-gray-900 dark:text-white file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-white/80 border rounded-lg p-2" />
            <div wire:loading.delay wire:target="csvFile">
                <div class="flex items-center justify-center py-2">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-gray-600 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </div>
            </div>
            

            @error('csvFile')
                <div class="text-red-600 text-xs">{{ $message }}</div>
            @enderror

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300" 
                    wire:click="closeUploadModal"
                    {{ $isUploading ? 'disabled' : '' }}>
                    Cancel
                </button>
                <button type="button" 
                    class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center min-w-[100px]" 
                    wire:click="uploadCsvBookings"
                    wire:loading.attr="disabled"
                    {{ $isUploading ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="uploadCsvBookings">Upload</span>
                    <span wire:loading wire:target="uploadCsvBookings">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif
    <style>
        [wire\:loading] {
            opacity: 0.7;
            pointer-events: none;
        }
        .opacity-70 {
            opacity: 0.7;
        }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Show loading overlay when upload starts
        Livewire.on('csv-upload-started', () => {
            // You can add any additional UI feedback here if needed
        });

        Livewire.on('csv-uploaded', (payload) => {
            Swal.fire({
                icon: 'success',
                title: 'Upload completed',
                text: payload?.message || 'Bookings CSV uploaded successfully.',
                timer: 3000,
                showConfirmButton: false
            });
        });

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