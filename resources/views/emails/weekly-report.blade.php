<?php
use Illuminate\Support\Facades\Session;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Your One-Time Password (OTP)</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }

        h1 {
            color: #d9534f;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            margin-bottom: 15px;
        }

        blockquote {
            background-color: #f2f2f2;
            border-left: 5px solid #d9534f;
            margin: 15px 0;
            padding: 10px 20px;
            border-radius: 8px;
            color: #555;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        ul li {
            background-color: #f9f9f9;
            margin-bottom: 8px;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #777;
            font-size: 14px;
        }
    </style>
</head>

<body>
    @php
        $url = request()->url();
        $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

        $teamId = $team_id;

        $logo = App\Models\SiteDetail::viewImage($headerPage, $teamId);
    @endphp

    <div
        style="background:#e8e8e8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:13px;line-height:1.4;padding:2% 7%">
 <img id="Qwaiting" src="{{url($logo)}}" alt="logo" class="CToWUd" style="vertical-align:middle;" width="100">

        <div
            style="background:#fff;border-top-color:#6e8cce;border-top-style:solid;border-top-width:4px;margin:25px auto;
        border-radius: 8px;">
            <div style="border-color:#e5e5e5;border-style:none solid solid;border-width:2px;padding:7%">



<div style="font-family: Arial, sans-serif; color: #333; padding: 20px;">
    <h2 style="color: #6b21a8;">Weekly Report for {{ $locationName }}</h2>
    <p>Here's a summary of what happened between {{ \Carbon\Carbon::parse($start)->format('M jS') }} and {{ \Carbon\Carbon::parse($end)->format('M jS') }}.</p>

    <hr style="margin: 20px 0;">

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong style="color: #6b21a8;">Visitor:</strong> {{ $waitlisted }}</td>
            <td><strong>Bookings:</strong> {{ $bookings }}</td>
            <td><strong>Served:</strong> {{ $served }}</td>
        </tr>
        <tr>
            <td><strong style="color: #6b21a8;">No shows:</strong> {{ $noShows }}</td>
            <td><strong>Cancellations:</strong> {{ $cancellations }}</td>
            <td><strong>Waiting:</strong> {{ $waiting }}</td>
        </tr>
        <tr>
            <td><strong>New Customers:</strong> {{ $newCustomerCount }}</td>
            <td><strong>Serve Rate:</strong> {{ $serveRate }}%</td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Avg Served Time:</strong> {{ formatDuration($avgServedTime) }}</td>
            <td><strong>Max Served Time:</strong> {{ formatDuration($maxServedTime) }}</td>
            <td><strong>Avg Waiting Time:</strong> {{ formatDuration($avgWaitingTime) }}</td>
        </tr>
        <tr>
            <td><strong>Max Waiting Time:</strong> {{ formatDuration($maxWaitingTime) }}</td>
            <td><strong>Min Waiting Time:</strong> {{ formatDuration($minWaitingTime) }}</td>
            <td></td>
        </tr>
    </table>

    <hr style="margin: 20px 0;">

    {{--  top service table according to category --}}
    <h3 style="color: #6b21a8;">Top Services</h3>
<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr style="background-color: #f3e8ff; color: #6b21a8;">
            <th style="padding: 8px; text-align: left;">Service</th>
            <th style="padding: 8px;">Arrived</th>
            <th style="padding: 8px;">Served</th>
            <th style="padding: 8px;">No-shows</th>
            <th style="padding: 8px;">Avg wait time</th>
            <th style="padding: 8px;">Avg serve time</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($categories as $cat)
            <tr>
                <td style="padding: 8px;">{{ $cat->category->name ?? 'N/A' }}</td>
                <td style="padding: 8px; text-align: center;">{{ $cat->total_calls ?? 0 }}</td>
                <td style="padding: 8px; text-align: center;">{{ $cat->served_calls ?? 0 }}</td>
                <td style="padding: 8px; text-align: center;">{{ $cat->no_show ?? 0 }}</td>
                <td style="padding: 8px; text-align: center;">{{ $cat->average_wait_time ?? 'n/a' }}</td>
                <td style="padding: 8px; text-align: center;">{{ $cat->average_served_time ?? 'n/a' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@php
    $url = "https://{$domain['domain']}/analytics";
@endphp
<p style="font-size: 14px; color: #6b7280;">SLooking for more stats? Check out your. <a href={{$url}} target="_blank"> Analytics page.</a></p>
   <hr style="margin: 20px 0;">
    <p style="font-size: 14px; color: #6b7280;">Sent to admin: <strong>{{ $adminName }}</strong></p>
</div>

                <p class="footer">Thank you,<br>Team</p>
            </div>
        </div>
        <div style="text-align:center" align="center">
            <p style="color:#999;font-size:11px;line-height:1.4;margin:5px 0">Copyright ' .date("Y"). ' © Qwaiting Inc.
                All Rights Reserved.</p>
        </div>
    </div>
</body>

</html>
