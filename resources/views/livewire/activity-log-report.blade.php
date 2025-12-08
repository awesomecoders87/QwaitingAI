<div class="p-6">
       <div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('tenant.all-report') }}"
       class="text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-white">
        {{ __('sidebar.All Reports') }}
    </a>
    <span class="text-gray-400">/</span>
    <span class="font-semibold text-gray-900 dark:text-white">
        {{ __('report.Activity Logs') }}
    </span>
</div>

       <div class="flex flex-wrap items-center justify-between gap-1 mb-6">
        <h2 class="text-xl font-semibold dark:text-white">{{ __('report.Activity Logs') }}</h2>
    </div>
    <div class="flex items-center space-x-4 mb-4 py-2 flex-wrap">
        <label for="fromDate">{{ __('report.From') }}:</label>
        <input type="date"  onclick="this.showPicker()" wire:model.live="fromSelectedDate" id="fromDate" class="border border-gray-300 rounded p-2">

        <label for="toDate">{{ __('report.To') }}:</label>
        <input type="date"  onclick="this.showPicker()" wire:model.live="toSelectedDate" id="toDate" class="border border-gray-300 rounded p-2">
    </div>

    <div class="mb-4 md:text-right">
    <button wire:click="exportcsv" class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
        {{ __('report.Export CSV') }}
    </button>
    <button wire:click="exportPdf" class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
        {{ __('report.Export PDF') }}
    </button>
</div>
<div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
        <thead>
            <tr>
                <th class=" p-2">{{ __('report.Date') }}</th>
                <th class=" p-2">{{ __('report.Event') }}</th>
                <th class=" p-2">{{ __('report.Description') }}</th>
                <th class=" p-2">{{ __('report.IP Address') }}</th>
                <th class=" p-2">{{ __('report.Username') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class=" p-2">{{ $log->created_at->format($datetimeFormat) }}</td>
                    <td class=" p-2">{{ $log->type }}</td>
                    <td class=" p-2">{{ $log->text }}</td>
                    <td class=" p-2">{{ $log->ip_address }}</td>
                    <td class=" p-2">{{ $log->creator->name ?? 'N/A' }}</td>
                </tr>
            @empty
            <tr>
                            <td colspan="6" class="text-center py-6">
                                 <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                            </td>
                        </tr>
            @endforelse
        </tbody>
    </table>
</div>
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
