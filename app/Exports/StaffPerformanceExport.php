<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\CarbonInterval;

class StaffPerformanceExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
 {
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $records;
    protected $headings;
    protected $selectedLocation;
    protected $catIDs;

    public function __construct( $records, $headings, $selectedLocation, $catIDs )
 {
        $this->records = $records;
        $this->headings = $headings;
        $this->selectedLocation = $selectedLocation;
        $this->catIDs = $catIDs;
    }

    public function title(): string
 {
        return 'Sheet 1';
    }

    public function array(): array
    {
        $data = [];
        $data[] = ['Staff Performance Report', null, null, Carbon::now()->format('F j, Y h:i A')];
        $data[] = $this->headings;
    
        foreach ($this->records as $staff) {
            $totalServedTime = 0;
            $queues = $staff->queues->where('locations_id', $this->selectedLocation);
    
            $queueCount = $queues->count();
            foreach ($queues as $queue) {
                if (!empty($queue->start_datetime) && !empty($queue->closed_datetime)) {
                    $totalServedTime += $queue->closed_datetime->diffInSeconds($queue->start_datetime);
                }
            }
    
            $catCount = [];
            foreach ($this->catIDs as $catID) {
                $catCount[] = $queues->where('category_id', $catID)->count();
            }
    
            $interval = CarbonInterval::seconds($totalServedTime)->cascade();
            $formattedServedTime = $interval->format('%H:%I:%S');
    
            $averageServedTime = $queueCount > 0 ? $totalServedTime / $queueCount : 0;
    
            $data[] = array_merge(
                [$staff->name, $queueCount],
                $catCount,
                [
                    $formattedServedTime,
                    gmdate('H:i:s', $averageServedTime) 
                ]
            );
        }
    
        return $data;
    }
    
    
    public function registerEvents(): array
 {
        return [
            AfterSheet::class => function ( AfterSheet $event ) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getRowDimension( 1 )->setRowHeight( 30 );
                $headerRow = 2;
                // Assuming your header is in the second row

                // Get the highest column dynamically based on header row
                $highestColumn = $sheet->getHighestColumn( $headerRow );

                $range = "A{$headerRow}:{$highestColumn}{$headerRow}";

                $sheet->mergeCells( 'A1:C1' );
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
                $sheet->mergeCells( 'D1:'.$highestColumn.'1' );
                $sheet->getStyle( 'C1' )->applyFromArray( [
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

                $sheet->getStyle( $range )->applyFromArray( [
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
                        $sheet->getStyle( 'A'. $row. ':'.$highestColumn. $row )->applyFromArray( [
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
