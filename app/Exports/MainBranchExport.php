<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainBranchExport implements WithMultipleSheets
{
    use Exportable;

    protected $rows;
    protected $filters;
    protected $domain;
    protected $questions;

    public function __construct($rows, $filters, $domain, $questions)
    {
        $this->rows      = $rows;
        $this->filters   = $filters;
        $this->domain    = $domain;
        $this->questions = $questions;
    }

    public function sheets(): array
    {
        return [
            'Sheet 1' => new BranchExport($this->rows, $this->domain, $this->questions),
            'Sheet 2' => new BranchSettingExport($this->filters, $this->questions),
        ];
    }
}
