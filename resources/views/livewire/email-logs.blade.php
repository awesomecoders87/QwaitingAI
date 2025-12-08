<div class="p-6">
    <h2 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('report.Email Logs') }}</h2>
    <div class="grid grid-cols-2 items-center space-x-4 mb-4 py-2 w-lg">
        <div>
        <label for="fromDate">{{ __('report.From') }}</label>
        <input type="date"  onclick="this.showPicker()" wire:model.live="fromSelectedDate" id="fromDate" class="w-full border border-gray-300 rounded p-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
        <div>
        <label for="toDate">{{ __('report.To') }}</label>
        <input type="date"  onclick="this.showPicker()" wire:model.live="toSelectedDate" id="toDate" class="w-full border border-gray-300 rounded p-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-3 justify-between mb-4">

        <div class="relative w-full lg:w-[300px]">
            <span class="pointer-events-none absolute top-1/3 left-3 -translate-y-1/4">
                <svg class="fill-gray-500 dark:fill-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37381C3.04199 5.87712 5.87735 3.04218 9.37533 3.04218C12.8733 3.04218 15.7087 5.87712 15.7087 9.37381C15.7087 12.8705 12.8733 15.7055 9.37533 15.7055C5.87735 15.7055 3.04199 12.8705 3.04199 9.37381ZM9.37533 1.54218C5.04926 1.54218 1.54199 5.04835 1.54199 9.37381C1.54199 13.6993 5.04926 17.2055 9.37533 17.2055C11.2676 17.2055 13.0032 16.5346 14.3572 15.4178L17.1773 18.2381C17.4702 18.531 17.945 18.5311 18.2379 18.2382C18.5308 17.9453 18.5309 17.4704 18.238 17.1775L15.4182 14.3575C16.5367 13.0035 17.2087 11.2671 17.2087 9.37381C17.2087 5.04835 13.7014 1.54218 9.37533 1.54218Z" fill=""></path>
                </svg>
            </span>
            <input type="text" wire:model.live.debounce.500ms="searchTerm" id="search" placeholder="Search message/contact"
         class="bg-white dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-[42px] w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-[42px] text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
        </div>
        <div class="flex gap-x-3">
        <button wire:click="exportCsv" class="px-3 py-2 primary-btn font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2"><i class="ri-file-list-3-line"></i> {{ __('report.Export CSV') }}</button>
                <div>
         
            <button wire:click="exportPdf"
                class="inline-flex items-center gap-2 secondry-btn rounded-lg border border-gray-500 bg-white px-3 py-2 font-medium text-gray-700 hover:bg-gray-900 hover:text-white hover:border-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
            <i class="ri-file-pdf-2-line"></i>  {{ __('report.Export PDF') }}
            </button>
            
        </div>
        </div>
    </div>
 
<div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">   
<div class="overflow-x-auto max-w-fulll"> 
    <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
        <thead>
            <tr>
                <th class=" p-2">{{ __('report.Date') }}</th>
                <th class=" p-2">{{ __('report.Queue ID') }}</th>
                <th class=" p-2">{{ __('report.Booking ID') }}</th>
                <th class=" p-2">{{ __('report.Email Sent To') }}</th>
                <th class=" p-2">{{ __('report.Event Name') }}</th>
                <th class=" p-2">{{ __('report.Status') }}</th>
            </tr>
        </thead>
        <tbody>
               <?php  $datetimeFormat = App\Models\AccountSetting::showDateTimeFormat(); ?>
            @forelse($emailLogs as $log)
                <tr>
                    <td class=" p-2">{{ $log->created_at->format($datetimeFormat) }}</td>
                    <td class=" p-2">{{ $log->queue_id }}</td>
                    <td class=" p-2">{{ $log->booking_id }}</td>
                    <td class=" p-2">{{ $log->email }}</td>
                    <td class=" p-2">{{ $log->event_name }}</td>
                    <td class=" p-2">{{ $log->status }}</td>
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

    <div class="mt-4">
        {{ $emailLogs->links() }}
    </div>
</div>
</div>
</div>
