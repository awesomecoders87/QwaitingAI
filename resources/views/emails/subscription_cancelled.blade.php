
<?php 
    use Illuminate\Support\Facades\Session;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Appointment Cancellation</title>
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

            $teamId = $data ['team_id'];
            $locationId=$data ['location_id'] ?? Session::get('selectedLocation');
            $logo =  App\Models\SiteDetail::viewImage($headerPage,$teamId,$locationId);
    @endphp 

    <div style="background:#e8e8e8;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:13px;line-height:1.4;padding:2% 7%">

           <img id="Qwaiting" src="{{url($logo)}}" alt="logo" class="CToWUd" style="vertical-align:middle;" width="100">
        
        <div style="background:#fff;border-top-color:#6e8cce;border-top-style:solid;border-top-width:4px;margin:25px auto;
        border-radius: 8px;">
            <div style="border-color:#e5e5e5;border-style:none solid solid;border-width:2px;padding:7%">
            <div>
        </div>
      <h2>Subscription Cancelled</h2>
<p>Dear Customer,</p>
<p>Your subscription to <strong>{{ $data['plan'] }}</strong> has been cancelled successfully.</p>
<p><strong>Cancelled At:</strong> {{ $data['cancelled_at'] }}</p>
<p>If this was a mistake or you wish to re-subscribe, feel free to contact support.</p>
<p>Thank you for using our service!</p>

        <p class="footer">Thank you,<br>Team</p>
    </div>
        </div>
        <div style="text-align:center" align="center">
            <p style="color:#999;font-size:11px;line-height:1.4;margin:5px 0">Copyright ' .date("Y"). ' © Qwaiting Inc. All Rights Reserved.</p>
        </div>
        </div>
</body>
