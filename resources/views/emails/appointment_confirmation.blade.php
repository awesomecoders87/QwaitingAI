<!DOCTYPE html>
<html>
<head>
    <title>Appointment Confirmation</title>
</head>
<body>
    <p>Dear {{ $name }},</p>

    <p>Thank you for your appointment. Here are your details:</p>

    <ul>
        @php
        $timeInMinutes = (int) $servingTime;
        @endphp

        <li><strong>Serving Time:</strong>
            @if($timeInMinutes > 60)
                {{ floor($timeInMinutes / 60) }}h {{ $timeInMinutes % 60 }}m
            @else
                {{ $timeInMinutes }} minutes
            @endif
        </li>
        <li><strong>Note:</strong> {{ $note }}</li>
    </ul>

    <p>We look forward to seeing you!</p>

    <p>Best regards,<br>Your Team</p>
</body>
</html>
