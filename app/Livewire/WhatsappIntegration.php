<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SmsAPI as ModelSMS;
use App\Models\ActivityLog; 
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class WhatsappIntegration extends Component
{
     #[Title('Sms Api')]

    public $type;
    public $status;
    public $parameter_of_sms = [];
    public $teamId;
    public $locationId;
    public $sms_api_url;
    public $request_method;
    public $is_whatsapp;
    public $successMessage = null; // For alert handling
    public $contact;
    public $message;
    public $authentication;
    public $token;
    public $is_template;
    public $userAuth;

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
        $this->userAuth = Auth::user();
        $whatsappintegration = ModelSMS::where('team_id', $this->teamId)->where('location_id',$this->locationId)->where('is_whatsapp', 1)->where('status',1)->first();


        if ($whatsappintegration) {
            $this->type = $whatsappintegration->type;
            $this->sms_api_url = $whatsappintegration->sms_api_url;
            $this->request_method = $whatsappintegration->url_method ?? 'post';
            $this->status = (bool) ($whatsappintegration->status ?? false);
            $this->is_template = (bool) ($whatsappintegration->is_template ?? false);
            $this->is_whatsapp = 1;
            $this->contact = $whatsappintegration->contact ?? 'number';
            $this->message = $whatsappintegration->message ?? 'message';
            $this->parameter_of_sms = $whatsappintegration->json ? json_decode($whatsappintegration->json, true) : [];
            $this->authentication = $whatsappintegration->authentication;
            $this->token = $whatsappintegration->token;
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
            ['team_id' => $this->teamId,'location_id'=>$this->locationId,'is_whatsapp' => 1],
            [
                'json' => json_encode($this->parameter_of_sms),
                'type' => $this->type,
                'sms_api_url' => $this->sms_api_url ?? '',
                'is_sms' => 0,
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
        session()->flash('success', 'Settings Updated Successfully.');
        
        // Set success message property
        $this->successMessage = 'Settings Updated Successfully.';

        //  ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Whatsapp Integration Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);

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
        return view('livewire.whatsapp-integration');
    }
}
