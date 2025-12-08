<div class="p-6">
    <h2 class="text-xl font-semibold mb-4">{{ __('Statistics Report') }}</h2>

    <div class="flex flex-wrap gap-3 w-full border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] py-2 items-end mb-3">
        <div class="flex-1 flex flex-col">
            <label for="fromSelectedDate" class="font-semibold text-gray-700">{{ __('report.From Date') }}</label>
            <input  onclick="this.showPicker()" type="date" id="fromSelectedDate" wire:model.live="fromSelectedDate" class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input active">
        </div>
        <div class="flex-1 flex flex-col">
            <label for="toSelectedDate" class="font-semibold text-gray-700">{{ __('report.To Date') }}</label>
            <input  onclick="this.showPicker()" type="date" id="toSelectedDate" wire:model.live="toSelectedDate" class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input active">
        </div>

        <div class="flex justify-end md:w-2/5">
		
    <button wire:click="exportCsv" class="flex gap-x-2 text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 primary-btn"><i class="ri-file-list-3-line"></i>
		{{ __('report.Export CSV') }}
            </button>
             <button wire:click="exportPdf" class="flex gap-x-2 ml-4 text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 secondry-btn"><i class="ri-file-pdf-2-line"></i>
    {{ __('report.Export PDF') }}
</button> 
        </div>
    </div>


    <div class="card">

        <div class="card-header mb-6">
            @livewire(\App\Livewire\Widgets\StatisticsSummaryChart::class)
        </div>
        <div class="card-header mb-6">
            @livewire(\App\Livewire\Widgets\StatisticsCallHistoryChart::class) 
        </div>
        <div class="card-header mb-2">
            @livewire(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class)
        </div>
    </div>
</div>
