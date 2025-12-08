<div>
    <style>        
        canvas {
            display: block;
        }
    </style>

    <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
        <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Appointments') }}</h2>

        <div wire:ignore class="chart-container">
            <canvas id="appointmentChart"></canvas>
        </div>
    </div>
</div>

<script>
   
    var apptChart = null;
    var appointmentchart = @json($appointmentchart); // Initial chart data

    document.addEventListener('livewire:init', function () {
        initChartAppointment(appointmentchart);

        Livewire.on('updateAppChat', (newData) => {
            // console.log('Received new chart data:', newData);
            if (Array.isArray(newData) && Array.isArray(newData[0])) {
                newData = newData[0]; // Extract inner array
                // console.log('Received new chart data 2:', newData);
            }

            updateAppointment(newData);
        });
    });

    function updateAppointment(newData) {
        // console.log('Updating chart...');
        
        if (apptChart) {
            apptChart.destroy(); // Destroy the old chart before reinitializing
        }

        initChartAppointment(newData[0]); // Reinitialize chart with new data
    }

        function initChartAppointment(chartData) {
            var ctxAppointment = document.getElementById('appointmentChart').getContext('2d');

            if (apptChart) {
                apptChart.destroy(); // Destroy previous instance before reinitializing
            }
// console.log(chartData);
          apptChart = new Chart(ctxAppointment, {
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
