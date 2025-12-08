<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub Services Report</title>
    <style>
       body { font-family: sans-serif; font-size: 12px; }
        .filters {
            margin: 20px 0;
        }

        .filters ul {
            list-style: none;
            padding: 0;
        }

        .filters li {
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

       
        .filters { margin-bottom: 15px; }
        .filters p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f0f0f0; }
        .logo { width: 100px; }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header with logo and title -->
        <table width="100%">
            <tr>
            <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
                <td style="text-align: right;"><h2>{{ __('report.Sub Services Report') }}</h2></td>
                <td style="text-align: right;">
                    <p>{{ __('report.Report Period') }}: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} {{ __('report.to') }} {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
                </td>
            </tr>
        </table>

        <!-- Filters Section -->
        <div class="filters">
            <strong>{{ __('report.Filters Applied') }}:</strong>
            <ul>
                <li><strong>{{ $level1 }}:</strong> {{ $selectedLevel1 ?? __('text.All') }}</li>
                <li><strong>{{ $level2 }}:</strong> {{ $selectedLevel2 ?? __('text.All') }}</li>
                <li><strong>{{ $level3 }}:</strong> {{ $selectedLevel3 ?? __('text.All') }}</li>
                <li><strong>{{ __('report.status') }}:</strong> {{ $status ?? __('text.All') }}</li>
                <li><strong>{{ __('report.Date Range') }}:</strong>  {{ $startDate ?? '—' }} {{ __('report.to') }} {{ $endDate ?? '-' }}</li>
                <li><strong>{{ __('report.Generated On') }}:</strong> {{ now()->format('d M Y, h:i A') }}</li>
            </ul>
        </div>

        <!-- Categories Table -->
        <table>
            <thead>
                <tr>
                    <th>{{ $level1 }}</th>
                    <th>{{ $level2 }}</th>
                    <th>{{ $level3 }}</th>
                    <th>{{ __('report.Arrived') }}</th>
                    <th>{{ __('report.Pending') }}</th>
                    <th>{{ __('report.%') }}</th>
                    <th>{{ __('report.Served') }}</th>
                    <th>{{ __('report.%') }}</th>
                    <th>{{ __('report.Cancelled') }}</th>
                    <th>{{ __('report.%') }}</th>
                    <th>{{ __('report.No Show') }}</th>
                    <th>{{ __('report.%') }}</th>
                    <th>{{ __('report.Workload') }}</th>
                    <th>{{ __('report.Average') }}</th>
                    <th>{{ __('report.Max') }}</th>
                    <th>{{ __('report.< SL') }}</th>
                    <th>{{ __('report.< SL %') }}</th>
                    <th>{{ __('report.> SL') }}</th>
                    <th>{{ __('report.> SL %') }}</th>
                    <th>{{ __('report.Average') }}</th>
                    <th>{{ __('report.Max') }}</th>
                </tr>
            </thead>
            <tbody>
    
                <?php
                    function timeToSecondsnew($time) {
                        $timeParts = explode(':', $time);
                        if (count($timeParts) === 3) {
                            list($hours, $minutes, $seconds) = $timeParts;
                            return ($hours * 3600) + ($minutes * 60) + $seconds;
                        }
                        return 0;
                    }

                    function secondsToTimenew($seconds) {
                        $hours = floor((float)$seconds / 3600);
                        $minutes = floor(((float)$seconds % 3600) / 60);
                        $seconds = (float)$seconds % 60;
                        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                    }

                    $totals = [
                        'total_calls' => 0,
                        'pending_calls' => 0,
                        'cancel_calls' => 0,
                        'served_calls' => 0,
                        'no_show' => 0,
                        'total_served_time' => 0,
                        'average_served_time' => 0,
                        'max_served_time' => 0,
                        'total_waiting_less_15_min' => 0,
                        'total_waiting_greater_15_min' => 0,
                        'average_wait_time' => 0,
                        'max_waiting_time' => 0,
                        'served_percentage_total' => 0,  // Initialize served_percentage_total
                        'no_show_percentage_total' => 0  
                    ];

                    foreach ($categories as $row) {
                        $total_calls = $row['total_calls'];
                        $served_calls = $row['served_calls'];
                        $cancel_calls = $row['cancel_calls'];
                        $pending_calls = $row['pending_calls'];
                        $served_percentage = ($total_calls > 0) ? ($served_calls / $total_calls) * 100 : 0;
                        $no_show_percentage = rtrim($row['no_show_percentage'], '%');

                        $totals['total_calls'] += $total_calls;
                        $totals['served_calls'] += $served_calls;
                        $totals['cancel_calls'] += $cancel_calls;
                        $totals['pending_calls'] += $pending_calls;
                        $totals['served_percentage_total'] += $served_percentage;
                        $totals['no_show'] += $row['no_show'];
                        $totals['no_show_percentage_total'] += (float)$no_show_percentage;
                        $totals['total_waiting_less_15_min'] += $row['total_waiting_less_15_min'];
                        $totals['total_waiting_greater_15_min'] += $row['total_waiting_greater_15_min'];

                        $served_time_seconds = !empty($row['total_served_time']) ? timeToSecondsnew($row['total_served_time']) : 0;
                        $totals['total_served_time'] += $served_time_seconds;

                        if ($row['average_served_time']) {
                            $parts1 = explode(':', $row['average_served_time']);
                            $current_served_time_seconds = ($parts1) ? ($parts1[0] * 3600) + ($parts1[1] * 60) + $parts1[2] : 0;
                            $totals['average_served_time'] += $current_served_time_seconds;
                        }

                        $current_max_served_time = strtotime($row['max_served_time']) - strtotime('TODAY');
                        if ($current_max_served_time > $totals['max_served_time']) {
                            $totals['max_served_time'] = $current_max_served_time;
                        }

                        if ($row['average_wait_time']) {
                            $parts = explode(':', $row['average_wait_time']);
                            $current_wait_time_seconds = ($parts) ? ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2] : 0;
                            $totals['average_wait_time'] += $current_wait_time_seconds;
                        }

                        $current_max_waiting_time = strtotime($row['max_waiting_time']) - strtotime('TODAY');
                        if ($current_max_waiting_time > $totals['max_waiting_time']) {
                            $totals['max_waiting_time'] = $current_max_waiting_time;
                        }
                    }
                ?>

                @foreach($categories as $key => $row)
                    <tr>
                        <td>{{ $row->category->name ?? 'N/A' }}</td>
                        <td>{{ $row->subCategory->name ?? 'N/A' }}</td>
                        <td>{{ $row->childCategory->name ?? 'N/A' }}</td>
                        <td>{{ $row['total_calls'] }}</td>
                        <td>{{ $row['pending_calls'] }}</td>
                        <td>{{ number_format($row['pending_percentage'], 2) }}</td>
                        <td>{{ $row['served_calls'] }}</td>
                        <td>{{ number_format($row['served_percentage'], 2) }}</td>
                        <td>{{ $row['cancel_calls'] }}</td>
                        <td>{{ number_format($row['cancel_percentage'], 2) }}</td>
                        <td>{{ $row['no_show'] }}</td>
                        <td>{{ number_format($row['no_show_percentage'], 2) }}</td>
                        <td>{{ $row['total_served_time'] }}</td>
                        <td>{{ $row['average_served_time']}}</td>
                        <td>{{ $row['max_served_time'] }}</td>
                        <td>{{ $row['total_waiting_less_15_min'] }}</td>
                        <td>{{ number_format($row['waiting_less_15_min_percentage'], 2) }}</td>
                        <td>{{ $row['total_waiting_greater_15_min'] }}</td>
                        <td>{{ number_format($row['waiting_greater_15_min_percentage'], 2) }}</td>
                        <td>{{ $row['average_wait_time'] }}</td>
                        <td>{{ $row['max_waiting_time'] }}</td>
                    </tr>
                @endforeach
     

            <!-- Total Row -->
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">Total</td>
                <td>{{ $totals['total_calls'] }}</td>
                <td>{{ $totals['pending_calls'] }}</td>
                <td>{{ number_format(($totals['total_calls'] > 0) ? ($totals['pending_calls'] / $totals['total_calls']) * 100 : 0, 2) }}</td>
                <td>{{ $totals['served_calls'] }}</td>
                <td>{{ number_format(($totals['total_calls'] > 0) ? ($totals['served_calls'] / $totals['total_calls']) * 100 : 0, 2) }}</td>
                <td>{{ $totals['cancel_calls'] }}</td>
                <td>{{ number_format(($totals['total_calls'] > 0) ? ($totals['cancel_calls'] / $totals['total_calls']) * 100 : 0, 2) }}</td>
                <td>{{ $totals['no_show'] }}</td>
                <td>{{ number_format(($totals['total_calls'] > 0) ? ($totals['no_show_percentage_total'] / $totals['total_calls']) * 100 : 0, 2) }}</td>
                <td>{{ secondsToTimenew($totals['total_served_time']) }}</td>
                <td>{{ secondsToTimenew($totals['average_served_time']/count($categories)) }} </td>
                <td>{{ secondsToTimenew($totals['max_served_time']) }}</td>
                <td>{{ $totals['total_waiting_less_15_min'] }}</td>
                <td>{{ number_format(($totals['total_calls'] > 0) ? ($totals['total_waiting_less_15_min'] / $totals['total_calls']) * 100 : 0, 2) }}</td>
                <td>{{ $totals['total_waiting_greater_15_min'] }}</td>
                <td>{{ number_format(($totals['total_calls'] > 0) ? ($totals['total_waiting_greater_15_min'] / $totals['total_calls']) * 100 : 0, 2) }}</td>
                <td>{{ secondsToTimenew($totals['average_wait_time']/count($categories)) }}</td>
                <td>{{ secondsToTimenew($totals['max_waiting_time']) }}</td>
            </tr>

            </tbody>
        </table>
    </div>

</body>
</html>
