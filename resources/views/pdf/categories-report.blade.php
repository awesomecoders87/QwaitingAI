<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header img {
            width: 120px;
        }

        .header h1 {
            font-size: 24px;
            color: #007BFF;
        }

        /* .header p {
            font-size: 14px;
            color: #555;
        } */

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
/* 
        th {
            background-color: #f8f9fa;
            color: #007BFF;
        } */

        /* tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #e9ecef;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #888;
        } */
        body { font-family: sans-serif; font-size: 12px; }
        .filters { margin-bottom: 15px; }
        .filters p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f0f0f0; }
        .logo { width: 100px; }
    </style>
    </style>
</head>
<body>

    <div class="container">
        <!-- Header with logo and title -->
      
        <table width="100%">
        <tr>
        <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
            <td style="text-align: right;"><h2>{{ __('report.Services Report') }}</h2></td>
            <td style="text-align: right;"><p>{{ date('F Y') }}</p></td>
        </tr>
    </table>

        <!-- Filters Section -->
        <div class="filters">
            <strong>{{ __('report.Filters Applied') }}:</strong>
            <ul>
                <li><strong>{{ __('report.Service Level 1') }}:</strong> {{ $selectedLevel1 ?? __('report.All') }}</li>
                <li><strong>{{ __('report.status') }}:</strong> {{ $status ?? __('report.All') }}</li>
                <li><strong>{{ __('report.Date Range') }}:</strong> {{ $startDate ?? __('report.All') }} to {{ $endDate ?? __('report.All') }}</li>
            </ul>
        </div>

        <!-- Categories Table -->
        <table>
            <thead>
                <tr>
                    <th>{{ __('report.Queue') }}</th>
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
                    <th>{{ __('report.Avg') }}</th>
                    <th>{{ __('report.Max') }}</th>
                    <th>{{ __('report.< SL') }}</th>
                    <th>{{ __('report.< SL %') }}</th>
                    <th>{{ __('report.> SL') }}</th>
                    <th>{{ __('report.> SL %') }}</th>
                    <th>{{ __('report.Avg Wait') }}</th>
                    <th>{{ __('report.Max Wait') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $row)
                    <tr>
                        <td>{{ $row['category']['name'] ?? '-' }}</td>
                        <td>{{ $row['total_calls'] }}</td>
                        <td>{{ $row['pending_calls'] }}</td>
                        <td>{{ round($row['pending_percentage'], 2) }}%</td>
                        <td>{{ $row['served_calls'] }}</td>
                        <td>{{ round($row['served_percentage'], 2) }}%</td>
                        <td>{{ $row['cancel_calls'] }}</td>
                        <td>{{ round($row['cancel_percentage'], 2) }}%</td>
                        <td>{{ $row['no_show'] }}</td>
                        <td>{{ $row['no_show_percentage'] }}%</td>
                        <td>{{ $row['total_served_time'] }}</td>
                        <td>{{ $row['average_served_time'] }}</td>
                        <td>{{ $row['max_served_time'] }}</td>
                        <td>{{ $row['total_waiting_less_15_min'] }}</td>
                        <td>{{ $row['waiting_less_15_percentage'] ?? '-' }}%</td>
                        <td>{{ $row['total_waiting_greater_15_min'] }}</td>
                        <td>{{ $row['waiting_greater_15_percentage'] ?? '-' }}%</td>
                        <td>{{ $row['average_wait_time'] }}</td>
                        <td>{{ $row['max_waiting_time'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} Your Company Name. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
