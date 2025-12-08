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
    Counter,
    Category
};
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

#[Layout('components.layouts.custom-display-layout')]
class ServiceDisplay extends Component
{

    public $teamId;
    public $location;
    public $locationName;
    public $showlocationpage = false;
    public $showdisplaypage = false;
    public $allLocations = [];
    public $subcategories = [];
    public $timezone;

    public function mount()
    {
        $this->teamId = tenant('id');

        $siteDetail = SiteDetail::where('team_id', $this->teamId)
                ->select('select_timezone')
                ->first();
                $this->timezone = $siteDetail->select_timezone ?? 'UTC';


            // Current time in user's timezone
            // $now = Carbon::now($this->timezone)->toDateString();
            $now = Carbon::today($this->timezone);

        if (Session::has('selectedLocation')) {
            $this->location = Session::get('selectedLocation');
            $this->showlocationpage = false;
            $this->showdisplaypage = true;
            $this->subcategories = Category::with(['queuesSubCategoryId' => function ($query) use($now) {
                 $query->select('id', 'sub_category_id', 'token','status','arrives_time','called')
                    ->whereDate('arrives_time',$now)
                    ->where('status', 'Progress')
                    ->where('called', 'yes')
                    ->whereNotNull('start_datetime')
                    ->where(function ($q) {
                        $q->whereNull('closed_datetime')
                            ->orWhere('closed_datetime', '');
                    });
            }])
                ->select('id', 'name')
                ->where('team_id', $this->teamId)
                ->where('level_id', 2)
                ->whereJsonContains('category_locations', (string)$this->location)
                ->get();
        } else {
            $this->allLocations = Location::select('id', 'location_name', 'address', 'location_image')
                ->where('team_id', $this->teamId)
                ->where('status', 1)->get();

            $this->showlocationpage = false;
            $this->showdisplaypage = true;

            if ($this->allLocations->isEmpty()) {
                abort(403, 'No active locations found.');
            }

            // ✅ If only one location, set session automatically
            if ($this->allLocations->count() == 1) {

                $locationId = $this->allLocations->first()->id;
                Session::put('selectedLocation', $locationId);
                $this->location = Session::get('selectedLocation');
                $this->showlocationpage = false;
                $this->showdisplaypage = true;
                $this->subcategories = Category::with(['queuesSubCategoryId' => function ($query) use($now) {
                $query->select('id', 'sub_category_id', 'token','status','arrives_time','called')
                    ->whereDate('arrives_time', $now)
                    ->where('called', 'yes')
                    ->where('status', 'Progress')
                    ->whereNotNull('start_datetime')
                    ->where(function ($q) {
                        $q->whereNull('closed_datetime')
                            ->orWhere('closed_datetime', '');
                    });
            }])
                ->select('id', 'name')
                ->where('team_id', $this->teamId)
                ->where('level_id', 2)
                ->whereJsonContains('category_locations', (string)$this->location)
                ->get();
            }
        }

    }

    public function selectLocation($locationId)
    {
              $now = Carbon::today($this->timezone);

        Session::put('selectedLocation', $locationId);
        $this->location = Session::get('selectedLocation');
        $siteDetails = SiteDetail::where('location_id', $locationId)->first();
        $timezone = $siteDetails->select_timezone ?? 'UTC';
        Session::put('timezone_set', $timezone);
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        $this->subcategories = Category::with(['queuesSubCategoryId' => function ($query) use($now) {
                  $query->select('id', 'sub_category_id', 'token','status','arrives_time','called')
                    ->whereDate('arrives_time', $now)
                    ->where('called', 'yes')
                    ->where('status', 'Progress')
                    ->whereNotNull('start_datetime')
                    ->where(function ($q) {
                        $q->whereNull('closed_datetime')
                            ->orWhere('closed_datetime', '');
                    });
            }])
                ->select('id', 'name')
                ->where('team_id', $this->teamId)
                ->where('level_id', 2)
                ->whereJsonContains('category_locations', (string)$this->location)
                ->get();

        $this->dispatch('refreshComponent');
        $this->showlocationpage = false;
        $this->showdisplaypage = true;
    }



    public function render()
    {

        return view('livewire.service-display');
    }
}
