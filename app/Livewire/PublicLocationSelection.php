<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use App\Models\Domain;
use App\Models\VirtualQueueSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;

#[Layout('components.layouts.custom-layout')]
class PublicLocationSelection extends Component
{
    public $locations = [];
    public $teamId;

    public function mount($team_id = null)
    {
        // Get team ID from current domain
        // if ($team_id) {
        //     // If team_id is passed as parameter, use it
        //     $this->teamId = $team_id;
        // } else {
        //     // Get current domain from request
        //     $currentDomain = request()->getHost();
            
        //     // Look up team_id from domains table
        //     $domainRecord = Domain::where('domain', $currentDomain)->first();
            
        //     if ($domainRecord) {
        //         $this->teamId = $domainRecord->team_id;
        //     } else {
        //         // Fallback: try tenant() helper
        //         $currentTenant = tenant();
        //         $this->teamId = $currentTenant ? $currentTenant->id : 1;
        //     }
        // }


        $this->teamId = tenant('id');

        $locationId = Session::get('selectedLocation');
        if($locationId){

            return redirect()->route('public.virtual-queue-type-selection');
            // return redirect()->route('public.virtual-queue-type-selection', [
            //     'location_id' => $locationId,
            //     'team_id' => $this->teamId
            // ]);
        }
        
        // Log for debugging
        Log::info('PublicLocationSelection - Domain: ' . request()->getHost());
        Log::info('PublicLocationSelection - Team ID: ' . $this->teamId);
        
        // Load all active locations for this team
        $locationsQuery = Location::where('team_id', $this->teamId);
        
        // Log total locations before status filter
        Log::info('Total locations for team: ' . $locationsQuery->count());
        
        $this->locations = $locationsQuery
            ->where('status', true) // status is boolean
            ->get()
            ->map(function($location) {
                // Get average waiting time for this location
                $avgWaitTime = $this->getAverageWaitTime($location->id);
                
                return [
                    'id' => $location->id,
                    'name' => $location->location_name ?? $location->name ?? 'Location ' . $location->id,
                    'address' => $location->address,
                    'city' => $location->city,
                    'state' => $location->state,
                    'country' => $location->country,
                    'avg_wait_time' => $avgWaitTime,
                    'image' => $location->location_image ?? $location->branch_image ?? null,
                ];
            });
        
        // Log final count
        Log::info('Active locations found: ' . count($this->locations));
    }

    protected function getAverageWaitTime($locationId)
    {
        // Get current queue count (waiting)
        $currentQueue = DB::table('queues_storage')
            ->where('team_id', $this->teamId)
            ->where('locations_id', $locationId)
            ->whereNull('start_datetime')
            ->whereNull('called_datetime')
            ->whereNull('cancelled_datetime')
            ->whereNull('closed_datetime')
            ->whereDate('arrives_time', now()->toDateString())
            ->count();
        
        // Get average service time from completed queues
        $completedQueues = DB::table('queues_storage')
            ->where('team_id', $this->teamId)
            ->where('locations_id', $locationId)
            ->whereNotNull('start_datetime')
            ->whereNotNull('closed_datetime')
            ->whereDate('arrives_time', '>=', now()->subDays(7)->toDateString())
            ->select(DB::raw('TIMESTAMPDIFF(MINUTE, start_datetime, closed_datetime) as duration'))
            ->get();
        
        $avgMinutes = 5; // Default
        if ($completedQueues->count() > 0) {
            $totalMinutes = $completedQueues->sum('duration');
            $avgMinutes = round($totalMinutes / $completedQueues->count());
        }
        
        // Calculate estimated wait
        $estimatedWait = $currentQueue * $avgMinutes;
        
        return $estimatedWait;
    }

    public function selectLocation($locationId)
    {
        if($locationId){
          Session::put('selectedLocation', $locationId);
           return redirect()->route('public.virtual-queue-type-selection');
        }
        // Redirect to virtual queue type selection with location and team ID


        // return redirect()->route('public.virtual-queue-type-selection', [
        //     'location_id' => $locationId,
        //     'team_id' => $this->teamId
        // ]);
    }

    public function render()
    {
        return view('livewire.public-location-selection');
    }
}
