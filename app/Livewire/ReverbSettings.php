<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Country;
use App\Models\ReverbDetail;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;


class ReverbSettings extends Component
{
    #[Title('Reverb Setting')]

    public $reverbSettings = [];
    public $data = [];
    public $teamId;
    public $locationId;

    public function mount()
    {
        $checkuser = Auth::user();
        if (!$checkuser->hasPermissionTo('Reverb Settings') && !$checkuser->hasPermissionTo('Pusher Settings')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

        $this->reverbSettings = ReverbDetail::where('team_id', tenant('id'))->where('location_id',$this->locationId)->first();

        if ($this->reverbSettings) {
            $this->data = $this->reverbSettings->toArray();
        } else {
            // Set defaults from environment
            $this->data = [
                'key' => env('REVERB_APP_KEY', ''),
                'secret' => env('REVERB_APP_SECRET', ''),
                'app_id' => env('REVERB_APP_ID', ''),
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
            ];
        }
    }

    protected function rules()
    {
        return [
            'data.key' => 'required|string',
            'data.secret' => 'required|string',
            'data.app_id' => 'required|string',
            'data.host' => 'required|string',
            'data.port' => 'required|integer',
            'data.scheme' => 'required|string|in:http,https',
        ];
    }

    protected $messages = [
        'data.key.required' => 'The Reverb App Key is required.',
        'data.secret.required' => 'The Reverb Secret is required.',
        'data.app_id.required' => 'The Reverb APP ID is required.',
        'data.host.required' => 'The Reverb Host is required.',
        'data.port.required' => 'The Reverb Port is required.',
        'data.scheme.required' => 'The Reverb Scheme is required.',
    ];

    public function save()
    {
        $this->validate();

        if (!empty($this->reverbSettings)) {
            $this->reverbSettings->update($this->data);
        } else {
            ReverbDetail::firstOrCreate(
                ['team_id' => $this->teamId, 'location_id' => $this->locationId],
                $this->data
            );
        }

        $this->dispatch('reverb-settings-updated');
    }


    public function render()
    {
        return view('livewire.reverb-settings');
    }
}

