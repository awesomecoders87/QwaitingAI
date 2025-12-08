<div>
    <style>
        
        canvas {
            display: block;
        }
    </style>

    <h2 class="text-xl font-semibold mb-4">{{ __('report.Summary') }}</h2>
    <div wire:ignore class="chart-container bg-white shadow rounded dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
        <canvas id="overviewChartTime"></canvas>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', function() {
        let overviewChartTime = null;
        let chartData = @json($chartData ?? []);

        if (!chartData || !Array.isArray(chartData.labels) || !Array.isArray(chartData.datasets)) {
            console.error("Invalid chart data:", chartData);
            chartData = {
                labels: [],
                datasets: []
            };
        }

        initChartTime(chartData);
        Livewire.on('updateChartTimeData', (newData) => {
            if (!Array.isArray(newData) || !newData.length || typeof newData[0] !== 'object') {
                console.error("Invalid new chart data format:", newData);
                return;
            }

            const parsedData = newData[0]; // Extract the first object

            if (!parsedData.labels || !parsedData.datasets) {
                console.error("Invalid new chart data:", parsedData);
                return;
            }

            updateChartTime(parsedData);
        });
    });

    function initChartTime(chartData) {
        let ctx = document.getElementById('overviewChartTime').getContext('2d');
        console.log('data' + chartData);
        console.log('labels' + chartData.labels);
        console.log('set' + chartData.datasets);
        overviewChartTime = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: dataset.borderColor || getRandomColor(index),
                    backgroundColor: dataset.backgroundColor || "rgba(0, 0, 0, 0.1)",
                    borderWidth: dataset.borderWidth || 2,
                    fill: false,
                    tension: 0.3 // Smooth curves
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function updateChartTime(newData) {
        if (overviewChartTime) {
            overviewChartTime.destroy();
        }
        initChartTime(newData);
    }

    function getRandomColor(index) {
        const colors = [
            "rgba(46, 204, 113, 1)", // Green
            "rgba(52, 152, 219, 1)", // Blue
            "rgba(231, 76, 60, 1)" // Red
        ];
        return colors[index % colors.length];
    }
</script>