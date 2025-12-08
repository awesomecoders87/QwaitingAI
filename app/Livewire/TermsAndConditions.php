<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TermsCondition;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class TermsAndConditions extends Component
{
    use WithPagination;
    
    #[Title('Terms And Condition')]

    public $team_id;
    public $selectedLocation;
    public $isExist=false;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Term and Condition')) {
            abort(403);
        }
        $this->team_id = tenant('id');
        $this->selectedLocation = Session::get('selectedLocation');
        $this->isExist = TermsCondition::where('team_id', $this->team_id)->where('location_id', $this->selectedLocation)->exists();
    }

    public function render()
    {
        $termsConditions = TermsCondition::where('team_id', $this->team_id)->where('location_id', $this->selectedLocation)->paginate(5);

        return view('livewire.terms-and-conditions', compact('termsConditions'));
    }
}
