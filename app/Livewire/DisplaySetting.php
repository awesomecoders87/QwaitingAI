<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DisplaySettingModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class DisplaySetting extends Component
{
    #[Title('Display Screen Setting')]

    public $screen_tune;
    public $created_by;
    public $teamId;
    public $locationId;
    public $userAuth;
    public $successMessage = null; // For alert handling

    public function mount()
    {
        $this->userAuth = Auth::user();

        if (!$this->userAuth->hasPermissionTo('Display Settings')) {
            abort(403);
        }

        $this->teamId = tenant('id'); // Adjust as per your tenant system
        $this->locationId = Session::get('selectedLocation');
        $siteDetail = DisplaySettingModel::getDetails($this->teamId,$this->locationId );

        if(!empty($siteDetail)){

            $this->screen_tune = $siteDetail?->screen_tune;
            $this->created_by = $siteDetail?->created_by ?? $this->userAuth->id;
        }else{
             $siteDetail = DisplaySettingModel::create(
            ['team_id' => $this->teamId,'location_id'=>$this->locationId,
            'screen_tune' => 0, 'created_by' => $this->userAuth->id]
        );

         $this->screen_tune = $siteDetail?->screen_tune;
        $this->created_by = $siteDetail?->created_by ?? $this->userAuth->id;
        }

    }

    public function save()
    {
        DisplaySettingModel::updateOrCreate(
            ['team_id' => $this->teamId,'location_id'=>$this->locationId],
            ['screen_tune' => $this->screen_tune, 'created_by' => $this->created_by]
        );

        // Save settings logic...
        session()->flash('success', 'Settings Updated Successfully.');
        
        // Set success message property
        $this->successMessage = 'Settings Updated Successfully.';

        // Dispatch event to hide the alert after timeout
        $this->dispatch('hide-alert');
    }

    public function resetSuccessMessage()
    {
        $this->successMessage = null;
    }

    public function render()
    {
        return view('livewire.display-settings', [
            'voiceMessages' => DisplaySettingModel::getVoiceMessages(),
        ]);
    }
}
