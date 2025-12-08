<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ScreenTemplate;
use App\Models\Counter;
use App\Models\Category;
use App\Models\DisplaySettingModel;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class CreateScreenTemplate extends Component
{
    #[Title('Screen Template Create')]

    public $name, $type, $template, $templateClass, $display_screen_tune;
    public $counter_ids = [], $selectedCategories = [];
    public $teamId;
    public $locationId;
    public $userAuth;
    public $showSingleCounter = false;
    public $selectAll = false;
    public $counters;


    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string',
        'template' => 'required|string',
        'counter_ids' => 'nullable|array',
        'selectedCategories' => 'nullable|array',
    ];

    public function mount()
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
         $this->counters = Counter::where('team_id', tenant('id'))->whereJsonContains('counter_locations', (string)$this->locationId)->pluck('name', 'id');
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedCategories = Category::where('team_id', $this->teamId)
                ->whereJsonContains('category_locations', "$this->locationId")
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedCategories = [];
        }
    }

    public function updatedTemplate($value)
    {
        $this->templateClass = ScreenTemplate::getTemplatesClass($value);
        $this->showSingleCounter = $value === 'single_counter';
        // $this->counter_ids = [];
        // $this->selectedCategories = [];
    }

    public function save()
    {
        $this->validate();

        $screenTemplate = ScreenTemplate::create([
            'team_id' => $this->teamId,
            'location_id' => $this->locationId,
            'name' => $this->name,
            'type' => $this->type,
            'template' => $this->template,
            'template_class' => $this->templateClass,
            'display_screen_tune' => $this->display_screen_tune
        ]);

        if ($this->type === 'Counter') {
           if($this->showSingleCounter){
                $screenTemplate->counters()->sync([$this->counter_ids[0]]);
            }else{
                $screenTemplate->counters()->sync($this->counter_ids);
            }
            
        } elseif ($this->type === 'Category') {
            $screenTemplate->categories()->sync($this->selectedCategories);
        }

        session()->flash('message', 'Screen Template created successfully.');
        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Add Screen Template', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);
        return redirect()->route('tenant.screen-templates');
    }

    private function buildCategoryTree($categories, $parentId = null)
    {
        return $categories->where('parent_id', $parentId)->map(function ($category) use ($categories) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $this->buildCategoryTree($categories, $category->id) // Recursive call
            ];
        });
    }

    public function render()
    {
        $categories = Category::where('team_id', $this->teamId)
            ->whereJsonContains('category_locations', "$this->locationId")
            ->select('id', 'parent_id', 'level_id', 'name')
            ->get();

        // Structure categories into hierarchy
        $structuredCategories = $this->buildCategoryTree($categories);

        return view('livewire.create-screen-template', [
            'templates' => ScreenTemplate::getTemplates(),
          
            'getcategories' => $structuredCategories,
            'voiceMessages' => DisplaySettingModel::getVoiceMessages(),
        ]);
    }
}
