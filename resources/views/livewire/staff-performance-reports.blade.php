<div class="p-4">
    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-4">{{ __('report.Staff Performance Report') }}</h2>
        <div
            class="gap-3 flex flex-wrap w-full border-gray-200 dark:border-gray-800 mb-2">
            <div class="flex-1 flex flex-col sm:py-2">
                <label for="fromSelectedDate" class="font-semibold text-gray-700 dark:text-white">{{ __('report.From Date') }}</label>
                <input type="date" onclick="this.showPicker()" wire:model.live="created_from"
                    class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:rand-300 focus:ring-brand-500/10 dark:focus:rand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 flatpickr-input">
            </div>
            <div class="flex-1 flex flex-col sm:py-2">
                <label for="toSelectedDate" class="font-semibold text-gray-700 dark:text-white">{{ __('report.To Date') }}</label>
                <input type="date" onclick="this.showPicker()" wire:model.live="created_until"
                    class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:rand-300 focus:ring-brand-500/10 dark:focus:rand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder:text-white/30 flatpickr-input">
            </div>
            <div class="flex-1 flex flex-col sm:py-2">
                <label for="search" class="font-semibold text-gray-700 dark:text-white">{{ __('report.Search Staff') }}</label>
                <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search by name"
                    class="bg-white border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>
            <div class="flex-1 flex flex-col sm:py-2" wire:ignore>
            <label>{{ __('Location') }}</label>
            <select multiple wire:model.live="selectedlocation" class="border rounded p-2  multiple  w-full" id="location_id">
                @foreach ($allLocation as $id => $location)
                <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                @endforeach
            </select>
        </div>
        </div>

        <div class="mb-4 text-right flex justify-end">
            <button wire:click="exportCsv"
                class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 primary-btn"><i class="ri-file-list-3-line"></i>
                {{ __('report.Export CSV') }}
            </button>
            <button wire:click="exportPdf"
                class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 ml-2 secondry-btn"><i class="ri-file-pdf-2-line"></i>
                {{ __('report.Export PDF') }}
            </button>
        </div>
    </div>
    <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
                <div class="max-w-full overflow-x-auto">   
    <table class="w-full table-auto mb-4 table-auto w-full border-collapse">
        <thead>
            <tr>
                <th class=" p-2">{{ __('report.Staff') }}</th>
                <th class=" p-2">{{ __('report.Visitors Served') }}</th>
                @foreach ($categories as $category)
                <th class=" p-2">{{ $category->name }}</th>
                @endforeach
                <th class=" p-2">{{ __('report.Total Served Time') }}</th>
                <th class=" p-2">{{ __('report.Average Served Time') }}</th>
            </tr>
        </thead>
      <tbody>
    @foreach ($users as $user)
        @php
      
             $userQueuesQuery = $user->queues()
        ->where('status', '!=', 'Cancelled')
         ->whereDate('arrives_time', '>=', $created_from)
        ->whereDate('arrives_time', '<=', $created_until);

    // Apply location filter only if selected
    if (!empty($selectedlocation)) {
    
            $userQueuesQuery->whereIn('locations_id', $selectedlocation);
        
    }

    $userQueues = $userQueuesQuery->get();
    
            $queueCount = $userQueues->count();
            $totalServedTime = 0;
            $categoryCounts = [];

            foreach ($userQueues as $queue) {
                if ($queue->start_datetime && $queue->closed_datetime) {
                    $totalServedTime += $queue->closed_datetime->diffInSeconds($queue->start_datetime);
                }

                // Pre-count category-wise
                $catId = $queue->category_id;
                if (!isset($categoryCounts[$catId])) {
                    $categoryCounts[$catId] = 0;
                }
                $categoryCounts[$catId]++;
            }

            $averageServedTime = $queueCount > 0 ? $totalServedTime / $queueCount : 0;
        @endphp

        <tr>
            <td class="p-2">{{ $user->name }}</td>
            <td class="p-2">{{ $queueCount }}</td>

            @foreach ($categories as $category)
                <td class="p-2">{{ $categoryCounts[$category->id] ?? 0 }}</td>
            @endforeach

            <td class="p-2">
                {{ \Carbon\CarbonInterval::seconds($totalServedTime)->cascade()->format('%H:%I:%S') }}
            </td>
            <td class="p-2">
                {{ \Carbon\CarbonInterval::seconds($averageServedTime)->cascade()->format('%H:%I:%S') }}
            </td>
        </tr>
    @endforeach
</tbody>
    </table>
    </div>           
</div>     
    <div class="mt-4">
        {{ $users->links() }}
    </div>
      <script>
    document.addEventListener("DOMContentLoaded", function() {
        $(document).ready(function() {
            $('.multiple').select2();

            $('#location_id').on("change", function(e) {
                let data = $(this).val();
                @this.set('selectedlocation', data);
            });

        });

    });
    </script>
</div>