<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Location;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SiteDetail;
use App\Models\ColorSetting;
use App\Models\Category;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;
use Illuminate\Support\Arr;

class Locations extends Component
{
    use WithPagination;

    #[Title('Location')]

    public $search = '';
    public $selectedId;
    public $teamId;
    public $locationId;
    public $userAuth;

    public $copySettingsModal = false;
    public $targetLocationId; // The location you clicked copy for
    public $sourceLocationId; // The location you select in modal
    public $copyTicketSettings = false;
    public $copyColorSettings = false;
    public $copyServices = false;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Location')) {
            abort(403);
        }
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
    }

    public function updatedSearch($value)
        {
        //    $this->search =$value; // This will show the latest search input
        }
    public function deleteconfirmation($id)
    {
        $this->selectedId = $id;

        $this->dispatch('confirmation-delete');
        // $this->resetPage();
    }

    #[On('confirmed-delete')]
    public function deleteLocation()
    {

        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Delete Location', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        Location::findOrFail($this->selectedId)->delete();
        // session()->flash('message', 'Location deleted successfully.');
        Session::put('selectedLocation', User::getDefaultLocation());
        // $this->resetPage();
        $this->reset('selectedId');


        // Dispatch event to notify frontend
        $this->dispatch('deleted');
    }

    public function openCopySettingsModal($locationId)
{
    $this->reset(['sourceLocationId', 'copyTicketSettings', 'copyColorSettings']);
    $this->targetLocationId = $locationId;
    $this->copySettingsModal = true;
}

public function copySettings()
{
    $this->validate([
        'sourceLocationId' => 'required|different:targetLocationId|exists:locations,id',
    ]);

    $copyfromlocation_id = $this->sourceLocationId;
    $copyTolocation_id   = $this->targetLocationId;

    //Tickets setting,Logo update
    if ($this->copyTicketSettings) {
        $copySiteDetail = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id', $copyfromlocation_id)
            ->first();

        if ($copySiteDetail) {
            $dataToUpdate = Arr::except(
                $copySiteDetail->toArray(),
                ['id', 'team_id', 'location_id', 'created_at', 'updated_at']
            );

            SiteDetail::where('team_id', $this->teamId)
                ->where('location_id', $copyTolocation_id)
                ->update($dataToUpdate);
        }
    }

    if ($this->copyColorSettings) {
        $copySiteDetail = ColorSetting::where('team_id', $this->teamId)
            ->where('location_id', $copyfromlocation_id)
            ->first();

        if ($copySiteDetail) {
            $dataToUpdate = Arr::except(
                $copySiteDetail->toArray(),
                ['id', 'team_id', 'location_id', 'created_at', 'updated_at']
            );

            ColorSetting::where('team_id', $this->teamId)
                ->where('location_id', $copyTolocation_id)
                ->update($dataToUpdate);
        }
    }

   if ($this->copyServices) {
    $categories = Category::whereJsonContains('category_locations', (string) $copyfromlocation_id)->get();

    foreach ($categories as $category) {
        $locations = $category->category_locations;

        // If it's stored as a string, decode it
        if (is_string($locations)) {
            $locations = json_decode($locations, true);
        }

        // Ensure it's an array
        if (!is_array($locations)) {
            $locations = [];
        }

        // Add new location
        if (!in_array((string) $copyTolocation_id, $locations, true)) {
            $locations[] = (string) $copyTolocation_id;
        }

        // ✅ Save as proper JSON array (not double encoded)
        $category->update([
            'category_locations' => $locations, // will be JSON if cast is set
        ]);
    }
}
    // Close modal
    $this->copySettingsModal = false;

    // Reset variables so modal is fresh next time
    $this->reset(['sourceLocationId', 'targetLocationId', 'copyTicketSettings', 'copyColorSettings']);

    // Fire success event
    $this->dispatch('copy-success');
}

    public function render()
    {
        $query = Location::where('team_id',$this->teamId);

        // if (!empty($this->search)) {
        //     $query->where(function ($q) {
        //         $q->where('location_name', 'like', '%' . $this->search . '%')
        //           ->orWhere('address', 'like', '%' . $this->search . '%')
        //           ->orWhere('city', 'like', '%' . $this->search . '%')
        //           ->orWhere('state', 'like', '%' . $this->search . '%')
        //           ->orWhere('country', 'like', '%' . $this->search . '%')
        //           ->orWhere('zip', 'like', '%' . $this->search . '%');
        //     });

        // }
        $query->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('location_name', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%')
                  ->orWhere('state', 'like', '%' . $this->search . '%')
                  ->orWhere('country', 'like', '%' . $this->search . '%');
            });
        });

        $locations = $query->paginate(10);

        return view('livewire.locations', compact('locations'));
    }
}
