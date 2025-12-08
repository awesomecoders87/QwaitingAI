<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url({{ storage_path('fonts/DejaVuSans.ttf') }}) format('truetype');
        }
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path('fonts/DejaVuSans-Bold.ttf') }}) format('truetype');
        }
        body { 
            font-family: 'DejaVu Sans', sans-serif;
            direction: ltr;
        }
       .custom-bordered th,
        .custom-bordered td {
            border: 1px solid #dee2e6;
            text-align: center;
        }

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
            <td><img class="logo" src="{{ public_path($logo) }}" alt="Logo"></td>
            <td style="text-align: right;"><h2>{{  __('report.Staff Rating Reports') }}</h2></td>
            <td style="text-align: right;"><p>{{ __('report.From') }}: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}  {{ __('report.to') }} {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p></td>
            
        </tr>
</table>

    <table class="table custom-bordered text-center">
        <thead>
            <tr>
                <th class="col">Staff</th>
                <th class="col">Guest Served</th>
                <th class="col">Total Feedback</th>
                <th class="col">4 Stars</th>
                <th class="col">3 Stars</th>
                <th class="col">2 Stars</th>
                <th class="col">1 Star</th>
                <th class="col">Avg. Rating</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{!! html_entity_decode(htmlspecialchars($record->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}</td>
                    <td class="text-center">{{ $record->guest_served }}</td>
                    <td class="text-center">{{ $record->total_feedback }}</td>
                    <td class="text-center">{{ $record->star4 }}</td>
                    <td class="text-center">{{ $record->star3 }}</td>
                    <td class="text-center">{{ $record->star2 }}</td>
                    <td class="text-center">{{ $record->star1 }}</td>
                    <td class="text-center">{{ number_format($record->avg_rating, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No records found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>