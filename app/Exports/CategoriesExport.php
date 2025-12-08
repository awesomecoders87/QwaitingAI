<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesExport implements FromArray, WithHeadings, WithTitle
{
    protected $data;
    protected $filter;
    protected $totals;

    public function __construct($data, $filter,$totals)
    {
        $this->data = $data;
        $this->filter = $filter;
        $this->totals = $totals;
    }
    
    public function array(): array
    {

       
        $categoriesData = [];
        
        // Extract categories data for the rows
        foreach ($this->data['categories'] as $category) {
            // Assume category data is structured as your example
            $categoriesData[] = [
                $category['category']['name'] ?? '', 
                $category['total_calls'],
                $category['pending_calls'],
                $category['pending_percentage'],
                $category['served_calls'],
                $category['served_percentage'],
                $category['cancel_calls'],
                $category['cancel_percentage'],
                $category['no_show'],
                $category['no_show_percentage'],
                $category['total_served_time'],
                $category['average_served_time'],
                $category['max_served_time'],
                $category['total_waiting_less_15_min'],
                // Calculate the percentage here for waiting < 15 min
                ($category['total_calls'] > 0) ? round(($category['total_waiting_less_15_min'] / $category['total_calls']) * 100, 2) . '%' : '0%',
                $category['total_waiting_greater_15_min'],
                // Calculate the percentage here for waiting > 15 min
                ($category['total_calls'] > 0) ? round(($category['total_waiting_greater_15_min'] / $category['total_calls']) * 100, 2) . '%' : '0%',
                $category['average_wait_time'],
                $category['max_waiting_time']
            ];
        }

    
      // Add totals row
      $categoriesData[] = [
        'Total',
        $this->totals['total_calls'],
        $this->totals['pending_calls'],
        // Calculate the percentage of pending calls
        ($this->totals['total_calls'] > 0) ? round(($this->totals['pending_calls'] / $this->totals['total_calls']) * 100, 2) . '%' : '0%',
        $this->totals['served_calls'],
        // Calculate the percentage of served calls
        ($this->totals['total_calls'] > 0) ? round(($this->totals['served_calls'] / $this->totals['total_calls']) * 100, 2) . '%' : '0%',
        $this->totals['cancel_calls'],
        // Calculate the percentage of cancelled calls
        ($this->totals['total_calls'] > 0) ? round(($this->totals['cancel_calls'] / $this->totals['total_calls']) * 100, 2) . '%' : '0%',
        $this->totals['no_show'],
        // Calculate the percentage of no shows
        ($this->totals['total_calls'] > 0) ? round(($this->totals['no_show'] / $this->totals['total_calls']) * 100, 2) . '%' : '0%',
        $this->secondToTime($this->totals['total_served_time']),
        gmdate("H:i:s", $this->totals['average_served_time'] / max(count($this->data['categories']), 1)),
        gmdate("H:i:s", $this->totals['max_served_time']),
        $this->totals['total_waiting_less_15_min'],
        // Calculate the percentage of waiting < 15 min for total
        ($this->totals['total_calls'] > 0) ? round(($this->totals['total_waiting_less_15_min'] / $this->totals['total_calls']) * 100, 2) . '%' : '0%',
        $this->totals['total_waiting_greater_15_min'],
        // Calculate the percentage of waiting > 15 min for total
        ($this->totals['total_calls'] > 0) ? round(($this->totals['total_waiting_greater_15_min'] / $this->totals['total_calls']) * 100, 2) . '%' : '0%',
        gmdate("H:i:s", $this->totals['average_wait_time'] / max(count($this->data['categories']), 1)),
        gmdate("H:i:s", $this->totals['max_waiting_time'])
    ];

        return $categoriesData;
    }

    public function headings(): array
    {
        return [
            [ __('report.Services Report')],
            [ __('report.Filters')],
            [ __('report.Level 1') . ':' . ($this->filter['selectedLevel1'] ?? 'All')],
            [ __('report.Date Range') . ':' . ($this->filter['startDate'] ?? 'N/A') . ' to ' . ($this->filter['endDate'] ?? 'N/A')],
            [ __('report.status') . ':' . ($this->filter['status'] ?? 'All')],
            [''],
            [ __('report.Queue'),
            __('report.Arrived'),
            __('report.Pending'),
            __('report.%'),
            __('report.Served'),
            __('report.%'),
            __('report.Cancelled'),
            __('report.%'),
            __('report.No Show'),
            __('report.%'),
            __('report.Workload'),
            __('report.Average'),
            __('report.Max'),
            __('report.< SL'),
            __('report.< SL %'),
            __('report.> SL'),
            __('report.> SL %'),
            __('report.Average'),
            __('report.Max')]
        ];
    }

    public function title(): string
    {
        return 'Services Report';
    }

    private function timeToSecond($time) {
        $timeParts = explode(':', $time);
        if (count($timeParts) === 3) {
            list($hours, $minutes, $seconds) = $timeParts;
            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }
        return 0;
    }
    
    private function secondToTime($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set specific column widths
                $columnWidths = [
                    'A' => 20,
                    'B' => 10,
                    'C' => 10,
                    'D' => 10,
                    'E' => 10,
                    'F' => 10,
                    'G' => 10,
                    'H' => 10,
                    'I' => 10,
                    'J' => 10,
                    'K' => 15,
                    'L' => 15,
                    'M' => 15,
                    'N' => 10,
                    'O' => 10,
                    'P' => 10,
                    'Q' => 10,
                    'R' => 15,
                    'S' => 15,
                    ];

                    foreach ($columnWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                    }

            // Merge title and filter headings
            $sheet->mergeCells('A1:S1');
            $sheet->mergeCells('A2:S2');
            $sheet->mergeCells('A3:S3');
            $sheet->mergeCells('A4:S4');
            $sheet->mergeCells('A5:S5');
            $sheet->mergeCells('A6:S6');

            // Styling Title Row
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'ffffff'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '165BAA'],
                ],
            ]);

            // Style for filters (A2 to A5)
            $sheet->getStyle('A2:A5')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                ],
            ]);

            // Styling the header row
            $headerRow = 7;
            $sheet->getStyle("A{$headerRow}:S{$headerRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'ffffff'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '165BAA'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // Center align all data rows (except the title and filter rows)
            $dataRowStart = 8;
            $dataRowEnd = $dataRowStart + count($this->data['categories']); // without totals row

            // Total row comes right after
            $totalRow = $dataRowEnd + 1;

            $sheet->getStyle("A{$dataRowStart}:S{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Bold the total row
                    $sheet->getStyle("A{$totalRow}:S{$totalRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                        ],
                    ]);
                },
            ];
}
}