<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TermsCondition;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class CreateTermsAndConditions extends Component
{
    #[Title('Terms and Conditions Create')]

    public $title;
    public $term_content;
    public $team_id;
    public $location;

    protected $rules = [
        'title' => 'required|string|max:255',
        'term_content' => 'nullable|string',
        // 'team_id' => 'required|exists:teams,id',
    ];

    public function mount()
    {
        $this->team_id = tenant('id');
        $this->location = Session::get( 'selectedLocation' );
    }

    public function save()
    {
        $this->validate();

        TermsCondition::create([
            'team_id' => $this->team_id,
            'location_id' => $this->location,
            'title' => $this->title,
            'term_content' => $this->term_content,
        ]);

        session()->flash('message', 'Terms & Conditions created successfully.');
        return redirect()->route('tenant.terms-conditions');
    }

    public function render()
    {
        return view('livewire.create-terms-and-conditions');
    }
}
