<?php
use Illuminate\Support\Facades\Session;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Payment Notification</title>
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

        h2 {
            color: #4a69bd;
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        p {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        table td:first-child {
            font-weight: bold;
            color: #555;
            width: 40%;
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
        $teamId = $data['team_id'] ?? null;
        $locationId = $data['locations_id'] ?? Session::get('selectedLocation');
        // Fetch logo if teamId is present, otherwise might need a fallback or system logo
        $logo = $teamId ? App\Models\SiteDetail::viewImage($headerPage, $teamId, $locationId) : asset('img/logo.png'); 
    @endphp 

    <div
        style="background:#e8e8e8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:13px;line-height:1.4;padding:2% 7%">

        @if($logo)
        <img id="Qwaiting" src="{{ url($logo) }}" alt="logo" class="CToWUd" style="vertical-align:middle;" width="100">
        @endif

        <div
            style="background:#fff;border-top-color:#6e8cce;border-top-style:solid;border-top-width:4px;margin:25px auto;
        border-radius: 8px;">
            <div style="border-color:#e5e5e5;border-style:none solid solid;border-width:2px;padding:7%">

                <h2>Prepaid Wallet Payment Successful</h2>
                <p>Hello Admin,</p>
                <p>A user has successfully completed a prepaid wallet payment.</p>

                <h3>User Details</h3>
                <table>
                    <tr>
                        <td>Name</td>
                        <td>{{ $data['user_name'] }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $data['user_email'] }}</td>
                    </tr>
                    <tr>
                        <td>User ID</td>
                        <td>{{ $data['user_id'] }}</td>
                    </tr>
                </table>

                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <td>Amount Paid</td>
                        <td>${{ $data['amount'] }}</td>
                    </tr>
                    <tr>
                        <td>Payment ID</td>
                        <td>{{ $data['payment_id'] }}</td>
                    </tr>
                    <tr>
                        <td>Payment Date</td>
                        <td>{{ $data['payment_date'] }}</td>
                    </tr>
                    <tr>
                        <td>Wallet Balance</td>
                        <td>${{ $data['wallet_balance'] }}</td>
                    </tr>
                </table>

                <p>Please log in to the <a href="{{ url('/superadmin/login') }}">admin panel</a> for more details.</p>

                <p class="footer">Regards,<br>{{ $data['app_name'] ?? 'System' }}</p>
            </div>
        </div>
        <div style="text-align:center" align="center">
            <p style="color:#999;font-size:11px;line-height:1.4;margin:5px 0">Copyright {{ date("Y") }} © Qwaiting Inc.
                All Rights Reserved.</p>
        </div>
    </div>
</body>

</html>
