<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Addon;
use App\Models\ActivityLog;
use Session;
use Auth;

class TwillioVideoSetting extends Component
{

    #[Title('Twillio video Setting')]

    public string $twillio_video_key = '';
    public string $twillio_video_secret = '';
    public string $twillio_video_accountSid = '';
    public string $qrCode;
    public $teamId;
    public $userAuth;
    public $locationId;


    public function mount()
    {

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
        $addon = Addon::where('team_id',$this->teamId)->where('location_id',$this->locationId)->first();

        if ($addon) {
            $this->twillio_video_key = $addon->twillio_video_key ?? '';
            $this->twillio_video_secret = $addon->twillio_video_secret ?? '';
            $this->twillio_video_accountSid = $addon->twillio_video_accountSid ?? '';
        }
    }


    public function save()
    {
        // ✅ Validation rules
        $this->validate([
            'twillio_video_key' => 'required|string|max:255',
            'twillio_video_secret' => 'required|string|max:255',
            'twillio_video_accountSid' => 'required|string|max:255',
        ], [
            'twillio_video_key.required' => 'The Twilio Video Key is required.',
            'twillio_video_secret.required' => 'The Twilio Video Secret is required.',
            'twillio_video_accountSid.required' => 'The Twilio Account SID is required.',
        ]);

        // ✅ Save or update
        $addon = Addon::updateOrCreate(
            [
                'team_id' => $this->teamId,
                'location_id' => $this->locationId,
            ],
            [
                'user_id' => Auth::id(),
                'twillio_video_key' => $this->twillio_video_key,
                'twillio_video_secret' => $this->twillio_video_secret,
                'twillio_video_accountSid' => $this->twillio_video_accountSid,
            ]
        );

        ActivityLog::storeLog(
            $this->teamId,
            $this->userAuth->id,
            null,
            null,
            'Addons',
            $this->locationId,
            ActivityLog::SETTINGS,
            null,
            $this->userAuth
        );

        // ✅ Emit event for frontend
        $this->dispatch('addons-updated');
    }
    public function render()
    {
        return view('livewire.twillio-video-setting');
    }
}
