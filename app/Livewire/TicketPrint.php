<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Category,Queue, SiteDetail, FeedbackSetting, GenerateQrCode, Level, QueueStorage,AccountSetting,PusherDetail,Location};
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('components.layouts.custom-ticket-print-layout')]
class TicketPrint extends Component
{
public $teamId;
public $location;
public ?SiteDetail $siteDetails = null; // If using model object
public bool $showQrcode = false;
public bool $showlogo = false;
public bool $showusername = false;
public bool $showarrived = false;
public bool $showlocation = false;
public bool $showcategory = false;
public bool $showTextmessage = false;
public bool $showToken = false;
public string $nameLabel = '';
public string $tokenLabel = '';
public string $arrivedLabel = '';
public string $qrcodeSvg = '';
public string $logo = '';
public ?string $showTicketText = '';
public ?string $showTicketText_2 = '';
public int $counterID = 0;
public int $countCatID = 0;
public array $data = [];
public $enablePriority = false;
public $ticket_image;
public $pendingCount;
public $waitingTime;

    public function mount( $id ) {

    $id = base64_decode($id);
   $queueStorage = QueueStorage::with([
		'category',       // relation to main category
		'subCategory',    // relation to sub category
		'childCategory',  // relation to child category
		'location'        // relation to location if needed
	])->find($id);

    $this->teamId = tenant('id');
    $this->location = $queueStorage->locations_id ??  Session::get('selectedLocation');
    $this->pendingCount = $queueStorage->queue_count;
    $this->waitingTime = $queueStorage->waiting_time;
    $this->siteDetails = SiteDetail::where(['team_id'=>$this->teamId,'location_id'=>$this->location])->first();

        $this->showQrcode =$this->siteDetails->is_qrcode_ticket == 1 ? true : false;
        $this->showlogo =$this->siteDetails->is_logo_on_print == 1 ? true : false;
        $this->showusername =$this->siteDetails->is_name_on_print == 1 ? true : false;
        $this->showarrived =$this->siteDetails->is_arrived_on_print == 1 ? true : false;
        $this->showlocation =$this->siteDetails->is_location_on_print == 1 ? true : false;
        $this->showcategory =$this->siteDetails->is_category_on_print == 1 ? true : false;
        $this->showTextmessage =$this->siteDetails->ticket_text_enable == 1 ? true : false;
        $this->showToken =$this->siteDetails->is_token_on_print == 1 ? true : false;
        $this->nameLabel =$this->siteDetails->print_name_label ?? 'Name';
        $this->tokenLabel =$this->siteDetails->print_token_label ?? 'Token';
        $this->arrivedLabel =$this->siteDetails->arrived_time_label ?? 'Arrived';
        $this->enablePriority = $this->siteDetails->use_staff_priority ?? false;
        $this->ticket_image = $this->siteDetails->ticket_image ?? '';
        $baseencodeQueueId = base64_encode($queueStorage->queue_id);
        $customUrl = url("/visits/{$baseencodeQueueId}");
            $this->qrcodeSvg = QrCode::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate($customUrl);
        $fieldCatName='';
        $this->logo =  SiteDetail::viewImage(SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId,$this->location);

         $thirdCategoryName =$categoryName =$secondCategoryName =$locationName ='';

         $thirdCategoryName = $secondCategoryName = $categoryName = '';

		if (!empty($queueStorage->child_category_id)) {
			$thirdCategoryName = $queueStorage->childCategory?->name ?? '';
		}

		if (!empty($queueStorage->sub_category_id)) {
			$secondCategoryName = $queueStorage->subCategory?->name ?? '';
		}

		if (!empty($queueStorage->category_id)) {
			$categoryName = $queueStorage->category?->name ?? '';
		}

            if ($this->siteDetails?->category_estimated_time == SiteDetail::STATUS_YES){
                 if (!empty($queueStorage->child_category_id)) {
            if ($this->siteDetails?->category_level_est == 'automatic') {
                $fieldCatName = 'child_category_id';
                $this->countCatID =  $queueStorage->child_category_id;
            } elseif ($this->siteDetails?->category_level_est == 'child') {
                $fieldCatName = 'sub_category_id';
                $this->countCatID =  $queueStorage->sub_category_id;
            } else {
                $fieldCatName = 'category_id';
                $this->countCatID =  $queueStorage->category_id;
            }
        } else if (!empty($queueStorage->sub_category_id)) {

            if ($this->siteDetails?->category_level_est == 'child') {
                $fieldCatName = 'sub_category_id';
                $this->countCatID =  $queueStorage->sub_category_id;
            } else {
                $fieldCatName = 'category_id';
                $this->countCatID =  $queueStorage->category_id;
            }
        } else {
            $fieldCatName = 'category_id';
            $this->countCatID =  $queueStorage->category_id;
        }
            }


            if ($this->siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO)
                $this->counterID  = 0;

            

                $locationName = Location::locationName($this->location);
                $dateformat = AccountSetting::showDateTimeFormat();

$pendingCount =$this->pendingCount;
        $this->data = [
                'name' => $queueStorage->name,
                'phone' => $queueStorage->phone,
                'phone_code' => $queueStorage->phone_code ?? '91',
                'queue_no' => $queueStorage->queue_id,
                'arrives_time' => Carbon::parse($queueStorage->arrives_time)->format($dateformat),
                'category_name' => $categoryName,
                'thirdC_name' => $thirdCategoryName,
                'secondC_name' => $secondCategoryName,
                'token' => $queueStorage->token,
                'locations_id' => $this->location,
                'location_name' => $locationName,
                'acronym' => $queueStorage->start_acronym,
                'pending_count' => $this->pendingCount ?? 0,

        ];



        $waitingTime = 0;
            if (!empty($this->siteDetails)) {
                if ($this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES) {
              

                    $waitingTime = $this->waitingTime;

                    if (!empty($this->siteDetails->ticket_text_2))
                           $text = str_replace('{{QUEUE COUNT}}', $this->data['pending_count'], $this->siteDetails->ticket_text_2);
                        $this->showTicketText_2 = str_replace('{{Waiting Time}}', $waitingTime, $text);

                    if (!empty($this->siteDetails->ticket_text)) {
                        $text = str_replace('{{QUEUE COUNT}}', $this->data['pending_count'], $this->siteDetails->ticket_text);
                        $this->showTicketText = str_replace('{{Waiting Time}}', $waitingTime, $text);
                    }
                }
            }

     }

    public function render()
    {
        return view('livewire.ticket-print');
    }
}
