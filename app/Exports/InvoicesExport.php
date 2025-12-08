<?php 
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements WithDrawings, WithEvents, WithTitle
{
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is a logo');
        $drawing->setPath(public_path('/images/logo-transparent.png')); // Path to your image file
        $drawing->setHeight(90);
        $drawing->setCoordinates('B2');

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\BeforeSheet::class => function (\Maatwebsite\Excel\Events\BeforeSheet $event) {
                $worksheet = $event->sheet->getDelegate();

                // Add some sample data
                $worksheet->setCellValue('A1', 'Sample Data');
                $worksheet->setCellValue('A2', 'Name');
                $worksheet->setCellValue('B2', 'Value');
                $worksheet->setCellValue('A3', 'Sample');
                $worksheet->setCellValue('B3', '123');
            },
        ];
    }

    public function title(): string
    {
        return 'Sheet with Image';
    }
}
