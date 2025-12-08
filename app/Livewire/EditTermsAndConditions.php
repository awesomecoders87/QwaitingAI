<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TermsCondition;
use Livewire\Attributes\Title;

class EditTermsAndConditions extends Component
{
    #[Title('Edit Terms and Condition')]

    public $term;
    public $title;
    public $term_content;
    public $team_id;

    protected $rules = [
        'title' => 'required|string|max:255',
        'term_content' => 'nullable|string',
    ];

    public function mount($id)
    {
        $this->term = TermsCondition::findOrFail($id);
        $this->title = $this->term->title;
        $this->term_content = $this->term->term_content;
        $this->team_id = $this->term->team_id;
    }

    public function update()
    {
        $this->validate();

        $this->term->update([
            'title' => $this->title,
            'term_content' => $this->term_content,
        ]);

        session()->flash('message', 'Terms & Conditions updated successfully.');
        return redirect()->route('tenant.terms-conditions');
    }

    public function render()
    {
        return view('livewire.edit-terms-and-conditions');
    }
}
