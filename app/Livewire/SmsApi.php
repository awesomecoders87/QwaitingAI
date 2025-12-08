<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SmsAPI as ModelSMS;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class SmsApi extends Component
{
    #[Title('Sms Api')]

    public $type;
    public $status;
    public $contact;
    public $message;
    public $parameter_of_sms = [];
    public $teamId;
    public $locationId;
    public $sms_api_url;
    public $request_method;
    public $is_sms;
    public $is_template;
    public $successMessage = null; // For alert handling
    public $authentication;
    public $token;

    public function rules()
    {
        return [
            'authentication' => 'required|in:no_auth,bearer_token',
            'token' => $this->authentication === 'bearer_token' ? 'required|string' : 'nullable',
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Message Template Edit')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        $smsIntegration = ModelSMS::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('is_sms', 1)
            ->where('status',1)
            ->first();

        if ($smsIntegration) {
            $this->type = $smsIntegration->type;
            $this->sms_api_url = $smsIntegration->sms_api_url;
            $this->request_method = $smsIntegration->url_method ?? 'post';
            $this->status = (bool) ($smsIntegration->status ?? false);
            $this->is_template = (bool) ($smsIntegration->is_template ?? false);
            $this->is_sms = 1;
            $this->contact = $smsIntegration->contact ?? 'number';
            $this->message = $smsIntegration->message ?? 'message';
            $this->parameter_of_sms = $smsIntegration->json ? json_decode($smsIntegration->json, true) : [];
            $this->authentication = $smsIntegration->authentication;
            $this->token = $smsIntegration->token;
        }
    }

    public function updatedtype($value)
    {

        $this->parameter_of_sms = [];
        $this->contact = 'number';
        $this->message = 'message';
        $this->type = $value;
        $this->sms_api_url = '';
        $this->request_method = '';
        $this->status = (bool) true;
        $this->is_sms = 1;

        $smsIntegration = ModelSMS::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('type', $value)
            ->where('is_sms', 1)
            ->first();
        if ($smsIntegration) {
            $this->type = $smsIntegration->type;
            $this->sms_api_url = $smsIntegration->sms_api_url;
            $this->request_method = $smsIntegration->url_method ?? 'post';
            $this->status = (bool) ($smsIntegration->status ?? false);
            $this->is_template = (bool) ($smsIntegration->is_template ?? false);
            $this->is_sms = 1;
            $this->contact = $smsIntegration->contact ?? 'number';
            $this->message = $smsIntegration->message ?? 'message';
            $this->parameter_of_sms = $smsIntegration->json ? json_decode($smsIntegration->json, true) : [];
            $this->authentication = $smsIntegration->authentication;
            $this->token = $smsIntegration->token;
        } else {
            $this->contact = 'number';
            $this->message = 'message';
        }
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|string',
            'status' => 'boolean',
            'parameter_of_sms.*.parameter_key' => 'required|string|distinct',
            'parameter_of_sms.*.parameter_value' => 'required|string',
        ]);

        $rules = [];

        // Conditionally add sms_api_url validation
        if ($this->type !== 'twillio') {
            $rules['sms_api_url'] = 'required|url'; // must be a valid URL
        }

         // Conditionally require token if authentication is bearer_token
        if ($this->authentication === 'bearer_token') {
            $rules['token'] = 'required|string';
        }

        // Validate again if there are conditional rules
        if (!empty($rules)) {
            $this->validate($rules);
        }

        // If Twillio, make sure sms_api_url is empty
        if ($this->type == 'twillio') {
            $this->sms_api_url = '';
            $this->request_method = '';
        }

        ModelSMS::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->locationId, 'is_sms' => 1, 'type' => $this->type],
            [
                'json' => json_encode($this->parameter_of_sms),
                'sms_api_url' => $this->sms_api_url ?? '',
                'contact' => $this->contact ?? '',
                'message' => $this->message ?? '',
                'is_template' => $this->is_template ?? 0,
                'url_method' => $this->request_method ?? '',
                'status' => $this->status,
                'authentication' => $this->authentication,
                'token' => $this->token
            ]
        );

        // Save settings logic...
        // session()->flash('success', 'Settings Updated Successfully.');

        // Set success message property
        $this->successMessage = 'Settings Updated Successfully.';
        $this->dispatch('updated');
        // Dispatch event to hide the alert after timeout
        $this->dispatch('hide-alert');
    }

    public function addParameter()
    {
        $this->parameter_of_sms[] = ['parameter_key' => '', 'parameter_value' => ''];
    }

    public function removeParameter($index)
    {
        unset($this->parameter_of_sms[$index]);
        $this->parameter_of_sms = array_values($this->parameter_of_sms);
    }

    public function render()
    {
        return view('livewire.sms-api');
    }
}
