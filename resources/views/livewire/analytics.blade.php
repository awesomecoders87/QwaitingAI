<?php use Illuminate\Support\Str; ?>

<div class="p-6 space-y-6 bg-gray-50 min-h-screen text-gray-800 dark:bg-transparent">
    
<div class="rounded-xl border border-yellow-300 bg-yellow-50 p-4 shadow-sm flex items-start space-x-4">
    <svg class="w-6 h-6 text-yellow-500 mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z"></path>
    </svg>
    <div>
      <p class="font-semibold text-yellow-800">{{ __('report.Report Access Notice') }}</p>
      <p class="text-sm text-yellow-700">
        {{ __('report.This report is available for') }} <span class="font-medium"> {{ __('report.free until') }} August 31, 2025</span>.  {{ __('report.After that, access will be limited to') }} <span class="font-medium">{{ __('report.Premium users only') }}</span>.
      </p>
    </div>
  </div>

    <!-- Navigation Tabs -->
    <div class="flex space-x-8 border-b pb-2">
        @foreach (['Overview', 'Visit', 'Services', 'Operations', 'Messages', 'Guest Experience'] as $tab)
            <button wire:click="setTab('{{ $tab }}')"
                class="{{ $activeTab === $tab ? 'text-violet-600 border-b-2 border-violet-600' : 'text-gray-400 hover:text-black dark:hover:text-white' }} font-semibold text-sm pb-2 transition">
                {{ $tab }}
            </button>
        @endforeach
    </div>
    @if ($activeTab == 'Overview')
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">

            @foreach ($cards as $card)
                <div class="bg-white p-4 rounded-xl shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                    <div class="text-xs font-semibold text-gray-500 dark:text-white">{{ $card['title'] }}</div>
                    <div class="text-lg font-bold">{{ $card['value'] }}</div>
                     <div class="text-sm font-semibold {{ str_replace('%', '', $card['change']) < 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $card['change'] }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ $card['sub'] }}</div>
                </div>
            @endforeach
        </div>

        <!-- Traffic and Customer Path Charts -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Traffic Chart -->
            <div class="bg-white p-6 rounded-xl shadow dark:bg-white/[0.03] dark:text-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Traffic</h3>
                    <select class="text-xs border rounded px-2 py-1" wire:model.live="overview_traffic_timePeriod">
                        <option value="today">Today</option>
                        <option value="hour">Hour</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
                <canvas id="trafficChart" class="h-40 w-full"></canvas>
            </div>

            <!-- Customer Path Chart -->
            <div class="bg-white p-6 rounded-xl shadow  dark:bg-white/[0.03] dark:text-white">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 dark:text-white">Customer Path</h3>

                <!-- Summary Data -->
                <div class="flex justify-between text-center text-xs font-medium text-gray-600 mb-4  dark:text-white">
                    <div>
                        <div class="text-lg font-bold">
                            {{ number_format($customerPathData['summary']['registration'] ?? 0) }}</div>
                        <div>Registration</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold">{{ number_format($customerPathData['summary']['served'] ?? 0) }}
                        </div>
                        <div>Served</div>
                    </div>
                    {{-- <div>
                    <div class="text-lg font-bold">{{ number_format($customerPathData['summary']['completed'] ?? 0) }}</div>
                    <div>Completed</div>
                </div> --}}
                </div>

                <!-- Chart Canvas -->
                <canvas id="customerPathChart" class="h-40 w-full"></canvas>
            </div>
        </div>

        <!-- Top Visited Locations Table -->
         <div class="p-4 md:p-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow dark:text-white">
                
            <h3 class="text-sm font-semibold text-gray-700 mb-4 dark:text-white">Top Visited Locations</h3>
            <div class="max-w-full overflow-x-auto">
                <table class="w-full table-auto mb-4 table-auto w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="px-2 py-2">Location</th>
                            <th class="px-2 py-2">Visits</th>
                            <th class="px-2 py-2">Waitlisted</th>
                            <th class="px-2 py-2">Bookings</th>
                            <th class="px-2 py-2">Served</th>
                            <th class="px-2 py-2">Dropoff</th>
                            <th class="px-2 py-2">Hold</th>
                            <th class="px-2 py-2">Avg Wait</th>
                            <th class="px-2 py-2">Avg Serve Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topVisitedLocations as $location)
                            <tr class="border-b">
                                <td class="px-2 py-2 font-semibold  dark:text-white">{{ $location['location'] }}</td>

                                @foreach (['visits', 'waitlisted', 'bookings', 'served', 'dropoff', 'hold', 'avg_wait', 'avg_serve'] as $field)
                                    @php
                                        $value = $location[$field];
                                        $colorClass = Str::contains($value, '↑')
                                            ? 'text-green-600'
                                            : (Str::contains($value, '↓')
                                                ? 'text-red-500'
                                                : 'text-gray-700');
                                    @endphp
                                    <td class="px-2 py-2 {{ $colorClass }}  dark:text-white">{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    <!-- Visit Tab Content -->
  @if ($activeTab === 'Visit')
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Customer Distribution Donut -->
            <div class="bg-white rounded-xl p-4 shadow flex flex-col h-full dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <h3 class="text-sm font-semibold text-gray-700 mb-2 dark:text-white">
                    Customer distribution <span class="text-xs text-gray-400">ⓘ</span>
                </h3>

                <!-- Legends -->
                <div class="flex space-x-4 text-xs mb-2">
                    <div class="flex items-center space-x-1">
                        <span class="w-3 h-3 rounded-sm inline-block bg-[#6366f1]"></span>
                        <span class="text-gray-600">New</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="w-3 h-3 rounded-sm inline-block bg-[#86efac]"></span>
                        <span class="text-gray-600">Returning</span>
                    </div>
                </div>

                <!-- Chart -->
                <div class="w-full h-64 relative">
                    <canvas id="customerDistributionChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Visits Distribution Heatmap -->
            <div class="bg-white rounded-xl p-4 shadow flex flex-col h-full dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">
                        Visits distribution <span class="text-xs text-gray-400">ⓘ</span>
                    </h3>

                    <!-- Filter Dropdown -->
                    <div class="relative text-xs text-gray-600">
                        <button wire:click="$toggle('showVisitFilter')" class="flex items-center gap-1">
                            {{ ucwords(str_replace('-', ' ', $visitTypeFilter)) }} ▼
                        </button>

                        @if ($showVisitFilter)
                            <div class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 shadow-md rounded z-10">
                                @foreach ([
                                    'all-visits' => 'All Visits',
                                    'bookings' => 'Bookings',
                                    'waitlisted' => 'Waitlisted',
                                    'dropoffs' => 'Dropoffs',
                                    'hold' => 'Hold',
                                    'served' => 'Served',
                                    'cancelled' => 'Cancelled',
                                ] as $key => $label)
                                    <button wire:click.prevent="setVisitTypeFilter('{{ $key }}')"
                                            class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Heatmap Grid -->
                <div class="overflow-x-auto">
                    <div class="grid grid-cols-[30px_repeat(24,20px)] auto-rows-[20px] gap-1">
                        <!-- Days -->
                        @foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $dayIndex => $day)
                            <div class="text-[10px] text-gray-400 text-center">{{ $day }}</div>
                            @for ($h = 0; $h < 24; $h++)
                                @php
                                    $matrix = $visitDistributionMatrix ?? [];
                                    $visitCount = $matrix[$dayIndex][$h] ?? 0;
                                    $intensity = match (true) {
                                        $visitCount > 15 => 'bg-indigo-900',
                                        $visitCount > 10 => 'bg-indigo-700',
                                        $visitCount > 5 => 'bg-indigo-400',
                                        $visitCount > 0 => 'bg-indigo-200',
                                        default => 'bg-gray-100 dark:bg-gray-700',
                                    };
                                @endphp
                                <div class="{{ $intensity }} w-5 h-5 rounded-sm"></div>
                            @endfor
                        @endforeach

                        <!-- Hour Labels -->
                        <div></div>
                        @for ($h = 0; $h < 24; $h++)
                            <div class="text-[10px] text-center text-gray-400 col-span-1">
                                {{ $h % 12 === 0 ? '12' : $h % 12 }}{{ $h < 12 ? 'AM' : 'PM' }}
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

        </div>
    </div>
