<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ScreenTemplate;
use App\Models\Counter;
use App\Models\Category;
use App\Models\DisplaySettingModel;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;

class EditScreenTemplate extends Component
{
    #[Title('Edit Screen Template')]

    public $screenTemplateId;
    public $name, $type, $template, $templateClass, $display_screen_tune;
    public $counter_ids = [];
    public $teamId;
    public $locationId;
    public $selectedCategories = [];
    public $selectAll = false;
    public $showSingleCounter = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|string',
        'template' => 'required|string',
        'counter_ids' => 'nullable|array',
        'selectedCategories' => 'nullable|array',
    ];

    public function mount($id)
    {
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $screenTemplate = ScreenTemplate::findOrFail($id);

        $this->screenTemplateId = $screenTemplate->id;
        $this->name = $screenTemplate->name;
        $this->type = $screenTemplate->type;
        $this->template = $screenTemplate->template;
        $this->templateClass = $screenTemplate->template_class;
        $this->display_screen_tune = $screenTemplate->display_screen_tune;

        // Load related counters and categories if applicable
        $this->counter_ids = $screenTemplate->counters()->pluck('id')->toArray();
        $this->selectedCategories = $screenTemplate->categories->pluck('id')->toArray();
    }

    public function updatedTemplate($value)
    {
        $this->templateClass = ScreenTemplate::getTemplatesClass($value);
        $this->showSingleCounter = $value === 'single_counter';
    }

    public function update()
    {
        $this->validate();

        $screenTemplate = ScreenTemplate::findOrFail($this->screenTemplateId);
        $screenTemplate->update([
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

        session()->flash('message', 'Screen Template updated successfully.');
        return redirect()->route('tenant.screen-templates');
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

        return view('livewire.edit-screen-template', [
            'templates' => ScreenTemplate::getTemplates(),
            'counters' => Counter::where('team_id', tenant('id'))->whereJsonContains('counter_locations', (string)$this->locationId)->pluck('name', 'id'),
            // 'categories' => Category::where('team_id', tenant('id'))->whereJsonContains('category_locations', (string) $this->locationId)->pluck('name', 'id'),
            'getcategories' => $structuredCategories,
            'voiceMessages' => DisplaySettingModel::getVoiceMessages(),
        ]);
    }
}
