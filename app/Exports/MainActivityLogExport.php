<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainActivityLogExport implements WithMultipleSheets
{
    use Exportable;

    protected $records;
    protected $filters;
    
    public function __construct($records,$filters)
    {
        $this->records = $records;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        
        $sheets = [
           'Sheet 1'=> new ActivityLogExport($this->records),
           'Sheet 2'=>new ActivityLogSettingExport($this->filters),


        ];
        return $sheets;
    }
}
