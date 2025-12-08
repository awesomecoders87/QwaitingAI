<div class="p-6">
    <h2 class="text-xl font-semibold mb-4">{{ __('Feedback Statistics Report') }}</h2>

    <div class="-mx-2.5 flex flex-wrap w-full border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] md:pl-5 md:pr-5  p-2">
        <div class="flex-1 flex flex-col mx-2 py-4 sm:py-2">
            <label for="fromSelectedDate" class="font-semibold text-gray-700">{{ __('From Date') }}</label>
            <input  onclick="this.showPicker()" type="date" id="fromSelectedDate" wire:model.live="fromSelectedDate" class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input">
        </div>
        <div class="flex-1 flex flex-col mx-2 py-4 sm:py-2">
            <label for="toSelectedDate" class="font-semibold text-gray-700">{{ __('To Date') }}</label>
            <input  onclick="this.showPicker()" type="date" id="toSelectedDate" wire:model.live="toSelectedDate" class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input">
        </div>
        <div class="flex-1 flex flex-col mx-2 py-4 sm:py-2" style="align-items: baseline;justify-content: end;">
            <button wire:click="exportPdf" class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Export PDF</button>
        </div>
    </div>


    <div class="card">
        <div class="card-header mb-4">
            @livewire(\App\Livewire\Widgets\FeedbackStatisticsChart::class)
        </div>
    </div>
</div>

