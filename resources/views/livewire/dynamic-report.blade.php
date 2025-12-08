<div class="p-4">

    <div class="flex flex-wrap items-center justify-between gap-1 mb-6">
        <h2 class="text-xl font-semibold dark:text-white/90">{{ __($reportName) }}</h2>

    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-2 mb-3" wire:ignore>
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

    @if (count($filteredColumns) > 0)
        <div class="p-4 bg-white shadow-md rounded-lg">
            <div class="overflow-x-auto">
                <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
                    <thead>
                        <tr>

                            @foreach ($filteredColumns as $columns)
                                <th class="px-5 py-3 sm:px-6">{{ $columns }}</th>
                            @endforeach

                        </tr>
                    </thead>
                    <tbody>

                        @if (count($reports) > 0)
                            @php
                                $serialNumber = $reports->firstItem();
                                $dateformat = Auth::user()->date_format ?? 'd M Y';
                            @endphp
                            @foreach ($reports as $report)
                                <tr>
                                      @foreach ($filteredColumns as $key => $label)
                                        <td class="px-5 py-3 sm:px-6">{{ $report[$key] }}</td>
                                    @endforeach
                                  
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
                @if (count($reports) > 0)
                    {{ $reports->links() }}
                @endif
            </div>
        </div>
    @else
        <table>
            <tr>
                <td colspan="15" class="text-center py-6">
                    <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                </td>
            </tr>
        </table>
    @endif
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
