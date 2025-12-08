<div>

    <main>
        <div class="mx-auto max-w-screen-2xl p-4 md:p-6">

            <h1 class="text-2xl font-bold mb-4">Queue Overview Report</h1>

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

            <div class="flex flex-col space-y-4 md:space-y-0 md:flex-row md:space-x-4 level-selector">
                <!-- Level 1 Category -->
                <div class="w-full md:w-1/5 pr-1">
                    <select wire:model.live="selectedLevel1"
                        class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categoriesList as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Status Filter -->
                <div class="w-full md:w-1/5 mx-1" wire:ignore>
                    <select multiple wire:model.live="status" class="border rounded p-2  multiple  w-full" id="status">
                        <option value="Skip">{{ __('text.Missed') }}</option>
                        <option value="Pending">{{ __('text.Pending') }}</option>
                        <option value="Progress">{{ __('text.Progress') }}</option>
                        <option value="Close">{{ __('text.Close') }}</option>
                        <option value="Cancelled">{{ __('text.Cancelled') }}</option>
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div class="flex space-x-4 mt-4 md:mt-0 mx-1">
                    <input type="date" wire:model.live="startDate"
                        class="p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mx-1"
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
                <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
                    <thead class="">
                        <tr>

                            <th class="px-4 py-2 "><?php echo __('Branch'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Queue'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Arrived'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Pending'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Served'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Cancelled'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('No Show'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Workload'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Average'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Max'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('< SL'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('< SL %'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('> SL'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('> SL %'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Average'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('Max'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($categories))
                        <?php 
					
					// Helper function to convert HH:MM:SS to seconds
					function timeToSeconds($time) {
						// Check if $time is in HH:MM:SS format
						$timeParts = explode(':', $time);
						
						// Ensure we have exactly 3 parts (hours, minutes, seconds)
						if (count($timeParts) === 3) {
							list($hours, $minutes, $seconds) = $timeParts;
							return ($hours * 3600) + ($minutes * 60) + $seconds;
						} else {
							// Handle cases where time format is invalid
							return 0; // or handle as needed, e.g., throw an error or return null
						}
					}
					
					function secondsToTime($seconds){

						// Calculate hours, minutes, and seconds
						$hours = floor($seconds / 3600);
						$minutes = floor(($seconds % 3600) / 60);
						$seconds = $seconds % 60;

						// Format to HH:ii:ss
						$time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
						return $time; // Outputs: 375:18:01
						
					}
					
					$totals = [
						'total_calls' => 0,
						'pending_calls' => 0,
						'pending_percentage_total' => 0,
						'cancel_calls' => 0,
						'cancel_percentage_total' => 0,
						'served_calls' => 0,
						'served_percentage_total' => 0,
						'no_show' => 0,
						'no_show_percentage_total' => 0,
						'total_served_time' => 0,
						'average_served_time' => 0,
						'max_served_time' => 0,
						'total_waiting_less_15_min' => 0,
						'total_waiting_greater_15_min' => 0,
						'average_wait_time' => 0,
						'max_waiting_time' => 0
					];
					
					 foreach ($categories as $row) { 
						$total_calls = $row['total_calls'];
						$served_calls = $row['served_calls'];
						$cancel_calls = $row['cancel_calls'];
						$pending_calls = $row['pending_calls'];
						$served_percentage = ($total_calls > 0) ? ($served_calls / $total_calls) * 100 : 0;
						$no_show_percentage = rtrim($row['no_show_percentage'], '%');

						$totals['total_calls'] += $total_calls;
						$totals['served_calls'] += $served_calls;
						$totals['cancel_calls'] += $cancel_calls;
						$totals['pending_calls'] += $pending_calls;
						$totals['served_percentage_total'] += $served_percentage;
						$totals['no_show'] += $row['no_show'];
						$totals['no_show_percentage_total'] += (float)$no_show_percentage;
						$totals['total_waiting_less_15_min'] += $row['total_waiting_less_15_min'];
						$totals['total_waiting_greater_15_min'] += $row['total_waiting_greater_15_min'];

						// Convert the served time to seconds
						$served_time_seconds = !empty($row['total_served_time']) ? timeToSeconds($row['total_served_time']) : 0;
						$totals['total_served_time'] += $served_time_seconds;
				
						
						// Convert average wait time to seconds and accumulate
						if ($row['average_served_time']) {
							$parts1 = explode(':', $row['average_served_time']);
							$current_served_time_seconds = ($parts1) ? ($parts1[0] * 3600) + ($parts1[1] * 60) + $parts1[2] : 0;
							$totals['average_served_time'] += $current_served_time_seconds;
						}
						
						$current_max_served_time = strtotime($row['max_served_time']) - strtotime('TODAY');
						// Update the max_waiting_time only if the current row has a higher value
						if ($current_max_served_time > $totals['max_served_time']) {
							$totals['max_served_time'] = $current_max_served_time;
						}
						
				
						
						// Convert average wait time to seconds and accumulate
						if ($row['average_wait_time']) {
							$parts = explode(':', $row['average_wait_time']);
							$current_wait_time_seconds = ($parts) ? ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2] : 0;
							$totals['average_wait_time'] += $current_wait_time_seconds;
						}
						
		
						$current_max_waiting_time = strtotime($row['max_waiting_time']) - strtotime('TODAY');
						// Update the max_waiting_time only if the current row has a higher value
						if ($current_max_waiting_time > $totals['max_waiting_time']) {
							$totals['max_waiting_time'] = $current_max_waiting_time;
						}
					} 
					
							
					?>


                        <?php  $lastBranchId = null; ?>
                        @foreach($categories as $key=>$row)
                        <tr $key>
                            <td class="">
                            @if(isset($row['category']['category_locations']) && is_array($row['category']['category_locations']))
                                @php
                                    $locationIds = $row['category']['category_locations'];
                                @endphp

                                @foreach ($locationIds as $id)
                                    {{ $locations[$id] ?? 'Unknown Location' }}
                                    @if (!$loop->last), @endif
                                @endforeach
                            @else
                                N/A
                            @endif
                        </td>
                            <td class=""><?php echo $row['category']['name'] ?? '';?></td>
                            <td class=""><?php echo $row['total_calls'] ?? '';?></td>
                            <td class=""><?php echo $row['pending_calls'];?></td>
                            <td class=""><?php echo $row['pending_percentage'];?></td>
                            <td class=""><?php echo $row['served_calls'];?></td>
                            <td class=""><?php echo $row['served_percentage'];?></td>
                            <td class=""><?php echo $row['cancel_calls'];?></td>
                            <td class=""><?php echo $row['cancel_percentage'];?></td>
                            <td class=""><?php echo $row['no_show'];?></td>
                            <td class=""><?php echo $row['no_show_percentage'];?></td>
                            <td class=""><?php echo $row['total_served_time'];?></td>
                            <td class=""><?php echo $row['average_served_time'];?></td>
                            <td class=""><?php echo $row['max_served_time'];?></td>
                            <td class=""><?php echo $row['total_waiting_less_15_min'];?></td>
                            <td class="">
                                <?php 
							// Calculate percentage of waiting less than 15 minutes
							$waiting_less_15_min_percentage = ($row['total_calls'] > 0) ? 
								round(($row['total_waiting_less_15_min'] / $row['total_calls']) * 100, 2) . '%' : '0%'; 
							echo $waiting_less_15_min_percentage; 
							?>


                            </td>
                            <td class=""><?php echo $row['total_waiting_greater_15_min'];?></td>
                            <td class="">

                                <?php 
							// Calculate percentage of waiting less than 15 minutes
							$waiting_greater_15_min_percentage = ($row['total_calls'] > 0) ? 
								round(($row['total_waiting_greater_15_min'] / $row['total_calls']) * 100, 2) . '%' : '0%'; 
							echo $waiting_greater_15_min_percentage; 
							?>

                            </td>
                            <td class=""><?php echo $row['average_wait_time'];?></td>
                            <td class=""><?php echo $row['max_waiting_time'];?></td>
                        </tr>



                        @endforeach
                        <tr>
                            <td class="" colspan="2"><strong><?php echo __('Total'); ?></strong></td>
                            <td class="">
                                <strong><?php echo htmlspecialchars($totals['total_calls']); ?></strong></td>
                            <td class="">
                                <strong><?php echo htmlspecialchars($totals['pending_calls']); ?></strong></td>
                            <td class=""><strong>
                                    <?php 
							// Calculate the served percentage based on the total and served calls
							$pending_percentage_total = ($totals['total_calls'] > 0) ? 
                            ($totals['pending_calls'] / $totals['total_calls']) * 100 : 0;
							echo round($pending_percentage_total, 2) . '%'; 
							?>
                                </strong></td>
                            <td class="">
                                <strong><?php echo htmlspecialchars($totals['served_calls']); ?></strong></td>
                            <td class=""><strong><?php 
							// Calculate the served percentage based on the total and served calls
							$served_percentage_total = ($totals['total_calls'] > 0) ? 
								($totals['served_calls'] / $totals['total_calls']) * 100 : 0;
							echo round($served_percentage_total, 2) . '%'; 
							 ?></strong></td>
                            <td class="">
                                <strong><?php echo htmlspecialchars($totals['cancel_calls']); ?></strong></td>
                            <td class=""><strong><?php 
							// Calculate the served percentage based on the total and served calls
							$served_percentage_total = ($totals['total_calls'] > 0) ? 
								($totals['cancel_calls'] / $totals['total_calls']) * 100 : 0;
							echo round($served_percentage_total, 2) . '%'; 
							 ?></strong></td>
                            <td class=""><strong><?php echo htmlspecialchars($totals['no_show']); ?></strong>
                            </td>
                            <td class=""><strong><?php 
							// Calculate the served percentage based on the total and served calls
							$served_percentage_total = ($totals['total_calls'] > 0) ? 
                            ($totals['no_show'] / $totals['total_calls']) * 100 : 0;
							echo round($served_percentage_total, 2) . '%'; 
                            ?></strong></td>
                            <td class="">
                                <strong><?php echo secondsToTime($totals['total_served_time']); ?></strong></td>
                            <td class="">
                                <strong><?php echo gmdate("H:i:s", $totals['average_served_time'] / max(count($categories), 1)); ?></strong>
                            </td>
                            <td class=""><strong class="<?php echo $totals['max_served_time']; ?>">
                                    <?php echo gmdate("H:i:s", $totals['max_served_time']); ?></strong></td>
                            <td class="">
                                <strong><?php echo  htmlspecialchars($totals['total_waiting_less_15_min']); ?></strong>
                            </td>
                            <td class=""><strong>
                                    <?php 
                            $waiting_less_15_min_percentage_total = ($totals['total_calls'] > 0) ? 
                            round(($totals['total_waiting_less_15_min'] / $totals['total_calls']) * 100, 2) . '%' : '0%'; 
							echo $waiting_less_15_min_percentage_total;  
                            ?>
                                </strong></td>
                            <td class="">
                                <strong><?php echo htmlspecialchars($totals['total_waiting_greater_15_min']); ?></strong>
                            </td>
                            <td class=""><strong>
                                    <?php 
							// Calculate percentage of waiting greater than 15 minutes
							$waiting_greater_15_min_percentage_total = ($totals['total_calls'] > 0) ? 
								round(($totals['total_waiting_greater_15_min'] / $totals['total_calls']) * 100, 2) . '%' : '0%'; 
							echo $waiting_greater_15_min_percentage_total; 
							?>
                                </strong></td>
                            <td class="">
                                <strong><?php echo gmdate("H:i:s", $totals['average_wait_time'] / max(count($categories), 1)); ?></strong>
                            </td>
                            <td class="">
                                <strong><?php echo gmdate("H:i:s", $totals['max_waiting_time']); ?></strong></td>
                        </tr>

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