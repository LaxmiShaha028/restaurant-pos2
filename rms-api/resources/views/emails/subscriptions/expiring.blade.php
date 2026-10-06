<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expiry Reminder</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#243044;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f7fb;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,.08);">
                <tr>
                    <td style="padding:30px 32px 22px;background:#162033;color:#ffffff;">
                        <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#aebad0;margin-bottom:8px;">
                            {{ $mailData['app_name'] ?? 'RMS' }}
                        </div>
                        <div style="font-size:26px;line-height:1.25;font-weight:800;">
                            Subscription expires in {{ $mailData['reminder_hours'] }} hours
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px 32px;">
                        <p style="margin:0 0 18px;font-size:16px;line-height:1.7;">
                            Hello <strong>{{ $mailData['owner_name'] }}</strong>,
                        </p>

                        <p style="margin:0 0 22px;color:#5b6678;font-size:14px;line-height:1.75;">
                            Your <strong style="color:#243044;">{{ $mailData['restaurant_name'] }}</strong>
                            subscription is approaching its expiry time. Please renew or update the
                            subscription before expiry to avoid interruption to restaurant access.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e7ecf3;border-radius:12px;overflow:hidden;margin-bottom:22px;">
                            <tr>
                                <td style="padding:13px 15px;background:#f8fafc;color:#7b8798;font-size:12px;width:36%;">Restaurant</td>
                                <td style="padding:13px 15px;background:#f8fafc;font-size:13px;font-weight:700;">{{ $mailData['restaurant_name'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:13px 15px;border-top:1px solid #e7ecf3;color:#7b8798;font-size:12px;">Current Plan</td>
                                <td style="padding:13px 15px;border-top:1px solid #e7ecf3;font-size:13px;font-weight:700;">{{ $mailData['plan_name'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:13px 15px;border-top:1px solid #e7ecf3;color:#7b8798;font-size:12px;">Expiry</td>
                                <td style="padding:13px 15px;border-top:1px solid #e7ecf3;color:#c2410c;font-size:13px;font-weight:800;">{{ $mailData['expires_at'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:13px 15px;border-top:1px solid #e7ecf3;color:#7b8798;font-size:12px;">Time Remaining</td>
                                <td style="padding:13px 15px;border-top:1px solid #e7ecf3;font-size:13px;font-weight:800;">Approximately {{ $mailData['reminder_hours'] }} hours</td>
                            </tr>
                        </table>

                        @if ((int) ($mailData['reminder_hours'] ?? 0) === 24)
                            <div style="margin:0 0 22px;padding:14px 16px;border:1px solid #fed7aa;border-radius:10px;background:#fff7ed;color:#9a3412;font-size:13px;line-height:1.65;">
                                <strong>Important:</strong>
                                Restaurant access will become inactive after the subscription expires
                                until a valid plan is renewed or activated.
                            </div>
                        @endif

                        <p style="margin:0;color:#7b8798;font-size:12px;line-height:1.7;">
                            If your subscription has already been renewed or updated, please ignore this message.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 32px;border-top:1px solid #edf1f5;background:#fafbfc;color:#98a2b3;font-size:11px;line-height:1.6;">
                        This is an automated subscription reminder from {{ $mailData['app_name'] ?? 'RMS' }}.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
