<div>

    <main>
        <div class="mx-auto p-4 md:p-4">

            <h1 class="text-xl font-semibold dark:text-white/90 mb-4">{{ __('report.Sub Services Report') }}</h1>
            <style>
            .level-selector {
                align-items: start;
            }

            .download-button {
                width: 100px;
                height: 40px;
                background: black;
                margin: 5px;
            }
            </style>
            <div class="flex flex-col space-y-2 md:space-y-0 md:flex-row md:space-x-2 level-selector">
                <!-- Level 1 Category -->
                <div class="w-full md:w-1/5">
                    <select wire:model.live="selectedLevel1"
                        class="w-full p-2 border bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">{{ __('report.All Services') }}</option>
                        @foreach($categoriesList as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Level 2 Category -->
                @if($selectedLevel1)
                <div class="w-full md:w-1/5">
                    <select wire:model.live="selectedLevel2"
                        class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">{{ __('report.All Services') }}</option>
                        @foreach(App\Models\Category::withTrashed()->where('parent_id', $selectedLevel1)->get() as $subcategory)
                        <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Level 3 Category -->
                @if($selectedLevel2)
                <div class="w-full md:w-1/5">
                    <select wire:model.live="selectedLevel3"
                        class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">{{ __('report.All Services') }}</option>
                        @foreach(App\Models\Category::withTrashed()->where('parent_id', $selectedLevel2)->get() as $subsubcategory)
                        <option value="{{ $subsubcategory->id }}">{{ $subsubcategory->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <!-- </div>



    <div class="w-full flex flex-col space-y-4 md:space-y-0 md:flex-row md:space-x-4 mb-6"> -->

                <!-- <div class="w-full md:w-1/5 mx-1">
        <select wire:model.live="selectedStatuses" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" multiple="multiple" id="statusFilter">
            <option value="All">All</option>
            <option value="Pending">Pending</option>
            <option value="Close">Close</option>
            <option value="Skip">Skip</option>
            <option value="Move">Move</option>
            <option value="Cancelled">Cancelled</option>
        </select>
    </div> -->

                <!-- Status Filter -->
                <div class="w-full md:w-1/5" wire:ignore>
                    <select multiple wire:model.live="status" class="border rounded p-2  multiple  w-full dark:border-gray-600 dark:bg-gray-800 dark:text-white" id="status">
                        <option value="Skip">{{ __('report.Missed') }}</option>
                        <option value="Pending">{{ __('report.Pending') }}</option>
                        <option value="Progress">{{ __('report.Progress') }}</option>
                        <option value="Close">{{ __('report.Close') }}</option>
                        <option value="Cancelled">{{ __('report.Cancelled') }}</option>
                    </select>
                </div>
                <!-- Date Range Filters -->
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <input type="date" wire:model.live="startDate"
                        class="p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="Start Date" onclick="this.showPicker()">
                    <input type="date" wire:model.live="endDate"
                        class="p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="End Date" onclick="this.showPicker()">
                </div>

                <!-- Search Input -->
                <!-- <div class="w-1/8 mt-4 md:mt-0 mx-1">
            <input type="text" wire:model.live="searchTerm" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mx-1" placeholder="Search...">
        </div> -->


                <!-- PDF Button -->


                <div class="flex justify-end gap-x-2 mb-6 md:w-2/5">
                    <button wire:click="downloadCsv"
                        class="px-3 py-2 font-medium text-white transition-colors rounded-md bg-brand-500 hover:bg-brand-600 flex gap-x-2"><i class="ri-file-list-3-line"></i> 
                        {{ __('report.Export CSV') }}
                    </button>
                    <button wire:click="downloadPdf"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-500 bg-white px-3 py-2 font-medium text-gray-700 hover:bg-gray-900 hover:text-white hover:border-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"><i class="ri-file-pdf-2-line"></i> 
                        {{ __('report.Export PDF') }}
                    </button>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div wire:loading class="text-center mt-2 text-blue-600">
                Loading...
            </div>

            <!-- Table Section -->
             <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow">
                <div class="max-w-full overflow-x-auto">
                <table class="table-auto w-full border-collapse">
                    <thead>

                        <tr>

                            <th class="px-4 py-2 ">{{ $level1 }}</th>
                            <th class="px-4 py-2 ">{{ $level2 }}</th>
                            <th class="px-4 py-2 ">{{ $level3 }}</th>
                            <th class="px-4 py-2 "><?php echo __('report.Arrived'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Pending'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Served'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Cancelled'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.No Show'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.%'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Workload'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Average'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Max'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.< SL'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.< SL %'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.> SL'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.> SL %'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Average'); ?></th>
                            <th class="px-4 py-2 "><?php echo __('report.Max'); ?></th>
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
                            <td class="">{{ $row->category->name ?? 'N/A' }}</td>
                            <td class="">{{ $row->subCategory->name ?? 'N/A' }}</td>
                            <td class="">{{ $row->childCategory->name ?? 'N/A' }}</td>
                            <td class=""><?php echo $row['total_calls'];?></td>
                            <td class=""><?php echo $row['pending_calls'];?></td>
                            <td class=""><?php echo number_format($row['pending_percentage'],2);?></td>
                            <td class=""><?php echo $row['served_calls'];?></td>
                            <td class=""><?php echo number_format($row['served_percentage'],2);?></td>
                            <td class=""><?php echo $row['cancel_calls'];?></td>
                            <td class=""><?php echo number_format($row['cancel_percentage'],2);?></td>
                            <td class=""><?php echo $row['no_show'];?></td>
                            <td class=""><?php echo number_format($row['no_show_percentage'],2);?></td>
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
                            <td class="" colspan='3'><strong><?php echo __('Total'); ?></strong></td>
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