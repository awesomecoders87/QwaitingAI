<div>
     <div class="mx-auto space-y-6 p-6">

    <!-- Filters -->
    <div class="">
      <h2 class="text-xl font-bold mb-4">{{ __('report.Filter by Date') }}</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block mb-1 font-medium">{{ __('report.From') }}</label>
          <input type="date" wire:model.lazy="startDate" onclick="this.showPicker()" class="w-full border border-gray-300 rounded-md px-3 py-2" />
        </div>
        <div>
          <label class="block mb-1 font-medium">{{ __('report.To') }}</label>
          <input type="date" wire:model.lazy="endDate"  onclick="this.showPicker()" class="w-full border border-gray-300 rounded-md px-3 py-2" />
        </div>
        {{-- <div class="flex items-end">
          <button class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Apply Filter</button>
        </div> --}}
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <h3 class="text-sm text-gray-500">{{ __('report.Total Transactions') }}</h3>
        <p class="text-2xl font-bold">{{ $allTransactions->count() }}</p>
      </div>
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <h3 class="text-sm text-gray-500">{{ __('report.Total Revenue') }}</h3>
        <p class="text-2xl font-bold">{{ $totalRevenue }}</p>
      </div>
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <h3 class="text-sm text-gray-500">{{ __('report.Avg. Revenue') }}</h3>
        <p class="text-2xl font-bold">{{ $avgRevenue }}</p>
      </div>
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <h3 class="text-sm text-gray-500">{{ __('report.Last Payment Date & Time') }}</h3>
        <p class="text-2xl font-bold">{{ $lastPaymentDate ?? '-'}}</p>
      </div>
      <div class="bg-white p-4 rounded-xl shadow text-center">
        <h3 class="text-sm text-gray-500">{{ __('report.Last Payment') }}</h3>
        <p class="text-2xl font-bold">{{ $lastPaymentAmount }}</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Revenue by Category -->
      <div class="bg-white p-4 rounded-xl shadow">
        <h3 class="text-lg font-semibold mb-2">{{ __('report.Revenue by Service') }}</h3>
        <canvas id="categoryChart" height="200"></canvas>
      </div>

      <!-- Revenue Over Time -->
      <div class="bg-white p-4 rounded-xl shadow">
        <h3 class="text-lg font-semibold mb-2">{{ __('report.Revenue Over Time') }}</h3>
        <canvas id="revenueChart" height="200"></canvas>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white p-4 rounded-xl shadow">
      <h3 class="text-lg font-semibold mb-4">{{ __('report.Transaction Records') }}</h3>
      <div class="overflow-auto">
        <table class="table-auto w-full ti-custom-table ti-custom-table-hover">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2">{{ __('report.Txn ID') }}</th>
              <th class="px-4 py-2">{{ __('report.Token') }}</th>
              <th class="px-4 py-2">{{ __('report.name') }}</th>
              <th class="px-4 py-2">{{ __('report.contact') }}</th>
              <th class="px-4 py-2">{{ __('report.Date') }}</th>
              <th class="px-4 py-2">{{ __('report.Amount') }}</th>
              <th class="px-4 py-2">{{ __('report.Currency') }}</th>
              <th class="px-4 py-2">{{ __('report.Service') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y">
             @forelse ($transactions as $txn)
                            <tr>
                                <td class="px-4 py-2">{{ $txn->payment_intent_id }}</td>
                                <td class="px-4 py-2">{{ $txn->queue?->start_acronym ?? ''}} {{ $txn->queue?->token ?? 'Booked'}}</td>
                                <td class="px-4 py-2">{{ $txn->queue?->queueStorages->first()?->name ?? ''}}</td>
                                <td class="px-4 py-2">{{ $txn->queue?->queueStorages->first()?->phone ?? ''}}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($txn->created_at)->format('Y-m-d') }}</td>
                                <td class="px-4 py-2">{{ $txn->amount }}</td>
                                <td class="px-4 py-2">{{ strtoupper($txn->currency) }}</td>
                                <td class="px-4 py-2">{{ ucfirst($txn->category?->name ?? '') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center py-3"> 
                              <p class="text-center"><strong>{{ __('report.No records found.') }}</strong></p>
                            </td></tr>
                        @endforelse
            
          </tbody>
        </table>
      </div>
      <div class="mt-4">
    {{ $transactions->links() }}
</div>
    </div>

  </div>

  <!-- ChartJS Script -->
<script>
  let categoryChart;
  let revenueChart;

  document.addEventListener('DOMContentLoaded', () => {
    const categoryLabels = @json($categoryChartLabels ?? []);
    const categoryData = @json($categoryChartData ?? []);

    const revenueLabels = @json($revenueChartLabels ?? []);
    const revenueData = @json($revenueChartData ?? []);

    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
// console.log(categoryLabels,categoryData);
    categoryChart = new Chart(categoryCtx, {
      type: 'bar',
      data: {
        labels: categoryLabels,
        datasets: [{
          label: 'Revenue',
          data: categoryData,
          backgroundColor: '#3b82f6'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });

    revenueChart = new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels: revenueLabels,
        datasets: [{
          label: 'Revenue',
          data: revenueData,
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.2)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  });

  // Listen for Livewire event and update chart data
  document.addEventListener('livewire:init', () => {
    Livewire.on('updatedchart', ({ categoryLabels, categoryData, revenueLabels, revenueData }) => {
      console.log(categoryLabels,categoryData);
      if (categoryChart && revenueChart) {
        // Update bar chart
        categoryChart.data.labels = categoryLabels;
        categoryChart.data.datasets[0].data = categoryData;
        categoryChart.update();

        // Update line chart
        revenueChart.data.labels = revenueLabels;
        revenueChart.data.datasets[0].data = revenueData;
        revenueChart.update();
      }
    });
  });
</script>
</div>
