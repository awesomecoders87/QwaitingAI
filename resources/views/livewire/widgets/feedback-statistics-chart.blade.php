<div>
<style>
      
        /* .chart-container {
            width: 100%;
            height: 60vh;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        } */
        canvas {
            display: block;
        }
    </style>  
    <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-gray-700 dark:border-gray-600 dark:text-white">  
    <h2 class="text-xl font-semibold mb-4">Feedback Statistics Report</h2>
        <div  wire.ignore class="chart-container">
            <canvas id="overviewChartFeedback"></canvas>
        </div>
    </div>
    </div>
<script>
    var overviewChartFeedback = null; // Store the chart instance
    var chartDataFeedback = @json($chartDataFeedback); // Initial chart data

    document.addEventListener('livewire:init', function () {
        initChartFeedback(chartDataFeedback);

        Livewire.on('updateChartFeedback', (newData) => {
            console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
                // console.log('Received new chart data 2:', newData);
            }

            updateChartFeedback(newData);
        });
    });

    function initChartFeedback(chartData) {
       

        var ctxFeedback = document.getElementById('overviewChartFeedback').getContext('2d');

        console.log(chartData);
        let labels = chartData.labels || [];
        let arrivedCounts = chartData.data || [];
        let backgroundColor = chartData.backgroundColor || [];
        let borderColor= chartData.borderColor || [];
    
console.log(labels,arrivedCounts,backgroundColor,borderColor);
overviewChartFeedback = new Chart(ctxFeedback, {
            type: 'bar',
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

    function updateChartFeedback(newData) {
        console.log('Updating chart...');
        
        if (overviewChartFeedback) {
            overviewChartFeedback.destroy(); // Destroy the old chart before reinitializing
        }

        initChartFeedback(newData[0]); // Reinitialize chart with new data
    }
</script>

