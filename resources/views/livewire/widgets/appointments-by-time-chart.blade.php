<div>
<style>
      
        canvas {
            display: block;
        }
    </style> 
    <div class="bg-white p-4 rounded shadow mb-4 dark:shadow dark:bg-white/[0.03] dark:border-gray-600 dark:text-white">
        <h2 class="text-base uppercase font-semibold mb-4">{{ __('text.Appointments By Time of Day') }}</h2>
        <div  wire.ignore class="chart-container">
            <canvas id="appointmentsByTimeChart"></canvas>
        </div>
    </div>
</div>

 <!-- Chart.js Script -->
 <script>
        document.addEventListener('livewire:init', function () {
            let ctx = document.getElementById('appointmentsByTimeChart').getContext('2d');
            let appointmentsByTimeChart;

        renderChart(chartAppointmentsByService);

        Livewire.on('updateAppointmentsByTimeChart', (newData) => {
            updateppointmentsByTimeChart(newData[0]);
        });
    

    function updateppointmentsByTimeChart(newData) {
        if (appointmentsByTimeChart) {
            appointmentsByTimeChart.destroy();
        }
        renderChart(newData);
    }

            function renderChart(chartData) {
                if (appointmentsByTimeChart) {
                    appointmentsByTimeChart.destroy();
                }

                appointmentsByTimeChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: "{{ __('text.Total Visitors') }}",
                            data: chartData.data,
                            backgroundColor: chartData.backgroundColor,
                            borderColor: chartData.borderColor,
                            pointBackgroundColor: chartData.pointBackgroundColor,
                            pointBorderColor: chartData.pointBorderColor,
                            pointHoverBackgroundColor: chartData.pointHoverBackgroundColor,
                            pointHoverBorderColor: chartData.pointHoverBorderColor,
                            borderWidth: chartData.borderWidth,
                            tension: chartData.tension
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });
            }

            // Livewire.on('updateAppointmentsByTimeChart', (chartData) => {
            //     renderChart(chartData[0]);
               
            // });
        });
    </script>

