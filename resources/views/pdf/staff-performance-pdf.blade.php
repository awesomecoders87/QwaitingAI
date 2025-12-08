<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Performance Report</title>
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
            <td style="text-align: right;"><h2>{{ __('report.Staff Performance Report') }}</h2></td>
            <td style="text-align: right;"><p>{{ __('report.Date Range') }}: {{ $from }} {{ __('report.to') }} {{ $to }}</p></td>
        </tr>
</table>
    <table>
        <thead>
            <tr>
                <th>{{ __('report.Staff') }}</th>
                <th>{{ __('report.Visitors Served') }}</th>
                @foreach ($categories as $category)
                    <th>{{ $category->name }}</th>
                @endforeach
                <th>{{ __('report.Total Served Time') }}</th>
                <th>{{ __('report.Average Served Time') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                @php
                   $queuesQuery = $user->queues()
                    ->where('status', '!=', 'Cancelled')
                    ->whereDate('arrives_time', '>=', $from)
                    ->whereDate('arrives_time', '<=', $to);

    if (!empty($selectedLocation)) {
        if (is_array($selectedLocation)) {
            $queuesQuery->whereIn('locations_id', $selectedLocation);
        } else {
            $queuesQuery->where('locations_id', $selectedLocation);
        }
    }

    $queues = $queuesQuery->get();

    $totalServedTime = $queues->sum(function ($q) {
        return ($q->start_datetime && $q->closed_datetime)
            ? $q->closed_datetime->diffInSeconds($q->start_datetime)
            : 0;
    });

    $queueCount = $queues->count();
                    $avgTime = $queueCount ? ($totalServedTime / $queueCount) : 0;
                @endphp
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $queueCount }}</td>
                    @foreach ($categories as $category)
                        <td>{{ $queues->where('category_id', $category->id)->count() }}</td>
                    @endforeach
                    <td>{{ \Carbon\CarbonInterval::seconds($totalServedTime)->cascade()->format('%H:%I:%S') }}</td>
                    <td>{{ \Carbon\CarbonInterval::seconds($avgTime)->cascade()->format('%H:%I:%S') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
