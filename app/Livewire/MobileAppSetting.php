<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SiteDetail;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class MobileAppSetting extends Component
{
    #[Title('Mobile Sitting')]

    public $teamId;
    public $locationId;
    public $rateLimitSec;
    public $rateLimitMinute;
    public $rateLimitDay;
    public $concurrencyLimit;
    public $rateLimitBy;
    public $siteDetailId;

    public function mount()
    {
   
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $siteDetails = SiteDetail::where(['team_id'=> $this->teamId,'location_id' => $this->locationId])
        ->select('id','rate_limit_sec','rate_limit_minute','rate_limit_day','concurrency_limit','rate_limit_by')
        ->first();

        if ($siteDetails) {
            $this->siteDetailId = $siteDetails->id;
            $this->rateLimitSec = $siteDetails->rate_limit_sec;
            $this->rateLimitMinute = $siteDetails->rate_limit_minute;
            $this->rateLimitDay = $siteDetails->rate_limit_day;
            $this->concurrencyLimit = $siteDetails->concurrency_limit;
            $this->rateLimitBy = $siteDetails->rate_limit_by;
        }

    }

    public function updateSetting()
    {
        $this->validate([
            'rateLimitSec' => 'required|integer|min:1',
            'rateLimitMinute' => 'required|integer|min:1',
            'rateLimitDay' => 'required|integer|min:1',
            'concurrencyLimit' => 'required|integer|min:1',
            // 'rateLimitBy' => 'required|in:ip,user,email',
        ]);

        SiteDetail::updateOrCreate(
            ['id' => $this->siteDetailId],
            [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
                'rate_limit_sec' => $this->rateLimitSec,
                'rate_limit_minute' => $this->rateLimitMinute,
                'rate_limit_day' => $this->rateLimitDay,
                'concurrency_limit' => $this->concurrencyLimit,
                'rate_limit_by' => 'ip'
            ]
        );
        $this->dispatch('saved');
        // session()->flash('success', 'Mobile App settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.mobile-app-setting');
    }
}
