<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainStaffPerformanceExport implements WithMultipleSheets
{
    use Exportable;

    protected $records;
    protected $filters;
    protected $headings;
    protected $selectedLocation;
    protected $catIDs;
    
    public function __construct($records,$filters,$headings,$selectedLocation,$catIDs)
    {
        $this->records = $records;
        $this->filters = $filters;
        $this->headings = $headings;
        $this->selectedLocation = $selectedLocation;
        $this->catIDs = $catIDs;
    }

    public function sheets(): array
    {
         $sheets = [
           'Sheet 1'=> new StaffPerformanceExport($this->records,$this->headings,$this->selectedLocation, $this->catIDs),
           'Sheet 2'=>new StaffPerformanceSettingExport($this->filters),
        ];
        return $sheets;
    }
}
