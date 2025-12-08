<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TemplateVariable;
use App\Models\WhatsappTemplate as WhatsappMessageTemplate;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\SmsAPI;

class WhatsappTemplate extends Component
{
   public array $templates = [
        'ticket_generation_message' => ['name' => 'Ticket Generation Message'],
        'reminder_message' => ['name' => 'Reminder Message'],
        'next_call_message' => ['name' => 'Next Call Message'],
        'skip_call_message' => ['name' => 'Skip Call Message'],
        'recall_message' => ['name' => 'Recall Message'],
        'feedback_sms_message' => ['name' => 'Feedback SMS'],
        'new_booking_sms' => ['name' => 'New Booking SMS'],
        'reschedule_booking_sms' => ['name' => 'Reschedule Booking SMS'],
        'cancel_booking_sms' => ['name' => 'Cancel Booking SMS'],
    ];

    public string $selectedTemplate = 'ticket_generation_message';
    public bool $status = false;
    public string $body = '';
    public string $selectedVariable = '';
    public array $variables = [];
    public int $teamId;
    public $locationId;
    public $whatsappApi;
    public $isTemplate;
    public $successMessage = null;
    public $template_name;
    public $userAuth;


    public function mount()
    {
   
        $user = Auth::user();
        if (!$user->hasPermissionTo('Message Template Edit')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
        $this->whatsappApi = SmsAPI::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('is_whatsapp', 1)
            ->where('status', 1)
            ->first();
            if ($this->whatsappApi) {
               $this->isTemplate = $this->whatsappApi->is_template == 1 ? true : false;
            }else{
                $this->isTemplate = false;
            }

        $this->loadTemplate();
        $this->loadVariables();
    }

    public function loadTemplate()
    {
        $template = WhatsappMessageTemplate::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

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

    public function appendVariableToBody()
    {
        if ($this->selectedVariable) {
            $this->body .= " {$this->selectedVariable} ";
            $this->selectedVariable = '';
        }
    }

    public function saveTemplate()
    {
        WhatsappMessageTemplate::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->locationId],
            [
                $this->selectedTemplate.'_template' => $this->template_name,
                $this->selectedTemplate => $this->body,
                $this->selectedTemplate . '_status' => $this->status,
                   'enable_template_name' => $this->isTemplate  ? 1 : 0
            ]
        );

        $this->successMessage = 'WhatsApp Template Updated Successfully.';

        // ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Whatsapp Template Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);

        $this->dispatch('hide-alert');
    }

    public function render()
    {
        return view('livewire.whatsapp-template');
    }
}
