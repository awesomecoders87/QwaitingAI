<?php
namespace App\Livewire\Package;

use Livewire\Component;
use Illuminate\Support\Carbon;
use App\Models\Domain;

class SubscriptionReminder extends Component
{
    public $showPopup = false;

    public function mount()
    {
        // Skip on buy-subscription route
        if (request()->routeIs('buy-subcription')) {
            return;
        }

        $teamId = tenant('id');
        $domain = auth()->user()?->customOwner()?->first()?->domains()?->first()
            ?? Domain::where('team_id', $teamId)->first();

        if ($domain && $domain->expired && Carbon::parse($domain->expired)->lt(now())) {
            $this->showPopup = true;
        }
    }

    public function redirectToSubscription()
    {
        return redirect()->route('buy-subcription');
    }

    public function render()
    {
        return view('livewire.package.subscription-reminder');
    }
}