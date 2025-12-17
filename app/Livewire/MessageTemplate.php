<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TemplateVariable;
use App\Models\SmsAPI;
use App\Models\MessageTemplate as MessageTemplateModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class MessageTemplate extends Component
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
        'stripe_sms_message' => ['name' => 'Stripe SMS Message'],
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
    public $isTemplate;
    public $successMessage = null; // For alert handling

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Message Template Edit')) {
            abort(403);
        }


        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->smsapi = SmsAPI::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('is_sms', 1)
            ->where('status', 1)
            ->first();
            if ($this->smsapi) {
               $this->isTemplate = $this->smsapi->is_template == 1 ? true : false;
            }else{
                $this->isTemplate = false;
            }


//             $template = "Hello, your ticket is created and the token number is  {{token}} .test  awdu iasdxkas  {{panel_name}}  . sahdfsa  aisdnxask aksh  {{category_1}} .shfsd  {{booking_date}}";

// preg_match_all('/\{\{\s*(.*?)\s*\}\}/', $template, $matches);

// $variables = $matches[1]; // array of variable names

// dd($variables);
        $this->loadTemplate();
        $this->loadVariables();
    }

    public function loadTemplate()
    {
        $template = MessageTemplateModel::where('team_id', $this->teamId)->where('location_id', $this->locationId)->first();

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


        MessageTemplateModel::updateOrCreate(
            ['team_id' => $this->teamId,'location_id'=> $this->locationId],
            [
                $this->selectedTemplate.'_template' => $this->template_name,
                $this->selectedTemplate => $this->body,
                $this->selectedTemplate . '_status' => $this->status,
                'enable_template_name' => $this->isTemplate  ? 1 : 0
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
        if (Auth::check() && !Auth::user()->can('Message Template Edit')) {
            abort(403);
        }

        return view('livewire.message-template');
    }
}
