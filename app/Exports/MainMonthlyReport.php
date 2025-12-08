<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainMonthlyReport implements WithMultipleSheets
{
    use Exportable;

    protected $records;
    protected $filters;
    protected $levels;
    protected $formFields;
    protected $options;

    public function __construct($records,$filters,$levels,$formFields, $options = [])
    {
        $this->records = $records;
        $this->filters = $filters;
        $this->levels = $levels;
        $this->formFields = $formFields;
        $this->options = $options;
    }

    public function sheets(): array
    {
        $sheets = [
           'Sheet 1'=> new MonthlyReport($this->records,$this->levels,$this->formFields, $this->options),
           'Sheet 2'=>new MonthlySettingReport($this->filters),
        //    'Sheet 3'=>new InvoicesExport(),


        ];
        return $sheets;
    }
}
