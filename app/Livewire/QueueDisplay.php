<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Queue,
    Team,
    Location,
    DisplaySettingModel,
    QueueStorage,
    ReverbDetail,
    Counter
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

#[Layout('components.layouts.custom-display-layout')]
class QueueDisplay extends Component
{

    #[Title('Call Display')]
    public $teamId;
    public $queueToDisplay;
    public $missedCalls;
    public $isFullscreen = false;
    public $header = true;
    public $allLocations = [];
    public $location;
    public $locationName;
    public $imageTemplates = [];
    public $videoTemplates = [];
    public $currentTemplate;
    public $displaySetting;
    public $holdCalls;
    public $reverbDetails;
    public $reverbKey, $reverbHost, $reverbPort, $reverbScheme;

    public function mount()
    {

        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }
        
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
    
        if (!empty($this->location))
            $this->locationName =  Location::locationName($this->location);
        $this->allLocations = Location::where('team_id', $this->teamId)->pluck('location_name', 'id');

        if ($this->location == '' || !Auth::check()) {
            $this->location = '';
            $this->allLocations = Location::where('team_id', $this->teamId)->pluck('location_name', 'id');
            if (isset($this->allLocations)) {
                $this->location = Location::where('team_id', $this->teamId)->value('id');
            }
        }
        $this->displaySetting = DisplaySettingModel::getDetails($this->teamId);
        $this->reverbDetails = ReverbDetail::viewReverbDetails($this->teamId,$this->location);
        $this->reverbKey = $this->reverbDetails->key ?? env('REVERB_APP_KEY');
        $this->reverbHost = $this->reverbDetails->host ?? env('REVERB_HOST', '127.0.0.1');
        $this->reverbPort = $this->reverbDetails->port ?? env('REVERB_PORT', 8080);
        $this->reverbScheme = $this->reverbDetails->scheme ?? env('REVERB_SCHEME', 'http');

    }

    public function render()
    {
        if (!empty($this->location)) {

            $this->queueToDisplay = Queue::displayQueue($this->teamId, (int) $this->location, Queue::MAX_QUEUE_DISPLAY);
        //    dd($this->queueToDisplay);
            $this->missedCalls = Queue::getMissedCallId(['team_id' => $this->teamId]);
            $this->holdCalls = QueueStorage::getHoldCall(['team_id' => $this->teamId], 0, (int) $this->location);
        }
     
        return view('livewire.queue-display');
    }



    // public function getListeners()
    // {
    //     // return [
    //     //     "echo:queue-progress.{$this->teamId},QueueProgress" => 'pushLiveQueue',
    //     // ];

    //     return [
    //         "echo:queue-progress.{$this->teamId},.queue.progress" => 'pushLiveQueue',
    //     ];
    // }

    #[On('display-update')]
    public function pushLiveQueue($event)
    {

        if (!empty($event['queue'])) {

            if (isset($event['queue']['status'], $event['queue']['called_datetime']) && $event['queue']['status'] == Queue::STATUS_PROGRESS && !empty($event['queue']['called_datetime'])) {

                $screenTune =  $this->displaySetting?->screen_tune ?? DisplaySettingModel::DEFAULT_SETTING_TUNE;

                $voice  = DisplaySettingModel::getVoiceChosen($screenTune);

              if(!empty($event['queue']['counter_id'])){
                        $counterName= Counter::where('id',$event['queue']['counter_id'])->value('name');
                    }


                    if(!empty($counterName)){
                        if ($voice && ($voice['lang'] != DisplaySettingModel::DEFAULT_EN_LANG)  && $voice['lang'] == 'es-ES') {
                            $speech  = 'Tiquete number ' . $event['queue']['start_acronym'] . $event['queue']['token'].' on '.$counterName;
                        }else{
                            $speech  = 'token number ' . $event['queue']['start_acronym'] . $event['queue']['token'].' on '.$counterName;

                        }
                    }else{

                        $speech  = 'token number ' . $event['queue']['start_acronym'] . $event['queue']['token'];
                    }

             
   
                if ($voice && $voice['lang'] != DisplaySettingModel::DEFAULT_EN_LANG) {

                    $voiceLang = substr($voice['lang'], 0, 2);
                    $tr = new GoogleTranslate();
                    $tr->setTarget($voiceLang);
                    $speech = $tr->translate($speech);
                }

                $this->dispatch('announcement-display', [
                    'speech' => $speech,
                    'screen_tune' => $screenTune,
                    'voice_lang' => $voice['lang']
                ]);
            }


            $this->queueToDisplay = Queue::displayQueue($this->teamId, (int)  $this->location,  Queue::MAX_QUEUE_DISPLAY);
            $this->missedCalls = Queue::getMissedCallId(['team_id' => $this->teamId]);
            $this->holdCalls = QueueStorage::getHoldCall(['team_id' => $this->teamId], 0, (int) $this->location);
           
        }
    }

    public function toggleFullscreen()
    {
        $this->isFullscreen = !$this->isFullscreen;

        $this->dispatch('event-fullscreen', ['isFullscreen' => $this->isFullscreen]);
    }

    public function updatedLocation($value)
    {
        $this->location = $value;
        Session::put('selectedLocation',  $this->location);
        $this->locationName =  Location::locationName($value);
        $this->locationStep = false;
        $this->firstStep = true;
    }
}
