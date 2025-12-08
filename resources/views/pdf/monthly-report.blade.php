<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
            <td style="text-align: right;"><h2>{{ __('report.Monthly Report') }}</h2></td>
        </tr>
    </table>

    <div class="filters">
        <p><strong>{{ __('report.created from') }}:</strong> {{ $created_from }}</p>
        <p><strong>{{ __('report.created until') }}:</strong> {{ $created_until }}</p>
        <p><strong>{{ __('report.staff') }}:</strong>
            @foreach ($closed_by as $id)
                {{ $users[$id] ?? '' }},
            @endforeach
        </p>
        <p><strong>{{ __('report.Counter') }}:</strong>
            @foreach ($counter_id as $id)
                {{ $counters[$id] ?? '' }},
            @endforeach
        </p>
        <p><strong>{{ __('report.status') }}:</strong> {{ implode(', ', $status ?? []) }}</p>
        <p><strong>{{ __('report.Walk-IN/Appt') }}:</strong> {{ implode(', ', $ticket_mode ?? []) }}</p>
    </div>

    <table>
        <thead>
            <tr>
               <th>{{ __('report.S.No') }}</th>
                <th>{{ __('report.Token') }}</th>
                <th>{{ __('report.created at') }}</th>
                <th>{{ $level1 }}</th>
                <th>{{ $level2 }}</th>
                <th>{{ $level3 }}</th>
                <th>{{ __('report.Counter') }}</th>
                <th>{{ __('report.Called') }}</th>

                  @if(!empty($formfields))
                    @foreach($formfields as $formField)
                        <th>{{ $formField->title }}</th>
                    @endforeach
                @endif
                <th>{{ __('report.Closed By') }}</th>
                <th>{{ __('report.Assign To') }}</th>
                <th>{{ __('report.Note') }}</th>
                 <th>{{ __('report.called at') }}</th>
                <th>{{ __('report.closed at') }}</th>
                <th>{{ __('report.Response Time') }}</th>
                <th>{{ __('report.Serving Time') }}</th>
                @if($enable_export_buttons)
                    <th>{{ $doc_file_label }}</th>
                @endif
                <th>{{ __('report.Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $serial = 1; @endphp
            @foreach($reports as $report)
                <tr>
                    <td>{{ $serial++ }}</td>
                    <td>{{ $report->start_acronym . $report->token }}</td>
                    <td>{{ \Carbon\Carbon::parse($report->datetime)->format($dateformat) }}</td>
                    <td>{{ $report->category->name ?? '' }}</td>
                    <td>{{ $report->subCategory->name ?? '' }}</td>
                    <td>{{ $report->childCategory->name ?? '' }}</td>
                    <td>{{ $report->Counter->name ?? '' }}</td>
                    <td>{{ $report->servedBy->name ?? '' }}</td>
                    <!-- <td>{{ $report->name ?? '' }}</td>
                    <td>{{ $report->phone ?? '' }}</td>
                    <td>
                        @php
                            $json = json_decode($report->json, true);
                            echo $json['Email'] ?? ($json['email'] ?? $json['email_address'] ?? '');
                        @endphp
                    </td> -->

                     @php
                            $json = json_decode($report->json, true);

                        @endphp
                    @if(!empty($formfields))
    @foreach($formfields as $formfield)
        @php
            $value = $json[\Illuminate\Support\Str::lower($formfield->title)] ?? '';
        @endphp

        <td class="px-5 py-4 sm:px-6">
            @if(is_array($value))
                {{ implode(', ', $value) }} {{-- Join array values --}}
            @else
                {{ $value }}
            @endif
        </td>
    @endforeach
@endif
                    <td>{{ $report->closedBy->name ?? '' }}</td>
                    <td>{{ $report->assignStaff->name ?? '' }}</td>
                    <td>{{ $report->esitmate_note ?? '' }}</td>
                     <td>{{ !empty($report->called_datetime) ? \Carbon\Carbon::parse($report->called_datetime)->format($dateformat) : ''}}</td>
                         <td>{{ !empty($report->closed_datetime) ? \Carbon\Carbon::parse($report->closed_datetime)->format($dateformat) : ''}}</td>
                       
                    <td>
                        @php
                            $responseTime = '';
                            if ($report->called_datetime && $report->arrives_time) {
                                $responseTime = $report->called_datetime->diff($report->arrives_time)->format('%H:%I:%S');
                            }
                            echo $responseTime;
                        @endphp
                    </td>
                    <td>
                        @php
                            $servedTime = '';
                            if ($report->closed_datetime && $report->start_datetime) {
                                $servedTime = $report->closed_datetime->diff($report->start_datetime)->format('%H:%I:%S');
                            }
                            echo $servedTime;
                        @endphp
                    </td>
                    @if($enable_export_buttons)
                        <td>
                            @if(!empty($report->doc_file))
                                <a href="{{ asset('storage/' . $report->doc_file) }}" target="_blank" download>{{ __('report.View File') }}</a>
                            @else
                                -
                            @endif
                        </td>
                    @endif
                    <td>{{ $report->status ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
