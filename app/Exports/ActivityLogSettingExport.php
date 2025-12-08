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

class ActivityLogSettingExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
 {
    protected $filters;

    public function __construct( $filters )
 {
        $this->filters = $filters;
    }
    public function title(): string
    {
        return 'Sheet 2';
    }

    public function array(): array
 {
        $data = [];
        $data []  = [ 'Activity Log Report Setting', Carbon::now()->format( 'F j, Y h:i A' ) ];

        foreach ( $this->filters as $key=> $filter ) {
            $data [] = [
                $key,
                $filter
            ];
        }
        return $data;

    }

    public function registerEvents(): array
 {
        return [
            AfterSheet::class => function ( AfterSheet $event ) {
                $sheet = $event->sheet->getDelegate();

                // Set specific column widths
                $columnWidths = [
                    'A' => 25,
                    'B' => 25,

                ];

                foreach ( $columnWidths as $column => $width ) {
                    $sheet->getColumnDimension( $column )->setWidth( $width );
                }
                $sheet->getRowDimension( 1 )->setRowHeight( 30 );

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
                $sheet->getStyle( 'B1' )->applyFromArray( [
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

                $highestRow = $sheet->getHighestDataRow() ;

                // Styling the rest of the rows with alternating colors starting from the first record row
                for ( $row = 2; $row <= $highestRow; $row++ ) {

                    if ( $row % 2 == 0 ) {
                        $sheet->getStyle( 'A'. $row. ':B'. $row )->applyFromArray( [
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => [ 'argb' => 'D3D3D3' ], // Light Grey background
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ] );
                    }
                }
            }
            ,
        ];
    }

}
