<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\AccountSetting;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithChunkReading;


class MonthlyReport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $records;
    protected $levels;
    protected $formFields;
    protected $options = [];

    public function __construct($records, $levels, $formFields, $options = [])
    {
        ini_set('memory_limit', '-1');
        // ini_set('max_execution_time', 300);
        $this->records = $records;
        $this->levels = $levels;
        $this->formFields = $formFields;
        $this->options = $options ?? [];
    }

    public function collection()
    {
        return $this->records;
    }


    public function title(): string
    {
        return 'Sheet 1';
    }


    public function array(): array
    {

        $data = [];
        $data[]  = [__('report.Monthly Report'), null, null, null, null, null, null, null, null, Carbon::now()->format('F j, Y h:i A')];



        // Fixed header row
        $header = [
            __('report.S.No'),
            __('report.Token'),
            __('report.created at'),
            $this->levels['level1'],
            $this->levels['level2'],
            $this->levels['level3'],
            __('report.Counter'),
            __('report.Called'),
        ];

        // Add dynamic form fields
        if (!empty($this->formFields)) {
            foreach ($this->formFields as $formField) {
                $header[] = $formField->title;
            }
        }

        // Continue with static headers
        $header = array_merge($header, [
            __('report.Closed By'),
            __('report.Assign To'),
            __('report.Note'),
            __('report.called at'),
            __('report.closed at'),
            __('report.Response Time'),
            __('report.Serving Time'),
            __('report.Status'),
        ]);

        if (!empty($this->options['enable_export_buttons'])) {
            $header[] = $this->options['doc_file_label'];
        }

        $data[] = $header;

        $teamId = tenant('id');
        $selectedLocation = Session::get('selectedLocation');
        $datetimeFormat = AccountSetting::showDateTimeFormat($teamId, $selectedLocation);
        $serial = 1;
        foreach ($this->records as $monthly) {
            $created_at = '';
            $called_at = '';
            $closed_at = '';
            $created_at =  Carbon::parse($monthly->datetime)->format($datetimeFormat);
            $called_at =  !empty($monthly->called_datetime) ? Carbon::parse($monthly->called_datetime)->format($datetimeFormat) : '';
            $closed_at =  !empty($monthly->closed_datetime) ? Carbon::parse($monthly->closed_datetime)->format($datetimeFormat) : '';
            
            $isCalled =     $monthly->called_datetime  ? __('text.yes') : __('text.no');
            $json = json_decode($monthly->json, true);
            $email = $json['email'] ?? null;
            $responseTime  = null;
            $servingTime = null;
            // if ( $monthly->called_datetime && $monthly->arrives_time ) {
            //     $responseTime = $monthly->called_datetime->diffInMinutes( $monthly->arrives_time );
            // }
            // $servingTime  = null;
            // if ( $monthly->closed_datetime && $monthly->start_datetime ) {
            //     $servingTime  = $monthly->closed_datetime->diffInMinutes( $monthly->start_datetime );
            // }

            if ($monthly->called_datetime && $monthly->arrives_time) {
                $responseSeconds = abs($monthly->called_datetime->diffInSeconds($monthly->arrives_time));
                $responseHours = intdiv($responseSeconds, 3600);
                $responseMinutes = intdiv($responseSeconds % 3600, 60);
                $responseSeconds = $responseSeconds % 60;
                $responseTime = sprintf('%02d:%02d:%02d', $responseHours, $responseMinutes, $responseSeconds);
            }

            if ($monthly->closed_datetime && $monthly->start_datetime) {
                $servingSeconds = abs($monthly->closed_datetime->diffInSeconds($monthly->start_datetime));
                $servingHours = intdiv($servingSeconds, 3600);
                $servingMinutes = intdiv($servingSeconds % 3600, 60);
                $servingSeconds = $servingSeconds % 60;
                $servingTime = sprintf('%02d:%02d:%02d', $servingHours, $servingMinutes, $servingSeconds);
            }

            $custom_form_values = [];
            if (!empty($this->formFields)) {
                    foreach ($this->formFields as $formField) {
                        $value = $json[\Illuminate\Support\Str::lower($formField->title)] ?? '';

                        if (is_array($value)) {
                            // Join array values into a comma-separated string
                            $value = implode(', ', $value);
                        }

                        $custom_form_values[] = $value;
                    }
                }

            $row = [
                $serial++,
                $monthly->start_acronym . '' . $monthly->token,
                $created_at,
                $monthly->category?->name,
                $monthly->subCategory?->name,
                $monthly->childCategory?->name,
                $monthly->counter?->name,
                $isCalled,
            ];

            // merge dynamic form values here
            $row = array_merge($row, $custom_form_values);

            $row = array_merge($row, [
                $monthly->closedBy?->name,
                $monthly->assignStaff?->name,
                $monthly->esitmate_note,
                $called_at,
                $closed_at,
                $responseTime,
                $servingTime,
                $monthly->status,
            ]);

            if (!empty($this->options['enable_export_buttons'])) {
                $doc = null;
                if (!empty($monthly->doc_file)) {
                    $doc = url('storage/' . $monthly->doc_file);
                }
                $row[] = $doc;
            }

            $data[] = $row;
        }

        return $data;
    }



    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
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
                    'O' => 25,
                    'P' => 25,
                    'Q' => 25,
                ];
                foreach ($columnWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }
                $sheet->getRowDimension(1)->setRowHeight(30);

                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['argb' => '4F46E5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->mergeCells('J1:O1');
                $sheet->getStyle('J1')->applyFromArray([
                    'font' => [
                        'bold' => false,
                        'size' => 12,
                        'color' => ['argb' => '4A4B49'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A2:Q2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => '4F46E5'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'ertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $highestRow = $sheet->getHighestDataRow();

                // Styling the rest of the rows with alternating colors starting from the first record row
                for ($row = 3; $row <= $highestRow; $row++) {

                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['argb' => 'D3D3D3'], // Light Grey background
                            ],
                        ]);
                    }
                }
            }

        ];
    }
}
