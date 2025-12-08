<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
     Booking, SiteDetail, FormField, AccountSetting,Location,Level}
    ;
    use App\Traits\SendsEmails;
    use Illuminate\Support\Facades\Crypt;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Session;
    use Livewire\Attributes\Layout;
    use Livewire\Attributes\Title;
    use BaconQrCode\Renderer\ImageRenderer;
    use BaconQrCode\Renderer\RendererStyle\RendererStyle;
    use BaconQrCode\Writer;
    use BaconQrCode\Renderer\Image\SvgImageBackEnd;
    use Livewire\Attributes\On;

    #[Layout('components.layouts.custom-display-layout')]
    class BookingConfirmed extends Component
 {
        use SendsEmails;
        #[Title('Booking Confirmed')]

        public $domainSlug;
        public $teamId;
        public $siteDetails;
        public $booking;
        public $userDetails;
        public $encrypedBookingID;
        public $bookingSetting;
        public $cancellationDeadline;
        public $cancellationDeadlineTimestamp;
        public $currentTimestamp;
        public $currentDate;
        public $location;
        public $locationName;
        public $level1,$level2,$level3;
        public $qrCode;

        public function mount( $id )
 {
            
            $this->teamId = tenant('id');
        
            $bookingId = base64_decode($id);
            $this->booking = Booking::viewBooking(
                $bookingId,
                $this->teamId
            );
            if ( empty( $this->booking ) ) {
                abort( 404 );
            }

            $this->location = $this->booking->location_id ?? Session::get( 'selectedLocation' );
            $this->siteDetails = SiteDetail::getMyDetails( $this->teamId,$this->location );
            if ( $this->booking->status == Booking::STATUS_CANCELLED ) {
                return redirect( 'book-appointment' );
            }
            $this->locationName = Location::locationName($this->booking->location_id);
            $this->encrypedBookingID = base64_encode( $this->booking->id );
            $this->userDetails = json_decode( $this->booking->json, true );
            // $this->bookingSetting = AccountSetting::getDetails( $this->teamId,$this->booking->location_id );
            $this->bookingSetting = AccountSetting::where('team_id',$this->teamId)
            ->where('location_id',$this->location)
            ->where('slot_type',AccountSetting::BOOKING_SLOT)->first();
       
            $allowCancelBefore = !empty($this->bookingSetting->allow_cancel_before) ? $this->bookingSetting->allow_cancel_before : AccountSetting::STATIC_DAY;
            $bookingDate = Carbon::createFromFormat('Y-m-d',$this->booking->booking_date);
            $this->currentDate = Carbon::now('UTC');
            $this->cancellationDeadline = $bookingDate->copy()->subDays($allowCancelBefore)->setTimezone('UTC');
            $this->cancellationDeadlineTimestamp = $this->cancellationDeadline->timestamp ?? $this->currentDate->timestamp;
            $this->currentTimestamp = $this->currentDate->timestamp;

             $levels = Level::where('team_id', $this->teamId)
                ->where('location_id', $this->location)
                ->whereIn('level', [1, 2, 3])
                ->get()
                ->keyBy('level');

            $this->level1 = $levels[1]->name ?? 'Level 1';
            $this->level2 = $levels[2]->name ?? 'Level 2';
            $this->level3 = $levels[3]->name ?? 'Level 3';

            $isCheckinQREnabled = AccountSetting::where('team_id',$this->teamId)
            ->where('location_id',$this->location)->where('slot_type', 'booking')->value('checkin_qrcode');

            if($isCheckinQREnabled)
            {

            // $qrCodeUrl = url('checkin/' . base64_encode($id) . '/' . base64_encode($this->location));
            $qrCodeUrl = url('booking-confirmed/' .$id);

            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );

            $writer = new Writer($renderer);

            $this->qrCode = $writer->writeString($qrCodeUrl);
            }
            
        }

     
        public function render() {
            return view( 'livewire.booking-confirmed' );
        }

        #[On('print-button-clicked')]
        public function printBooked() {
            $logo =  SiteDetail::viewImage( SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId,$this->location);
            $date =  $this->booking->booking_date ?  Carbon::parse( $this->booking->booking_date )->format( 'd M, Y' ) : 'N/A';
            $html = '';

            $html = '<div class="container mx-auto flex justify-center items-center md:min-h-screen">
            <div class="bg-zinc-100 rounded-lg shadow-lg md:p-6 p-2 w-full max-w-xl border rounded-lg mb-3"> 
            <div>  <div class="mb-6">
                <h4 class="text-xl font-bold text-blue-300 my-4">'.__( 'text.booking details' ).'</h4>
                <div class="my-4"> 
                    <div class="flex justify-between py-2 flex-wrap gap-3">
   <div class="text-gray-600">
   '.  __( 'text.Booking ID' ).'</div>
                           <div class="font-semibold text-right">'.  $this->booking->refID .'</div>
                    </div>
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'.__( 'text.appointment date' ).': </div>
                        <div class="font-semibold text-right">'.$date.'</div>
                    </div>
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'.__( 'text.appointment time' ).': </div>
                        <div class="font-semibold text-right">'.$this->booking->booking_time  .'</div>
                    </div><div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'.__( 'text.branch name' ).': </div>
                        <div class="font-semibold text-right">'.$this->locationName  .'</div>
                    </div>';
            if ( !empty( $this->booking->category_id ) ) {
                $html .= ' <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'.( $this->level1 ).': </div>
                        <div class="font-semibold text-right">'. $this->booking->categories?->name  .'</div>
                    </div>';
            }
            if ( !empty( $this->booking->sub_category_id ) ) {
                $html .= ' <div class = "flex justify-between py-2 flex-wrap gap-3">
                    <div class = "text-gray-600">'.( $this->level2 ).': </div>
                    <div class = "font-semibold text-right">'. $this->booking->sub_category?->name  .'</div>
                    </div>';
            }

            if ( !empty( $this->booking->child_category_id ) ) {
                $html .= ' <div class = "flex justify-between py-2 flex-wrap gap-3">
                <div class = "text-gray-600">'.( $this->level3 ).': </div>
                <div class = "font-semibold text-right">'. $this->booking->child_category?->name  .'</div>
                </div>';

            }

            $html .= ' <h4 class = "text-xl font-bold text-blue-300">Contact Details</h4>';

            if ( !empty( $this->userDetails ) ) {
                foreach ( $this->userDetails as $key => $userD ) {
                    $html .= '<div class = "flex justify-between py-2 flex-wrap gap-3">
                    <div class = "text-gray-600">'. FormField::viewLabel( $this->teamId, $key ) .'</div>
                    <div class = "font-semibold text-right">'. $userD .'</div>
                    </div>';
                }

            }

            $html .= '  </div> </div>';
            $this->dispatch( 'booked-print', [
                'html'=>$html,
            ] );

        }

    }
