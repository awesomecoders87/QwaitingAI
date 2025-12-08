<!DOCTYPE html>
<html>
<head>
    <title>Bookings List PDF</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>

        body {
        margin: 0;
        padding: 0;
        /* font-family: sans-serif; */
        font-family: DejaVu Sans,
        font-size: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
        word-wrap: break-word;
        word-break: break-word;
        white-space: normal;
        font-size: 8px;
    }

    th {
        background-color: #f1f1f1;
    }
        /* table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #cccccc;
            padding: 5px;
            text-align: left;
        } */
        .logo { width: 100px; }
    </style>
</head>
<body>
  <table width="100%">
        <tr>
        <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
            <td style="text-align: right;"><h2>Booking List</h2></td>
            <td style="text-align: right;"><p><strong>Location:</strong> {{ $locationName }}</p></td>
            <td style="text-align: right;"><p><strong>From:</strong> {{ $fromDate }} | <strong>To:</strong> {{ $toDate }}</p></td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>{{ $level1 }}</th>
                <th>{{ $level2 }}</th>
                <th>{{ $level3 }}</th>
                <th>Ref ID</th>
                <th>Status</th>
                <th>Datetime</th>
                <th>Email</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Booking Date</th>
                <th>Booking Time</th>
                <th>Booking Status</th>
                <th>Booking Type</th>
                <th>Booked By</th>
                <th>Cancel Reason</th>
                <th>Cancel Remark</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->categories->name ?? '' }}</td>
                <td>{{ $booking->sub_category->name ?? '' }}</td>
                <td>{{ $booking->child_category->name ?? '' }}</td>
                <td>{{ $booking->refID }}</td>
                <td>{{ $booking->is_convert }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->convert_datetime)->format('Y-m-d H:i') ?? '' }}</td>
                <td>{{ $booking->email }}</td>
                <td>{{ $booking->name }}</td>
                <td>{{ $booking->phone }}</td>
                <td>{{ $booking->booking_date }}</td>
                <td>{{ $booking->booking_time }}</td>
                <td>{{ $booking->status }}</td>
                <td>{{ $booking->booking_type }}</td>
                <td>{{ $booking->createdBy->name ?? '' }}</td>
                <td>{{ $booking->cancel_reason }}</td>
                <td>{{ $booking->cancel_remark }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
