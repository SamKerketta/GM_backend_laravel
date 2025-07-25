<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Payment Confirmation</title>
</head>

<body style="margin:0; padding:0; font-family: 'Segoe UI', sans-serif; background-color: #f4f4f4;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 10px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="padding: 30px; text-align: center;">
                            <h2 style="color: #222;">{{ config('constants.GYM_NAME') }}</h2>
                            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                            <h3 style="color: #333;">Hey {{ $name }},</h3>
                            <p style="font-size: 16px; color: #555;">
                                Thanks for your payment of <strong style="color: #e67e22;">₹ {{ $amountPaid }} </strong> <strong> for {{ $month }} on {{ $paymentDate }} 🎉</strong>.
                                <br><br>
                                We’re glad to have you as part of the<strong> Gravity Fitness family.</strong>.
                                <br><br>
                                Stay strong and keep lifting! 💪
                                <br>
                                Thanking You !!
                            </p>
                            <br><br>
                            <p style="font-size: 14px; color: #999;">Best Regards,<br><strong>{{ config('constants.GYM_NAME') }} Team</strong></p>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 12px; color: #aaa; padding-top: 20px;">
                    © {{ date('Y') }} {{ config('constants.GYM_NAME') }}. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>

</html>