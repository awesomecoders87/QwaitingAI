<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Overview Per Day Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .filters { margin-bottom: 15px; }
        .filters p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f0f0f0; }
        .logo { width: 100px; }
    </style>
</head>
<body>

<table width="100%">
        <tr>
            <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
            <td style="text-align: right;"><h2></h2></td>
            <td style="text-align: right;"><p>{{ __('report.Date Range') }}: {{ $from }} {{ __('report.to') }} {{ $to }}</p></td>
        </tr>
</table>

   
    <table>
        <thead>
            <tr>
                <th>{{ __('report.Date') }}</th>
                <th>{{ __('report.Arrived') }}</th>
                <th>{{ __('report.Served') }}</th>
                <th>{{ __('report.Waiting') }}</th>
                <th>{{ __('report.Percentage') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPoints as $point)
                <tr>
                    <td>{{ $point['date'] }}</td>
                    <td>{{ $point['arrived_count'] }}</td>
                    <td>{{ $point['served_count'] }}</td>
                    <td>{{ ($point['arrived_count'] - $point['served_count']) >= 0 ? ($point['arrived_count'] - $point['served_count']) : 0 }}</td>
                    <td>{{ number_format($point['percentage'], 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="chart">
        <img src="{{ $chart_url }}" alt="Chart" style="width: 100%; height: auto;">
    </div>
</body>
</html>
