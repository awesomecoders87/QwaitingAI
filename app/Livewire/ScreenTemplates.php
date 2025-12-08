<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ScreenTemplate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Auth;

class ScreenTemplates extends Component
{
    use WithPagination;
    
    #[Title('Screen Template')]

    public $teamId;
    public $locationId;
    public $selectedId =null;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Screen Templates Setting')) {
            abort(403);
        }


        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
    }

    public function deletetemplate($id){
     $this->selectedId = $id;
     $this->dispatch('confirm-delete');
    }

    #[On('confirmed-delete')]
    public function confirmedDelete(){
        if(isset($this->selectedId)){
            ScreenTemplate::where('id', $this->selectedId)->delete();
            $this->selectedId = null;
            $this->dispatch('deleted');

        }
    }

    public function render()
    {
        $screenTemplates = ScreenTemplate::where('team_id', $this->teamId)->where('location_id',$this->locationId)
            ->paginate(10);

        return view('livewire.screen-templates', compact('screenTemplates'));
    }
}
