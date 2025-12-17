<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\TemplateVariable;
use App\Models\NotificationTemplate as NotificationTemplateModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class NotificationTemplates extends Component
{
    #[Title('Notification Template')]

    public $templates = [
        'ticket' => ['name' => 'Ticket Generation Notifications'],
        'service_call' => ['name' => 'Service Call Notifications'],
        'service_recall' => ['name' => 'Service Recall Notifications'],
        'feedback' => ['name' => 'Feedback Message'],
        'call_skip' => ['name' => 'Call Skip Notifications'],
        'booking_confirmed' => ['name' => 'Booking Confirmed Notifications'],
        'booking_reschedule' => ['name' => 'Booking Reschedule Notifications'],
        'booking_cancel' => ['name' => 'Booking Cancel Notifications'],
        'reminder' => ['name' => 'Reminder Notifications'],
        'stripe_sms' => ['name' => 'Stripe Credit SMS Notification'],
    ];

    public $selectedTemplate = 'ticket';
    public $subject = '';
    public $body = '';
    public $selectedVariable;
    public $variables = [];
    public $templateStatus = [];
    public $successMessage = null; // For alert handling
    public int $teamId;
    public $locationId;

    public function mount()
    {

        $user = Auth::user();
        if (!$user->hasPermissionTo('Message Template Edit')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        $this->loadTemplate();
        $this->loadVariables();
    }

   public function loadTemplate()
{
    $template = NotificationTemplateModel::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->first();

    // Always initialize templateStatus with default 0 (disabled)
    foreach ($this->templates as $key => $info) {
        $this->templateStatus[$key] = 0;
    }

    // If template exists, fill data
    if ($template) {
        foreach ($this->templates as $key => $info) {
            $this->templateStatus[$key] = $template->{$key . '_notification_status'} == 1 ? 1 : 0;
        }
        $this->subject = $template->{$this->selectedTemplate . '_notification_subject'} ?? '';
        $this->body = $template->{$this->selectedTemplate . '_notification'} ?? '';
    } else {
        // Reset subject and body if no template is found
        $this->subject = '';
        $this->body = '';
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
        $data = [
            $this->selectedTemplate . '_notification' => $this->body,
            $this->selectedTemplate . '_notification_subject' => $this->subject,
            $this->selectedTemplate . '_notification_status' => $this->templateStatus[$this->selectedTemplate] ?? 0,
        ];

        NotificationTemplateModel::updateOrCreate(
            ['team_id' => $this->teamId,'location_id'=> $this->locationId],
            $data
        );

        // Save settings logic...
        session()->flash('success', 'Settings Updated Successfully.');
        
        // Set success message property
        $this->successMessage = 'Settings Updated Successfully.';

        // Dispatch event to hide the alert after timeout
        $this->dispatch('hide-alert');
    }

    public function appendVariableToSubject()
    {
        if ($this->selectedVariable) {
            $this->subject .= ' ' . $this->selectedVariable;
        }
    }

    public function appendVariableToBody()
    {
        if ($this->selectedVariable) {
            $this->body .= ' ' . $this->selectedVariable;
        }
    }

    public function render()
    {
        return view('livewire.notification-templates');
    }
}
