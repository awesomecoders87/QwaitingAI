<div>
<style>
      
        
        canvas {
            display: block;
        }
    </style>
        <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
            <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.overview') }}</h2>
            <div wire.ignore class="chart-container">
                <canvas id="overviewChartOverview"></canvas>
            </div>
        </div>
</div>

<script>
    var overviewChartOverview = null; // Store the chart instance
    var chartDataOverview = @json($chartDataOverview); // Initial chart data

    document.addEventListener('livewire:init', function () {
        initChartOverview(chartDataOverview);

        Livewire.on('updateChartDataOverview', (newData) => {
            // console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
                // console.log('Received new chart data 2:', newData);
            }

            updateChartOverview(newData);
        });
    });

    function initChartOverview(chartData) {
       

        var ctxOverview = document.getElementById('overviewChartOverview').getContext('2d');

        // console.log(chartData);
        let labels = chartData.labels || [];
        let arrivedCounts = chartData.data || [];
        let backgroundColor = chartData.backgroundColor || [];
        let borderColor= chartData.borderColor || [];
    
// console.log(labels,arrivedCounts,backgroundColor,borderColor);
overviewChartOverview = new Chart(ctxOverview, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Calls',
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

    function updateChartOverview(newData) {
        // console.log('Updating chart...');
        
        if (overviewChartOverview) {
            overviewChartOverview.destroy(); // Destroy the old chart before reinitializing
        }

        initChartOverview(newData[0]); // Reinitialize chart with new data
    }
</script>

