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
        <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Walk-In Visitor By Service') }}</h2>

        <div wire:ignore class="chart-container">
            <canvas id="walkInQueueVisitsChartService"></canvas>
        </div>
    </div>
</div>

<script>
    var walkInQueueVisitsChartService = null;
    var chartWalkinQueueService = @json($chartWalkinQueueService);

    document.addEventListener('livewire:init', function () {
        initWalkInQueueVisitsChartService(chartWalkinQueueService);

        Livewire.on('updateWalkinQueueService', (newData) => {
            updateWalkInQueueVisitsChartService(newData[0]);
        });
    });

    function updateWalkInQueueVisitsChartService(newData) {
        if (walkInQueueVisitsChartService) {
            walkInQueueVisitsChartService.destroy();
        }
        initWalkInQueueVisitsChartService(newData);
    }

    function initWalkInQueueVisitsChartService(chartData) {
        // console.log(chartData);
        let ctxwalkInQueueVisits = document.getElementById('walkInQueueVisitsChartService').getContext('2d');

        if (walkInQueueVisitsChartService) {
            walkInQueueVisitsChartService.destroy();
        }

        walkInQueueVisitsChartService = new Chart(ctxwalkInQueueVisits, {
            type: 'pie',
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
