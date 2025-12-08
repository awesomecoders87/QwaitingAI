<!DOCTYPE html>
<html>
<head>
    <title>Feedback Report PDF</title>

    <style>
        @font-face {
            font-family: 'NotoSansEmoji';
            src: url('{{ public_path("fonts/NotoSans-Regular.ttf") }}') format("truetype");
        }

        body { 
            font-family: 'NotoSansEmoji', sans-serif; 
            font-size: 11px;
        }

        table {
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .logo {
            width: 100px;
        }

        .header-table td {
            border: none;
        }
    </style>
</head>

<body>

<table class="header-table" width="100%">
    <tr>
        <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
        <td style="text-align: right;">
            <h2>Feedback Report</h2>
            <p><strong>Branch:</strong> {{ $filters['Branch Name'] }}</p>
            <p><strong>From:</strong> {{ $filters['Created From'] }} 
               <strong>To:</strong> {{ $filters['Created Until'] }}</p>
               <p><strong>Staff:</strong> {{ $filters['Staff'] }}</p>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>{{ __('report.name') }}</th>
            <th>{{ __('report.token') }}</th>
            <th>{{ __('report.contact') }}</th>
            <th>{{ __('report.comment') }}</th>
            <th>{{ __('report.average rating') }}</th>
         
            <th>{{ __('report.datetime') }}</th>
            <th>{{ __('report.staff') }}</th>

            {{-- Dynamic question headers --}}
            @foreach($questions as $question)
                <th>{{ $question['question'] }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
    @foreach($records as $report)
        @php
            // Determine emoji based on rating range
            $emojiData = collect(\App\Models\Queue::getEmojiText())
                ->first(function ($item) use ($report) {
                    return $report->average_rating >= $item['range'][0]
                        && $report->average_rating <= $item['range'][1];
                });
        @endphp

        <tr>
            <td>{{ $report->name ?? 'N/A' }}</td>
            <td>{{ $report->token ?? 'N/A' }}</td>
            <td>{{ $report->contact ?? 'N/A' }}</td>
            <td>{{ $report->comment ?? 'N/A' }}</td>

            <td>{{ number_format($report->average_rating, 2) ?? 'N/A' }}</td>


            <td>{{ $report->datetime ?? 'N/A' }}</td>
            <td>{{ $report->staff ?? 'N/A' }}</td>

            {{-- Dynamic question values --}}
            @foreach($questions as $question)
                <td>{{ $report->{$question['question']} ?? 'N/A' }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
