<div>
<style>
        canvas {
            display: block;
        }
    </style>
    <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
    <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Call Counter') }}</h2>
        <div  class="chart-container">
            <canvas id="overviewChartCounter"></canvas>
        </div>
    </div>
</div>

<script>
    var overviewChartCounter = null; // Store the chart instance
    var chartDataCounter = @json($chartDataCounter); // Initial chart data

    document.addEventListener('livewire:init', function () {
        initChartCounter(chartDataCounter);

        Livewire.on('updateChartDataCounter', (newData) => {
            // console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
                // console.log('Received new chart data 2:', newData);
            }

            updateChartCounter(newData);
        });
    });

    function initChartCounter(chartData) {
       

        var ctxCounter = document.getElementById('overviewChartCounter').getContext('2d');

        // console.log(chartData);
        let labels = chartData.labels || [];
        let arrivedCounts = chartData.data || [];
        let backgroundColor = chartData.backgroundColor || [];
        let borderColor= chartData.borderColor || [];
    
// console.log(labels,arrivedCounts,backgroundColor,borderColor);
overviewChartCounter = new Chart(ctxCounter, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "{{ __('text.Calls Counter') }}",
                        data: arrivedCounts,
                        backgroundColor:backgroundColor,
                        borderColor : borderColor,
                        borderWidth : 1,
                        hoverOffset : 3
                    },
                 
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }

    function updateChartCounter(newData) {
        // console.log('Updating chart...');
        
        if (overviewChartCounter) {
            overviewChartCounter.destroy(); // Destroy the old chart before reinitializing
        }

        initChartCounter(newData[0]); // Reinitialize chart with new data
    }
</script>

