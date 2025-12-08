<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{ScreenTemplate, Team,SiteDetail};
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use App\Models\Translation;


class Header extends Component
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
    public $translations;
    public $logoSize;
    public $enbaleHeaderTitle =true;

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
        if (!empty($id))
            $this->currentTemplate = ScreenTemplate::viewDetails(tenant('id'), $id);

        if (!empty($this->currentTemplate)) {
            $this->imageTemplates =  $this->currentTemplate->json_data ? json_decode($this->currentTemplate->json_data, true) : [];

            $this->videoTemplates =  $this->currentTemplate->json ? json_decode($this->currentTemplate->json, true) : [];
        }
        if (Session::has('selectedLocation')) {
            $this->showHeader = true;
            $this->locationId = Session::get('selectedLocation');
            $this->siteData = SiteDetail::where('team_id', $this->teamId)->where('location_id',$this->locationId)->first();
            $this->logoSize = $this->siteData->logo_size ?? 'small';

        } else {
            $this->showHeader = false;
            $this->siteData = '';
             $this->logoSize = 'small';
        }

          $this->translations = Translation::where('team_id', $this->teamId)
            ->get()
            ->groupBy('name') // Group by category name
            ->map(function ($items) {
                return $items->pluck('value', 'language'); // ['es' => 'Categoría 1']
            })
            ->toArray();

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
        $this->dispatch('getLocation');
        $this->dispatch('refesh');

    }

    #[On('header-hide')]
    public function header_hide()
    {
        $this->showHeader = false;
    }
    #[On('header-hide-title')]
    public function header_hide_title()
    {
        $this->enbaleHeaderTitle = false;
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
        return view('livewire.header');
    }
}
