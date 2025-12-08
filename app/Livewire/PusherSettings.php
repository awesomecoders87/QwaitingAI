<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Country;
use App\Models\PusherDetail;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;


class PusherSettings extends Component
{
    #[Title('Pusher Setting')]

    public $pusherSettings = [];
    public $data = [];
    public $teamId;
    public $locationId;

    public function mount()
    {
        $checkuser = Auth::user();
        if (!$checkuser->hasPermissionTo('Pusher Settings')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        $this->pusherSettings = PusherDetail::where('team_id', tenant('id'))->where('location_id',$this->locationId)->first();

        if ($this->pusherSettings) {
            $this->data = $this->pusherSettings->toArray();
        }
    }

    protected function rules()
    {
        return [
            'data.key' => 'required|string',
            'data.secret' => 'required|string',
            'data.app_id' => 'required|string',
            'data.options_cluster' => 'required|string',
        ];
    }

    protected $messages = [
        'data.key.required' => 'The Pusher App Key is required.',
        'data.secret.required' => 'The Pusher Secret is required.',
        'data.app_id.required' => 'The Pusher APP ID is required.',
        'data.options_cluster.required' => 'The Pusher Option Cluster is required.',
    ];

    public function save()
    {
        $this->validate(); // ✅ Validate before saving

        if (!empty($this->pusherSettings)) {
            $this->pusherSettings->update($this->data);
        } else {
            PusherDetail::firstOrCreate(
                ['team_id' => $this->teamId, 'location_id' => $this->locationId],
                $this->data
            );
        }

        $this->dispatch('pusher-settings-updated');
    }


    public function render()
    {
        return view('livewire.pusher-settings');
    }
}
