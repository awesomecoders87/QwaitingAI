<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DynamicReport;
use App\Models\Level;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Session;

class DynamicReportsList extends Component
{
    use WithPagination;

    public $team_id;
    public $location_id;
    public $selectedId;
    public $search;


    public function mount()
    {
        $this->team_id = tenant('id');
        $this->location_id = Session::get('selectedLocation');
    }

    public function deleteconfirmation($id)
    {
       $this->selectedId = $id;
       $this->dispatch('confirm-delete');
    }

      #[On('confirmed-delete')]
    public function delete()
    {
        if ($this->selectedId) {
            DynamicReport::where('team_id', $this->team_id)
            ->where('location_id', $this->location_id)->where('id',$this->selectedId)->delete();
            $this->dispatch('deleted');
        }

      
    }


    public function render()
    {
        $reports = DynamicReport::where('team_id', $this->team_id)
            ->where('location_id', $this->location_id)
             ->when($this->search, function ($query) {
                $query->where('report_name', 'like', '%' . $this->search . '%');
            })
            ->paginate('10');

        return view('livewire.dynamic-reports-list', compact('reports'));
    }
}
