<div>
    <style>
        
        canvas {
            display: block;
        }
    </style>
    <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
        <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Call Handling Overview') }}</h2>

        <div wire:ignore class="chart-container">
            <canvas id="callHandlingChart"></canvas>
        </div>
    </div>
</div>

<script>
   
    var callHandlingChart = null;
    var chartCallHandling = @json($chartCallHandling); // Initial chart data

    document.addEventListener('livewire:init', function () {
        initChartcallHandlingChart(chartCallHandling);

        Livewire.on('updateCallHandling', (newData) => {
            // console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
                // console.log('Received new chart data 2:', newData);
            }

            updatecallHandling(newData);
        });
    });

    function updatecallHandling(newData) {
        // console.log('Updating chart...');
        
        if (callHandlingChart) {
            callHandlingChart.destroy(); // Destroy the old chart before reinitializing
        }

        initChartcallHandlingChart(newData[0]); // Reinitialize chart with new data
    }

        function initChartcallHandlingChart(chartData) {
            var ctxcallHandlingChart = document.getElementById('callHandlingChart').getContext('2d');

            if (callHandlingChart) {
                callHandlingChart.destroy(); // Destroy previous instance before reinitializing
            }
// console.log(chartData);
            callHandlingChart = new Chart(ctxcallHandlingChart, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: "{{ __('text.Calls') }}",
                        data: chartData.data,
                        backgroundColor: chartData.backgroundColor,
                        borderColor: chartData.borderColor,
                        borderWidth: chartData.borderWidth,
                        barThickness: chartData.barThickness
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }


    
</script>
