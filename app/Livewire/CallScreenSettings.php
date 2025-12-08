<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Country;
use App\Models\SiteDetail;
use App\Models\Domain;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class CallScreenSettings extends Component
{
    #[Title('Call Screen setting')] 

    public $siteDetails = [];
    public $data = [];
    public $categoryLevels = [];
    public $hidebuttons = [];
    public $teamId;
    public $location;
    public $hold_queue_feature = false;
    public $userAuth;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Call Screen Setting')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $domain = Domain::where('team_id',$this->teamId)->first();
        $this->hold_queue_feature = $domain['hold_queue_feature'] == 1 ? true : false;
        $this->location = (int)Session::get('selectedLocation');
        $this->hidebuttons =SiteDetail::hideButton();
        $this->siteDetails = SiteDetail::where(['team_id'=> $this->teamId,'location_id'=> $this->location])->first();
        // $this->siteDetails = SiteDetail::where(['team_id'=> $this->teamId])->first();
        if ($this->siteDetails) {
            $this->data = $this->siteDetails->toArray();
        }
        
        $this->categoryLevels = SiteDetail::getCategoryLevelEnable();
        $this->userAuth = Auth::user();
    }
    public function save()
    {
       
        $data = array_merge($this->data, [
            'team_id' => $this->teamId,
            'location_id' => $this->location,
        ]);
    
        if ($this->siteDetails) {
            $this->siteDetails->update($data);
            ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Call Screen Settings', $this->location, ActivityLog::SETTINGS, null, $this->userAuth);
            $this->dispatch('call-screen-settings-updated');
        } else {
            $this->siteDetails = SiteDetail::create($data);
            ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Call Screen Settings', $this->location, ActivityLog::SETTINGS, null, $this->userAuth);
            $this->dispatch('call-screen-settings-created');
        }
    }

    public function render()
    {
        return view('livewire.call-screen-settings', [
            'countryCodes' => Country::select('name', 'phonecode')->get()->toArray()
        ]);
    }
}
