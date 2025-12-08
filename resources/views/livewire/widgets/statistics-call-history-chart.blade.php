<div>
<style>
        canvas {
            display: block;
        }
    </style>
    <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
        <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Call History') }}</h2>
        <div  class="chart-container">
            <canvas id="overviewChartHistory"></canvas>
        </div>
    </div>
</div>

<script>
    var overviewChartHistory = null; // Store the chart instance
    var chartDataHistory = @json($chartDataHistory); // Initial chart data

    document.addEventListener('livewire:init', function () {
        initChartHistory(chartDataHistory);

        Livewire.on('updateChartDataHistory', (newData) => {
            // console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
                // console.log('Received new chart data 2:', newData);
            }

            updateChartHistory(newData);
        });
    });

    function initChartHistory(chartData) {
       

        var ctxHistory = document.getElementById('overviewChartHistory').getContext('2d');

        // console.log(chartData);
        let labels = chartData.labels || [];
        let arrivedCounts = chartData.data || [];
        let backgroundColor = chartData.backgroundColor || [];
        let borderColor= chartData.borderColor || [];
    
// console.log(labels,arrivedCounts,backgroundColor,borderColor);
overviewChartHistory = new Chart(ctxHistory, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "{{ __('text.Calls History') }}",
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

    function updateChartHistory(newData) {
        // console.log('Updating chart...');
        
        if (overviewChartHistory) {
            overviewChartHistory.destroy(); // Destroy the old chart before reinitializing
        }

        initChartHistory(newData[0]); // Reinitialize chart with new data
    }
</script>

