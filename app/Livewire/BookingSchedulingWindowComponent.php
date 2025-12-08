<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceSetting;
use App\Models\SiteDetail;
use App\Models\AccountSetting;
use App\Models\ActivityLog;
use App\Models\CustomSlot;
use Illuminate\Support\Facades\Session;
use Auth;

class BookingSchedulingWindowComponent extends Component
{

    public $teamId;
    public $locationId;
    public $minNotice;
    public $maxNotice;
    public $cancelNotice;
    // public $categoryleveltimeslot;
    public $requestMode;
    public $bookingReminder;
    public $weekStart;
    public $showCategoryPerRow;
    public $inputPlaceholder;
    public $bookingConvertLabel;
    public $bookingHeadingText;
    public $appointmentLabel;
    public $walkInLabel;
    public $bookingAutoCancel;
    public $slots = [];
    public $modeSlots = [];
    public $weekSlots = [];
    public $reminderSlots = [];
    public $rowSlots = [];
    public $levelSlots = [];
      public $userAuth;
    public $bookingsidebarHeading;

    public function mount($teamId, $locationId)
    {
        $this->teamId = $teamId;
        $this->locationId = $locationId;
         $this->userAuth = Auth::user();
        $this->slots = AccountSetting::reqBeforeDay();
        $this->modeSlots = AccountSetting::reqAcceptMode();
        $this->weekSlots = AccountSetting::getWeek();
        $this->reminderSlots = AccountSetting::bookingReminderOptions();
        $this->rowSlots = AccountSetting::showCategoryPerRowOptions();
        $this->levelSlots = SiteDetail::getCategoryLevelEnable();
        // $this->categoryleveltimeslot = SiteDetail::where('team_id', $this->teamId)->value('category_slot_level');
        $sitedetail =  SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->locationId)->select('app_heading_third','booking_sidebar_heading')->first();

        $this->bookingHeadingText =   $sitedetail->app_heading_third ?? '';
        $this->bookingsidebarHeading =   $sitedetail->booking_sidebar_heading ?? '';

        $accountdetail = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->first();

        $this->minNotice =  $accountdetail->allow_req_min_before ?? '';
        $this->maxNotice =  $accountdetail->allow_req_before ?? '';
        $this->cancelNotice =  $accountdetail->cancelNotice ?? '';
        $this->requestMode =  $accountdetail->req_accept_mode ?? 'Auto Confirm';
        $this->weekStart =  $accountdetail->week_start ?? '';
        $this->bookingReminder =  $accountdetail->booking_reminder ?? '';
        $this->showCategoryPerRow =  $accountdetail->show_category_per_row ?? '';
        $this->inputPlaceholder =  $accountdetail->con_app_input_placeholder ?? '';
        $this->bookingConvertLabel =  $accountdetail->booking_convert_label ?? '';
        $this->walkInLabel =  $accountdetail->walk_in_label ?? '';
        $this->appointmentLabel =  $accountdetail->appointment_label ?? '';
        $this->bookingAutoCancel =  $accountdetail->booking_auto_cancel ?? '';

    }

    public function saveSetting()
    {

        $accountdetail = AccountSetting::where('team_id', $this->teamId)
            ->where('location_id', $this->locationId)
            ->where('slot_type', AccountSetting::BOOKING_SLOT)
            ->update([
                'allow_req_before' => !empty($this->maxNotice) ? $this->maxNotice : 365,
                'allow_req_min_before' => $this->minNotice,
                'allow_cancel_before' => $this->cancelNotice,
                'req_accept_mode' => !empty($this->requestMode) ? $this->requestMode : 'Auto Confirm',
                'week_start' => $this->weekStart,
                'show_category_per_row' => $this->showCategoryPerRow,
                'con_app_input_placeholder' => $this->inputPlaceholder,
                'booking_convert_label' => $this->bookingConvertLabel,
                'walk_in_label' => $this->walkInLabel,
                'appointment_label' => $this->appointmentLabel,
                'booking_reminder' => $this->bookingReminder,
                'booking_auto_cancel' => $this->bookingAutoCancel,
            ]);
     SiteDetail::where('team_id', $this->teamId)->where('location_id', $this->locationId)->update(['app_heading_third'=>$this->bookingHeadingText,'booking_sidebar_heading' =>$this->bookingsidebarHeading]);
        // SiteDetail::where('team_id', $this->teamId)->update(['category_slot_level' => (int)$this->categoryleveltimeslot, 'app_heading_third' => $this->bookingHeadingText]);
 ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Booking Settings', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        $this->dispatch('saved', ['message' => 'Updated Successfully']);
    }

    public function render()
    {
        return view('livewire.booking-scheduling-window-component');
    }
}
