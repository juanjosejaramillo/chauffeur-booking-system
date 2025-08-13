<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
        }
        .code-box {
            background-color: #F3F4F6;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #1F2937;
        }
        .booking-details {
            background-color: #F9FAFB;
            border-left: 4px solid #4F46E5;
            padding: 15px;
            margin: 20px 0;
        }
        .detail-row {
            margin: 10px 0;
        }
        .label {
            font-weight: 600;
            color: #6B7280;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            color: #6B7280;
            font-size: 14px;
        }
        .warning {
            background-color: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 6px;
            padding: 12px;
            margin: 20px 0;
            color: #92400E;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TaxiBook</div>
            <p style="color: #6B7280; margin-top: 10px;">Premium Chauffeur Service</p>
        </div>

        <h2 style="color: #1F2937;">Hello {{ $customerName }},</h2>
        
        <p>Thank you for choosing TaxiBook. To complete your booking, please enter the verification code below:</p>

        <div class="code-box">
            <div style="color: #6B7280; font-size: 14px; margin-bottom: 10px;">Your verification code is:</div>
            <div class="code">{{ $code }}</div>
            <div style="color: #6B7280; font-size: 14px; margin-top: 10px;">This code expires in 10 minutes</div>
        </div>

        <div class="booking-details">
            <h3 style="margin-top: 0; color: #1F2937;">Booking Preview</h3>
            <div class="detail-row">
                <span class="label">Pickup:</span><br>
                {{ $pickupAddress }}
            </div>
            <div class="detail-row">
                <span class="label">Dropoff:</span><br>
                {{ $dropoffAddress }}
            </div>
            <div class="detail-row">
                <span class="label">Date & Time:</span><br>
                {{ $pickupDate }}
            </div>
        </div>

        <div class="warning">
            <strong>Didn't receive the code?</strong><br>
            Please check your spam folder. If you still can't find it, you can request a new code.
        </div>

        <p style="color: #6B7280;">If you didn't request this booking, please ignore this email.</p>

        <div class="footer">
            <p>© {{ date('Y') }} TaxiBook. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>