<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SubCategoriesExport implements FromArray, WithHeadings, WithTitle
{
    protected $data;
    protected $filter;
    protected $totals;
    protected $levels;

    public function __construct($data, $filter,$totals,$levels)
    {
        $this->data = $data;
        $this->filter = $filter;
        $this->totals = $totals;
        $this->levels = $levels;
    }

    public function array(): array
    {
       
        $categoriesData = [];
        
        // Extract categories data for the rows
        foreach ($this->data['categories'] as $category) {
            // Assume category data is structured as your example
            $categoriesData[] = [
                $category['category']['name'] ?? '', 
                $category['subCategory']['name'] ?? '', 
                $category['childCategory']['name'] ?? '', 
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
        '',
        'Total',
        '',
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
            [ __('report.Sub Services Report')],
            [ __('report.Filters')],
            [ $this->levels['level1'] . ($this->filter['selectedLevel1'] ?? 'All')],
            [ $this->levels['level2'] . ($this->filter['selectedLevel2'] ?? 'All')],
            [ $this->levels['level3'] . ($this->filter['selectedLevel3'] ?? 'All')],
            [ __('report.Date Range') .':' . ($this->filter['startDate'] ?? 'N/A') . ' to ' . ($this->filter['endDate'] ?? 'N/A')],
            ['Status: ' . ($this->filter['status'] ?? 'All')],
            [''],
            [ 
            $this->levels['level1'],
            $this->levels['level2'],
            $this->levels['level3'],
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
        return 'Sub Services Report';
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
               AfterSheet::class => function ( AfterSheet $event ) {
                   $sheet = $event->sheet->getDelegate();
   
                   // Set specific column widths
                   $columnWidths = [
                       'A' => 5,
                       'B' => 15,
                       'C' => 20,
                       'D' => 15,
                       'E' => 15,
                       'F' => 15,
                       'G' => 15,
                       'H' => 10,
                       'I' => 20,
                       'J' => 15,
                       'K' => 25,
                       'L' => 15,
                       'M' => 25,
                       'N' => 25,
                   ];
                   foreach ( $columnWidths as $column => $width ) {
                       $sheet->getColumnDimension( $column )->setWidth( $width );
                   }
                   $sheet->getRowDimension( 1 )->setRowHeight( 30 );
   
                   $sheet->mergeCells( 'A1:I1' );
                   $sheet->getStyle( 'A1' )->applyFromArray( [
                       'font' => [
                           'bold' => true,
                           'size' => 16,
                           'color' => [ 'argb' => '4F46E5' ],
                       ],
                       'alignment' => [
                           'horizontal' => Alignment::HORIZONTAL_LEFT,
                           'vertical' => Alignment::VERTICAL_CENTER,
                       ],
                   ] );
                   $sheet->mergeCells( 'J1:N1' );
                   $sheet->getStyle( 'J1' )->applyFromArray( [
                       'font' => [
                           'bold' => false,
                           'size' => 12,
                           'color' => [ 'argb' => '4A4B49' ],
                       ],
                       'alignment' => [
                           'horizontal' => Alignment::HORIZONTAL_LEFT,
                           'vertical' => Alignment::VERTICAL_CENTER,
                       ],
                   ] );
   
                   $sheet->getStyle( 'A2:N2' )->applyFromArray( [
                       'font' => [
                           'bold' => true,
                           'color' => [ 'argb' => 'FFFFFF' ],
                       ],
                       'fill' => [
                           'fillType' => Fill::FILL_SOLID,
                           'color' => [ 'argb' => '4F46E5' ],
                       ],
                       'alignment' => [
                           'horizontal' => Alignment::HORIZONTAL_CENTER,
                           'ertical' => Alignment::VERTICAL_CENTER,
                       ],
                   ] );
   
                   $highestRow = $sheet->getHighestDataRow();
   
                   // Styling the rest of the rows with alternating colors starting from the first record row
                   for ( $row = 3; $row <= $highestRow; $row++ ) {
   
                       if ( $row % 2 == 0 ) {
                           $sheet->getStyle( 'A'. $row. ':N'. $row )->applyFromArray( [
                               'fill' => [
                                   'fillType' => Fill::FILL_SOLID,
                                   'color' => [ 'argb' => 'D3D3D3' ], // Light Grey background
                               ],
                           ] );
                       }
                   }
               }
               ,
           ];
       }
    
}
