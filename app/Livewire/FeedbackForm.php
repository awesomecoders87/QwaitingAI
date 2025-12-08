<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{FeedbackQuestion, FeedbackSetting};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Title;

class FeedbackForm extends Component
{
    #[Title('Feedback Form')]

    public ?array $questions = [];
    public $teamId;
    public $locationId;
    public $successMessage = null;
    
    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Feedback Setting')) {
            abort(403);
        }
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $feedbackQuestion = FeedbackQuestion::where('team_id', $this->teamId)->where('location_id', $this->locationId)->first();
        $this->questions = $feedbackQuestion ? json_decode($feedbackQuestion->questions, true) : [];
    }

    public function addQuestion()
    {
        $this->questions[] = ['question' => ''];
    }

public function removeQuestion($index)
{
    unset($this->questions[$index]);
    $this->questions = array_values($this->questions);
}


    public function save()
    {
        $this->validate([
            'questions.*.question' => 'required|string',
        ]);

        FeedbackQuestion::updateOrCreate(
            ['team_id' => $this->teamId,'location_id'=> $this->locationId],
            ['questions' => json_encode($this->questions)]
        );

        // Save settings logic...
        session()->flash('success', 'Settings Updated Successfully.');
        $this->successMessage = 'Settings Updated Successfully.';
        $this->dispatch('hide-alert');
    }

    public function render()
    {
        return view('livewire.feedback-form');
    }
}
