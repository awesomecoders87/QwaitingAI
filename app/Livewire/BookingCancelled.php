<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{Team, Booking, SiteDetail, AccountSetting, Location, SmtpDetails, Level, MessageDetail};
// use App\Models\QueueFreeSlotCount;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.custom-display-layout')]
class BookingCancelled extends Component
{
    #[Title('Booking Cancel')]
    public $teamId;
    public $siteDetails;
    public $booking;
    public $userDetails;
    public $bookingSetting;
    public $location;
    public $locationName;
    public $locationStep;
    public $firstStep;
    public $allLocations;
    public $level1, $level2, $level3;

    public function mount($id)
    {
        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->siteDetails = SiteDetail::getMyDetails($this->teamId);

        if (!empty($this->location))
        {
            $this->locationName =  Location::locationName( $this->location );
            $this->locationStep = false;
            $this->firstStep = true;

            if ( $this->location == '' || !Auth::check() )
            {
                $this->location = '';
                $this->allLocations = Location::where( 'team_id', $this->teamId )->pluck( 'location_name', 'id' );
                $this->locationStep = true;
                $this->firstStep = false;
            }
        }

        $bookingId = base64_decode($id);
        $this->booking = Booking::viewBooking(
            $bookingId,
            $this->teamId
        );
        if (empty($this->booking)) {
            abort(404);
        }
        if($this->booking->status == Booking::STATUS_COMPLETED){
            return redirect('book-appointment');
        }
        $this->location = $this->booking->location_id ?? Session::get( 'selectedLocation' );

        $this->booking->status = Booking::STATUS_CANCELLED;
        $this->booking->save();

        $this->location = $this->booking->location_id ?? Session::get( 'selectedLocation' );
        $this->siteDetails = SiteDetail::getMyDetails( $this->teamId,$this->location );
        $this->userDetails = json_decode($this->booking->json, true);
        // $this->bookingSetting = AccountSetting::getDetails( $this->teamId ,$this->booking->location_id);
        $this->bookingSetting = AccountSetting::where('team_id',$this->teamId)
        ->where('location_id',$this->location)
        ->where('slot_type',AccountSetting::BOOKING_SLOT)->first();

        $url = route('booking-confirmed', ['id' => base64_encode($this->booking->id)]);
        $cleanedUrl = str_replace('/public', '', $url);

        $userAuth = '';
        if(Auth::check()){
            $userAuth = Auth::user();
        }


             $levels = Level::where('team_id', $this->teamId)
                ->where('location_id', $this->location)
                ->whereIn('level', [1, 2, 3])
                ->get()
                ->keyBy('level');

            $this->level1 = $levels[1]->name ?? 'Level 1';
            $this->level2 = $levels[2]->name ?? 'Level 2';
            $this->level3 = $levels[3]->name ?? 'Level 3';

        $data = [
            'to_mail' => $this->booking->email,
            'booking_id' => $this->booking->id,
            'name' => $this->booking->name,
            'phone' => $this->booking->phone,
            'booking_date' => $this->booking->booking_date,
            'booking_time' => $this->booking->booking_time,
            'booked_by' => $userAuth->name ?? '',
            'category_name' => $this->booking->categories?->name,
            'secondC_name' => $this->booking->sub_category?->name,
            'thirdC_name' => $this->booking->child_category?->name,
            'location' => $this->booking->location?->location_name,
            'status' => $this->booking->status,
            'json' => $this->booking->json,
            'refID'=>$this->booking->refID,
            'view_booking'=>$cleanedUrl,
            'locations_id' => $this->location,
            'team_id' => $this->teamId,
        ];

        $message = 'Appointment Cancelled Successfully';

         $logData = [
            'team_id' => $this->teamId,
            'location_id' => $this->location,
            'customer_id' => $this->booking->created_by,
            'email' => $this->booking->email,
            'contact' => $this->booking->phone,
            'type' => MessageDetail::CUSTOM_TYPE,
            'event_name' => 'Booking Cancelled',
        ];

        $this->sendNotification($data, 'booking cancelled', $message, $logData);

    }
    public function render()
    {
        return view("livewire.booking-cancel");
    }

    public function sendNotification( $data,$title,$template, $logData = null) {
     if ( isset( $data[ 'to_mail' ] ) && $data[ 'to_mail' ] != '' )
    {
        if (!empty($logData)) {
           $logData['channel'] = 'email';
           $logData['status'] = MessageDetail::SENT_STATUS;
           // MessageDetail::storeLog($logData);
       }
        SmtpDetails::sendMail( $data, $title, $template, $this->teamId,$logData);
        $data['location'] = Location::find( $this->location)->value('location_name');
    }
    if (!empty($this->phone)) {
        $logData['channel'] = 'sms';
        $logData['status'] = MessageDetail::SENT_STATUS;
        SmsAPI::sendSms( $this->teamId, $data,$title,$title,$logData);


    }
    }
}
