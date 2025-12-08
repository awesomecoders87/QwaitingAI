<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Models\PaymentSetting;
use App\Models\Level;
use App\Models\Location;
use App\Models\FormField;
use Livewire\WithFileUploads;
use Auth;
use Livewire\Attributes\Title;

class CategoryCreateComponent extends Component
{

    use WithFileUploads;

    #[Title('Service Create')]

    public $locationId, $teamId, $tab = '1';
    public $name,$other_name,$acronym,$visitor_in_queue,$locations,$img,$sort,$amount,$is_paid;
    public $display_on ="Display on Transfer & Ticket Screen";
    public $for_screen = "Display on Walk-In & Appointment Screen";
    public $booking_category_show_for ="Backend & Online Appointment Screen";
    public $isEdit = false;
    public $allLocations = [];
    public $category = [];
    public $parent_id = null;
    public $paymentSet;
    public $parentCategory = [];
    public $redirectUrl;
    public $isService =false;
    public $serviceTime;
    public $note;
    public $description;
    public $ticket_note;
    public $label_image;
    public $service_color;
    public $label_background_color;
    public $label_font_color;
    public $label_text;
    public $bg_color;

    public function mount($level = null,$categoryId = null){

        $user = Auth::user();
        if (!$user->hasAnyPermission(['Service Add','Service Edit']) ) {
            abort(403);
        }

        if($level == null){
            return redirect()->back();
        }

        $this->teamId = tenant('id'); // Get the current tenant ID
        $this->locationId = Session::get('selectedLocation');

        $levels =  Level::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        // ->where('level', $level)
        ->get();

        $this->paymentSet = PaymentSetting::where('team_id', $this->teamId)
        ->where('location_id', $this->locationId)
        ->select('id','enable_payment','category_level')
        ->first();

        //   if(empty($this->paymentSet)){
        //   return redirect('ticket-screen-settings');
        // }

         $this->tab = $level;
        $this->visitor_in_queue = 1;
        $this->sort = 0;

        if ($categoryId) {
            $this->category = Category::findOrFail($categoryId);
            $this->isEdit = true;
            $this->loadCategoryData();

        }

        if($level == 2)
        {

            $this->parentCategory = Category::where('team_id',$this->teamId)
            ->whereJsonContains('category_locations',"$this->locationId")
            ->where('level_id',1)
            ->where(function ($query) {
                $query->whereNull('parent_id')
                      ->orWhere('parent_id', '');
            })
            ->select('id','name')
            ->get();

        }elseif($level == 3){
            $this->parentCategory = Category::where('team_id',$this->teamId)
            ->whereJsonContains('category_locations',"$this->locationId")
            ->where('level_id',2)
            ->whereNotNull('parent_id')
            ->select('id','name')
            ->get();
        }

        if(Auth::user()->is_admin == 1){
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('status',1)
            ->select('location_name', 'id')
            ->get();
        }else{
            $this->allLocations = Location::where('team_id', tenant('id'))
            ->where('id', Auth::user()?->locations)
            ->where('status',1)
            ->select('location_name', 'id')
            ->get();
        }

    }

    private function loadCategoryData()
    {
        $this->name = $this->category->name;
        $this->other_name = $this->category->other_name;
        $this->acronym = $this->category->acronym;
        $this->display_on = $this->category->display_on;
        $this->sort = $this->category->sort ?? 0;
        $this->for_screen = $this->category->for_screen;
        $this->booking_category_show_for = $this->category->booking_category_show_for;
        $this->visitor_in_queue = $this->category->visitor_in_queue ?? 1;
        $this->locations = $this->category->category_locations;
        $this->parent_id = $this->tab > 1 ? $this->category->parent_id : '';
        $this->is_paid = $this->category->is_paid ?? 0;
        $this->amount = $this->category->amount ?? 0;
        $this->redirectUrl = $this->category->redirect_url ?? '';
        $this->isService = (bool)$this->category->is_service_template;
        $this->serviceTime = $this->category->service_time ?? '';
        $this->note = $this->category->note ?? '';
        $this->description = $this->category->description ?? '';
        $this->ticket_note = $this->category->ticket_note ?? '';
        $this->service_color = $this->category->service_color ?? '#fff';
        $this->label_background_color = $this->category->label_background_color ?? '#ffffff';
        $this->label_font_color = $this->category->label_font_color ?? '#000000';
        $this->label_text = $this->category->label_text ?? '';
        $this->bg_color = $this->category->bg_color ?? '';
    }


    public function removeTempImage()
    {
        $this->img = null;
    }

    public function deleteOldImage()
    {
        if ($this->isEdit && $this->category->img && \Storage::disk('public')->exists($this->category->img)) {
            \Storage::disk('public')->delete($this->category->img);
        }

        $this->category->img = null;
        $this->category->save();

        // Also clear preview
        $this->img = null;
    }
    public function deleteOldLabelImage()
    {
        if ($this->isEdit && $this->category->label_image && \Storage::disk('public')->exists($this->category->label_image)) {
            \Storage::disk('public')->delete($this->category->label_image);
        }

        $this->category->label_image = null;
        $this->category->save();

        // Also clear preview
        $this->img = null;
    }


