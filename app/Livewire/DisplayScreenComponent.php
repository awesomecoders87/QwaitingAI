<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Team;
use App\Models\ScreenTemplate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class DisplayScreenComponent extends Component
{
    #[Title('Display Screen')]
    
    public $domainSlug;
    public $teamId;
    public $locationId;
    public $screenNames = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Display Screen')) {
            abort(403);
        }
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->screenNames = ScreenTemplate::where(['team_id' => $this->teamId])->where('location_id',$this->locationId)->get();
    }

    public function render()
    {
        return view('livewire.display-screen-component');
    }
}
