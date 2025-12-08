<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DynamicReport;
use App\Models\Level;
use Illuminate\Support\Facades\Auth;

class CreateDynamicReports extends Component
{
    public $team_id;
    public $location_id;
    public $report_name;
    public $report_fields = [];
    public $status = 1;
    public $selectAll = [];
    public $columns = [];
    public $level1,$level2,$level3;
    public $availableFields = [];

    public function mount()
    {
        $this->team_id = tenant('id');
        $this->location_id = session('selectedLocation');
        $this->availableFields = DynamicReport::availableFields($this->team_id,$this->location_id);
        $this->status = true;
    }

     public function toggleSelectAll()
{
    if ($this->selectAll) {
        $this->columns = [];
        $this->selectAll = false;
    } else {
        $this->columns = array_keys($this->availableFields);
        $this->selectAll = true;
    }
}

    public function saveReport()
    {
        $this->validate([
            'report_name' => 'required|string|max:255',
            'report_fields' => 'required|array|min:1',
        ]);

        DynamicReport::create([
            'team_id'      => $this->team_id,
            'location_id'  => $this->location_id,
            'report_name'  => $this->report_name,
            'report_fields'=> $this->report_fields,
            'status'       => $this->status,
        ]);

        session()->flash('success', 'Report saved successfully!');
        $this->reset('report_name', 'report_fields', 'status');
        $this->dispatch('created');
    }

    public function render()
    {

        return view('livewire.create-dynamic-reports');
    }
}
