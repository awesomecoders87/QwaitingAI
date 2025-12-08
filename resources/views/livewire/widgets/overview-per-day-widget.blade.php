<div class="mt-4">
    <h2 class="text-xl font-semibold mb-4 dark:text-white">{{ __('report.Summary') }}</h2>
    <div wire:ignore class="chart-container bg-white shadow rounded dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
        <canvas id="overviewChart"></canvas>
    </div>
</div>

<script>
    var overviewChart = null; // Store the chart instance
    var chartData = @json($chartData); // Initial chart data

    document.addEventListener('livewire:init', function() {
        initChart(chartData);

        Livewire.on('updateChartData', (newData) => {
            console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
            }

            updateChart(newData);
        });
    });

    function initChart(chartData) {
        console.log('Initializing chart with data:', chartData);

        var ctx = document.getElementById('overviewChart').getContext('2d');
        var labels = chartData.map(item => item.date);
        var arrivedCounts = chartData.map(item => item.arrived_count);
        var servedCounts = chartData.map(item => item.served_count);
        var waitingCounts = chartData.map(item => item.arrived_count - item.served_count);
        console.log(labels, arrivedCounts, servedCounts, waitingCounts)
        overviewChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: "{{ __('report.Arrived') }}",
                        data: arrivedCounts,
                        borderColor: 'green',
                        fill: false,
                    },
                    {
                        label: "{{ __('report.Served') }}",
                        data: servedCounts,
                        borderColor: 'blue',
                        fill: false,
                    },
                    {
                        label: "{{ __('report.Waiting') }}",
                        data: waitingCounts,
                        borderColor: 'orange',
                        fill: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }

    function updateChart(newData) {
        console.log('Updating chart...');

        if (overviewChart) {
            overviewChart.destroy(); // Destroy the old chart before reinitializing
        }

        initChart(newData); // Reinitialize chart with new data
    }
</script>