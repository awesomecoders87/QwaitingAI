<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Booking, PreferBooking,SiteDetail, FormField, AccountSetting,Location,Category,SmtpDetails,SmsAPI,QueueStorage,Queue,Level};
   use Carbon\Carbon;
   use Livewire\WithPagination;
   use Illuminate\Support\Facades\Session;
   use Livewire\Attributes\Layout;
   use Livewire\Attributes\Title;
   use Livewire\Attributes\On;
   use SimpleSoftwareIO\QrCode\Facades\QrCode;
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Config;
   use Illuminate\Support\Facades\DB;
   use Illuminate\Support\Facades\Log;
   use App\Events\QueueCreated;
   use Auth;

class PreferBookingList extends Component
{
     use WithPagination;

    #[Title('Prefer Booking List')]

    public $teamId;
    public $location;
    public $siteDetails;
    public $accountdetail;
    public $search;
    public $fromDate;
    public $toDate;
    public $status;
    public $interviewMode;
    public $categoryId;
    public $subCategoryId;
    public $childCategoryId;
    public $showupdatestatus = false;
    public $categories = [];
    public $subCategories = [];
    public $childCategories = [];
    public $bookingStatus = [];
    public $changeStatus;
    public $selectBookingId;
    public $selectedCategoryId;
    public $secondChildId;
    public $thirdChildId;
    public $token_start;
    public $acronym;
    public $countCatID = 0;
    public $fieldCatName = '';
    public $counterID = 0;
    public $showTicketText;
    public $showTicketText_2;
    public $categoryName = '';
    public $secondCategoryName = '';
    public $thirdCategoryName = '';
    public $queue_form = false;
    public $dynamicForm = [];
    public $dynamicProperties = [];
    public $allCategories = [];
     public $level1,$level2,$level3;

     public function mount(){
          $this->teamId = tenant('id');
        $this->location = Session::get( 'selectedLocation' );

        $this->siteDetails = SiteDetail::getMyDetails( $this->teamId,$this->location);
       
        $this->accountdetail = AccountSetting::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->where('slot_type', AccountSetting::BOOKING_SLOT)->first();

        if(empty($this->siteDetails) || empty($this->accountdetail)){
            abort(403);
        }

        if($this->accountdetail->booking_system == 0){
            abort(403);
        }
        $this->categories = Category::where('team_id', $this->teamId)
        ->where(function ($query) {
            $query->whereNull('parent_id')
                  ->orWhere('parent_id', '');
        })
        ->whereJsonContains('category_locations', (string)$this->location)
        ->orderBy('id')
        ->get();

          $levels = Level::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

       $this->level1 = $levels[1]->name ?? 'Level 1';
       $this->level2 = $levels[2]->name ?? 'Level 2';
       $this->level3 = $levels[3]->name ?? 'Level 3';

        $this->bookingStatus = Booking::getBookingStatus();
     }


      public function deleteBooking($value){
      
        $this->selectBookingId = $value;
        $this->dispatch('confirm-delete');

    }  

    #[On('confirmed-delete')]
    public function confirmedDelete()
    {
        // Retrieve booking details from the database
        $this->bookingDetails = PreferBooking::find($this->selectBookingId)->delete();
        $this->selectBookingId = '';
        $this->dispatch('deleted');
    }

    #[On('confirmed-check-in')]
    public function confimedforcheckin(){
       
        $booking = PreferBooking::where('team_id', $this->teamId)
        ->where('location_id', $this->location)
        ->where('is_convert', Booking::STATUS_NO)
        ->where('status', Booking::STATUS_CONFIRMED)
        ->where('id', $this->selectBookingId)
        ->first();

        if ($booking) {

        $queueCreated = $this->convertToQueue($booking);
     
        if($queueCreated['status'] == "success"){

            // $this->dispatch('updated');
            $this->dispatch('swal:saved-queue',$queueCreated['ticket']);
            
        }else{
           
            $this->dispatch('error', message: $queueCreated['message']);
          
        }
        } else {
            $this->dispatch('error', message: 'Booking not found or does not belong to your team/location.');
            
        }
    }

     public function updatedCategoryId()
    {
        $this->subCategoryId = null;
        $this->childCategoryId = null;
        $this->subCategories = Category::where('parent_id', $this->categoryId)
            ->whereJsonContains('category_locations', (string)$this->location)
            ->get();
        $this->childCategories = [];
    }

     public function updatedSubCategoryId()
    {
        $this->childCategoryId = null;
        $this->childCategories = Category::where('parent_id', $this->subCategoryId)
            ->whereJsonContains('category_locations', (string)$this->location)
            ->get();
    }


    public function resetFilters()
    {
        $this->fromDate = null;
        $this->toDate = null;
        $this->status = null;
        $this->interviewMode = null;

        $this->categoryId = null;
        $this->subCategoryId = null;
        $this->childCategoryId = null;

        $this->subCategories = [];
        $this->childCategories = [];
        $this->showupdatestatus = false;
    }


    public function render()
    {
        $query = PreferBooking::where('team_id', $this->teamId)
        ->where('location_id', $this->location);

        // Apply role and permission check
        $user = Auth::user();

        // If user is NOT admin AND doesn't have permission to view all bookings
        if (!$user->is_admin == 1 && !$user->hasPermissionTo('Show All Booking')) {
            $query->where('staff_id', $user->id);
        }

        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('booking_date', [$this->fromDate, $this->toDate]);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->interviewMode) {
            $query->where('interview_mode', $this->interviewMode);
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }
        
        if ($this->subCategoryId) {
            $query->where('sub_category_id', $this->subCategoryId);
        }
        
        if ($this->childCategoryId) {
            $query->where('child_category_id', $this->childCategoryId);
        }

        $bookings = (clone $query)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('refID', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
        // Booking Stats
    $totalBookings = (clone  $bookings)->count();
    $checkinCount = (clone  $bookings)->where('is_convert', Booking::STATUS_YES)->count();
    $pendingCount = (clone  $bookings)->where('status', Booking::STATUS_PENDING)->count();
    $cancelledCount = (clone  $bookings)->where('status', Booking::STATUS_CANCELLED)->count();

    return view('livewire.prefer-booking-list', compact(
        'bookings',
        'totalBookings',
        'checkinCount',
        'pendingCount',
        'cancelledCount'
    ));
        
    }
}
