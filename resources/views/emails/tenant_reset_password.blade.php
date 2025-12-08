<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
</head>
<body>
    <p>Hello {{ $name }},</p>
    <p>You requested a password reset. Click the link below to reset your password:</p>
    <p><a href="{{ $resetLink }}">Reset Password</a></p>
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
