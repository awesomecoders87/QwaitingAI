<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Models\SiteDetail;
use Livewire\Attributes\On;
use App\Models\Location;
use App\Models\User;
use App\Models\CustomSlot;
use App\Models\AccountSetting;
use App\Models\Level;
use App\Models\ActivityLog;
use DB;
use Auth;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Http\Request;

class CategoryManagement extends Component
{
    use WithPagination;
    #[Title('Service List')]

    public $locationId, $teamId, $tab = '1';
    // public $categories = [];
    public $search = '';
    public $selectedCategory=null;
    public $selectedMultiple = [];
    public $selectAll = false;
    public $level1;
    public $level2;
    public $level3;
    public $userAuth;


    public function mount(Request $request)
    {
         $this->userAuth = Auth::user();
        if (!$this->userAuth->hasPermissionTo('Service Read')) {
            abort(403);
        }

        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');
        $levels =  Level::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->whereIn('level', [1, 2, 3])
        ->get()
        ->keyBy('level');

        $this->level1 = $levels[1]->name ?? 'Level 1';
        $this->level2 = $levels[2]->name ?? 'Level 2';
        $this->level3 = $levels[3]->name ?? 'Level 3';

        $this->tab = $request->query('tab') ?? 1;
    }


// Trigger loadCategories when search is updated
public function updatedSearch()
{
    // $this->loadCategories();
    $this->resetPage();
}

public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination when searching
    }
    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage(); // Reload categories when tab changes
    }


    public function deleteCategory($id)
    {
        $this->selectedCategory = $id;
        $this->dispatch('confirm-delete');
    }

    #[On('confirmed-delete')]
     public function confirmDelete()
    {
        if ($this->selectedCategory) {
            // **Optimized Delete**: Use DB transactions to ensure integrity
            DB::transaction(function () {
                DB::table('category_user')->where('category_id', $this->selectedCategory)->delete();
                AccountSetting::where('category_id', $this->selectedCategory)->delete();
                CustomSlot::where('category_id', $this->selectedCategory)->delete();
                Category::where('id', $this->selectedCategory)->delete();

                ActivityLog::storeLog(
                    $this->teamId,
                    $this->userAuth->id,
                    null,
                    null,
                    ActivityLog::DELETE,
                    $this->locationId,
                    ActivityLog::CATEGORY,
                    null,
                    $this->userAuth
                );
            });

            $this->resetPage();
            $this->dispatch('deleted');
        }
    }

    #[On('bulkDelete')]
    public function updatedSelectAll($ids)
    {

        if ($ids) {
            $this->selectedMultiple = $ids;
        } else {
            $this->selectedMultiple = [];
        }
        $this->dispatch('confirm-multiple-delete');
    }

#[On('confirmed-multiple-delete')]
    public function bulkDelete()
    {

        if (!empty($this->selectedMultiple)) {

            DB::table('category_user')->whereIn('category_id', $this->selectedMultiple)->delete();
            AccountSetting::whereIn('category_id', $this->selectedMultiple)->delete();
            CustomSlot::whereIn('category_id', $this->selectedMultiple)->delete();

            Category::whereIn('id', $this->selectedMultiple)->delete();
            $this->selectedMultiple = [];
            $this->selectAll = false;
              ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, ActivityLog::BULK_DELETE, $this->locationId, ActivityLog::CATEGORY, null, $this->userAuth);
            $this->dispatch('deleted');
        }
    }

    public function render()
    {

        $siteSetting = SiteDetail::where('team_id', $this->teamId)
            ->where('location_id',$this->locationId)
            ->select('category_slot_level','enable_time_slot')
            ->first();
		
		$query = Category::where('level_id', $this->tab)
            ->where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', (string) $this->locationId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            });
			

        // Eager load relationships based on level
        if ($this->tab == 2) {
            $query->with('getparent');
        } elseif ($this->tab == 3) {
            $query->with(['getparent.getparent']);
        }

        $categories = $query->paginate(10);
		
		 // Collect all location IDs used by these categories
		$allLocationIds = collect($categories->items())
			->flatMap(function($cat) {
				$locations = $cat->category_locations;

				if (is_array($locations)) {
					return $locations;
				}

				if (is_string($locations) && $locations !== '') {
					return json_decode($locations, true) ?? [];
				}

				return [];
			})
			->unique()
			->toArray();

		// Fetch all related locations at once
		$locations = Location::whereIn('id', $allLocationIds)
    ->pluck('location_name', 'id'); // key = id, value = name

        return view('livewire.category-management', [
            'categories' => $categories,
            'siteSetting' => $siteSetting,
			'locations' => $locations,
        ]);
    }
}
