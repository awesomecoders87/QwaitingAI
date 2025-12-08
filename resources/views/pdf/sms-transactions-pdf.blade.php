<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SMS Transactions</title>
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
            <td style="text-align: right;"><h2>{{ __('report.SMS Transactions Report') }}</h2></td>
            <td style="text-align: right;">{{ __('report.From') }}:  {{ $from }} {{ __('report.to') }} {{ $to }}</td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
               <th>{{ __('report.Date') }}</th>
                <th>{{ __('report.contact') }}</th>
                <th>{{ __('report.Channel') }}</th>
                <th>{{ __('report.Event Name') }}</th>
                <th>{{ __('report.Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($smsdetails as $log)
                <tr>
                    <td>{{ $log->created_at->format($datetimeFormat) }}</td>
                    <td>{{ $log->contact }}</td>
                    <td>{{ $log->channel }}</td>
                    <td>{{ $log->event_name }}</td>
                    <td>{{ $log->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
