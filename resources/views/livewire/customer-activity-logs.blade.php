<div class="p-4 md:p-6">

    <div class="flex flex-wrap items-center justify-between gap-1 mb-6">
        <h2 class="text-xl font-semibold "> {{ __('report.Activity Logs for') }} {{ $customer->name }} ({{ $customer->phone }})</h2>

    </div>

     <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-4 gap-2 mb-3" wire:ignore>
        <div class="monthly-filters">
            <label>{{ __('report.created from') }}</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="created_from"
                class="border rounded p-2 w-full border-gray-400">
        </div>

        <div class="monthly-filters">
            <label>{{ __('report.created until') }}</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="created_until"
                class="border rounded p-2 w-full border-gray-400">
        </div>
         <div class="monthly-filters">
            <label>{{ __('report.Counter') }} {{ __('report.for Queue') }}</label>
            <select multiple wire:model.live="counter_id" class="border rounded p-2  multiple  w-full" id="counter_id">
                @foreach ($counters as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="monthly-filters">
            <label>{{ __('text.status') }} {{ __('report.for Queue') }}</label>
            <select multiple wire:model.live="status" class="border rounded p-2  multiple  w-full" id="status">
                <option value="Skip">{{ __('text.Missed') }}</option>
                <option value="Pending">{{ __('text.Pending') }}</option>
                <option value="Progress">{{ __('text.Progress') }}</option>
                <option value="Close">{{ __('text.Close') }}</option>
                <option value="Cancelled">{{ __('text.Cancelled') }}</option>
            </select>
        </div>
    </div>

    <div class="mb-4 pt-4">
           <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('report.Queue Log Details') }}</h2>
        <div class="bg-white shadow-md rounded-lg p-4">
            <div class="overflow-x-auto">
            <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
                <thead>
                    <tr>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.S.No') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Token') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.created at') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ $level1 }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ $level2 }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ $level3 }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Counter') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Called') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Email') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Closed By') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Response Time') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Serving Time') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                   
                    @if(count($reports) > 0)
                     @php
                    $serialNumber = $reports->firstItem();
                    $dateformat = Auth::user()->date_format ?? 'd M Y';
                    @endphp
                    @foreach ($reports as $report)
                    <tr>
                        <td class="px-5 py-4 sm:px-6">{{ $serialNumber++ }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->start_acronym . $report->token }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ \Carbon\Carbon::parse($report->datetime)->format($dateformat) }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->category->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->subCategory->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->childCategory->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->Counter->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->servedBy->name ?? '' }}</td>
                    
                        <td class="px-5 py-4 sm:px-6">
                            @php
                            $json = json_decode($report->json, true);
                            $email = $json['Email'] ?? ($json['email'] ?? $json['email_address'] ?? null);
                            echo $email;
                            @endphp
                        </td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->closedBy->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6">
                            @php
                            $responseTime = '';
                            if ($report->called_datetime && $report->arrives_time) {
                            $responseTime = $report->called_datetime->diff($report->arrives_time);
                            $responseTime = $responseTime->format('%H:%I:%S');
                            }
                            echo $responseTime;
                            @endphp
                        </td>
                        <td class="px-5 py-4 sm:px-6">
                            @php
                            $servedTime = '';
                            if ($report->closed_datetime && $report->start_datetime) {
                            $servedTime = $report->closed_datetime->diff($report->start_datetime);
                            $servedTime = $servedTime->format('%H:%I:%S');
                            }
                            echo $servedTime;
                            @endphp

                        </td>
                        <td class="px-5 py-4 sm:px-6 text-center">
                        <button wire:click="viewReport({{ $report->id }})" class="text-blue-600 hover:text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 3C5.5 3 1.7 6.1 0 10c1.7 3.9 5.5 7 10 7s8.3-3.1 10-7c-1.7-3.9-5.5-7-10-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>
                            </svg>
                        </button>
                    </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="20" class="text-center py-6">
                            <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
           </div>
     @if(count($reports) > 0)
            {{ $reports->links() }}
            @endif
        </div>
    </div>



    <div class="pt-4">
        
         <div class="mb-4 flex justify-between flex-wrap">
            <h2 class="mt-3 text-xl font-semibold dark:text-white/90">{{ __('report.Booking Log Details') }}</h2>
            <div class="flex justify-end items-end">
                <div class="mr-6">
                    <label class="text-sm font-medium text-gray-700 dark:text-white">{{ __('report.Booking Status') }}</label>
                    <select wire:model.live="bookingstatus"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:text-white bg-white">
                        <option value="">{{ __('report.All') }}</option>
                        <option value="Pending">{{ __('report.Pending') }}</option>
                        <option value="Confirmed">{{ __('report.Confirmed') }}</option>
                        <option value="In Progress">{{ __('report.Progress') }}</option>
                        <option value="Completed">{{ __('report.Completed') }}</option>
                        <option value="Cancelled">{{ __('report.Cancelled') }}</option>
                    </select>
                </div>
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('report.Search') }}..."
                        class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                </div>
            </div>

        </div>

        <div class="bg-white shadow-md rounded-lg p-4">
            <div class="overflow-x-auto">
            <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
                <thead>
                    <tr>
                       

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

                                {{__('report.Ref ID')}}
                            </th>
                            <th class="px-5 py-3 sm:px-6">
                                {{ $accountdetail->booking_convert_label.' Status' ??__('Is Convert')}}

                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{$accountdetail->booking_convert_label.' DateTime' ??__('Convert Datetime')}}
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

                                {{__('report.Booking Date') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{__('report.Booking Time') }}
                            </th>
                            <th class="px-5 py-3 sm:px-6">

                                {{ __('report.Booking Status') }}
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
          
          
                    </tr>
                </thead>
                <tbody>
                   
                    @if(count($bookings) > 0)
                     @php
                    $serialNumber = $bookings->firstItem();
                    $dateformat = Auth::user()->date_format ?? 'd M Y';
                    @endphp
                    @foreach ($bookings as $booking)
                     <tr>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex items-center gap-3">

                                        <div>
                                            <span
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">

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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">

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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                                                class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
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
                       
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="10" class="text-center py-6">
                           <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
     @if(count($bookings) > 0)
            {{ $bookings->links() }}
            @endif
        </div>
    </div>

 @if($showModal && $selectedReport)
<div id="slideModal" class="fixed inset-0 z-50 flex justify-end bg-opacity-30 bg-black" x-data="{ activeTab: 'details' }">
    <div class="bg-white w-full sm:w-1/2 md:w-1/3 h-full shadow-lg overflow-y-auto transform transition-transform duration-300 translate-x-0">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h2 class="text-lg font-semibold text-gray-800"> {{ __('report.Details') }}</h2>
            <button wire:click="$set('showModal', false)" class="text-gray-600 hover:text-gray-900 text-2xl">&times;</button>
        </div>

        {{-- Tabs --}}
        <div class="border-b">
            <nav class="flex space-x-4 px-4">
                <button @click="activeTab = 'details'" :class="activeTab === 'details' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'" class="py-2 text-sm font-medium focus:outline-none">{{ __('report.Details') }}</button>
                <button @click="activeTab = 'logs'" :class="activeTab === 'logs' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'" class="py-2 text-sm font-medium focus:outline-none">{{ __('report.Logs') }}</button>
                <button @click="activeTab = 'notes'" :class="activeTab === 'notes' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'" class="py-2 text-sm font-medium focus:outline-none">{{ __('report.Notes') }}</button>
                <button @click="activeTab = 'sms'" :class="activeTab === 'sms' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600'" class="py-2 text-sm font-medium focus:outline-none">SMS</button>
            </nav>
        </div>

        {{-- Tab Panels --}}
        <div class="p-4 text-sm text-gray-700 space-y-2">
            {{-- Details Tab --}}
            <div x-show="activeTab === 'details'">
                <p><strong>{{ __('report.Token') }}:</strong> {{ $selectedReport->start_acronym . $selectedReport->token }}</p>
                <p><strong>{{ __('report.Date') }}:</strong> {{ \Carbon\Carbon::parse($selectedReport->datetime)->format($dateformat) }}</p>
                <p><strong>{{ __('report.Category') }}:</strong> {{ $selectedReport->category->name ?? '-' }}</p>
                <p><strong>{{ __('report.Sub Category') }}:</strong> {{ $selectedReport->subCategory->name ?? '-' }}</p>
                <p><strong>{{ __('report.Child Category') }}:</strong> {{ $selectedReport->childCategory->name ?? '-' }}</p>
                <p><strong>{{ __('report.Counter') }}:</strong> {{ $selectedReport->Counter->name ?? '-' }}</p>
                <p><strong>{{ __('report.Served By') }}:</strong> {{ $selectedReport->servedBy->name ?? '-' }}</p>
                <p><strong>{{ __('report.Email') }}:</strong>
                    @php
                        $json = json_decode($selectedReport->json, true);
                        echo $json['Email'] ?? $json['email'] ?? $json['email_address'] ?? '-';
                    @endphp
                </p>
                <p><strong>Closed By:</strong> {{ $selectedReport->closedBy->name ?? '-' }}</p>
                <p><strong>Response Time:</strong>
                    @php
                        $responseTime = '';
                        if ($selectedReport->called_datetime && $selectedReport->arrives_time) {
                            $responseTime = $selectedReport->called_datetime->diff($selectedReport->arrives_time)->format('%H:%I:%S');
                        }
                        echo $responseTime;
                    @endphp
                </p>
                <p><strong>Serving Time:</strong>
                    @php
                        $servedTime = '';
                        if ($selectedReport->closed_datetime && $selectedReport->start_datetime) {
                            $servedTime = $selectedReport->closed_datetime->diff($selectedReport->start_datetime)->format('%H:%I:%S');
                        }
                        echo $servedTime;
                    @endphp
                </p>
            </div>

            {{-- Logs Tab --}}
            <div x-show="activeTab === 'logs'">
                 @forelse($activityLogs as $index => $log)
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-6 w-6 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ps-4 flex-1">
                        <div class="flex justify-between items-center space-y-2">
                            <p class="font-medium text-gray-600">{{ $log->text }} by {{ $log->createdBy?->name }}</p>
                            <span
                                class="text-gray-600 text-sm">{{ Carbon\Carbon::parse($log->created_at)->format('d-m-Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                {{ __('report.No logs found') }}
                @endforelse
            </div>

            {{-- Notes Tab --}}
            <div x-show="activeTab === 'notes'">
                    <div class="w-full mt-4">
                <form wire:submit.prevent="submitEstimateNote()">
                    <textarea wire:model="notice_sms" rows="5"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                </textarea>

                    <div class="my-4 flex space-x-2">
                        <!-- Submit Button -->
                        <button type="submit"
                            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                            {{ __('report.Submit') }}
                        </button>
                    </div>
                </form>
            </div>
            </div>

            {{-- SMS Tab --}}
            <div x-show="activeTab === 'sms'">
               <div>

            <!-- Modal Header -->
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('report.Send SMS') }}
            </div>

            <!-- Modal Body -->
            <div class="w-full mt-4">
                <form wire:submit.prevent="sendSMS">
                    <textarea wire:model="sms" rows="5"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        required></textarea>

                    <div class="flex items-center justify-end w-full gap-3 mt-8">
                        <!-- Submit Button -->
                        <button type="submit"
                            class="flex justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                            {{ __('report.Send') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>
@endif



<style>
    #slideModal{
           top: 75px;
           background-color: #e7e8eb61;
    }
    #slideModal .translate-x-full {
        transform: translateX(100%);
    }

    #slideModal .translate-x-0 {
        transform: translateX(0);
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        $(document).ready(function() {
            $('.multiple').select2();

            $('#counter_id').on("change", function(e) {
                let data = $(this).val();
                @this.set('counter_id', data);
            });

            $('#closed_by').on("change", function(e) {
                let data = $(this).val();
                @this.set('closed_by', data);
            });
            $('#ticket_mode').on("change", function(e) {
                let data = $(this).val();
                @this.set('ticket_mode', data);
            });
            $('#status').on("change", function(e) {
                let data = $(this).val();
                @this.set('status', data);
            });
        });

        Livewire.on('csvReady', (base64CSV) => {
            const csvData = atob(base64CSV); // Decode base64

            const blob = new Blob([csvData], {
                type: 'text/csv;charset=utf-8;'
            });

            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'report.csv';
            a.click();
            URL.revokeObjectURL(url);
        });
    });

        Livewire.on('event-success-call', (response) => {
        if (response[0].message == 'Call started Successfully')
            localStorage.removeItem(`${labelVisitorStorage}`);
        Swal.fire({
            icon: "success",
            title: response[0].message,
            showConfirmButton: false,
            timer: 5000
        });

          // Reload after 5 seconds (5000ms)
    //setTimeout(() => {
      //  location.reload();
    //}, 3000);
    });

    </script>
</div>
