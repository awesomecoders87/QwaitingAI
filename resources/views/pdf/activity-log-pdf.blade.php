<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Activity Log Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
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
            <td style="text-align: right;"><h2>Activity Log Report</h2></td>
            <td style="text-align: right;"><p><strong>Location:</strong> {{ $locationName }}</p></td>
            <td style="text-align: right;"><p><strong>From:</strong> {{ $fromDate }} | <strong>To:</strong> {{ $toDate }}</p></td>
        </tr>
    </table>


    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Event</th>
                <th>Description</th>
                <th>IP Address</th>
                <th>Username</th>
            </tr>
        </thead>
        <tbody>
        @foreach($records as $record)
            <tr>
                <td>{{ \Carbon\Carbon::parse($record->created_at)->format($datetimeFormat) }}</td>
                <td>{{ $record->type }}</td>
                <td>{{ $record->text }}</td>
                <td>{{ $record->ip_address }}</td>
                <td>
  
                        {{ $record->creator->name ?? 'N/A' }}
                  
                </td>
              
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
