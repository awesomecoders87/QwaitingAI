<?php
namespace App\Livewire;

use App\Models\SalesforceSetting as SalesforceSettingModel;
use App\Models\SalesforceConnection;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SalesForceSetting extends Component
{
    public $client_id;
    public $client_secret;
    public $redirect_uri;
    public $teamId;
    public $locationId;
    public $connectionData;
    public $enableConnectionBtn = true;
    public $enableFetchUserBtn = true;

    public function mount(){
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');

      $this->redirect_uri = url('/salesforce/callback');

       $this->connectionData = SalesforceConnection::where('team_id',$this->teamId)
        ->where('location_id',$this->locationId)
        ->where('user_id',Auth::user()->id)
        ->where('status',1)
        ->first();
      if($this->connectionData && !empty( $this->connectionData->salesforce_refresh_token)){
           $this->enableConnectionBtn = false;
           $this->enableFetchUserBtn = true;
        }else{
          $this->enableConnectionBtn = true;
        $this->enableFetchUserBtn = false;
      }

     

    
    }

    public function save()
    {
        $this->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
             'redirect_uri' => 'required|url',
        ]);

       $this->redirect_uri = url('/salesforce/callback');

        SalesforceSettingModel::updateOrCreate(
            [
                'team_id' => $this->teamId ?? Auth::user()->team_id,
                'location_id' => $this->locationId,
            ],
            [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'created_by' => Auth::id(),
            ]
        );

        session()->flash('message', 'Salesforce settings saved successfully.');
            $this->dispatch('updated');
    }

    public function render()
    {
        $setting = SalesforceSettingModel::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->first();

        if ($setting) {
            $this->client_id = $setting->client_id;
            $this->client_secret = $setting->client_secret;
            if(empty($this->client_id) || empty($this->client_secret)){
              $this->enableConnectionBtn = false;
              $this->enableFetchUserBtn = false;
            }
        }else{
             $this->enableConnectionBtn = false;
           $this->enableFetchUserBtn = false;
        }

        return view('livewire.sales-force-setting');
    }
}
