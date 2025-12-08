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
        <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Appointments By Services') }}</h2>

        <div wire:ignore class="chart-container">
            <canvas id="appointmentsByService"></canvas>
        </div>
    </div>
</div>

<script>
    var appointmentsByService = null;
    var chartAppointmentsByService = @json($chartAppointmentsByService);

    document.addEventListener('livewire:init', function () {
        initappointmentsByService(chartAppointmentsByService);

        Livewire.on('updateAppointmentsByService', (newData) => {
            updateappointmentsByService(newData[0]);
        });
    });

    function updateappointmentsByService(newData) {
        if (appointmentsByService) {
            appointmentsByService.destroy();
        }
        initappointmentsByService(newData);
    }

    function initappointmentsByService(chartData) {
        console.log(chartData);
        let ctxwalkInQueueVisits = document.getElementById('appointmentsByService').getContext('2d');

        if (appointmentsByService) {
            appointmentsByService.destroy();
        }

        appointmentsByService = new Chart(ctxwalkInQueueVisits, {
            type: 'pie',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: "{{ __('text.Appointments By Services') }}",
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
