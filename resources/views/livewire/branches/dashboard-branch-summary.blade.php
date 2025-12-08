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

               
                <div>
                    <h3 class="font-semibold mb-2">Location</h3>
                    <label class="block mb-2 font-medium text-blue-600">
                       <input type="checkbox" class="mr-2" wire:click="toggleAllLocation" {{ $selectedAllLocation ? 'checked' : ''}}> Select All
                    </label>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                         @foreach($allLocation as $location)
                            <label class="block">
                                <input type="checkbox" wire:model.live="selectedLocation" class="mr-2" value="{{ $location->id }}"> {{ $location->location_name }}
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

            <!-- Charts -->
            
           <div class="grid grid-cols-1 gap-6 mb-6">
                <!-- Guests Served Pie -->
                 <div class="bg-white rounded-xl shadow p-4 w-full">
                    <h4 class="text-lg font-bold mb-2">Guests Served by Office</h4>
                   

                    <div class="relative w-[400px] h-[400px]">
                    <canvas id="employeeBarChart" class="absolute top-0 left-0 w-full h-full"></canvas>
                </div>
                </div>

              
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Average Waiting Time -->
                <div class="bg-white rounded-xl shadow p-4">
                    <h4 class="text-lg font-bold mb-2">Average Waiting Time</h4>
                     <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="p-2">Office</th>
                            <th class="p-2">AWT</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                         @foreach($avgWaitingTimes as $entry)
                    <tr class="border-b">
                        <td class="p-2">{{ $entry['office'] }}</td>
                        <td class="p-2">{{ $entry['time'] }}</td>
                    </tr>
                @endforeach
                    </tbody>
                </table>
                </div>

                <!-- Average Transaction Time -->
                <div class="bg-white rounded-xl shadow p-4">
                    <h4 class="text-lg font-bold mb-2">Average Transaction Time</h4>
                     <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="p-2">Office</th>
                            <th class="p-2">ATT</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($avgTransactionTimes as $entry)
                    <tr class="border-b">
                        <td class="p-2">{{ $entry['office'] }}</td>
                        <td class="p-2">{{ $entry['time'] }}</td>
                    </tr>
                @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Rating Doughnut -->
              <!-- Feedback by Office -->
               <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow p-4">
                        <h4 class="ext-lg font-bold mb-2">Received Feedback by Office</h4>
                        <canvas id="feedbackStaffChart" height="260"></canvas>
                    </div>

                <div class="bg-white rounded-xl shadow p-4">
                    <h4 class="ext-lg font-bold mb-2">Average Rating Percentage by Office</h4>
                    <canvas id="ratingPieChart" height="260"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
Chart.register(ChartDataLabels);

let employeeBarChart, feedbackStaffChart, ratingPieChart;

function drawAllCharts(data) {
    employeeBarChart?.destroy();
    feedbackStaffChart?.destroy();
    ratingPieChart?.destroy();

    // 🍰 Guests Served Pie Chart
    employeeBarChart = new Chart(document.getElementById('employeeBarChart'), {
        type: 'pie',
        data: {
            labels: data.chartLabels,
            datasets: [{
                label: 'Queues Served',
                data: data.chartData,
                backgroundColor: generateUniqueColors(data.chartData.length)
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            return `${label}: ${value}`;
                        }
                    }
                },
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 15
                    }
                }
            }
        }
    });

    // 📊 Feedback Horizontal Bar
    const maxFeedback = Math.max(...data.feedbackCounts); // Get max value for scale

feedbackStaffChart = new Chart(document.getElementById('feedbackStaffChart'), {
    type: 'bar',
    data: {
        labels: data.feedbackLabels,
        datasets: [{
            label: 'Feedback Count',
            data: data.feedbackCounts,
            backgroundColor: "#4ade80"
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return `${context.label}: ${context.raw}`;
                    }
                }
            },
            datalabels: {
                anchor: 'end',
                align: 'end',
                formatter: (value) => value,
                color: '#000',
                font: {
                    weight: 'bold'
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                max: maxFeedback + 10, // Optional buffer space
                ticks: {
                    callback: value => `${value}`
                }
            }
        }
    },
    plugins: [ChartDataLabels]
});

    // 📈 Rating Percentage Horizontal Bar
    ratingPieChart = new Chart(document.getElementById('ratingPieChart'), {
        type: 'bar',
        data: {
            labels: data.ratingLabels,
            datasets: [{
                label: 'Rating %',
                data: data.ratingPercentages,
                // backgroundColor: generateUniqueColors(data.ratingPercentages.length)
                   backgroundColor: "#4ade80"
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (value) => `${value}%`,
                    color: '#000',
                    font: {
                        weight: 'bold'
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: value => `${value}%`
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

// 🌈 Color Generator
function generateUniqueColors(count) {
    const colors = [
        '#60a5fa', '#fcd34d', '#34d399', '#f87171', '#a78bfa', '#fb923c', '#4ade80',
        '#f472b6', '#facc15', '#c084fc', '#38bdf8', '#fb7185', '#14b8a6', '#e879f9',
        '#6366f1', '#10b981', '#fde68a', '#3b82f6', '#eab308', '#f43f5e', '#22d3ee',
        '#818cf8', '#eab308', '#6ee7b7', '#cbd5e1', '#fbbf24', '#f9a8d4', '#7dd3fc',
        '#fca5a5', '#a5b4fc', '#5eead4', '#fef08a', '#d8b4fe', '#fbcfe8', '#a3e635',
        '#67e8f9', '#f87171', '#2dd4bf', '#e0f2fe', '#bbf7d0', '#fef3c7', '#c7d2fe',
        '#e5e7eb', '#fecaca', '#fde68a', '#e0e7ff', '#d1fae5', '#fee2e2', '#fcd34d',
        '#bfdbfe'
    ];
    while (colors.length < count) colors.push(...colors);
    return colors.slice(0, count);
}

// 🚀 Livewire listener
Livewire.on('refreshCharts', chartData => {
    drawAllCharts(chartData[0]);
});
</script>
@endpush

