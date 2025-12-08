<div class="p-6">

    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-4">{{ __('report.Staff Service & Rating Summary') }}</h2>
        <div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 items-end">

        <div>
            <label>{{ __('report.From Date') }}</label>
            <input type="date" wire:model.live="startDate" onclick="this.showPicker()"
                class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 w-full">
        </div>

        <div>
            <label>{{ __('report.To Date') }}</label>
            <input type="date" wire:model.live="endDate" onclick="this.showPicker()"
                class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 w-full">
        </div>

        <div>
            <label>{{ __('report.staff') }}</label>
            <select wire:model.live="staff" class="bg-white border border-gray-300 text-gray-900 rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 w-full">
                <option value="">All Staff</option>
                @foreach($staffList as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="md:col-span-2">
            <div class="flex items-center  gap-x-2 justify-end">
               
                <button wire:click="exportCsv" class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    Export CSV
                </button>
                <button wire:click="exportPdf" class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    Export PDF
                </button>
            </div>
        </div>


    </div>

    <div class="bg-white shadow rounded overflow-x-auto dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
            <thead>
                <tr>
                    <th class="p-3">Staff</th>
                    <th class="p-3">Guest Served</th>
                    <th class="p-3">Total Feedback</th>
                    <th class="p-3">4 Stars</th>
                    <th class="p-3">3 Stars</th>
                    <th class="p-3">2 Stars</th>
                    <th class="p-3">1 Stars</th>
                    <th class="p-3">Avg. Rating</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($records as $row)
                    <tr>
                        <td class="p-3">{{ $row->name }}</td>
                        <td class="p-3">{{ $row->guest_served }}</td>
                        <td class="p-3">{{ $row->total_feedback }}</td>
                        <td class="p-3">{{ $row->star4 }}</td>
                        <td class="p-3">{{ $row->star3 }}</td>
                        <td class="p-3">{{ $row->star2 }}</td>
                        <td class="p-3">{{ $row->star1 }}</td>
                        <td class="p-3">{{ number_format($row->avg_rating, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3">
                            No Records Found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $records->links() }}
    </div>
</div>
