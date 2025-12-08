<div class="p-4">
    @php
        $locale = session('app_locale') ?? 'en';
    @endphp

<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('tenant.all-report') }}"
       class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
        {{ __('sidebar.All Reports') }}
    </a>
    <span class="text-gray-400">/</span>
    <span class="font-semibold text-gray-900 dark:text-white">
        {{ __('report.Monthly Report') }}
    </span>
</div>
    <div class="flex flex-wrap items-center justify-between gap-1 mb-6">
        <h2 class="text-xl font-semibold dark:text-white">{{ __('report.Monthly Report') }}</h2>

    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-2 mb-3" wire:ignore>
        <div class="monthly-filters">
           <label>{{ __('report.created from') }}</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="created_from"
                class="border rounded p-2 w-full border-gray-400 dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600">
        </div>

        <div class="monthly-filters">
         <label>{{ __('report.created until') }}</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="created_until"
                class="border rounded p-2 w-full border-gray-400 dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600">
        </div>

        <div class="monthly-filters">
          <label>{{ __('report.staff') }}</label>
            <select multiple wire:model.live="closed_by" class="border rounded p-2 multiple  w-full dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600" id="closed_by" data-placeholder="{{ __('report.select staff') }}">
                @foreach ($users as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="monthly-filters">
             <label>{{ __('report.Counter') }}</label>
            <select multiple wire:model.live="counter_id" class="border rounded p-2  multiple  w-full dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600" id="counter_id" data-placeholder="{{ __('report.select counter') }}">
                @foreach ($counters as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="monthly-filters">
            <label>{{ __('report.status') }}</label>
            <select multiple wire:model.live="status" class="border rounded p-2  multiple  w-full dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600" id="status" data-placeholder="{{ __('report.select status') }}">
                <option value="Skip">{{ __('report.Missed') }}</option>
                <option value="Pending">{{ __('report.Pending') }}</option>
                <option value="Progress">{{ __('report.Progress') }}</option>
                <option value="Close">{{ __('report.Close') }}</option>
                <option value="Cancelled">{{ __('report.Cancelled') }}</option>
            </select>
        </div>

        <div class="monthly-filters">
            <label>{{ __('report.Walk-IN/Appt') }}</label>
            <select multiple wire:model.live="ticket_mode" class="border rounded p-2  multiple  w-full dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600"
                id="ticket_mode" data-placeholder="{{ __('report.select ticket mode') }}">
                <option value="Walk-IN">{{ __('report.Walk-in') }}</option>
                <option value="Appointmnent">{{ __('report.Appointment') }}</option>
            </select>
        </div>
        <div class="monthly-filters">
            <label>{{ __('report.Search') }}</label>
            <input type="text" wire:model.live="search" placeholder="{{ __('report.Search by name, token, email...') }}"
                class="border rounded  border-gray-400 p-2 w-full dark:bg-gray-800 dark:text-white  bg-white dark:border-gray-600" />
        </div>
        <div class="monthly-filters items-end flex justify-end gap-2">
            <button wire:click="exportCSV()"
                class="flex gap-x-2 text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 primary-btn"><i class="ri-file-list-3-line"></i>
            {{ __('report.Export CSV') }}
            </button>
            <button wire:click="exportToPDF()"
                class="flex gap-x-2 text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 secondry-btn"><i class="ri-file-pdf-2-line"></i>
                {{ __('report.Export PDF') }}
            </button>
        </div>
    </div>


    <div class="p-4 bg-white shadow-md rounded-lg dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
                <thead>
                    <tr>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.S.No') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Token') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.created at') }}</th>
                       <th class="px-5 py-3 sm:px-6">{{ $level1 }}</th>
                        {{-- <th class="px-5 py-4 sm:px-6">{{ $locale !== 'en' ? (!empty($translations[$level1 ][$locale]) ? $translations[$level1 ][$locale] : $level1 ) : $level1  }}</th> --}}
                        <th class="px-5 py-3 sm:px-6">{{ $level2 }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ $level3 }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Counter') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Called') }}</th>
                        @if(isset($formfields))
                          @foreach($formfields as $formfield)
                          <th class="px-5 py-3 sm:px-6">{{ $formfield->title ?? '' }}</th>
                          @endforeach
                        @endif
                        {{-- <th class="px-5 py-3 sm:px-6">{{ __('report.name') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.contact') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Email') }}</th> --}}
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Closed By') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Assign to') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Note') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.called at') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.closed at') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Response Time') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Serving Time') }}</th>
                        @can('Read Document File')
                        @if($enable_doc_file_field)
                        <th class="px-5 py-3 sm:px-6">{{ $doc_file_label }}</th>
                        @endif
                        @endcan
                        <th class="px-5 py-3 sm:px-6">{{ __('report.Status') }}</th>
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
                        {{-- <td class="px-5 py-4 sm:px-6">{{ $report->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->phone ?? '' }}</td> --}}
                        @php

                            $json = json_decode($report->json, true);
                        @endphp
                        @if(!empty($formfields))
                            @foreach($formfields as $formfield)
                                @php
                                    $value = $json[\Illuminate\Support\Str::lower($formfield->title)] ?? '';
                                @endphp

                                <td class="px-5 py-4 sm:px-6">
                                    @if(is_array($value))
                                        {{ implode(', ', $value) }} {{-- Join array values --}}
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        @endif
                        {{-- <td class="px-5 py-4 sm:px-6">
                            @php

                            $email = $json['Email'] ?? ($json['email'] ?? $json['email_address'] ?? null);
                            echo $email;
                            @endphp
                        </td> --}}
                        <td class="px-5 py-4 sm:px-6">{{ $report->closedBy->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6">{{ $report->assignStaff->name ?? '' }}</td>
                        <td class="px-5 py-4 sm:px-6" title="{{ $report->esitmate_note ?? '' }}">
                           {{ Str::limit($report->esitmate_note ?? '', 20) ?? '' }}
                        </td>
                         <td class="px-5 py-4 sm:px-6">{{ !empty($report->called_datetime) ? \Carbon\Carbon::parse($report->called_datetime)->format($dateformat) : ''}}</td>
                         <td class="px-5 py-4 sm:px-6">{{ !empty($report->closed_datetime) ? \Carbon\Carbon::parse($report->closed_datetime)->format($dateformat) : ''}}</td>
                       
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
                         @can('Read Document File')
                        @if($enable_doc_file_field)
                        <td class="px-5 py-4 sm:px-6">
                            @if(!empty($report->doc_file))
                               
                                @if($teamId == 3)
                                <a href="{{ url('storage/' . $report->doc_file) }}" target="_blank" class="inline-block px-3 py-1 text-sm font-medium bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 transition-colors duration-200" style="color:#fff">
                                                                {{ __('report.View File') }}
                                                    </a>
                            @else
                            <a href="{{ 'https://prod.qwaiting.com/storage/' . $report->doc_file }}" target="_blank" class="inline-block px-3 py-1 text-sm font-medium bg-indigo-600 rounded-lg shadow-sm hover:bg-indigo-700 transition-colors duration-200" style="color:#fff">
                                                                {{ __('report.View File') }}
                                                    </a>
                           @endif
                            @else
                               <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        @endif
                        @endcan
                        <td class="px-5 py-4 sm:px-6">
                            {{ $report->status ?? '' }}
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="15" class="text-center py-6">
                             <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
      @if(count($reports) > 0)
            {{ $reports->links() }}
            @endif
        </div>
    </div>
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
    </script>
</div>
