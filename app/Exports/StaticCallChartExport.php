<?php 

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use App\Models\SiteDetail;
class StaticCallChartExport implements WithDrawings, WithEvents, ShouldAutoSize, WithTitle
{
    public $imageExcel;
    public function __construct($imageExcel){

        $this->imageExcel = $imageExcel;

    }

    public function drawings()
    {
        $image =  SiteDetail::viewImage( SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->imageExcel['teamId'] );

        $logo = new Drawing();
        $logo->setName('Statistics Report Of Call Summary');
        $logo->setPath(public_path($image)); 
        $logo->setHeight(50);
        $logo->setCoordinates('A1');
        $summaryChart = new Drawing();
        $summaryChart->setName('Statistics Report Of Call History');
        $summaryChart->setPath(public_path($this->imageExcel[ 'callHistoryChart' ])); 
        $summaryChart->setCoordinates('A12');
        $summaryChart->setHeight(400);

        return [$logo,$summaryChart];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\BeforeSheet::class => function (\Maatwebsite\Excel\Events\BeforeSheet $event) {
                $worksheet = $event->sheet->getDelegate();
                $worksheet->setCellValue('A7', 'Statistics Report Of Call History');
                $worksheet->mergeCells( 'A7:D7' );

                
                $worksheet->setCellValue('F1', Carbon::now()->format( 'F j, Y h:i A' ) );
                $worksheet->mergeCells( 'F1:H2' );
                $worksheet->getStyle( 'F1' )->applyFromArray( [
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

                $worksheet->getStyle( 'A7' )->applyFromArray( [
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
                $worksheet->setCellValue('A9', 'Start Date');
                $worksheet->setCellValue('B9', $this->imageExcel['start_date']);
                $worksheet->setCellValue('C9', 'End Date');
                $worksheet->setCellValue('D9', $this->imageExcel['end_date']);

                
                    // Add background to cells where images are drawn
                    $worksheet->getStyle('A1:B2')->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFFF'], // White background
                        ],
                    ]);

                    $worksheet->getStyle('A12:H34')->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFFF'], // White background
                        ],
                    ]);

                }
        ];
    }

    public function title(): string
    {
        return 'Sheet 2';
    }
}
