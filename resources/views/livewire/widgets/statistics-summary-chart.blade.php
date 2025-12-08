<div>
<style>
        canvas {
            display: block;
        }
    </style> 
  <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
    <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.summary') }}</h2>
        <div  class="chart-container">
            <canvas id="overviewChart"></canvas>
        </div>
    </div>
</div>

<script>
    var overviewChart = null; // Store the chart instance
    var chartData = @json($chartData); // Initial chart data

    document.addEventListener('livewire:init', function () {
        initChart(chartData);

        Livewire.on('updateChartData', (newData) => {
            // console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
                // console.log('Received new chart data 2:', newData);
            }

            updateChart(newData);
        });
    });

    function initChart(chartData) {
       

        var ctx = document.getElementById('overviewChart').getContext('2d');
        let labels = chartData.labels || [];
        let arrivedCounts = chartData.data || [];
    
// console.log(labels,arrivedCounts,servedCounts,waitingCounts)
        overviewChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "{{ __('text.Calls') }}",
                        data: arrivedCounts,
                        backgroundColor:'#2ecc71',
                        borderColor : '#2ecc71',
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

    function updateChart(newData) {
        // console.log('Updating chart...');
        
        if (overviewChart !== null) {
        overviewChart.destroy(); // Destroy the old chart
    }

        initChart(newData[0]); // Reinitialize chart with new data
    }
</script>

