<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Location, AccountSetting, SiteDetail,LanguageSetting};
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\App;

#[Layout('components.layouts.custom-layout')]
class Main extends Component
{
    public $header = true;
    public $teamId;
    public $location;
    public $locationName;
    public $bookingHeadingText;
    public $locationStep = true;
    public $firstStep = false;
    public $allLocations = [];
    public $accountSetting;

    public function mount($location_id = null){
        
        $this->teamId = tenant('id');
        if (empty($this->teamId)) {
            abort(404);
        }

         // Check for route parameter
        // if (!Session::has('selectedLocation') && $location_id !== null) {
        //     $this->location = base64_decode($location_id,true);
        //     Session::put('selectedLocation', $this->location); 
            
        // } else {
        //     $this->location = Session::get('selectedLocation');
        // }
         $this->location = base64_decode($location_id,true);
         Session::put('selectedLocation', $this->location); 
         $this->location = Session::get('selectedLocation');
    
        if(empty($this->location)){
            $this->location = '';
            $this->allLocations = Location::select('id', 'location_name', 'address', 'location_image')->where('team_id', $this->teamId)->where('status',1)->get();

            $this->locationStep = true;
            $this->firstStep = false;
        }else{

            $this->bookingHeadingText =  SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->location)->value('app_heading_third');
            $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->first();
          
            $this->locationStep = false;
            $this->firstStep = true;

            // if(empty($this->accountSetting)){
            //     abort(400);
            // }

              $setting = LanguageSetting::where('team_id',$this->teamId)
                ->where('location_id', $this->location)
                ->first();
                $checkLocal =false;

            if ($setting && $setting->enabled_language_settings && !empty($setting->default_language)) {
                App::setLocale($setting->default_language);
                Session::put('app_locale', $setting->default_language);

                if (!Session::has('language_applied_once') && $setting->default_language !== 'en') {
                        Session::put('language_applied_once', true);

                        // Dispatch JavaScript to reload the page once
                        $this->dispatch('reload');
                    }
            }
            if($this->accountSetting->booking_system == AccountSetting::STATUS_INACTIVE){
               return redirect()->to('queue');
            }
        }

    }

    public function updatedLocation($value)
    {
        $this->location = $value;
        $this->locationName =  Location::locationName($value);
        $this->locationStep = false;
        $this->firstStep = true;
        Session::forget('selectedLocation');
        Session::put('selectedLocation', $this->location);

        $this->bookingHeadingText =  SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->location)->value('app_heading_third');
      
        $this->accountSetting = AccountSetting::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->where('slot_type', AccountSetting::BOOKING_SLOT)
        ->first();

        if(empty($this->accountSetting)){
            abort(400);
        }

        if($this->accountSetting->booking_system == AccountSetting::STATUS_INACTIVE){
            return redirect()->to('queue');
         }

        $this->dispatch('header-show');
    }


    public function render()
    {

         $domainSlug = request()->route('domainSlug');

            return view('livewire.main')->with([
                'domainSlug' => $domainSlug,
            ]);
    }
}
