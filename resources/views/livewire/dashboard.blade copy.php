<div>
    @if($bookingsystem ==1)
    <div class="block p-6 border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div x-data="{ activeTab: 'summary' }" class="border-b border-gray-200 dark:border-gray-700">

            <!-- Tabs Navigation -->
           {{-- <nav class="flex space-x-4 border-b">
                <button @click="activeTab = 'summary'"
                    :class="activeTab === 'summary' ? 'inline-block p-4 text-blue-600 bg-white rounded-t-lg active' : 'inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-100'"
                   >
                   Summary
                </button>

                <button @click="activeTab = 'walkin'"
                    :class="activeTab === 'walkin' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-blue-200 hover:text-blue-600'"
                    class="py-2 px-4 border-b-2 border-transparent rounded-t-lg transition duration-300">
                    Walk-In
                </button>

                <button @click="activeTab = 'appointments'"
                    :class="activeTab === 'appointments' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-blue-200 hover:text-blue-600'"
                    class="py-2 px-4 border-b-2 border-transparent rounded-t-lg transition duration-300">
                    Appointments
                </button>
            
            </nav> --}}


            <div class="mb-4 border-gray-200">
                <ul class="flex text-sm font-medium text-center text-gray-500">
                    <li class="mr-2">

                        <a href="javascript:void(0)" 
                         :class="activeTab === 'summary' ? 'inline-block px-4 py-2 text-black bg-gray-300 rounded-lg active' : 'inline-block px-4 py-2 rounded-t-lg bg-white hover:text-gray-600 hover:bg-gray-100'"
                       
                            @click="activeTab = 'summary'">Summary</a>
                    </li>
                    <li class="mr-2">
                        <a href="javascript:void(0)" 
                         :class="activeTab === 'walkin' ? 'inline-block px-4 py-2 text-black bg-gray-300 rounded-lg active' : 'inline-block px-4 py-2 rounded-lg bg-white hover:text-gray-600 hover:bg-gray-100'"
                      
                            @click="activeTab = 'walkin'">Walk-In</a>
                    </li>
                    <li class="mr-2">
                        <a href="javascript:void(0)" 
                            :class="activeTab === 'appointments' ? 'inline-block px-4 py-2 text-black bg-gray-300 rounded-lg active' : 'inline-block  bg-white px-4 py-2 rounded-lg hover:text-gray-600 hover:bg-gray-100'"
                       
                            @click="activeTab = 'appointments'">Appointments</a>
                    </li>
                    <!-- <li>
          <a href="#" class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-100" @click="activeTab = 'walkin'">Feedbacks</a>
        </li> -->
                </ul>
            </div>

            <!-- Summary Tab Content -->
            <div x-show="activeTab === 'summary'" style="display:block">
                <div class=" mx-auto">

                    <!-- Date Filter -->
                    <div class="flex items-center space-x-4 mb-6">
                        <input type="date" wire:model.live="summaryfromSelectedDate"  onclick="this.showPicker()" class="border border-gray-300 rounded px-3 py-2" placeholder="Start Date">
                        <input type="date" wire:model.live="summarytoSelectedDate" 
                        onclick="this.showPicker()" class="border border-gray-300 rounded px-3 py-2" placeholder="End Date">
                    </div>

                    <!-- KPI Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Total Visits</p>
                            <h3 class="text-xl font-bold">{{ $totalVisits }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Served Visits</p>
                            <h3 class="text-xl font-bold">{{ $servedVisits }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>No Show</p>
                            <h3 class="text-xl font-bold">{{ $noShow }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Cancelled</p>
                            <h3 class="text-xl font-bold">{{ $cancelled }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Waiting</p>
                            <h3 class="text-xl font-bold">{{ $waiting }}</h3>
                        </div>
                    </div>

                    <!-- Hourly Bar Chart -->
                    <div class="bg-white p-4 rounded shadow mb-6" wire:ignore>
                        <h4 class="mb-2 font-semibold">Hourly Visits (12 AM - 11 PM)</h4>
                        <canvas id="hourlyChart"></canvas>
                    </div>

                    <!-- Monthly Bar Chart -->
                    <div class="bg-white p-4 rounded shadow mb-6" wire:ignore>
                        <h4 class="mb-2 font-semibold">Monthly Visits</h4>
                        <canvas id="monthlyChart"></canvas>
                    </div>
                        @php
                            function fmt($s) {
                                return sprintf('%02d:%02d:%02d', floor($s/3600), floor($s%3600/60), $s%60);
                            }
                        @endphp
                    <!-- Additional KPI Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" wire:ignore>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Avg Served Time</p>
                            <h3 class="text-xl font-bold">{{ fmt($avgServedTime) }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Avg Waiting Time</p>
                            <h3 class="text-xl font-bold">{{ fmt($avgWaitingTime) }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Max Waiting Time</p>
                            <h3 class="text-xl font-bold">{{ fmt($maxWaitingTime) }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded shadow text-center">
                            <p>Max Served Time</p>
                            <h3 class="text-xl font-bold">{{ fmt($maxServedTime) }}</h3>
                        </div>
                    </div>

                    <!-- Horizontal Bar Graphs -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="bg-white p-4 rounded shadow">
                            <h4 class="mb-2 font-semibold">Counter Visits</h4>
                            <canvas id="counterChart"></canvas>
                        </div>
                        <div class="bg-white p-4 rounded shadow">
                            <h4 class="mb-2 font-semibold">Service Visits</h4>
                            <canvas id="serviceChart"></canvas>
                        </div>
                        <div class="bg-white p-4 rounded shadow">
                            <h4 class="mb-2 font-semibold">Agent Served Visits</h4>
                            <canvas id="agentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Walk-In Tab Content -->
            <div x-show="activeTab === 'walkin'" style="display:block">
                <div class="mt-6 mb-6">
                    <select wire:model.live="filter"
                        class="bg-white  border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        style="width: 200px;">
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month"> This Month </option>

                    </select>
                </div>
                <div class="-mx-2.5 flex flex-wrap gap-y-5">
                    <div class="w-full px-2.5 xl:w-1/2">
                        @livewire(\App\Livewire\Widgets\CallHandlingOverviewChart::class)
                    </div>
                    <div class="w-full px-2.5 xl:w-1/2">
                        @livewire(\App\Livewire\Widgets\WalkinQueueVisitsChart::class)
                    </div>
                </div>

              
    <!-- Include the form using $this->form -->
    <div class="flex flex-wrap w-full border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] md:pl-5 md:pr-5  p-2">

        <div class="flex-1 flex flex-col mx-2 py-4 sm:py-2">
            <label for="fromSelectedDate" class="font-semibold text-gray-700">{{ __('From Date') }}</label>
            <input type="date" wire:model.live="fromSelectedDate" onclick="this.showPicker()"
                class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input active" />
        </div>
        <div class="flex-1 flex flex-col mx-2 py-4 sm:py-2">
            <label for="toSelectedDate" class="font-semibold text-gray-700">{{ __('To Date') }}</label>
            <input type="date" wire:model.live="toSelectedDate" onclick="this.showPicker()"
                class="bg-white dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-4 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 flatpickr-input active" />
        </div>

    </div>

    <?php isset($data)? dd($data['start_date']) : ''?>
    <!-- Livewire components for widgets -->
    <div class="p-5 space-y-6 border-t border-gray-100 dark:border-gray-800 sm:p-6">
        <div class="-mx-2.5 flex flex-wrap gap-y-5">

            <div class="card-header w-full px-2.5 xl:w-1/2">
                @livewire(\App\Livewire\Widgets\OverviewChart::class)
            </div>
            <div class="card-header w-full px-2.5 xl:w-1/2">
                @livewire(\App\Livewire\Widgets\StatisticsSummaryChart::class)
            </div>
            <div class="card-header w-full px-2.5 xl:w-1/2">
                @livewire(\App\Livewire\Widgets\StatisticsCallHistoryChart::class)
            </div>
            <div class="card-header w-full px-2.5 xl:w-1/2">
                @livewire(\App\Livewire\Widgets\StatisticsCounterHistoryChart::class)
            </div>
        </div>
    </div>

            </div>

            <div x-show="activeTab === 'walkin'" class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-6">
                <div>
                    @livewire(\App\Livewire\Widgets\WalkinByServiceChart::class)
                </div>
            </div>

            <!-- Appointments Tab Content -->
            <div x-show="activeTab === 'appointments'">

                <div class="grid grid-cols-4 md:grid-cols-4 gap-4 mt-6">
                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 mt-4">
                        <h5 class="mb-2 text-sm font-normal tracking-tight text-gray-900 text-center dark:text-white">
                            Completed Appointments</h5>
                        <p class="font-bold text-lg text-gray-700 text-center dark:text-gray-400">
                            {{ $completedAppointments }}</p>
                    </div>
                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 mt-4">
                        <h5 class="mb-2 text-sm font-normal tracking-tight text-gray-900 text-center dark:text-white">
                            Pending Appointments</h5>
                        <p class="font-bold text-lg text-gray-700 text-center dark:text-gray-400">
                            {{ $pendingAppointments }}</p>
                    </div>
                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 mt-4">
                        <h5 class="mb-2 text-sm font-normal tracking-tight text-gray-900 text-center dark:text-white">
                            Rescheduled Appointments</h5>
                        <p class="font-bold text-lg text-gray-700 text-center dark:text-gray-400">
                            {{ $rescheduledAppointments }}</p>
                    </div>

                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 mt-4">
                        <h5 class="mb-2 text-sm font-normal tracking-tight text-gray-900 text-center dark:text-white">
                            Cancelled Appointments</h5>
                        <p class="font-bold text-lg text-gray-700 text-center dark:text-gray-400">
                            {{ $cancelledAppointments }}</p>
                    </div>
                </div>



                <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mt-6" style="display:block">
                    <div class="mt-6 mb-6">
                        <select wire:model.live="appointmentFilter"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            style="width: 200px;">
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month"> This Month </option>

                        </select>
                    </div>
                    <div class="-mx-2.5 flex flex-wrap gap-y-5">
                        <div class="w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\AppointmentsChart::class)
                        </div>
                        <div class="w-full px-2.5 xl:w-1/2">
                            @livewire(\App\Livewire\Widgets\AppointmentsByServicesChart::class)
                        </div>
                    </div>
                </div>
            </div>


            <div x-show="activeTab === 'appointments'" class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-6">
                <div>
                    @livewire(\App\Livewire\Widgets\AppointmentsByTimeChart::class)
                </div>
            </div>

        </div>
    </div>
    @endif
  

    @push('scripts')
    <script>
    // Example datepicker change event handling
    document.addEventListener('change', function(event) {
        let element = event.target;

        if (element.tagName.toLowerCase() === 'input' && element.type === 'date' && element.hasAttribute(
                'wire:model.live')) {
            let modelAttribute = element.getAttribute('wire:model.live');
            let dateValue = element.value;

            if (modelAttribute === 'data.start_date') {
                console.log('Start Date Changed:', dateValue);
            } else if (modelAttribute === 'data.end_date') {
                console.log('End Date Changed:', dateValue);
            } else {
                console.warn('Unknown wire:model.live attribute:', modelAttribute);
            }
        }
    });
    </script>

     <script>

         let hourlyChart;
         let monthlyChart;
         let counterChart;
         let categoryChart;
         let AgentChart;

    Livewire.on('hourly-visits-updated', data => {
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        const labels = [...Array(24).keys()].map(i => `${i}:00`);

        if (hourlyChart) {
            hourlyChart.destroy();
        }

        hourlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Visits',
                    data:data['data'],
                  backgroundColor: '#93c5fd'
                }]
            },
           options: { scales: { y: { beginAtZero: true } } }
        });
    });


       Livewire.on('monthly-visits-updated', data => {
   
console.log('monthly:'+  data['data']);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

      if (monthlyChart) {
            monthlyChart.destroy();
        } 
     monthlyChart = new Chart(document.getElementById('monthlyChart'), {
      type: 'bar',
      data: {
        labels: months,
        datasets: [{
          label: 'Visits',
          data:data['data'],
          backgroundColor: '#a5b4fc'
        }]
      },
      options: { scales: { y: { beginAtZero: true } } }
    });
});

const horizontalOptions = {
      indexAxis: 'y',
      scales: { x: { beginAtZero: true } },
      plugins: { legend: { display: false } }
    };

Livewire.on('counter-updated', data => {

  

   if (!Array.isArray(data['data'])) {
        console.error('Expected array, got:', data);
        return;
    }

    const labels = data['data'].map(item => item.name);
    const values = data['data'].map(item => item.total);

    
      if (counterChart) {
            counterChart.destroy();
        } 

   

    counterChart = new Chart(document.getElementById('counterChart'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: '#fca5a5'
        }]
      },
      options: horizontalOptions
    });
});
Livewire.on('category-updated', data => {

  

   if (!Array.isArray(data['data'])) {
        console.error('Expected array, got:', data);
        return;
    }

    const labels = data['data'].map(item => item.name);
    const values = data['data'].map(item => item.total);

    
      if (categoryChart) {
            categoryChart.destroy();
        } 


    categoryChart = new Chart(document.getElementById('serviceChart'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: '#fdba74'
        }]
      },
      options: horizontalOptions
    });
});


Livewire.on('agent-updated', data => {

   if (!Array.isArray(data['data'])) {
        console.error('Expected array, got:', data);
        return;
    }

    const labels = data['data'].map(item => item.name);
    const values = data['data'].map(item => item.total_served);

    
      if (AgentChart) {
            AgentChart.destroy();
        } 

   

    AgentChart =  new Chart(document.getElementById('agentChart'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: '#6ee7b7'
        }]
      },
      options: horizontalOptions
    });
});

  </script>
    @endpush

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

</div>