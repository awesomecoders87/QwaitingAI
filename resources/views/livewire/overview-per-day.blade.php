<div class="p-4">
    <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('report.Overview Per Day') }}</h2>
<div class="flex flex-wrap mb-6 gap-4 w-full">
        <div class="flex-1 flex flex-col">
            <label for="fromSelectedDate" class="font-semibold text-gray-700 dark:text-white">{{ __('report.From Date') }}</label>
            <input 
                type="date" 
                wire:model.live="fromSelectedDate" 
                onclick="this.showPicker()"
                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full dark:border-gray-600 dark:bg-gray-800 dark:text-white"
            />
        </div>
        <div class="flex-1 flex flex-col">
            <label for="toSelectedDate" class="font-semibold text-gray-700 dark:text-white">{{ __('report.To Date') }}</label>
            <input 
                type="date" 
                wire:model.live="toSelectedDate" 
                onclick="this.showPicker()"
                class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full dark:border-gray-600 dark:bg-gray-800 dark:text-white"
            />
        </div>
       <div class="flex-1 flex items-end justify-end">
            <button 
                wire:click="exportPdf" 
                class="primary-btn flex gap-x-3 text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            ><i class="ri-file-pdf-2-line"></i>
                {{ __('report.Export PDF') }}
            </button>
        </div>
    </div>
    <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
                <div class="max-w-full overflow-x-auto">
    <table class="w-full table-auto divide-y divide-gray-200 mb-4 table-auto w-full border-collapse">
        <thead>
            <tr>
                <th class="px-5 py-3 sm:px-6">{{ __('report.Date') }}</th>
                <th class="px-5 py-3 sm:px-6">{{ __('report.Arrived') }}</th>
                <th class="px-5 py-3 sm:px-6">{{ __('report.Served') }}</th>
                <th class="px-5 py-3 sm:px-6">{{ __('report.Percentage') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
           @if(!empty($dataPoints['data']))
            @foreach($dataPoints['data'] as $point)
                <tr>
                    <td class="px-5 py-4 sm:px-6">{{ $point['date'] }}</td>
                    <td class="px-5 py-4 sm:px-6">{{ $point['arrived_count'] }}</td>
                    <td class="px-5 py-4 sm:px-6">{{ $point['served_count'] }}</td>
                    <td class="px-5 py-4 sm:px-6">{{ number_format($point['percentage'], 2) }}%</td>
                </tr>
            @endforeach
            @else
            <tr>
                            <td colspan="7" class="text-center py-6">
                                <p class="text-center"><strong>{{ __('text.No records found.') }}</strong></p>
                            </td>
                        </tr>
            @endif
        </tbody>
    </table>
</div>
</div>

    <div class="card-header" wire:key="overview-widget" wire.ignore>

      
            @livewire(\App\Livewire\Widgets\OverviewPerDayWidget::class)

        
        </div>
</div>
