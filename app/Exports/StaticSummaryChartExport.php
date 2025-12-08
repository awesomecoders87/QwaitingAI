<?php 
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class StaticSummaryChartExport  implements WithMultipleSheets, WithTitle
{
    use RegistersEventListeners;

    public function sheets(): array
    {
        return [
            new DataSheet(), // Sheet with static data
            new ChartSheet(), // Sheet with chart
        ];
    }

    public function title(): string
    {
        return 'Static Chart Export';
    }
}

class DataSheet implements WithEvents
{
    use RegistersEventListeners;

    public static function beforeSheet(\Maatwebsite\Excel\Events\BeforeSheet $event)
    {
        $worksheet = $event->sheet->getDelegate();
        $data = [
            ['Category', 'Value'],
            ['A', 4],
            ['B', 7],
            ['C', 5],
            ['D', 3],
            ['E', 6],
        ];
        // Populates the worksheet with static data
        $worksheet->fromArray($data, null, 'A1');
    }
}

class ChartSheet implements WithCharts
{
    public function charts()
    {
        // Labels for the data series
        $dataSeriesLabels = [
            new DataSeriesValues('String', 'DataSheet!$B$1', null, 1), // Title for the data series
        ];

        // X-Axis labels
        $xAxisTickValues = [
            new DataSeriesValues('String', 'DataSheet!$A$2:$A$6', null, 4), // X-Axis labels
        ];

        // Data points for the chart
        $dataSeriesValues = [
            new DataSeriesValues('Number', 'DataSheet!$B$2:$B$6', null, 4), // Data for the series
        ];

        // Create a DataSeries
        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,  // Chart type: Bar chart
            DataSeries::GROUPING_STANDARD,  // Grouping type
            range(0, count($dataSeriesValues) - 1), // Plot order
            $dataSeriesLabels, // Data series labels
            $xAxisTickValues, // X-Axis labels
            $dataSeriesValues // Data points
        );

        // Create the PlotArea
        $plotArea = new PlotArea(null, [$series]);

        // Create the Legend
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);

        // Create the Chart Title
        $title = new Title('Sample Chart');

        // Create the Chart
        $chart = new Chart(
            'chart1', // Chart name
            $title, // Chart title
            $legend, // Chart legend
            $plotArea, // Plot area
            true, // Plot visible only
            0, // Display blanks as
            null, // X-Axis label
            null  // Y-Axis label
        );

        // Set the position of the chart within the worksheet
        $chart->setTopLeftPosition('A1');
        $chart->setBottomRightPosition('H20');

        return [$chart];
    }
}