@endif

    <!-- Services Tab Content -->
    @if ($activeTab === 'Services')
        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center">
                <div class="text-sm font-semibold text-gray-700 dark:text-white">
                    Service combinations <span class="text-xs text-gray-400">ⓘ</span>
                </div>

                <!-- Filter Dropdown -->
                <div class="relative text-xs text-gray-600">
                    <button wire:click="$toggle('showServiceFilter')"
                        class="flex items-center gap-1 focus:outline-none">
                        {{ ucwords(str_replace('-', ' ', $serviceVisitTypeFilter)) }} ▼
                    </button>

                    @if ($showServiceFilter)
                        <div class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 shadow-md rounded z-10 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @foreach ([
        'all-visits' => 'All Visits',
        'bookings' => 'Bookings',
        'waitlisted' => 'Waitlisted',
        'dropoffs' => 'Dropoffs',
        'hold' => 'Hold',
        'served' => 'Served',
        'cancelled' => 'Cancelled',
    ] as $key => $label)
                                <button wire:click.prevent="setServiceVisitTypeFilter('{{ $key }}')"
                                    class="block w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Legend (optional) -->
            <div class="flex flex-wrap gap-3 text-xs text-gray-600 mb-2 dark:text-white">
                @foreach ($serviceLegend ?? [] as $label => $color)
                    <div class="flex items-center space-x-1">
                        <span class="w-3 h-3 inline-block rounded-sm"
                            style="background-color: {{ $color }}"></span>
                        <span>{{ $label }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Chart Box -->
            <div class="bg-white rounded-xl shadow p-6 dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="h-64">
                    <canvas id="serviceCombinationChart"></canvas>
                </div>
            </div>
        </div>
    @endif

    <!-- Operations Tab Content -->
    @if ($activeTab === 'Operations')
        <div class="flex flex-wrap gap-6">
            <!-- Visits by Location -->
            <div class="bg-white p-4 rounded-xl shadow flex-1 min-w-[300px] max-w-[48%] dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Visits by Location</h3>
                    <select class="text-xs border rounded px-2 py-1 dark:border-gray-600 dark:bg-gray-800 dark:text-white" wire:model.live="locationVisitFilter">
                        <option value="all">All visits</option>
                        <option value="bookings">Bookings</option>
                        <option value="waitlisted">Waitlisted</option>
                        <option value="Dropoffs">Dropoffs</option>
                        <option value="hold">Hold</option>
                        <option value="served">served</option>
                        <option value="cancelled">cancelled</option>
                    </select>
                </div>
                <canvas id="opertaionVisitsByLocationChart" class="w-full h-40"></canvas>
            </div>

            <!-- Visits by User -->
            <div class="bg-white p-4 rounded-xl shadow flex-1 min-w-[300px] max-w-[48%] dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700">Served By Staff</h3>
                    {{-- 
            <select class="text-xs border rounded px-2 py-1" wire:model.live="userVisitFilter">
                <option value="all">All visits</option>
                <option value="served">Served</option>
                <option value="dropoff">Dropoff</option>
            </select> 
            --}}
                </div>
                <canvas id="userVisitsChart" class="w-full h-40"></canvas>
            </div>
        </div>
    @endif

    <!-- Messages Tab Content -->
    @if ($activeTab === 'Messages')
        <div class="p-6 bg-white rounded-xl shadow space-y-6  dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
            <!-- Message Metrics Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 text-center text-sm text-gray-700">
                @foreach ($messageMetrics as $metric)
                    <div>
                        <div class="text-xs text-gray-400 flex items-center justify-center space-x-1">
                            <span>{{ strtoupper($metric['title']) }}</span>
                            <span class="text-gray-300 cursor-help" title="Info">ⓘ</span>
                        </div>
                        <div class="mt-1 text-xl font-semibold text-black">{{ $metric['value'] }}</div>
                        <div class="text-xs text-red-500 font-semibold mt-1 flex items-center justify-center gap-1">
                            <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 011.08 1.04l-4.25 4.655a.75.75 0 01-1.08 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $metric['change'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Message Channel Bar Chart -->
            <div class="bg-white p-6 rounded-xl shadow dark:bg-white/[0.03]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Message details</h3>
                    {{-- <span class="text-xs text-gray-500">Channels ▼</span> --}}
                </div>
                <canvas id="messageChannelChart" class="h-56 w-full"></canvas>
            </div>
        </div>
    @endif
    <!-- Guest Experience Tab Content -->
    @if ($activeTab === 'Guest Experience')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Dropoff by Waitlist Position -->
            <div class="bg-white rounded-xl p-4 shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <h3 class="text-sm font-semibold text-gray-700 mb-2 dark:text-white">Dropoff by Waitlist Position</h3>
                <div class="w-full h-64">
                    <canvas id="dropoffWaitlistChart"></canvas>
                </div>
            </div>

            <!-- Donut Chart: Dropoffs -->
            <div class="bg-white rounded-xl p-4 shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <h3 class="text-sm font-semibold text-gray-700 mb-2 dark:text-white">Dropoff Types</h3>
                <div class="w-full h-64">
                    <canvas id="dropoffDonutChart"></canvas>
                </div>
            </div>

            <!-- Line Length Chart -->
            <div class="bg-white rounded-xl p-4 shadow md:col-span-2 dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Line Length</h3>
                    <select wire:model.live="lineGraphFilter"
                        class="text-xs border-gray-300 rounded px-2 py-1 focus:ring focus:ring-indigo-200 focus:outline-none">
                        <option value="hour_of_day">Hour of Day</option>
                        <option value="hour_of_date">Hour of Date (Month)</option>
                        <option value="hour_of_week">Hour of Week</option>
                    </select>
                </div>

                <div class="mt-2 w-full h-80">
                    <canvas id="lineLengthChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Registration details <span
                            class="text-xs text-gray-400">ⓘ</span></h3>
                    <div class="flex space-x-2 text-xs text-gray-600">
                       

                        {{-- <select wire:model.live="modeFilter">
                            <option value="hour_of_day">Hour of Day</option>
                            <option value="hour_of_date">Hour of Date (Month)</option>
                            <option value="hour_of_week">Hour of Week</option>
                        </select> --}}

                       
                    </div>
                </div>
                <canvas id="registrationDetailsChart" class="h-56"></canvas>
            </div>
            <div class="bg-white rounded-xl p-4 shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Wait & serve duration <span
                            class="text-xs text-gray-400">ⓘ</span></h3>
                  
                </div>
               <canvas id="waitServeChart" class="h-56"></canvas>
            </div>
            <div class="bg-white rounded-xl p-4 shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Average durations <span
                            class="text-xs text-gray-400">ⓘ</span></h3>
                
                </div>
              <canvas id="averageDurationsChart" class="w-full h-56"></canvas>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        let trafficChartInstance = null;
        let customerPathChartInstance = null;

        function renderTrafficChart(data) {
            console.log('traffic'+data);
            const trafficCtx = document.getElementById('trafficChart').getContext('2d');

            if (trafficChartInstance) trafficChartInstance.destroy();

            trafficChartInstance = new Chart(trafficCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                            label: 'Waitlist',
                            data: data.waitlist,
                            borderColor: '#c4b5fd', // soft purple
                            backgroundColor: 'rgba(196,181,253,0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        },
                        {
                            label: 'Booking',
                            data: data.booking,
                            borderColor: '#a7f3d0', // soft mint
                            backgroundColor: 'rgba(167,243,208,0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        },
                        {
                            label: 'Serving',
                            data: data.serving,
                            borderColor: '#fbcfe8', // soft pink
                            backgroundColor: 'rgba(251,207,232,0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        },
                        {
                            label: 'Dropoff',
                            data: data.dropoff,
                            borderColor: '#fde68a', // soft yellow
                            backgroundColor: 'rgba(253,230,138,0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                color: '#f3f4f6'
                            }
                        }
                    }
                }
            });
        }

        function renderCustomerPathChart(data) {
            console.log('renderCustomerPathChart'+ JSON.stringify(data, null, 2));
            const ctx = document.getElementById('customerPathChart').getContext('2d');

            if (customerPathChartInstance) customerPathChartInstance.destroy();

          customerPathChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: data.labels,
        datasets: data.datasets,
        borderRadius: 4,
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 10
                    }
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            x: {
                stacked: true,
                ticks: {
                    font: {
                        size: 10
                    }
                },
                grid: {
                    display: false
                }
            },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    font: {
                        size: 10
                    }
                },
                grid: {
                    color: '#f3f4f6'
                }
            }
        }
    }
});
        }

        function waitForCanvasAndRender(id, callback, data, retry = 5) {
            const canvas = document.getElementById(id);
            if (canvas && canvas.getContext) {
                callback(data);
            } else if (retry > 0) {
                setTimeout(() => {
                    waitForCanvasAndRender(id, callback, data, retry - 1);
                }, 200); // retry every 200ms
            } else {
                console.warn(`Canvas with ID ${id} not found after multiple attempts`);
            }
        }

        Livewire.on('trafficChartUpdated', (data) => {
            waitForCanvasAndRender('trafficChart', renderTrafficChart, data[0]);
        });

        Livewire.on('customerPathChartUpdated', (data) => {
            waitForCanvasAndRender('customerPathChart', renderCustomerPathChart, data[0]);
        });
    </script>

    {{--  Visits graph --}}
    <script>
        let customerDistributionChart;

        function renderCustomerDistributionChart(data) {
            const ctx = document.getElementById('customerDistributionChart').getContext('2d');
            if (customerDistributionChart) customerDistributionChart.destroy();
            const pastelColors = ['hsl(240, 100%, 85%)', 'hsl(120, 70%, 85%)'];
            customerDistributionChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['New', 'Returning'],
                    datasets: [{
                        data: data[0],
                        backgroundColor: pastelColors,
                        borderWidth: 0
                    }]
                },
                options: {
                     responsive: true, // ✅ make chart responsive
                    maintainAspectRatio: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                    }
                }
            });
        }

        Livewire.on('customerDistributionUpdated', (data) => {
            waitForCanvasAndRender('customerDistributionChart', renderCustomerDistributionChart, data[0]);
        });
    </script>

    {{-- service --}}

    <script>
        let serviceChart;

        // Define the rendering function
        function renderServiceCombinationChart({
            labels,
            data,
            colors
        }) {
            const ctx = document.getElementById('serviceCombinationChart')?.getContext('2d');

            if (!ctx) {
                console.warn("Canvas element not found: serviceCombinationChart");
                return;
            }

            if (serviceChart) {
                serviceChart.destroy();
            }

            serviceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Service Combinations',
                        data: data,
                        backgroundColor: colors,
                        borderRadius: 8,
                        barThickness: 40,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6B7280' // Tailwind gray-500
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                color: '#E5E7EB' // Tailwind gray-200
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#F3F4F6',
                            titleColor: '#111827',
                            bodyColor: '#111827',
                            borderColor: '#D1D5DB',
                            borderWidth: 1
                        }
                    }
                }
            });
        }

        // Attach a single listener that waits for canvas
        Livewire.on('serviceCombinationUpdated', (data) => {
            waitForCanvasAndRender('serviceCombinationChart', renderServiceCombinationChart, data[0]);
        });



        // opertaion

        let opertaionVisitsByLocationChart;
        let userVisitsChart;

        function renderVisitsByLocationChart(data) {
            const ctx = document.getElementById('opertaionVisitsByLocationChart')?.getContext('2d');
            if (!ctx) return;

            if (opertaionVisitsByLocationChart) opertaionVisitsByLocationChart.destroy();

            opertaionVisitsByLocationChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Visits',
                        data: data.values,
                        backgroundColor: data.colors,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 10
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                color: '#E5E7EB'
                            }
                        }
                    }
                }
            });
        }

        function renderUserVisitsChart(data) {
            const ctx = document.getElementById('userVisitsChart')?.getContext('2d');
            if (!ctx) return;

            if (userVisitsChart) userVisitsChart.destroy();

            userVisitsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Users',
                        data: data.values,
                        backgroundColor: data.colors,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 10
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                color: '#E5E7EB'
                            }
                        }
                    }
                }
            });
        }

        Livewire.on('opertaionVisitsByLocationChartUpdated', (data) => {
            waitForCanvasAndRender('opertaionVisitsByLocationChart', renderVisitsByLocationChart, data[0]);
        });

        Livewire.on('userVisitsChartUpdated', (data) => {
            waitForCanvasAndRender('userVisitsChart', renderUserVisitsChart, data[0]);
        });


        //messages
        let messageChannelChart;

        function generatePastelColors(count) {
            const colors = [];
            for (let i = 0; i < count; i++) {
                const hue = (i * 360 / count) % 360; // Spread hues
                const saturation = 65 + Math.floor(Math.random() * 10); // 65–75%
                const lightness = 80 + Math.floor(Math.random() * 5); // 80–85%
                colors.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
            }
            return colors;
        }

        function renderMessageChannelChart(data) {
            const ctx = document.getElementById('messageChannelChart')?.getContext('2d');
            if (!ctx) return;

            if (messageChannelChart) messageChannelChart.destroy();

            const pastelColors = generatePastelColors(data.labels.length);

            messageChannelChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    // labels: data.labels,
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: pastelColors,
                        borderRadius: 5,
      ness: 40
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                color: '#E5E7EB'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#F3F4F6',
                            titleColor: '#111827',
                            bodyColor: '#111827'
                        }
                    }
                }
            });
        }
        Livewire.on('messageChannelChartUpdated', (data) => {

            waitForCanvasAndRender('messageChannelChart', renderMessageChannelChart, data[0]);
        });


        // guest 

