<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\AccountSetting;
use App\Models\CustomSlot;
use App\Models\Location;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class GeofenceComponent extends Component
{
    #[Title('Geofence')]
    
    public $teamId;
    public $locationId;
    public $type;
    public $geofence = false;
    // public $geofenceLatitude;
    // public $geofenceLongitude;
    public $geofenceMaxDistance;
    public $geofenceMaxDistanceUnit;
    public $slots =[];
    public $showSetting = true;
    public $locationCoodinate;

    public function mount($teamId,$locationId,$slotType){
        $this->teamId = $teamId;
        $this->locationId = $locationId;
        $this->type = $slotType;
        $this->slots = AccountSetting::distanceUnit();

        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', $this->type)
        ->select('id','is_geofence','geofence_latitude','geofence_longitude','geofence_max_distance','geofence_max_distance_unit')
        ->first();

        
        $this->geofence = $accountdetail->is_geofence ?? '';
        // $this->geofenceLatitude = $this->locationCoodinate->latitude ?? '';
        // $this->geofenceLongitude = $this->locationCoodinate->longitude ?? '';
        $this->geofenceMaxDistance = $accountdetail->geofence_max_distance ?? '';
        $this->geofenceMaxDistanceUnit = $accountdetail->geofence_max_distance_unit ?? 'feet';

        if($this->geofence){
            $this->showSetting = true;
        }


    }

    public function updatedGeofence($value){
    
        // $this->showSetting = $value;
        // $this->dispatch('')
       
    }

    public function saveSetting(){
 
        $this->locationCoodinate =  Location::where('id',$this->locationId)->select('longitude','latitude')->first();
      
        $accountdetail = AccountSetting::where( 'team_id', $this->teamId )
        ->where( 'location_id', $this->locationId )
        ->where('slot_type', $this->type)
        ->update([
            'is_geofence'=>$this->geofence,
            'geofence_latitude'=>$this->locationCoodinate->latitude,
            'geofence_longitude'=>$this->locationCoodinate->longitude,
            'geofence_max_distance'=>$this->geofenceMaxDistance,
            'geofence_max_distance_unit'=>$this->geofenceMaxDistanceUnit,
        ]);

        $this->dispatch( 'saved', [ 'message'=>'Updated Successfully' ] );
    // $this->dispatch('notify', type: 'success', message: 'Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.geofence-component');
    }
}
