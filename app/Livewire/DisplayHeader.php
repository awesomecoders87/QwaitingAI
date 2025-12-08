<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{ScreenTemplate, Team,SiteDetail};
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;



class DisplayHeader extends Component
{
    public $showLanguageSelector = false;
    public $showLocationSelector = false;
    public $currentTemplate;
    public $imageTemplates;
    public $videoTemplates;
    public $showHeader = false;
    public $teamId;
    public $locationId;
    public $siteData = '';

    public function toggleSelectors()
    {
        $this->showLanguageSelector = !$this->showLanguageSelector;
        $this->showLocationSelector = !$this->showLocationSelector;
     
    }

    public function mount()
    {

        $segments = request()->segments();
        $this->teamId = tenant('id');
        $id = end($segments);
    
        $screenId = base64_decode($id);
        if (!empty($id))
            $this->currentTemplate = ScreenTemplate::viewDetails(tenant('id'), $screenId);

        if (!empty($this->currentTemplate)) {
            $this->imageTemplates =  $this->currentTemplate->json_data ? json_decode($this->currentTemplate->json_data, true) : [];

            $this->videoTemplates =  $this->currentTemplate->json ? json_decode($this->currentTemplate->json, true) : [];
        }
        if (Session::has('selectedLocation')) {
            $this->showHeader = true;
            $this->locationId = Session::get('selectedLocation');
            $this->siteData = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->locationId)->first();
        
        } else {
            $this->showHeader = false;
            $this->siteData = '';
        }
     
    }

    #[On('header-show')]
    public function header_show()
    {

        if (Session::has('selectedLocation')) {
            $this->showHeader = true;
            $this->locationId = Session::get('selectedLocation');
            $this->siteData = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->locationId)->first();
        } else {
            $this->showHeader = false;
        }
        $this->dispatch('refesh');
        
    }

    #[On('header-hide')]
    public function header_hide()
    {
        $this->showHeader = false;
    }

    #[On('header-show-on-mobile')]
    public function header_show_on_mobile()
    {
        $this->showHeader = true;

    }
    #[On('redirect-to-queue')]
    public function redirect_to_queue()
    {
        $this->redirect(url('queue') . '?mobile=true');
    }

    public function render()
    {
        return view('livewire.display-header');
    }
}