let dropoffChart;

function renderDropoffChart(data) {
    const ctx = document.getElementById('dropoffWaitlistChart')?.getContext('2d');
    if (!ctx) return;

    if (dropoffChart) dropoffChart.destroy();

    dropoffChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels, // e.g. ['<1', '2', '3', ..., '8–15']
            datasets: [
                {
                    label: 'Dropoffs',
                    data: data.dropoffCounts,
                    backgroundColor: '#A5C8F0', // ✅ Pastel blue
                    borderRadius: 4,
                    order: 2 // ✅ Draw behind the line
                },
                {
                    label: 'Dropoff rate',
                    data: data.dropoffRates,
                    type: 'line',
                    borderColor: '#B9E4C9', // ✅ Pastel green
                    backgroundColor: '#B9E4C9',
                    fill: false,
                    yAxisID: 'y1',
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    order: 1 // ✅ Draw in front
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Dropoffs',
                        color: '#6B7280'
                    },
                    ticks: {
                        color: '#6B7280'
                    },
                    grid: {
                        color: '#E5E7EB'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Dropoff Rate (%)',
                        color: '#6B7280'
                    },
                    ticks: {
                        callback: val => `${val}%`,
                        color: '#6B7280'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                },
                x: {
                    ticks: {
                        color: '#6B7280'
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#6B7280'
                    }
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#111827',
                    bodyColor: '#111827',
                    borderColor: '#E5E7EB',
                    borderWidth: 1,
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.dataset.label === 'Dropoff rate') {
                                return `${ctx.dataset.label}: ${ctx.raw}%`;
                            }
                            return `${ctx.dataset.label}: ${ctx.raw}`;
                        }
                    }
                },
                annotation: {
                    annotations: {
                        avgLine: {
                            type: 'line',
                            xMin: data.averageDropoff,
                            xMax: data.averageDropoff,
                            borderColor: 'orange',
                            borderWidth: 2,
                            label: {
                                content: `Avg dropoff: ${data.averageDropoff}`,
                                enabled: true,
                                position: 'top'
                            }
                        }
                    }
                }
            }
        }
    });
}

