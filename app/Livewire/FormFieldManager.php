<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FormField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class FormFieldManager extends Component
{
    use WithPagination;
    #[Title('Form Field Manager')]
    
    public $showCreateForm = false;
    public $editingId = null;
    public $teamId;
    public $locationId;
    public $search = '';

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Form Field Read')) {
            abort(403);
        }
        
        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
      
    }

    public function render()
    {
        $query = FormField::where('team_id', $this->teamId)->where('location_id',$this->locationId)
        ->where(function ($q) {
            $q->where('title', 'like', "%{$this->search}%")
              ->orWhere('type', 'like', "%{$this->search}%")
              ->orWhere('label', 'like', "%{$this->search}%")
              ->orWhere('placeholder', 'like', "%{$this->search}%");
        })
        ->orderBy('sort');

        return view('livewire.form-field-manager', [
            'formFields' => $query->paginate(10),
        ]);
    }

    // Update order in the database
    public function updateOrder($orderedItems)
    {
        DB::transaction(function () use ($orderedItems) {
            foreach ($orderedItems as $index => $item) {
                FormField::where('id', $item['value'])->update(['sort' => $index + 1]);
            }
        });

        session()->flash('message', 'Sort order updated!');
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination when searching
    }

    public function create()
    {
        $this->showCreateForm = true;
        $this->editingId = null;
    }

    public function edit($id)
    {
        $this->editingId = $id;
        $this->showCreateForm = false;
    }

    public function delete($id)
    {
        FormField::findOrFail($id)->delete();

        // Show success message
        session()->flash('success', __('text.Form field has been saved successfully.'));

        return redirect(route('tenant.form-fields'));
    }
}
