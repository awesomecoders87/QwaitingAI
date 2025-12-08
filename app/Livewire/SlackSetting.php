<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SlackSetting as SlackSettingModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SlackSetting extends Component
{
    public $teamId;
    public $locationId;
    public $slack_user_auth_token;
    public $slack_user_bot_auth_token;
    public $status = true;

    public function mount(){
        $this->teamId =tenant('id');
        $this->locationId =  Session::get('selectedLocation');
        $setting = SlackSettingModel::where('team_id',$this->teamId)->where('location_id',$this->locationId)->first();

        if(!empty($setting)){
             $this->slack_user_auth_token = $setting->slack_user_auth_token;
             $this->slack_user_bot_auth_token = $setting->slack_user_bot_auth_token;
             $this->status = (bool)$setting->status ?? true;
        }
    }

     // ✅ Validation rules
    protected function rules()
    {
        return [
            'slack_user_auth_token' => 'required|string|max:255',
            'slack_user_bot_auth_token' => 'required|string|max:255',
            'status' => 'boolean',
        ];
    }

     public function save()
    {
        $this->validate();

        SlackSettingModel::updateOrCreate(
            [
                'team_id'     => $this->teamId,
                'location_id' => $this->locationId,
            ],
            [
                'slack_user_auth_token'     => $this->slack_user_auth_token,
                'slack_user_bot_auth_token' => $this->slack_user_bot_auth_token,
                'status'                    => $this->status,
            ]
        );

        $this->dispatch('updated');
    }

    public function render()
    {
        return view('livewire.slack-setting');
    }
}
