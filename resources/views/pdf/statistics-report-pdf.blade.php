<!DOCTYPE html>
<html>

<head>
    <title>Statistics Report</title>
    <style>
    body {
        font-family: sans-serif;
    }

    h2 {
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    img {
        max-width: 100%;
        height: auto;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>

    <table width="100%">
        <tr>
            <td><img class="logo" src="{{ public_path($logo_src) }}" alt="Logo" width="200" height="200"></td>
            <td style="text-align: right;">
                <h2>Statistics Report</h2>
            </td>
            <td style="text-align: right;">
                <p>From: {{ $start_date }} to {{ $end_date }}</p>
            </td>
        </tr>
    </table>

    <div class="text-center my-4">
        <h3>Summary Chart</h3>
        <img src="{{ public_path($summaryChart) }}" alt="Summary Chart">
    </div>
    <div class="text-center my-4">
        <h3>Call History Chart</h3>
        <img src="{{ public_path($callHistoryChart) }}" alt="Call History Chart">
    </div>
    <div class="text-center my-4">
        <h3>Counter History Chart</h3>
        <img src="{{ public_path($counterHistoryChart) }}" alt="Counter History Chart">

    </div>
</body>

</html>