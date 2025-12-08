<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ErrorList;
use Livewire\Attributes\On;

class ErrorLogs extends Component
{
    use WithPagination;

    // Form fields
    public string $code        = '';
    public string $message     = '';
    public string $description = '';
    public string $resolution  = '';
    public string $type  = '';
    public $editingId = null;    // ID of the record being edited
    public $confirmingDeleteId;  // ID to delete after confirmation

  
    // Validation rules
   protected function rules()
{
    $rules = [
        'code'        => ['required', 'string', 'max:20'],
        'message'     => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'resolution'  => ['nullable', 'string'],
        'type'        => ['required', 'string'],
    ];

    if ($this->editingId) {
        $rules['code'][] = 'unique:error_lists,code,' . $this->editingId;
    } else {
        $rules['code'][] = 'unique:error_lists,code';
    }

    return $rules;
}

   public function save()
    {
        $data = $this->validate();

        if ($this->editingId) {
            // Update existing
            ErrorList::find($this->editingId)->update($data);
            session()->flash('success', 'Error log updated.');
        } else {
            // Create new
            ErrorList::create($data);
            session()->flash('success', 'Error log saved.');
        }

        // Reset form & state
        $this->reset(['code','message','description','resolution','type','editingId']);
    }

    public function edit($id)
    {
        $log = ErrorList::findOrFail($id);

        // Populate form
        $this->code        = $log->code;
        $this->message     = $log->message;
        $this->description = $log->description;
        $this->resolution  = $log->resolution;
        $this->type        = $log->type;
        $this->editingId   = $id;
    }

    public function confirmDelete($id)
    {
        $this->confirmingDeleteId = $id;
        $this->dispatch('confirm-delete'); // optional: to show JS confirm
    }

    #[On('confirmed-delete')]
    public function delete()
    {
        ErrorList::findOrFail($this->confirmingDeleteId)->delete();
        $this->reset('confirmingDeleteId');
        $this->dispatch('deleted');


    }

    public function render()
    {
        return view('livewire.error-logs', [
            'items' => ErrorList::orderBy('code')->paginate(10),
        ]);
    }
}
