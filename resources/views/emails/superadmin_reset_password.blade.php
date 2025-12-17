<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your SuperAdmin Password</title>
    <!--[if mso]>
    <style type="text/css">
        table {border-collapse: collapse; border-spacing: 0; margin: 0;}
        div, td {padding: 0;}
        div {margin: 0 !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f3f4f6;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <!-- Logo Section -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 30px 40px;">
                            <img src="{{ url('images/logo/superadmin/qwaiting-logo.svg') }}" alt="Qwaiting Logo" style="max-width: 180px; height: auto; display: block;">
                        </td>
                    </tr>
                    
                    <!-- Content Section -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <h1 style="margin: 0 0 20px 0; font-size: 24px; font-weight: 600; color: #111827; line-height: 1.3;">
                                Reset Your Password
                            </h1>
                            <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6; color: #374151;">
                                Hello {{ $name }},
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6; color: #374151;">
                                You requested a password reset for your SuperAdmin account. Click the button below to reset your password:
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Button Section -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="background-color: #4f46e5; border-radius: 6px;">
                                        <a href="{{ $resetLink }}" style="display: inline-block; padding: 14px 32px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 6px; background-color: #4f46e5;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Alternative Link Section -->
                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.5; color: #6b7280;">
                                If the button doesn't work, copy and paste this link into your browser:
                            </p>
                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #9ca3af; word-break: break-all;">
                                {{ $resetLink }}
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Warning Section -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 4px;">
                            <p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.5; color: #92400e; font-weight: 500;">
                                ⚠️ Important:
                            </p>
                            <p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.5; color: #78350f;">
                                • This link will expire in 60 minutes
                            </p>
                            <p style="margin: 0; font-size: 14px; line-height: 1.5; color: #78350f;">
                                • If you did not request this password reset, please ignore this email. Your account remains secure.
                            </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer Section -->
                    <tr>
                        <td style="padding: 30px 40px 40px 40px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.5; color: #6b7280; text-align: center;">
                                Need help? Contact our support team.
                            </p>
                            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #9ca3af; text-align: center;">
                                © {{ date('Y') }} Qwaiting. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
