<?php

namespace App\Livewire;
use Livewire\Component;
use App\Models\{Queue, SiteDetail, QueueStorage, PusherDetail};
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\On;


 #[Layout('components.layouts.custom-layout')]
class ViewWaitlist extends Component
{
    #[Title('Ticket Waitlist')]
    public $queuePening;
    public $location;
    public $teamId;
    public $showModal = false;
    public $data = [];
    public $showTicketText;
    public $showTicketText_2;
    public $siteDetails;
    public $acronym;
    public $thirdCategoryName, $secondCategoryName, $categoryName, $selectedCategoryId;
    public $counterID = 0;
    public $fieldCatName;
    public $countCatID;
    public $pendingCount = 0;
    public $userDetails;
    public $queueStorageId;
    public $totalEsitmateTime;
    public $timezone;
    public $pusherKey, $pusherCluster, $pusherDetails;
    public $isOpen = false;

 public function mount($location = null, $id = null)
{
    $this->teamId = tenant('id');
    $this->location = $location ?? Session::get('selectedLocation');

    // Only decode once during first mount
    if (!$this->queueStorageId && $id !== null) {
        $decodedId = base64_decode($id, true);
        if (!$decodedId) {
            abort(404, 'Invalid queue ID');
        }
        $this->queueStorageId = $decodedId;
    }

    // If still null, something is wrong
    if (!$this->queueStorageId) {
        abort(404, 'Queue ID missing');
    }

    $this->siteDetails = SiteDetail::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->select('estimate_time', 'is_enable_waitlist_message', 'is_waitlist_table', 'waitlist_heading', 'waitlist_message_first', 'waitlist_message_second', 'select_timezone', 'category_estimated_time')
        ->first();

    if ($this->siteDetails && $this->siteDetails->select_timezone) {
        Config::set('app.timezone', $this->siteDetails->select_timezone);
        date_default_timezone_set($this->siteDetails->select_timezone);
    }

    $this->refresh();

    $this->pusherDetails = PusherDetail::viewPusherDetails($this->teamId, $this->location);
    $this->pusherKey = $this->pusherDetails->key ?? env('PUSHER_APP_KEY');
    $this->pusherCluster = $this->pusherDetails->options_cluster ?? env('PUSHER_APP_CLUSTER');
      $this->timezone = Config::get('app.timezone') ?? 'UTC';
     $this->dispatch('header-hide-title'); // hide header title from custom form

}

    #[On('display-update')]
    public function pushPendingQueue($event = null)
    {
        \Log::info('Pusher Event Triggered', ['event' => $event]);
        $this->refresh();
    }

    public function refresh()
    {
        if (!$this->queueStorageId) return;

        $queuestorage = QueueStorage::find($this->queueStorageId);
        if (!$queuestorage) return;

        $this->queuePening = Queue::getPendingQueues(
            [
                ['team_id', '=', $this->teamId],
                ['id', '<=', $this->queueStorageId],
            ],
            false,
            $this->location,
            null,
            null,
            null,
            null,
            null,
            $queuestorage->assign_staff_id ?? null
        );

        $this->totalEsitmateTime = 0;
        if (!empty($this->queuePening)) {
            foreach ($this->queuePening as $queue) {
                $this->totalEsitmateTime += $queue->waiting_time;
            }
        }



        $waitingcount = $this->queuePening->count() ?? 0;
        if ($waitingcount > 0) {
            $waitingcount -= 1;
        }

        $estimateTime = $this->siteDetails->estimate_time ?? 0;

        if (!empty($this->siteDetails->waitlist_message_first)) {
            $this->showTicketText = str_replace('{{QUEUE COUNT}}', $waitingcount, $this->siteDetails->waitlist_message_first);
        }

        if (!empty($this->siteDetails->waitlist_message_second)) {
            if ($this->siteDetails->category_estimated_time == SiteDetail::STATUS_YES && !empty($queuestorage->assign_staff_id)) {
                // $this->showTicketText_2 = str_replace('{{Waiting Time}}', $this->totalEsitmateTime, $this->siteDetails->waitlist_message_second);
                $this->showTicketText_2 = str_replace('{{Waiting Time}}', $queuestorage->waiting_time, $this->siteDetails->waitlist_message_second);
            } else {
                $this->showTicketText_2 = str_replace('{{Waiting Time}}', $queuestorage->waiting_time, $this->siteDetails->waitlist_message_second);
            }
        }

        // dd($queuestorage);
    }

    public function render()
    {
        return view('livewire.view-waitlist');
    }
}
