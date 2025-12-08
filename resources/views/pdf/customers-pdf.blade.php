<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border: 1px solid #ddd; 
            padding: 8px;
            font-size: 12px;
        }
        th {
            background-color: #f3f4f6; /* Light gray */
            text-align: left;
        }
        ul {
            padding-left: 20px;
            margin: 0;
        }
    </style>
</head>
<body>
     <table width="100%">
        <tr>
        <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
            <td style="text-align: right;"><h2>Customer List</h2></td>
            <td style="text-align: right;"><p><strong>Search:</strong> {{ $search }}</p></td>
            <td style="text-align: right;"><p><strong>From:</strong> {{ $fromDate }} | <strong>To:</strong> {{ $toDate }}</p></td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Other Details</th>
                <th>Queue Total</th>
                <th>Booking Total</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->phone }}</td>
                <td>
              
                     @php
                                $json = [];

                                if (!empty($customer?->json_data)) {
                                    $decoded = json_decode($customer->json_data, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $json = $decoded;
                                    }
                                }
                            @endphp

                            @if (!empty($json))
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($json as $key => $value)
                                        <li><span class="font-medium">{{ ucfirst($key) }}:</span> {{ $value }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-400 italic">No data</span>
                            @endif
                </td>
                <td>{{ $customer->queueCount }}</td>
                <td>{{ $customer->bookingCount }}</td>
                <td>{{ $customer->created_at->format($dateformat) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
