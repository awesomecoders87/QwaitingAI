<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainStaticsChartExport implements WithMultipleSheets
{
    use Exportable;

    protected $imageExcel;
    protected $filters;
    
    public function __construct($imageExcel)
    {
        $this->imageExcel = $imageExcel;
    }

    public function sheets(): array
    {
        $sheets = [
           'Sheet 1'=> new StatisticsChartExport($this->imageExcel),
           'Sheet 2'=>new StaticCallChartExport($this->imageExcel),
           'Sheet 3'=>new StaticSummaryCounterExport($this->imageExcel),
        ];
        return $sheets;
    }
}
