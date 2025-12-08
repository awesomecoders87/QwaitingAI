<?php
namespace App\Livewire\Package;

use Livewire\Component;
use App\Models\Domain;
use Illuminate\Support\Facades\Auth;

class SubscriptionWarning extends Component
{
    public $showWarning = false;
    public $expiryDate;

    public function mount()
    {
        $teamId = Auth::user()->team_id ?? tenant('id');

        $domain = Domain::where('team_id', $teamId)->first();

        if ($domain && $domain->isExpiringSoon()) {
            $this->expiryDate = \Carbon\Carbon::parse($domain->expired)->format('Y-m-d');
            $this->showWarning = true;
        }
    }

    public function render()
    {
        return view('livewire.package.subscription-warning');
    }
}
