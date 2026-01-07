<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\SiteDetail;
use App\Models\LanguageSetting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class BookingTypeSelection extends Component
{
    #[Title('Select Booking Type')]
    public function mount()
    {
        // Basic setup similar to MainBookingAppointment if needed (e.g. locale)
        $teamId = tenant('id');
        // We can just use the defaults for now, or fetch settings if strictly necessary
        // Replicating basic locale setting if available in session or settings could be good
        // but to keep it simple and robust, we'll rely on global middleware or default behavior
        // unless specific logic determines otherwise. 

        // However, MainBookingAppointment sets locale dynamically:
        $location_id = Session::get('selectedLocation');

        if ($location_id) {
            $setting = LanguageSetting::where('team_id', $teamId)
                ->where('location_id', $location_id)
                ->first();

            if ($setting && $setting->enabled_language_settings && !empty($setting->default_language)) {
                App::setLocale($setting->default_language);
            }
        }
    }

    public function render()
    {
        return view('livewire.booking-type-selection')->layout('components.layouts.custom-booking-layout');
    }
}