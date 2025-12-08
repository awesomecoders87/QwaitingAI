<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
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
    <div>
       
 
<table width="100%">
        <tr>
            <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo"></td>
            <td style="text-align: right;"><h2>Feedback Statistics Report</h2></td>
            <td style="text-align: right;"><p>From: {{ $from }} to {{ $to }}</p></td>
        </tr>
</table>
      
        <div class="text-center my-4">
            <img src="{{ $chart_url }}" alt="Chart" style="width: 100%; height: auto;">
        </div>
       
    </div>
</body>
</html>
