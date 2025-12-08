<div class="p-6">
    <style>
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        
    </style>
    <h2 class="text-xl font-semibold mb-4">{{ __('report.Feedback Report') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white">
            <div class="stats-heading mb-3">{{ __('report.Total Queue') }}</div>
            <div class="stats-value text-4xl font-semibold" id="totalQueue">{{ $cardsDetails['totalQueue'] ?? 0 }}</div>
        </div>

        <div class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white">
            <div class="stats-heading mb-3">{{ __('report.Closed Queue') }}</div>
            <div class="stats-value text-4xl font-semibold" id="closedQueue">{{ $cardsDetails['closedQueue'] ?? 0 }}</div>
        </div>

        <div class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white">
            <div class="stats-heading mb-3">{{ __('report.Average Rating') }}</div>
            <div class="stats-value text-4xl font-semibold" id="averageRating">{{ $cardsDetails['averageRating'] ?? 0 }}</div>
        </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 items-end">
        <div class="flex-col">
            <label for="createdFrom">{{ __('report.From Date') }}</label>
            <input 
                type="date" 
                wire:model.live="createdFrom" 
                onclick="this.showPicker()"
                class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 w-full"
            />
        </div>
        <div class="flex-col">
            <label for="createdUntil">{{ __('report.To Date') }}</label>
            <input 
                type="date" 
                wire:model.live="createdUntil" 
                onclick="this.showPicker()"
                class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 w-full "
            />
        </div>
        <div class="flex-col">
            <label for="staff">{{ __('report.Staff') }}</label>
            <select wire:model.live="staff" class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 w-full">
                <option value="">All</option>
                @foreach ($users as $id=>$user)
                    <option value="{{ $id }}">{{ $user }}</option>
                @endforeach
            </select>
        </div>
       <div class="md:col-span-2">
        <div class="flex items-center gap-x-2 justify-end">
            <button 
                wire:click="exportcsv" 
                class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
                {{ __('report.Export CSV') }}
            </button>
            <button 
        wire:click="exportpdf"
        class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200n"
    >
        {{ __('report.Export PDF') }}
    </button>
        </div>
        </div>
    </div>

    <div>
    
  <div class="overflow-x-auto bg-white shadow-md rounded-lg p-4 dark:border-gray-800 dark:bg-white/[0.03]">    
    <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
        <thead>
            <tr>
                <th class="p-2">{{ __('report.name') }}</th>
                <th class="p-2">{{ __('report.token') }}</th>
                <th class="p-2">{{ __('report.contact') }}</th>
                <th class="p-2">{{ __('report.comment') }}</th>
                <th class="p-2">{{ __('report.average rating') }}</th>
                <th class="p-2">{{ __('report.emotional rating') }}</th>
                <th class="p-2">{{ __('report.datetime') }}</th>
                <th class="p-2">{{ __('report.staff') }}</th>

                {{-- Dynamic question columns --}}
                @foreach($questions as $question)
                    <th class="p-2">{{ $question['question'] }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($reports as $key=>$report)
                <tr wire:key="report-{{ $report->queue_storage_id }}">
                    <td class="p-2">{{ $report->name ?? 'N/A' }}</td>
                    <td class="p-2">{{ $report->token ?? 'N/A' }}</td>
                    <td class="p-2">{{ $report->contact ?? 'N/A' }}</td>
                    <td class="p-2">{{ $report->comment ?? 'N/A' }}</td>

                    <td class="p-2">{{ number_format($report->average_rating ?? 'N/A', 2, '.', '') }}</td>
                        @php
                            $emojiData = collect(\App\Models\Queue::getEmojiText())
                                ->first(function ($item) use ($report) {
                                    return $report->average_rating >= $item['range'][0] &&
                                        $report->average_rating <= $item['range'][1];
                                });
                        @endphp

                        <td class="p-2">
                           <span class="text-2xl block text-center"> {{ $emojiData['emoji'] ?? 'N/A' }} </span>
                        </td>

                    <td class="p-2">{{ $report->datetime ?? 'N/A' }}</td>
                    <td class="p-2">{{ $report->staff ?? 'N/A' }}</td>

                    {{-- Question rating values --}}
                    @foreach($questions as $question)
                        <td class="p-2">
                            {{ $report->{$question['question']} ?? 'N/A' }}
                        </td>
                    @endforeach
                </tr>

            @empty
                <tr>
                    <td colspan="{{ 8 + count($questions) }}" class="text-center py-6">
                        <strong>{{ __('report.No records found.') }}</strong>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
</div>
