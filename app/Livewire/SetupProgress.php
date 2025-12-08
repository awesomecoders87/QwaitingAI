<?php
namespace App\Livewire;

use App\Models\Category;
use App\Models\Counter;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class SetupProgress extends Component
{
    public int $step = 1;
    public string $category_name = '';

    public $teamId;
    public $location;
    public $showProgressBar = true;

    public function mount()
    {
        $this->teamId = tenant('id');
        // if($this->teamId == 58){
        //   $this->showProgressBar = true;
        // }
       
      $this->location = Session::get('selectedLocation');

        if (!$this->location) {
            $this->step = 1;
            return;
        }

        // Step 1 - Location already selected (assumed as done)
        $step = 1;

        // Step 2 - Check Category
       $categoryExists = Category::whereJsonContains('category_locations', (string) $this->location)->exists();
 
        if ($categoryExists) {
            $step = 2;
        } else {
            $this->step = 2;
            return;
        }

        // Step 3 - Check Counter
        $counterExists = Counter::whereJsonContains('counter_locations', (string) $this->location)->exists();
// dd($counterExists);
        if ($counterExists) {
            $step = 3;
        } else {
            $this->step = 3;
            return;
        }

        // Step 4 - Check Staff
        $users = User::whereNotNull('locations')
            ->where('locations', '!=', '')
            ->where('id', '!=', Auth::id())
            ->whereRaw("JSON_VALID(locations)")
            ->where(function ($query) {
                $query->whereJsonContains('locations', (string) $this->location)
                      ->orWhereJsonContains('locations', (int) $this->location);
            })->exists();

        if ($users) {
            $step = 4;
        } else {
            $this->step = 4;
            
            return;
        }

        $this->step = $step;

        
        if($this->step == 4){
            $this->showProgressBar = false;
        }
       
    }

    public function previousStep()
    {
        switch ($this->step) {
            case 2:
                return redirect()->to('/locations');
            case 3:
                return redirect()->to('/category-management');
            case 4:
                return redirect()->to('/counters');
        }
    }

    public function nextStep()
    {
        switch ($this->step) {
            case 1:
                return redirect()->to('/category-management');
            case 2:
                return redirect()->to('/counters');
            case 3:
                return redirect()->to('/staff');
        }
    }

 

    public function render()
    {
        return view('livewire.setup-progress');
    }
}

