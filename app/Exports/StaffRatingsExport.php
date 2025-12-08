<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StaffRatingsExport implements 
    FromCollection, 
    WithHeadings, 
    WithTitle, 
    ShouldAutoSize, 
    WithStyles,
    WithCustomStartCell
{
    protected $data;
    protected $title;
    protected $startDate;
    protected $endDate;

    public function __construct($data, $title = 'Staff Ratings', $startDate = null, $endDate = null)
    {
        $this->data = $data->map(function($item) {
            return [
                'name' => $item->name,
                'guest_served' => $item->guest_served,
                'total_feedback' => $item->total_feedback,
                'star4' => $item->star4,
                'star3' => $item->star3,
                'star2' => $item->star2,
                'star1' => $item->star1,
                'avg_rating' => number_format($item->avg_rating, 2)
            ];
        });

        $this->title = $title;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    // ⛔ Do NOT insert headers manually
    public function collection()
    {
        return $this->data;
    }

    // Tell Excel to start headings at row 3
    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return [
            'STAFF',
            'GUEST SERVED',
            'TOTAL FEEDBACK',
            '4 STARS',
            '3 STARS',
            '2 STARS',
            '1 STAR',
            'AVERAGE RATING'
        ];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet)
    {
        // Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'STAFF RATING REPORT');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Date Range
        if ($this->startDate && $this->endDate) {
            $sheet->mergeCells('A2:H2');
            $sheet->setCellValue(
                'A2',
                'Period: ' .
                \Carbon\Carbon::parse($this->startDate)->format('M d, Y') .
                ' to ' .
                \Carbon\Carbon::parse($this->endDate)->format('M d, Y')
            );
            $sheet->getStyle('A2')->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // Header row style at real row 3
        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ]);

        return [];
    }
}
