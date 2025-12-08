<div class="p-6 md:p-6">

    <div class="flex flex-wrap items-center justify-between gap-1 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90"> Branches Monthly Report</h2>

    </div>

    <div class="grid grid-cols-6 sm:grid-cols-3 md:grid-cols-6 lg:grid-cols-6 gap-2 mb-3" wire:ignore>
        <div class="monthly-filters">
            <label>{{ __('text.created from') }}</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="created_from"
                class="border rounded p-2 w-full">
        </div>

        <div class="monthly-filters">
            <label>{{ __('text.created until') }}</label>
            <input type="date" onclick="this.showPicker()" wire:model.live="created_until"
                class="border rounded p-2 w-full">
        </div>

        <div class="monthly-filters">
            <label>{{ __('text.staff') }}</label>
            <select multiple wire:model.live="closed_by" class="border rounded p-2 multiple  w-full" id="closed_by">
                @foreach ($users as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="monthly-filters">
            <label>{{ __('text.Counter') }}</label>
            <select multiple wire:model.live="counter_id" class="border rounded p-2  multiple  w-full" id="counter_id">
                @foreach ($counters as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="monthly-filters">
            <label>{{ __('Location') }}</label>
            <select multiple wire:model.live="selectedlocation" class="border rounded p-2  multiple  w-full" id="location_id">
                @foreach ($allLocation as $id => $location)
                <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="monthly-filters">
            <label>{{ __('text.status') }}</label>
            <select multiple wire:model.live="status" class="border rounded p-2  multiple  w-full" id="status">
                <option value="Skip">{{ __('text.Missed') }}</option>
                <option value="Pending">{{ __('text.Pending') }}</option>
                <option value="Progress">{{ __('text.Progress') }}</option>
                <option value="Close">{{ __('text.Close') }}</option>
                <option value="Cancelled">{{ __('text.Cancelled') }}</option>
            </select>
        </div>

        <div class="monthly-filters">
            <label>{{ __('text.Walk-IN/Appt') }}</label>
            <select multiple wire:model.live="ticket_mode" class="border rounded p-2  multiple  w-full"
                id="ticket_mode">
                <option value="Walk-IN">{{ __('text.Walk-In') }}</option>
                <option value="Appointmnent">{{ __('text.Appointment') }}</option>
            </select>
        </div>
        <div class="monthly-filters">
            <label>{{ __('text.Search') }}</label>
            <input type="text" wire:model.live="search" placeholder="Search by name, token, email..."
                class="border rounded p-2 w-full" />
        </div>

    </div>
    <!-- <div class="monthly-filters mb-3">
        <button wire:click="exportCSV()"
            class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            Export CSV
        </button>
        <button wire:click="exportToPDF()"
            class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            Export PDF
        </button>
    </div> -->

    <div>
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="table-auto w-full border ti-custom-table ti-custom-table-hover">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b">{{ __('text.S.No') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('Location') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.Token') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.created at') }}</th>
                        <th class="px-4 py-2 border-b">{{ $level1 }}</th>
                        <th class="px-4 py-2 border-b">{{ $level2 }}</th>
                        <th class="px-4 py-2 border-b">{{ $level3 }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.Counter') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.Called') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.name') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.contact') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.Email') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.Closed By') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.Response Time') }}</th>
                        <th class="px-4 py-2 border-b">{{ __('text.Serving Time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $serialNumber = $reports->firstItem();
                    $dateformat = Auth::user()->date_format ?? 'd M Y';
                    @endphp
                    @if(count($reports) > 0)
                    @foreach ($reports as $report)
                    <tr>
                        <td class="border-b p-2">{{ $serialNumber++ }}</td>
                        <td class="border-b p-2">{{ $report->location->location_name }}</td>
                        <td class="border-b p-2">{{ $report->start_acronym . $report->token }}</td>
                        <td class="border-b p-2"> {{ \Carbon\Carbon::parse($report->datetime)->format($dateformat) }}
                        </td>
                        <td class="border-b p-2">{{ $report->category->name ?? '' }}</td>
                        <td class="border-b p-2">{{ $report->subCategory->name ?? '' }}</td>
                        <td class="border-b p-2">{{ $report->childCategory->name ?? '' }}</td>
                        <td class="border-b p-2">{{ $report->Counter->name ?? '' }}</td>
                        <td class="border-b p-2">{{ $report->servedBy->name ?? '' }}</td>
                        <td class="border-b p-2">{{ $report->name ?? '' }}</td>
                        <td class="border-b p-2">{{ $report->phone ?? '' }}</td>
                        <td class="border-b p-2">
                            @php
                            $json = json_decode($report->json, true);
                            $email = $json['Email'] ?? ($json['email'] ?? $json['email_address'] ?? null);
                            echo $email;
                            @endphp
                        </td>
                        <td class="border-b p-2">{{ $report->closedBy->name ?? '' }}</td>
                        <td class="border-b p-2">
                            @php
                            $responseTime = '';
                            if ($report->called_datetime && $report->arrives_time) {
                            $responseTime = $report->called_datetime->diff($report->arrives_time);
                            $responseTime = $responseTime->format('%H:%I:%S');
                            }
                            echo $responseTime;
                            @endphp
                        </td>
                        <td class="border-b p-2">
                            @php
                            $servedTime = '';
                            if ($report->closed_datetime && $report->start_datetime) {
                            $servedTime = $report->closed_datetime->diff($report->start_datetime);
                            $servedTime = $servedTime->format('%H:%I:%S');
                            }
                            echo $servedTime;
                            @endphp

                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="7" class="text-center py-6">
                            <img src="{{ url('images/no-record.jpg') }}" alt="No Records Found"
                                class="mx-auto h-30 w-30" style="">
                            <p class="text-gray-500 dark:text-gray-400 mt-2">No records found.</p>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            {{ $reports->links() }}
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
            
            $('#location_id').on("change", function(e) {
                let data = $(this).val();
                @this.set('selectedlocation', data);
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