// ✅ Livewire Event Handler
Livewire.on('dropoffByPositionChartUpdated', (data) => {
    waitForCanvasAndRender('dropoffWaitlistChart', renderDropoffChart, data[0]);
});
        let dropoffDonutChart;
        function renderGuestDropoffDonutDataChat(data) {

            const donutCanvas = document.getElementById('dropoffDonutChart');

            if (!donutCanvas) return;

            const donutCtx = donutCanvas.getContext('2d');


            if (dropoffDonutChart) dropoffDonutChart.destroy();

            // Dropoff Donut Chart
            dropoffDonutChart = new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: ['#A5C8F0', '#B6E4C1'],
                        borderColor: '#fff',
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true, // ✅ make chart responsive
                    maintainAspectRatio: true,
                    cutout: '70%',
                    plugins: {
                        title: {
                            display: false,
                            text: 'Dropoff Types',
                            color: '#374151',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#6B7280',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#111827',
                            bodyColor: '#111827',
                            borderColor: '#E5E7EB',
                            borderWidth: 1
                        }
                    }
                }
            });
        }

        Livewire.on('guestgetDropoffDonutDataChat', (data) => {

            waitForCanvasAndRender('dropoffDonutChart', renderGuestDropoffDonutDataChat, data[0]);
        });


        let lineLengthChart;

        function renderLineLengthChart(data) {
            const ctx = document.getElementById('lineLengthChart')?.getContext('2d');
            if (!ctx) return;

            if (lineLengthChart) lineLengthChart.destroy();

            const barColor = '#A5C8F0'; // pastel blue for bars
            // const lineColor = '#34D399'; // green line (#34D399 = Tailwind's green-400)

            lineLengthChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                            type: 'bar',
                            label: 'Max line length',
                            data: data.values,
                            backgroundColor: barColor,
                            borderRadius: 0
                        },
                        // {
                        //     type: 'line',
                        //     label: 'Average line length',
                        //     data: data.values, // same or different array, up to you
                        //     borderColor: lineColor,
                        //     backgroundColor: lineColor,
                        //     fill: false,
                        //     tension: 0.4,
                        //     pointRadius: 3,
                        //     pointHoverRadius: 5
                        // }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: false,
                            text: 'Queue Storage Count',
                            font: {
                                size: 16,
                                weight: 'bold'
                            },
                            color: '#374151'
                        },
                        legend: {
                            display: true,
                            labels: {
                                color: '#6B7280',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#111827',
                            bodyColor: '#111827',
                            borderColor: '#E5E7EB',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#6B7280'
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#6B7280'
                            },
                            grid: {
                                color: '#E5E7EB'
                            }
                        }
                    }
                }
            });
        }
        Livewire.on('lineLengthChartDataUpdated', (data) => {
            waitForCanvasAndRender('lineLengthChart', renderLineLengthChart, data[0]);
        });


        let registrationDetailsChart;

    function renderRegistrationDetailsChart(data) {

    const ctx = document.getElementById('registrationDetailsChart')?.getContext('2d');
    const legendContainer = document.getElementById('registrationLegend');
    if (!ctx) return;

    // Destroy previous chart
    if (registrationDetailsChart) registrationDetailsChart.destroy();

    // Render custom legend
    if (legendContainer) {
        legendContainer.innerHTML = ''; // Clear previous

        data.labels.forEach((label, index) => {
            const color = data.datasets[0].backgroundColor[index] || '#000';
            const legendItem = document.createElement('div');
            legendItem.className = 'flex items-center space-x-1';

            legendItem.innerHTML = `
                <span class="w-3 h-3 rounded-sm inline-block" style="background-color: ${color};"></span>
                <span>${label.toUpperCase()}</span>
            `;

            legendContainer.appendChild(legendItem);
        });
    }

    // Render chart
    registrationDetailsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: '',
                data: data.datasets[0].data,
                backgroundColor: data.datasets[0].backgroundColor,
                borderRadius: 5,
            }]
        },
        options: {
            responsive: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 10 }
                    },
                    grid: { color: '#f3f4f6' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: {
                    display: false // hidden, custom legend used
                }
            }
        }
    });
}
        Livewire.on('registrationDetailsChartUpdated', (data) => {
            waitForCanvasAndRender('registrationDetailsChart', renderRegistrationDetailsChart, data[0]);
        });


        
let registrationWaitServedChart;

function renderWaitServedChart(data) {
    const ctx = document.getElementById('waitServeChart')?.getContext('2d');
    if (!ctx) return;

    if (registrationWaitServedChart) registrationWaitServedChart.destroy();

    registrationWaitServedChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: data.datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        boxWidth: 12,
                        padding: 10
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Frequency'
                    }
                }
            }
        }
    });
}

Livewire.on('waitServeChartUpdated', (data) => {

    waitForCanvasAndRender('waitServeChart', renderWaitServedChart, data[0]);
});


let averageDurationsChart;

    function renderAverageDurationsChart(data) {
        const ctx = document.getElementById('averageDurationsChart')?.getContext('2d');
        if (!ctx) return;

        if (averageDurationsChart) averageDurationsChart.destroy();

        averageDurationsChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.x;
                                return val >= 1440
                                    ? `${(val / 1440).toFixed(1)} day(s)`
                                    : `${val} minute${val !== 1 ? 's' : ''}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Minutes'
                        }
                    }
                }
            }
        });
    }

    Livewire.on('averageDurationsChartUpdated', (data) => {

          waitForCanvasAndRender('averageDurationsChart', renderAverageDurationsChart, data[0]);
    });
    </script>
@endpush
