<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DynamicReport;
use App\Models\Level;

class EditDynamicReports extends Component
{
    public $team_id;
    public $location_id;
    public $report_name;
    public $report_fields = [];
    public $status = 1;
    public $selectAll = [];
    public $columns = [];
    public $level1, $level2, $level3;
    public $availableFields = [];
    public $report;
    public $editId;

    public function mount($id)
    {
        $this->team_id = tenant('id');
        $this->location_id = session('selectedLocation');
        $this->editId = $id;

        $this->report = DynamicReport::where('id',$this->editId)->where('team_id', $this->team_id)
        ->where('location_id', $this->location_id)->first();

        if(empty($this->report)){
            return redirect('dynamic-reports-list');
        }

        // Pre-fill form fields with existing data
        $this->report_name   = $this->report->report_name;
        $this->report_fields = $this->report->report_fields ?? [];
        $this->status        = (bool)$this->report->status;

        
        $this->availableFields = DynamicReport::availableFields($this->team_id,$this->location_id);
      
    }

    public function saveReport()
    {
        $this->validate([
            'report_name'   => 'required|string|max:255',
            'report_fields' => 'required|array|min:1',
        ]);

        $this->report->update([
            'team_id'       => $this->team_id,
            'location_id'   => $this->location_id,
            'report_name'   => $this->report_name,
            'report_fields' => $this->report_fields,
            'status'        => $this->status ? 1 : 0, // store as 1/0
        ]);

        // session()->flash('updated', 'Report updated successfully!');
           $this->dispatch('updated');
    }

    public function render()
    {
        return view('livewire.edit-dynamic-reports');
    }
}
