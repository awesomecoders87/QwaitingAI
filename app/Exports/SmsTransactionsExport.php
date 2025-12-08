<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\AccountSetting;

class SmsTransactionsExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $data;
    protected $fromDate;
    protected $toDate;

    public function __construct(Collection $data, $fromDate, $toDate)
    {
        $this->data = $data;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function collection()
    {

        return $this->data->map(function ($row) {
            return [
                $row->created_at->format(AccountSetting::showDateTimeFormat()),
                $row->contact,
                $row->channel,
                $row->email,
                $row->type,
                $row->event_name,
                $row->status,
            ];
        });
    }

    public function headings(): array
    {
        return [ __('report.Date'), __('report.contact'), __('report.Channel'), __('report.Type'),  __('report.Event Name'), __('report.Status')  ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Auto-size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Insert filters
                $sheet->insertNewRowBefore(1, 2);
                $sheet->setCellValue('A1', 'SMS Transactions Report');
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Add From and To date row
                $sheet->setCellValue('A2', "From: {$this->fromDate}    To: {$this->toDate}");
                $sheet->mergeCells('A2:C2');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
