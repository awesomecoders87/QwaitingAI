<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Queue;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BranchExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    protected $rows;
    protected $domain;
    protected $questions;
    protected $startDate;
    protected $endDate;

    public function __construct($rows, $domain, $questions, $startDate = null, $endDate = null)
    {
        $this->rows      = $rows;
        $this->domain    = $domain;
        $this->questions = $questions;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function title(): string
    {
        return 'Sheet 1';
    }

    public function array(): array
    {
        $data = [];

        // ROW 1 → MAIN TITLE (no background)
        $data[] = ['Branch Feedback Report'];

        // ROW 2 → DATE FILTER
        $dateFilterText = 'Date: ';
        if ($this->startDate && $this->endDate) {
            $dateFilterText .= Carbon::parse($this->startDate)->format('d M Y') .
                               ' to ' .
                               Carbon::parse($this->endDate)->format('d M Y');
        } else {
            $dateFilterText .= 'All Dates';
        }
        $data[] = [$dateFilterText];

        // ROW 3 → TABLE HEADERS
        $header = [
            'Token',
            'Name',
            'Contact',
            'Staff',
            'Date/Time',
            'Average Rating',
            'Comment',
        ];

        // Add dynamic question names
        foreach ($this->questions as $q) {
            $header[] = $q['question'];
        }

        $data[] = $header;

        // DATA ROWS
        foreach ($this->rows as $row) {

            $line = [
                $row->token,
                $row->name,
                (string)$row->contact,
                $row->staff,
                $row->datetime,
                number_format($row->average_rating, 2),
                $row->comment,
            ];

            foreach ($this->questions as $q) {
                $line[] = $row->{$q['question']} ?? 'N/A';
            }

            $data[] = $line;
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // 1️⃣ ROW 1 styling → Title (no background)
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Merge A1 to full header width
                $lastColumn = $sheet->getHighestColumn();
                $sheet->mergeCells("A1:{$lastColumn}1");

                // 2️⃣ ROW 2 → Date Filter (no color)
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => false,
                    ],
                ]);

                // 3️⃣ ROW 3 → HEADER (light blue background)
                $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color'    => ['argb' => 'D6EAF8'], // LIGHT BLUE
                    ],
                ]);
            }
        ];
    }
}
