<div>
    <style>        
        canvas {
            display: block;
        }
        select {
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>

    <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
    <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Walk-in Queue Visits') }}</h2>

    <div wire:ignore class="chart-container">
        <canvas id="walkInQueueVisitsChart"></canvas>
    </div>
    </div>
</div>

<script>
    var walkInQueueVisitsChart = null;
    var chartWalkinQueue = @json($chartWalkinQueue);

    document.addEventListener('livewire:init', function () {
        initWalkInQueueVisitsChart(chartWalkinQueue);

        Livewire.on('updateWalkinQueue', (newData) => {
            updateWalkInQueueVisitsChart(newData[0]);
        });
    });

    function updateWalkInQueueVisitsChart(newData) {
        if (walkInQueueVisitsChart) {
            walkInQueueVisitsChart.destroy();
        }
        initWalkInQueueVisitsChart(newData);
    }

    function initWalkInQueueVisitsChart(chartData) {
        // console.log(chartData);
        let ctxwalkInQueueVisits = document.getElementById('walkInQueueVisitsChart').getContext('2d');

        if (walkInQueueVisitsChart) {
            walkInQueueVisitsChart.destroy();
        }

        walkInQueueVisitsChart = new Chart(ctxwalkInQueueVisits, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: "{{ __('text.Walk-in Visits') }}",
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
