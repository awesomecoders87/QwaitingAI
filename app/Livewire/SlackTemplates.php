<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TemplateVariable;
use App\Models\SlackSetting;
use App\Models\SlackTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class SlackTemplates extends Component
{

     public array $templates = [
        'ticket_generation_message' => ['name' => 'Ticket Generation Message'],
        'reminder_message' => ['name' => 'Reminder Message'],
        'next_call_message' => ['name' => 'Next Call Message'],
        'skip_call_message' => ['name' => 'Skip Call Message'],
        'recall_message' => ['name' => 'Recall Message'],
        'feedback_sms_message' => ['name' => 'Feedback SMS'],
        'new_booking_sms_message' => ['name' => 'New Booking SMS'],
        'reschedule_booking_sms' => ['name' => 'Reschedule Booking SMS'],
        'cancel_booking_sms' => ['name' => 'Cancel Booking SMS'],
    ];

    public string $selectedTemplate = 'ticket_generation_message';
    public bool $status = false;
    public string $template_name = '';
    public string $body = '';
    public string $selectedVariable = '';
    public array $variables = [];
    public int $teamId;
    public $locationId;
    public $smsapi;
    public $slack_setting_enable;
    public $successMessage = null; // For alert handling

    public function mount()
    {
        $user = Auth::user();
        // if (!$user->hasPermissionTo('Message Template Edit')) {
        //     abort(403);
        // }


        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $slackSetting = SlackSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();
            if (isset($slackSetting)) {
               $this->slack_setting_enable = $slackSetting->status == 1 ? true : false;
            }else{
                $this->slack_setting_enable = false;
            }

        $this->loadTemplate();
        $this->loadVariables();
    }

    public function loadTemplate()
    {
        $template = SlackTemplate::where('team_id', $this->teamId)->where('location_id', $this->locationId)->first();

        if ($template) {
            $this->template_name = $template->{$this->selectedTemplate. '_template'} ?? '';
            $this->body = $template->{$this->selectedTemplate} ?? '';
            $this->status = $template->{$this->selectedTemplate . '_status'} == 1;
        }
    }

    public function loadVariables()
    {
        $this->variables = TemplateVariable::pluck('description', 'variable_name')->toArray();
    }

    public function updatedSelectedTemplate($value)
    {
        $this->selectedTemplate = $value;
        $this->loadTemplate();
    }

    public function saveTemplate()
    {


        SlackTemplate::updateOrCreate(
            ['team_id' => $this->teamId,'location_id'=> $this->locationId],
            [
                $this->selectedTemplate.'_template' => $this->template_name,
                $this->selectedTemplate => $this->body,
                $this->selectedTemplate . '_status' => $this->status,
            ]
        );

         // Save settings logic...
         session()->flash('success', 'Settings Updated Successfully.');
        
         // Set success message property
         $this->successMessage = 'Settings Updated Successfully.';
 
         // Dispatch event to hide the alert after timeout
         $this->dispatch('hide-alert');
    }

    public function appendVariableToBody()
    {
        if ($this->selectedVariable) {
            $this->body .= " {$this->selectedVariable} ";
            $this->selectedVariable = '';
        }
    }

    public function render()
    {
        return view('livewire.slack-templates');
    }
}
