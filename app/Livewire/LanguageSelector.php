<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LanguageSetting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSelector extends Component
{
     public $locationId;
    public $teamId;
    public $language;
    public $chooseLanguage;
    public $isEnabledlanguage = false;
    public $languages= [];
    public $defaultSelected = [];

    public function mount(){
        $this->teamId =tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->language =LanguageSetting::where('team_id',$this->teamId)->first();
        if($this->language){
            $this->isEnabledlanguage = $this->language->enabled_language_settings== 1 ? true: false;
            $this->defaultSelected = $this->language->default_language ?? 'en';
            if(!empty($this->language->available_languages)){
                 $this->languages = $this->language->available_languages;
            }
        }

        if( Session::has('app_locale')){
            $this->defaultSelected= Session::get('app_locale');
             $this->chooseLanguage =$this->defaultSelected;
            App::setLocale($this->defaultSelected);
     
        }
       
     
    }

    public function updatedchooseLanguage($value){
        $this->chooseLanguage = $value;
        $this->defaultSelected=$value;
        App::setLocale($value);
        Session::put('app_locale', $value);
         $this->dispatch('locationUpdated', $value);
    }

    public function render()
    {
        return view('livewire.language-selector');
    }
}
