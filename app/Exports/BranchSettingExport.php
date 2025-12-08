<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BranchSettingExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected $filters;
    protected $questions;

    public function __construct($filters, $questions)
    {
        $this->filters   = $filters;
        $this->questions = $questions;
    }

    public function title(): string
    {
        return 'Sheet 2';
    }

    public function array(): array
    {
        $data = [];

        $data[] = ['Branch Report Settings', Carbon::now()->format('F j, Y h:i A')];

        foreach ($this->filters as $key => $value) {
            $data[] = [$key, $value];
        }

        $data[] = ['Feedback Questions', ''];

        foreach ($this->questions as $q) {
            $data[] = [$q['question']];
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            }
        ];
    }
}
