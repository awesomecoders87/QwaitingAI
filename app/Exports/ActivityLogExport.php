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
use Filament\Facades\Filament;
use App\Models\{Queue,ActivityLog};
use App\Models\AccountSetting;
use Illuminate\Support\Facades\Session;

class ActivityLogExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
 {
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $records;
    protected $fromDate;
    protected $toDate;
    protected $locationName;
    
    public function __construct($records, $fromDate, $toDate, $locationName)
    {
        $this->records = $records;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->locationName = $locationName;
    }

    public function title(): string
 {
        return 'Sheet 1';
    }

    public function array(): array
 {

        $data = [];
        $data[] = [ __('report.Activity Log Report'), null, Carbon::now()->format('F j, Y h:i A')];
        $data[] = [ __('report.From Date') . ':', $this->fromDate];
        $data[] = [ __('report.To Date') . ':', $this->toDate];
        $data[] = [ __('report.Location') . ':', $this->locationName];
        $data[] = []; // Blank row

        $data[] =  [
            __('report.Date'),
            __('report.Event'),
            __('report.Description'),
            __('report.IP Address'),
            __('report.Username'),

        ];

        $teamId = tenant('id');
        $selectedLocation = Session::get('selectedLocation');
        $datetimeFormat = AccountSetting::showDateTimeFormat($teamId,$selectedLocation);

        foreach ( $this->records as $activity ) {
            $userName = '';
            $created_at= '';
            $created_at =  Carbon::parse($activity->created_at)->format($datetimeFormat);
            if ( $activity->type == ActivityLog::TYPE_CALL )
            $userName = $activity->creator->name ?? 'N/A';
            $data[] = [
                $created_at,
                $activity->type,
                $activity->text,
                $activity->ip_address,
                $userName,

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
                    'C' => 25,
                    'D' => 25,
                    'E' => 25,
                    'F' => 25,

                ];
                foreach ( $columnWidths as $column => $width ) {
                    $sheet->getColumnDimension( $column )->setWidth( $width );
                }
                $sheet->getRowDimension( 1 )->setRowHeight( 30 );

                $sheet->mergeCells( 'A1:B1' );
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
                $sheet->mergeCells( 'C1:E1' );
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

                $sheet->getStyle( 'A2:E2' )->applyFromArray( [
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
                        $sheet->getStyle( 'A'. $row. ':E'. $row )->applyFromArray( [
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