    public function saveCategory()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'acronym' => 'nullable|string',
            'display_on' => 'nullable|string',
            'for_screen' => 'nullable|string',
            'booking_category_show_for' => 'nullable|string',
            'visitor_in_queue' => 'nullable|integer',
            'sort' => 'nullable|integer',
            'ticket_note' => 'nullable|string|max:500',
             'note' => 'nullable|string|max:500',
            'serviceTime' => 'nullable|integer',
            'label_text' => 'nullable|string|max:50',
            'locations' => 'required|array',
            'img' => $this->isEdit ? 'nullable|mimes:jpg,jpeg,png|max:2048' : 'nullable|mimes:jpg,jpeg,png|max:2048',
            'label_image' => $this->isEdit ? 'nullable|mimes:jpg,jpeg,png|max:2048' : 'nullable|mimes:jpg,jpeg,png|max:2048',
            'parent_id' => $this->tab > 1 ? 'required|integer' : 'nullable',
        ],[

            'parent_id.required' => 'Parent Service is required',
        ]);

       if ($this->paymentSet && $this->paymentSet->enable_payment == 1 && $this->paymentSet->category_level == $this->tab) {
            $this->validate([
                'is_paid' => 'required|in:0,1',
            ]);

            if ($this->is_paid == 1) {
                $this->validate([
                    'amount' => 'required|numeric|min:0',
                ]);
            }
        }


    // Unique check for each location
    foreach ($this->locations as $locationId) {
        $exists = Category::where('name', $this->name)
            ->where('team_id', $this->teamId)
            ->when(!empty($this->parent_id), fn($q) => $q->where('parent_id', $this->parent_id))
            ->whereJsonContains('category_locations', "$locationId")
            ->when($this->isEdit, fn($q) => $q->where('id', '!=', $this->category->id))
            ->exists();

        if ($exists) {
            $this->addError('name', 'The category name already exists for one of the selected locations.');
            return;
        }
    }

    // image handling
        $imagePath = $this->isEdit ? $this->category->img : null;
        $labelImagePath = $this->isEdit ? $this->category->label_image : null;


        if ($this->img) {
            // Store new image
            $imagePath = $this->img->store('category', 'public');
        }

        if ($this->label_image) {
            // Store new image
            $labelImagePath = $this->label_image->store('category', 'public');
        }



       // Prepare data
    $data = [
        'team_id' => $this->teamId,
        'level_id' => $this->tab,
        'name' => $this->name,
        'parent_id' => $this->parent_id ?? null,
        'other_name' => $this->other_name,
        'acronym' => $this->acronym,
        'display_on' => $this->display_on,
        'sort' =>  empty($this->sort) ? 0 : $this->sort,
        'for_screen' => $this->for_screen,
        'booking_category_show_for' => $this->booking_category_show_for,
        'visitor_in_queue' => !empty($this->visitor_in_queue) ?(int) $this->visitor_in_queue : 1,
        'category_locations' => $this->locations,
        'img' => $imagePath,
        'redirect_url' => $this->redirectUrl,
        'is_service_template' => $this->isService ? 1 : 0,
        'service_time' => $this->serviceTime ?? '',
        'note' => $this->note ?? '',
        'description' => $this->description ?? '',
        'ticket_note' => $this->ticket_note ?? '',
        'label_image' => $labelImagePath,
        'service_color' => $this->service_color ?? '#fff',
        'label_background_color' => $this->label_background_color ?? '#ffffff',
        'label_font_color' => $this->label_font_color ?? '#000000',
        'label_text' => $this->label_text ?? '',
        'bg_color' => $this->bg_color ?? '',
    ];

    // Add paid details if applicable
    if ($this->paymentSet && $this->paymentSet->enable_payment == 1) {
        $data['is_paid'] = $this->is_paid ?? 0;
        $data['amount'] = $this->amount ?? 0;
    } else {
        $data['is_paid'] = 0;
        $data['amount'] = 0;
    }

    // Save or update
    $categoryDetail = Category::updateOrCreate(
        ['id' => $this->isEdit ? $this->category->id : null],
        $data
    );

       if($this->isEdit){

           $this->dispatch('updated',  '/category-management?tab='. $this->tab);
       }else{
           $formfield = FormField::where('team_id',$this->teamId)->where('location_id',$this->locationId )->first();

           if(!empty($formfield)){
               $formfield->categories()->sync($categoryDetail);
           }
           $this->dispatch('created', '/category-management?tab='. $this->tab);
       }
        // session()->flash('success', $this->isEdit ? 'Category updated successfully' : 'Category created successfully');
        // return redirect()->route('category.index');
    }

    public function render()
    {
        return view('livewire.category-create-component');
    }
}
