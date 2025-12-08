<div class="p-6 bg-white rounded-2xl shadow-md">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Category Grouping</h2>

    {{-- Category Dropdown --}}
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Category</label>
        <select wire:model.live="selectedLevel1"
            class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">-- Select Category --</option>
            @foreach ($level1Categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>



    {{-- Department & Staff Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Department</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Staff*</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Counter*</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Meeting Seq*</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($groupingData['data'] ?? [] as $catId => $row)
                    <tr>
                        {{-- Department --}}
                        <td class="px-4 py-2 text-sm text-gray-800">
                            {{ $row['categoryName'] }}
                        </td>

                        {{-- Staff --}}
                        <td class="px-4 py-2">
                            <select wire:model.live="groupingData.selected.{{ $catId }}.staff"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Select Staff --</option>
                                @foreach ($row['staff'] as $staffId => $staff)
                                    <option value="{{ $staffId }}">{{ $staff['staffName'] }}</option>
                                @endforeach
                            </select>
                            @error("groupingData.selected.$catId.staff")
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror

                        </td>

                        {{-- Counter (depends on staff) --}}
                        <td class="px-4 py-2">
                            @php
                                $selectedStaff = $groupingData['selected'][$catId]['staff'] ?? null;
                                $counters = [];

                                if ($selectedStaff && isset($row['staff'][$selectedStaff]['counters'])) {
                                    $counters = $row['staff'][$selectedStaff]['counters'];
                                }
                            @endphp

                            <select wire:model="groupingData.selected.{{ $catId }}.counter"
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Select Counter --</option>
                                @foreach ($counters as $counterId => $counterName)
                                    <option value="{{ $counterId }}">{{ $counterName }}</option>
                                @endforeach
                            </select>
                            @error("groupingData.selected.$catId.counter")
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </td>

                        {{-- Priority --}}
                        <td class="px-4 py-2">
                            <input type="number" min="1"
                                wire:model="groupingData.selected.{{ $catId }}.priority"
                                class="w-20 rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center">
                            @error("groupingData.selected.$catId.priority")
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Submit Button --}}
    <div class="mt-6">
        <button wire:click="save"
            class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl shadow hover:bg-indigo-700 transition">
            Submit
        </button>
    </div>

      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        document.addEventListener("DOMContentLoaded", function() {

             Livewire.on('created', () => {
                Swal.fire({
                    title: 'Success!',
                    text: 'Location Created successfully.',
                    icon: 'success',
                    // confirmButtonText: 'OK'
                }).then((result) => {
                    // if (result.isConfirmed) {
                        window.location.reload(); // Refresh the page when OK is clicked
                    // }
                });
    });
        });
    </script>
</div>
