<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{
    Queue,
    Location,
    ScreenTemplate,
    DisplaySettingModel,
    QueueStorage,
    SiteDetail,
    PusherDetail,
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
use Illuminate\Support\Facades\Log;


#[Layout('components.layouts.custom-display-layout')]
class DisplayScreensecond extends Component
{
    #[Title('Display Screen')]

    public $domainSlug;
    public $teamId;
    public $location;
    public $queueToDisplay;
    public $missedCalls;
    public $isFullscreen = false;
    public $header = true;
    public $allLocations = [];
    public $locationName;
    public $currentTemplate;
    public $videoTemplates = [];
    public $imageTemplates = [];
    public $displaySetting;
    public $holdCalls;
    public $showLogo;
    public $siteData = [];
    public $counterID = [];
    public $categoryID = [];
    public $pusherDetails;
    public $timezone;
    public $pusherKey, $pusherCluster;
    public $waitingCalls;
    public $selectedSound;

    public function mount($id)
    {
        $screenId = base64_decode($id);

        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        $this->teamId = tenant('id');
        $this->currentTemplate = ScreenTemplate::viewDetails($this->teamId, $screenId);
        if (empty($this->currentTemplate)) {
            abort(404);
        }

        $this->imageTemplates =  $this->currentTemplate->json_data ? json_decode($this->currentTemplate->json_data, true) : [];
        $this->videoTemplates =  $this->currentTemplate->json ? json_decode($this->currentTemplate->json, true) : [];

        if (Session::has('selectedLocation')) {
            $this->location = Session::get('selectedLocation');
        } else {
            Session::put('selectedLocation', $this->currentTemplate->location_id);
          $this->location = $this->currentTemplate->location_id;
        }

        if (empty($this->location)) {
            abort(404);
        }

        $datatimezone = Queue::timezoneSet();

        $this->timezone = Session::get('timezone_set');

        // $this->displaySetting = DisplaySettingModel::getDetails($this->teamId, $this->location);
        $this->siteData = SiteDetail::Where('team_id', $this->teamId)->where('location_id', $this->location)->select('id','team_id','location_id','queue_heading_first','queue_heading_second')->first();

        $this->pusherDetails = PusherDetail::viewPusherDetails($this->teamId, $this->location);
        $this->pusherKey = $this->pusherDetails->key ?? env('PUSHER_APP_KEY');
        $this->pusherCluster = $this->pusherDetails->options_cluster ?? env('PUSHER_APP_CLUSTER');
         $this->counterID = $this->currentTemplate?->counters?->pluck('id')?->toArray();
        $this->categoryID = $this->currentTemplate?->categories?->pluck('id')?->toArray();
         $this->getcallsdetail();
    }

   public function getcallsdetail()
{
    try {
        $queues = Queue::getAllQueues(
            $this->teamId,
            (int) $this->location,
            $this->currentTemplate?->show_queue_number,
            $this->currentTemplate->type === "Counter" ? $this->counterID : null,
            $this->currentTemplate->type === "Counter" ? null : $this->categoryID,
            $this->currentTemplate?->is_skip_closed_call_from_display_screen,
            $this->currentTemplate->is_waiting_call_show === ScreenTemplate::STATUS_ACTIVE,
            $this->currentTemplate->is_skip_call_show === ScreenTemplate::STATUS_ACTIVE,
            $this->currentTemplate->is_hold_queue === ScreenTemplate::STATUS_ACTIVE,
        );

        $this->queueToDisplay = $queues['display'];
        $this->waitingCalls   = $queues['waiting'];
        $this->missedCalls    = $queues['missed'];
        $this->holdCalls      = $queues['hold'];

    } catch (\Exception $e) {
        // Log the error
        Log::error('Queue fetch failed: ' . $e->getMessage(), [
            'team_id' => $this->teamId,
            'location' => $this->location
        ]);

        // Fallback empty values so Livewire doesn't break
        $this->queueToDisplay = collect();
        $this->waitingCalls   = collect();
        $this->missedCalls    = collect();
        $this->holdCalls      = collect();

        // $this->dispatch('refreshcomponent');
    }
}


    public function render()
    {

        return view('livewire.display-screen-second');
    }



    #[On('display-update')]
    public function pushLiveQueue($event)
    {

        $counterName = $primarySpeech='';
        if (!empty($event['queue'])) {

            if ($event['queue']['status'] == Queue::STATUS_PROGRESS && !empty($event['queue']['called_datetime'])) {

                $screenTune =  $this->currentTemplate?->display_screen_tune ?? DisplaySettingModel::DEFAULT_SETTING_TUNE;

                $voice  = DisplaySettingModel::getVoiceChosen($screenTune);

                if (!empty($event['queue']['counter_id'])) {
                    $counterName = Counter::where('id', $event['queue']['counter_id'])->value('name');
                }


                if (!empty($counterName)) {
                    if ($voice && ($voice['lang'] != DisplaySettingModel::DEFAULT_EN_LANG)  && $voice['lang'] == 'es-ES') {
                        $speech  = 'tiquete number ' . $event['queue']['start_acronym'] . $event['queue']['token'] . ' on counter ' . $counterName;
                    } else {
                        $speech  = 'token number ' . $event['queue']['start_acronym'] . $event['queue']['token'] . ' on counter ' . $counterName;
                    }
                } else {

                    $speech  = 'token number ' . $event['queue']['start_acronym'] . $event['queue']['token'];
                }



                if ($voice && $voice['lang'] != DisplaySettingModel::DEFAULT_EN_LANG) {

                    if($voice['dual'])
                    {
                        $primarySpeech = $speech;
                    }


                    $voiceLang = substr($voice['lang'], 0, 2);
                    $tr = new GoogleTranslate();
                    $tr->setTarget($voiceLang);
                    $speech = $tr->translate($speech);
                }

                $this->dispatch('announcement-display', [
                    'primary_speech' => isset($primarySpeech) ? $primarySpeech : '',
                    'speech' => $speech,
                    'screen_tune' => $screenTune,
                    'voice_lang' => $voice['lang'],
                    'dual' => $voice['dual'],

                ]);
            }
                $this->getcallsdetail();

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

    }

    #[On('frontend-error')]
public function logFrontendError($data)
{
    Log::error("Livewire frontend error: " . $data['message']);
}
}
