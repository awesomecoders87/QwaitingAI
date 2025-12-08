<?php

?>
<div class="flex gap-6 p-6">
    <!-- Filters Sidebar -->
    <div class="w-1/5 min-w-[220px]">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Filters</h2>
            <div class="grid grid-cols-1 gap-6 text-sm text-gray-800">
                <!-- Year Filter -->
                <div>
                    <h3 class="font-semibold mb-2">Year</h3>
                    <div class="space-y-2 max-h-32 overflow-y-auto">

                       @foreach($years as $y)
                        <label class="block">
                            <input type="radio" wire:model.live="year" value="{{ $y }}" class="mr-2"> {{ $y }}
                        </label>
                    @endforeach
                    </div>
                </div>

                <!-- Month Filter -->
                <div>
                    <h3 class="font-semibold mb-2">Month</h3>
                    <label class="block mb-2 font-medium text-blue-600">
                       <input type="checkbox" class="mr-2" wire:click="toggleAllMonths" {{ $selectedallMonth ? 'checked' : '' }}> Select All
                    </label>
                    <div class="space-y-1 max-h-40 overflow-y-auto">
                       @foreach(["01"=>"January","02"=>"February","03"=>"March","04"=>"April","05"=>"May","06"=>"June","07"=>"July","08"=>"August","09"=>"September","10"=>"October","11"=>"November","12"=>"December"] as $val => $month)
                            <label class="block">
                                <input type="checkbox" wire:model.live="months" class="mr-2" value="{{ $val }}" > {{ $month }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Staff Filter -->
                <div>
                    <h3 class="font-semibold mb-2">Staff</h3>
                    <label class="block mb-2 font-medium text-blue-600">
                       <input type="checkbox" class="mr-2" wire:click="toggleAllStaff" {{ $selectedAll ? 'checked' : ''}}> Select All
                    </label>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                         @foreach($staffData as $member)
                            <label class="block">
                                <input type="checkbox" wire:model.live="selectedStaff" class="mr-2" value="{{ $member->id }}"> {{ $member->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Area -->
    <div class="w-4/5">
        <!-- Header Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-100 text-blue-900 p-4 rounded shadow">
                <h4 class="text-sm font-medium">Overall Guest Served</h4>
                <p class="text-2xl font-bold">{{ $totalServedQueues }}</p>
            </div>
            <div class="bg-green-100 text-green-900 p-4 rounded shadow">
                <h4 class="text-sm font-medium">Overall Avg Trx Time</h4>
                <p class="text-2xl font-bold">{{ $formattedServedTime }}</p>
            </div>
            <div class="bg-green-200 text-green-900 p-4 rounded shadow">
                <h4 class="text-sm font-medium">Overall Avg Waiting Time</h4>
                <p class="text-2xl font-bold">{{ $formattedWaitingTime }}</p>
            </div>
            <div class="bg-blue-200 text-blue-900 p-4 rounded shadow">
                <h4 class="text-sm font-medium">Received Feedback</h4>
                <p class="text-2xl font-bold">{{ $feedbackCount }}</p>
            </div>
            <div class="bg-green-100 text-green-900 p-4 rounded shadow">
                <h4 class="text-sm font-medium">Avg Rating</h4>
                <p class="text-2xl font-bold">{{ $avgRating }}%</p>
            </div>
        </div>

        <!-- Staff Transaction Table and Graph -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded shadow">
                <h4 class="font-semibold mb-2">Employees Avg Transaction Time</h4>
                <canvas id="employeeBarChart" wire:key="bar-chart"></canvas>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h4 class="font-semibold mb-2">Employees Average Transaction Time & Rank</h4>
                  <div class="overflow-x-auto max-h-64">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="p-2">Employee Name</th>
                            <th class="p-2">Average Transaction Time</th>
                            {{-- <th class="p-2">Rank</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($avgTxnTimePerStaff))
                        @php $key= 1; @endphp
                        @foreach($avgTxnTimePerStaff as $index => $avgtime)
                            <tr class="border-b">
                                <td class="p-2">{{ $index }}</td>
                                <td class="p-2">{{ $avgtime  }}</td>
                                {{-- <td class="p-2">{{ $key++ }}</td> --}}
                            </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        <!-- Line Chart -->
        <div class="bg-white p-4 mt-6 rounded shadow">
            <h4 class="font-semibold mb-2">Daily Feedback and Guest Served</h4>
           
            <canvas id="feedbackLineChart" wire:key="line-chart"></canvas>
        </div>

        <!-- Bottom Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="bg-white p-4 rounded shadow">
                <h4 class="font-semibold mb-2">Received Feedback by Staff</h4>
               <canvas id="feedbackStaffChart" wire:key="staff-chart"></canvas>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h4 class="font-semibold mb-2">Feedback Rating Classification</h4>
               <canvas id="ratingPieChart" wire:key="pie-chart"></canvas>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

let employeeBarChart;
let feedbackLineChart;
let feedbackStaffChart;
let ratingPieChart;

    function drawAllCharts(chartData) {
        // Destroy existing charts if they exist
        employeeBarChart?.destroy();
        feedbackLineChart?.destroy();
        feedbackStaffChart?.destroy();
        ratingPieChart?.destroy();

        // Redraw with updated chartData passed from Livewire
        employeeBarChart = new Chart(document.getElementById('employeeBarChart'), {
            type: 'bar',
            data: {
                labels: chartData.chartLabels,
                datasets: [{
                    label: 'Queues Served',
                    data: chartData.chartData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                }]
            }
        });

        
        feedbackLineChart = new Chart(document.getElementById('feedbackLineChart'), {
            type: 'line',
            data: {
                labels: chartData.dailyLabels,
                datasets: [
                   
                    {
                        label: 'Guests Served',
                        data: chartData.dailyServed,
                        borderColor: 'blue',
                        fill: false
                    }, {
                        label: 'Feedback Received',
                        data: chartData.dailyFeedbacks,
                        borderColor: 'green',
                        fill: false
                    },
                ]
            }
        });

        feedbackStaffChart = new Chart(document.getElementById('feedbackStaffChart'), {
            type: 'bar',
            data: {
                labels: chartData.feedbackStaffLabels,
                datasets: [{
                    label: 'Feedback',
                    data: chartData.feedbackStaffCounts,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)'
                }]
            }
        });

        ratingPieChart = new Chart(document.getElementById('ratingPieChart'), {
            type: 'doughnut',
            data: {
                labels: ['4 Stars', '3 Stars', '2 Stars', '1 Star'],
                datasets: [{
                    data: chartData.ratingCounts,
                    backgroundColor: ['#4ade80', '#facc15', '#f97316', '#ef4444']
                }]
            }
        });
    }

   
    Livewire.on('refreshCharts', chartData => {
        drawAllCharts(chartData[0]);
    });
</script>
@endpush

