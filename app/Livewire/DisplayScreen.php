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
use Illuminate\Support\Facades\Log;


#[Layout('components.layouts.custom-display-layout')]
class DisplayScreen extends Component
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
    public $reverbDetails;
    public $timezone;
    public $reverbKey, $reverbHost, $reverbPort, $reverbScheme;
    public $waitingCalls;
    public $selectedSound;
	public $location_name;//New change

    private function upsertQueueToDisplayFromEvent(array $queueData): void
    {
        $id = $queueData['id'] ?? null;
        if (empty($id)) {
            return;
        }

        $tokenText = $queueData['token_with_acronym']
            ?? (($queueData['start_acronym'] ?? '') . ($queueData['token'] ?? ''));

        $counterText = $queueData['counter']
            ?? $queueData['counter_name']
            ?? null;

        if (empty($counterText) && !empty($queueData['counter_id'] ?? null)) {
            $counterText = Counter::where('id', $queueData['counter_id'])->value('name');
        }

        $displayItem = [
            'id' => $id,
            'token' => $tokenText,
            'name' => $queueData['name'] ?? '',
            'status' => $queueData['status'] ?? null,
            'counter' => $counterText,
        ];

        $collection = $this->queueToDisplay instanceof \Illuminate\Support\Collection
            ? $this->queueToDisplay
            : collect($this->queueToDisplay);

        $collection = $collection
            ->reject(fn($item) => (int)($item['id'] ?? 0) === (int)$id)
            ->prepend($displayItem);

        $limit = (int)($this->currentTemplate?->show_queue_number ?? 0);
        if ($limit > 0) {
            $collection = $collection->take($limit);
        }

        $this->queueToDisplay = $collection->values();
    }

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
          Session::put('selectedLocation', $this->currentTemplate->location_id);
          $this->location = $this->currentTemplate->location_id;
        if (empty($this->location)) {
            abort(404);
        }

        $datatimezone = Queue::timezoneSet();

        $this->timezone = Session::get('timezone_set');

        // $this->displaySetting = DisplaySettingModel::getDetails($this->teamId, $this->location);
        $this->siteData = SiteDetail::Where('team_id', $this->teamId)->where('location_id', $this->location)->select('id','team_id','location_id','queue_heading_first','queue_heading_second')->first();

        $this->reverbDetails = ReverbDetail::viewReverbDetails($this->teamId, $this->location);
        $this->reverbKey = $this->reverbDetails->key ?? env('REVERB_APP_KEY');
        $this->reverbHost = $this->reverbDetails->host ?? env('REVERB_HOST', '127.0.0.1');
        $this->reverbPort = $this->reverbDetails->port ?? env('REVERB_PORT', 8080);
        $this->reverbScheme = $this->reverbDetails->scheme ?? env('REVERB_SCHEME', 'http');
         $this->counterID = $this->currentTemplate?->counters?->pluck('id')?->toArray();
        $this->categoryID = $this->currentTemplate?->categories?->pluck('id')?->toArray();
         $this->getcallsdetail();
		//New changes 		
		 $this->location_name =Location::where('id', $this->location)->value('location_name');
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
		
		//dd($queues);
 
        $this->queueToDisplay = $queues['display'];
        $this->waitingCalls   = $queues['waiting'];
        $this->missedCalls    = $queues['missed'];
        $this->holdCalls      = $queues['hold'];
        
        // Force Livewire to detect the change
        $this->queueToDisplay = $this->queueToDisplay; // Trigger change detection

    } catch (\Exception $e) {
        // Log the error
        \Log::error('Queue fetch failed: ' . $e->getMessage(), [
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

        return view('livewire.display-screen');
    }



    #[On('display-update')]
    public function pushLiveQueue($event)
    {
        \Log::info('📺 display-update event received', ['event' => $event]);

        // $anounce_text = ' on counter ';
        // if($this->teamId ==410 && $this->location == 552){
        //     $anounce_text = ' on ';
        // }
        $anounce_text = ' on ';

        $counterName = $primarySpeech='';
        $checktype= false;
        
        // Extract queue data - handle both event structures
        $queueData = $event['queue'] ?? $event['event']['queue'] ?? null;
        
        if (!empty($queueData)) {
            $event['queue'] = $queueData; // Normalize structure

            // Update local state immediately from event payload.
            $this->upsertQueueToDisplayFromEvent($queueData);

            if( $this->currentTemplate->type === "Counter" && !empty($queueData['counter_id'] ?? null) && in_array($queueData['counter_id'] ?? null, $this->counterID)){
                $checktype= true;
            }
            
            if( $this->currentTemplate->type === "Counter" && !empty($queueData['forward_counter_id'] ?? null) && in_array($queueData['forward_counter_id'] ?? null, $this->counterID)){
                $checktype= true;
            }

            if( $this->currentTemplate->type === "Category" && !empty($queueData['category_id'] ?? null) && in_array($queueData['category_id'] ?? null, $this->categoryID)){
                $checktype= true;
            }

            if( $this->currentTemplate->type === "Category" && !empty($queueData['transfer_id'] ?? null) && in_array($queueData['transfer_id'] ?? null, $this->categoryID)){
                $checktype= true;
            }
            if (isset($queueData['is_missed']) && !empty($queueData['is_missed']) && $queueData['is_missed'] == 1) {
                    $checktype = false;
                }

            if (isset($queueData['status']) && $queueData['status'] == Queue::STATUS_PROGRESS && !empty($queueData['called_datetime'] ?? null)) {

                if($checktype){
                $screenTune =  $this->currentTemplate?->display_screen_tune ?? DisplaySettingModel::DEFAULT_SETTING_TUNE;

                $voice  = DisplaySettingModel::getVoiceChosen($screenTune);

                if (!empty($queueData['counter_id'] ?? null)) {
                    $counterName = Counter::where('id', $queueData['counter_id'] ?? null)->value('name');
                }


                if (!empty($counterName)) {
                    
                    if ($voice && ($voice['lang'] != DisplaySettingModel::DEFAULT_EN_LANG)  && $voice['lang'] == 'es-ES') {
                        $speech  = 'tiquete number ' . ($queueData['start_acronym'] ?? '') . ($queueData['token'] ?? '').$anounce_text . $counterName;
                    } else {
                        $speech  = 'token number ' . ($queueData['start_acronym'] ?? '') . ($queueData['token'] ?? '') . $anounce_text . $counterName;
                    }

                } else {

                    $speech  = 'token number ' . ($queueData['start_acronym'] ?? '') . ($queueData['token'] ?? '');
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
            }
            
            // CRITICAL: Refresh the display data
            \Log::info('🔄 Refreshing display screen data');
            $this->getcallsdetail();
            
            // Force Livewire to re-render
            $this->dispatch('$refresh');

            // Fallback: if DOM is inside wire:ignore and doesn't update, force reload.
            // (resources/views/livewire/display-screen.blade.php already listens for this.)
            $this->dispatch('refreshcomponent');

            $activeid = $queueData['id'] ?? null;
            if(!empty($queueData) &&  $this->currentTemplate->display_behavior == 2){

                $this->dispatch('highlight-color', [
                  'activeid' => $activeid

              ]);
            }

        } else {
            \Log::warning('⚠️ display-update event received but no queue data', ['event' => $event]);
            // Still refresh even if data structure is unexpected
            $this->getcallsdetail();
            $this->dispatch('$refresh');
            $this->dispatch('refreshcomponent');
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
