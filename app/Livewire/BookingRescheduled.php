<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Team, Booking, SiteDetail, FormField,AccountSetting}
    ;
    use App\Traits\SendsEmails;
    use Illuminate\Support\Facades\Crypt;
    use Carbon\Carbon;
    use Livewire\Attributes\Layout;
    use Livewire\Attributes\Title;

    #[Layout('components.layouts.custom-layout')]
    class BookingRescheduled extends Component
 {
        use SendsEmails;
        
     #[Title('Booking Rescheduled')]
        public $teamId;
        public $siteDetails;
        public $booking;
        public $userDetails;
        public $encrypedBookingID;
        public $bookingSetting;

        public function mount( $id )
 {
           
            $this->teamId = tenant('id');
            $this->siteDetails = SiteDetail::getMyDetails( $this->teamId );
            $bookingId = base64_decode($id);

            if(!is_int(base64_decode($id)))
            {
                $bookingId = Crypt::decrypt($id);
            }

            $this->booking = Booking::viewBooking(
                $bookingId,
                $this->teamId
            );

            if ( empty( $this->booking ) ) {
                abort( 404 );
            }
            if ( $this->booking->status == Booking::STATUS_CANCELLED ) {
                return redirect( 'main/booking' );
            }

            $this->encrypedBookingID = Crypt::encrypt( $this->booking->id );
            $this->userDetails = json_decode( $this->booking->json, true );
            $this->bookingSetting = AccountSetting::getDetails( $this->teamId ,$this->booking->location_id);

        }

        public function render()
 {
            return view( 'livewire.booking-rescheduled' );
        }

        public function printBooked() {
            $logo =  SiteDetail::viewImage( SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId );
            $date =  $this->booking->booking_date ?  Carbon::parse( $this->booking->booking_date )->format( 'd M, Y' ) : 'N/A';
            $html ='';

            $html = '<div class="container mx-auto flex justify-center items-center md:min-h-screen">
            <div class="bg-zinc-100 rounded-lg shadow-lg md:p-6 p-2 w-full max-w-xl border rounded-lg mb-3"> 
            <div><img src="'.asset( $logo ).'" class="w-100 h-100"/> </div>  <div class="mb-6">
                <h4 class="text-xl font-bold text-blue-300 my-4">'.__( 'text.booking details' ).'</h4>
                <div class="my-4"> 
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'. ($this->bookingSetting?->con_app_input_placeholder ? $this->bookingSetting->con_app_input_placeholder :  __('text.ID') .'( '. __('text.Email').' )').'</div>
                        <div class="font-semibold text-right">'.  $this->booking->refID .'</div>
                    </div>
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'.__( 'text.appointment date' ).': </div>
                        <div class="font-semibold text-right">'.$date.'</div>
                    </div>
                    <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'.__( 'text.appointment time' ).': </div>
                        <div class="font-semibold text-right">'.$this->booking->booking_time  .'</div>
                    </div>';
            if ( !empty( $this->booking->category_id ) ) {
                $html .= ' <div class="flex justify-between py-2 flex-wrap gap-3">
                        <div class="text-gray-600">'.__( 'text.Level' ).'1: </div>
                        <div class="font-semibold text-right">'. $this->booking->categories?->name  .'</div>
                    </div>';
            }
            if ( !empty( $this->booking->sub_category_id ) ) {
                $html .= ' <div class = "flex justify-between py-2 flex-wrap gap-3">
                    <div class = "text-gray-600">'.__( 'text.Level' ).'2: </div>
                    <div class = "font-semibold text-right">'. $this->booking->sub_category?->name .'</div>
                    </div>';
            }

            if ( !empty( $this->booking->child_category_id ) ) {
                $html .= ' <div class = "flex justify-between py-2 flex-wrap gap-3">
                <div class = "text-gray-600">'.__( 'text.Level' ).'2: </div>
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
