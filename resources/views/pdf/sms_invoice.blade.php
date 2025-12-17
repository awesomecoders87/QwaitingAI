<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->inv_num }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }
        .header {
            margin-bottom: 40px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .company-details {
            text-align: right;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-details table {
            width: 100%;
        }
        .invoice-details td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            padding: 10px;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .total-section {
            text-align: right;
            margin-top: 30px;
        }
        .total-row {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            display: inline-block;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table style="width: 100%; margin-bottom: 40px;">
            <tr>
                <td valign="top">
                    <div class="header">
                        <h1>INVOICE</h1>
                        <p><strong>Invoice #:</strong> {{ $invoice->inv_num }}<br>
                           <strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</p>
                    </div>
                </td>
                <td valign="top" class="company-details">
                    <h3>{{ $company['name'] }}</h3>
                    <p>
                        {{ $company['address'] }}<br>
                        {{ $company['city'] }}<br>
                        {{ $company['phone'] }}
                    </p>
                </td>
            </tr>
        </table>

        <div class="invoice-details">
            <h3>Bill To:</h3>
            <p>
                <strong>{{ $user->name }}</strong><br>
                {{ $user->email }}<br>
                {{ $user->company_name ?? '' }}
            </p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Quantity</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ $invoice->package ? $invoice->package->name : 'SMS Credits Package' }}
                        <!-- <br>
                        <small style="color: #777;">{{ $invoice->subscription->quantity ?? 0 }} Credits</small> -->
                    </td>
                    <td style="text-align: right;">1</td>
                    <td style="text-align: right;">{{ $invoice->package->currency ?? 'USD' }} {{ number_format($invoice->price, 2) }}</td>
                    <td style="text-align: right;">{{ $invoice->package->currency ?? 'USD' }} {{ number_format($invoice->price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row">
                Total: {{ $invoice->package->currency ?? 'USD' }} {{ number_format($invoice->price, 2) }}
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>If you have any questions about this invoice, please contact support.</p>
        </div>
    </div>
</body>
</html>
