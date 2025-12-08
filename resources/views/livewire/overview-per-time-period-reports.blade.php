<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">{{ __('report.Overview Per Time Report') }}</h2>

    <div class="grid grid-cols-2 md:grid-cols-5 flex-wrap md:row-cols-5 gap-4 mb-6 items-end">
        <div>
            <label class="text-sm font-medium">{{ __('report.From Date') }}</label>
            <input type="date" wire:model.live="fromSelectedDate" onclick="this.showPicker()" class="w-full border border-gray-300 p-2 rounded  dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="text-sm font-medium">{{ __('report.To Date') }}</label>
            <input type="date" wire:model.live="toSelectedDate" onclick="this.showPicker()" class="w-full border border-gray-300 p-2 rounded  dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="text-sm font-medium">{{ __('report.From Time') }}</label>
            <input type="time" wire:model.live="fromSelectedTime" onclick="this.showPicker()" class="w-full border border-gray-300 p-2 rounded dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
            <label class="text-sm font-medium">{{ __('report.To Time') }}</label>
            <input type="time" wire:model.live="toSelectedTime" onclick="this.showPicker()" class="w-full border border-gray-300 p-2 rounded  dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
        <div >
            <button wire:click="exportPdf" class="text-theme-sm shadow-theme-xs primary-btn inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"><i class="ri-file-list-3-line"></i> {{ __('report.Export PDF') }}</button>
        </div>
    </div>
    
    <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
                <div class="max-w-full overflow-x-auto">    
    <table class="w-full table-auto mb-4 table-auto w-full border-collapse">
        <thead>
            <tr>
                <th class=" p-2">{{ __('report.Time Slot') }}</th>
                <th class=" p-2">{{ __('report.Arrived') }}</th>
                <th class=" p-2">{{ __('report.Called') }}</th>
                <th class=" p-2">{{ __('report.Waiting') }}</th>
                <th class=" p-2">{{ __('report.Percentage') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dataPoints as $point)
            <tr>
                <td class=" p-2">{{ $point['time_slot'] }}</td>
                <td class=" p-2">{{ $point['arrived_count'] }}</td>
                <td class=" p-2">{{ $point['called_count'] }}</td>
                <td class=" p-2">{{ $point['waiting_count'] }}</td>
                <td class=" p-2">{{ $point['percentage'] }}%</td>
            </tr>
            @empty
           <tr>
                <td colspan="7" class="text-center py-6">
                    <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
    <div class="card-header mt-6" wire:key="overview-widget" wire.ignore>
       @livewire(\App\Livewire\Widgets\StatisticsSummaryTimeChart::class) 
    </div>

   
</div>