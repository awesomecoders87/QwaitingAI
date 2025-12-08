<div>

    <main>
        <div class="mx-auto max-w-screen-2xl p-4 md:p-6">

            <h1 class="text-2xl font-bold mb-4">Overall Overview Report</h1>

            <style>
            .level-selector {
                align-items: baseline;
            }

            .download-button {
                width: 100px;
                height: 40px;
                background: black;
                margin: 5px;
            }

            .mx-1 {
                margin: 0 10px;
            }

            .mx-2 {
                margin: 0 20px;
            }
            </style>

            <div class="grid grid-col-1 md:grid-cols-4 gap-3 pb-3 md:space-y-3 md:flex-row md:space-x-4 level-selector">
                <!-- Level 1 Category -->
                <div class="w-full">
                    <select wire:model.live="selectedLevel1"
                        class="w-full p-2 border rounded-md border-gray-500 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categoriesList as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Status Filter -->
                <div class="w-full" wire:ignore>
                    <select multiple wire:model.live="status" class="border rounded p-2  multiple  w-full" id="status">
                        <option value="Skip">{{ __('text.Missed') }}</option>
                        <option value="Pending">{{ __('text.Pending') }}</option>
                        <option value="Progress">{{ __('text.Progress') }}</option>
                        <option value="Close">{{ __('text.Close') }}</option>
                        <option value="Cancelled">{{ __('text.Cancelled') }}</option>
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div class="flex gap-4">
                    <input type="date" wire:model.live="startDate"
                        class="p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Start Date" onclick="this.showPicker()">
                    <input type="date" wire:model.live="endDate"
                        class="p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="End Date" onclick="this.showPicker()">
                </div>


                <!-- PDF Button -->

                <!-- <div class="flex justify-end mb-6 md:w-2/5">
                    <button wire:click="downloadCsv"
                        class="mx-2 text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        Export CSV
                    </button>
                    <button wire:click="downloadPdf"
                        class="text-theme-sm shadow-theme-xs inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        Export PDF
                    </button>
                </div> -->
            </div>

            <!-- Loading Indicator -->
            <div wire:loading class="text-center mt-2 text-blue-600">
                Loading...
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="table-auto w-full  ti-custom-table ti-custom-table-hover">
                    <thead class="">
                        <tr>

                            <th class="px-4 py-2  "><?php echo __('Branch'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Arrived'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Pending'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Served'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Cancelled'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('No Show'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Workload'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Average'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Max'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('< SL'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('< SL %'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('> SL'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('> SL %'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Average'); ?></th>
                            <th class="px-4 py-2  "><?php echo __('Max'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($categories))
               
                        <?php  $lastBranchId = null; ?>
                        @foreach($categories as $key=>$row)
                      
                            <td class=" "><?php echo $row['location']['location_name'] ?? '';?></td>
                            <td class=" "><?php echo $row['total_calls'] ?? '';?></td>
                            <td class=" "><?php echo $row['pending_calls'];?></td>
                            <td class=" "><?php echo $row['pending_percentage'];?></td>
                            <td class=" "><?php echo $row['served_calls'];?></td>
                            <td class=" "><?php echo $row['served_percentage'];?></td>
                            <td class=" "><?php echo $row['cancel_calls'];?></td>
                            <td class=" "><?php echo $row['cancel_percentage'];?></td>
                            <td class=" "><?php echo $row['no_show'];?></td>
                            <td class=" "><?php echo $row['no_show_percentage'];?></td>
                            <td class=" "><?php echo $row['total_served_time'];?></td>
                            <td class=" "><?php echo $row['average_served_time'];?></td>
                            <td class=" "><?php echo $row['max_served_time'];?></td>
                            <td class=" "><?php echo $row['total_waiting_less_15_min'];?></td>
                            <td class=" ">
                                <?php 
							// Calculate percentage of waiting less than 15 minutes
							$waiting_less_15_min_percentage = ($row['total_calls'] > 0) ? 
								round(($row['total_waiting_less_15_min'] / $row['total_calls']) * 100, 2) . '%' : '0%'; 
							echo $waiting_less_15_min_percentage; 
							?>


                            </td>
                            <td class=" "><?php echo $row['total_waiting_greater_15_min'];?></td>
                            <td class=" ">

                                <?php 
							// Calculate percentage of waiting less than 15 minutes
							$waiting_greater_15_min_percentage = ($row['total_calls'] > 0) ? 
								round(($row['total_waiting_greater_15_min'] / $row['total_calls']) * 100, 2) . '%' : '0%'; 
							echo $waiting_greater_15_min_percentage; 
							?>

                            </td>
                            <td class=" "><?php echo $row['average_wait_time'];?></td>
                            <td class=" "><?php echo $row['max_waiting_time'];?></td>
                        </tr>



                        @endforeach
                    
                        @endif
                    </tbody>
                </table>
            </div>




        </div>


</div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $(document).ready(function() {
        // $('#statusFilter').select2({
        //     placeholder: "Select Status",
        //     allowClear: true
        // });

        $('#status').select2();

        // Listen for changes in status filter and pass them to Livewire
        $('#status').on('change', function() {
            let data = $(this).val();
            @this.set('status', data);
        });
    });
});
</script>
</div>