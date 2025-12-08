<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StripeResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;


class PaymentReport extends Component
{
    use WithPagination;

    public $team_id;
   public $location_id;
    public $startDate;
    public $endDate;

    public $totalRevenue = 0;
    public $avgRevenue = 0;
    public $lastPaymentDate;
    public $lastPaymentAmount;
    public $categoryRevenue = [];
    public $revenueOverTime = [];

    protected $paginationTheme = 'tailwind';

    public function updatingStartDate() { $this->resetPage(); $this->dispatch('updatedchart');}
    public function updatingEndDate() { $this->resetPage(); $this->dispatch('updatedchart');}

    public function mount()
    {
        $this->team_id = tenant('id');
        $this->location_id = Session::get('selectedLocation');
        $this->startDate = now()->subDays(7)->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function render()
	{
		$query = StripeResponse::with([
				'queue.queueStorages', // Eager load queue and its related queueStorages
				'category'             // Eager load category
			])
			->where('team_id', $this->team_id)
			->where('location_id', $this->location_id)
			->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
			->where('status', 'succeeded');

		$allTransactions = (clone $query)->get(); // Full dataset
		$paginated = $query->latest()->paginate(4); // Paginated dataset

		// Stats
		$this->totalRevenue = $allTransactions->sum('amount');
		$this->avgRevenue = $allTransactions->avg('amount');

		$last = $allTransactions->sortByDesc('id')->first();
		$this->lastPaymentDate = optional($last)->created_at?->format('d-M-Y h:iA');
		$this->lastPaymentAmount = optional($last)->amount;

		// Revenue by category
		$grouped = $allTransactions->groupBy('category_id')->map(fn($g) => $g->sum('amount'));
		$categoryNames = \App\Models\Category::whereIn('id', $grouped->keys())->pluck('name', 'id');

		$this->categoryRevenue = $grouped->mapWithKeys(function($amount, $categoryId) use ($categoryNames) {
			return [$categoryNames[$categoryId] ?? 'Unknown' => $amount];
		});

		// Revenue over time
		$this->revenueOverTime = $allTransactions->groupBy(fn($item) =>
			Carbon::parse($item->created_at)->format('Y-m-d')
		)->map(fn($g) => $g->sum('amount'));

		// Dispatch updated chart
		$this->dispatch('updatedchart',
			categoryLabels: $this->categoryRevenue->keys()->values(),
			categoryData: $this->categoryRevenue->values()->values(),
			revenueLabels: $this->revenueOverTime->keys()->values(),
			revenueData: $this->revenueOverTime->values()->values(),
		);

		return view('livewire.payment-report', [
			'transactions' => $paginated,
			'allTransactions' => $allTransactions,
		]);
	}
}