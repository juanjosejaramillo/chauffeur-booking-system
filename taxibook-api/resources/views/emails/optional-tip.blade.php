<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add a Tip for Your Trip</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
        }
        h1 {
            color: #1F2937;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .trip-details {
            background-color: #F9FAFB;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .trip-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .trip-details-label {
            color: #6B7280;
            font-size: 14px;
        }
        .trip-details-value {
            font-weight: 600;
            color: #1F2937;
        }
        .tip-suggestions {
            text-align: center;
            margin: 30px 0;
        }
        .tip-amounts {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        .tip-option {
            text-align: center;
            padding: 10px;
            background-color: #F3F4F6;
            border-radius: 6px;
            min-width: 80px;
        }
        .tip-percentage {
            font-size: 12px;
            color: #6B7280;
        }
        .tip-amount {
            font-size: 16px;
            font-weight: bold;
            color: #1F2937;
        }
        .cta-button {
            display: inline-block;
            background-color: #4F46E5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #4338CA;
        }
        .optional-note {
            background-color: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .optional-note-icon {
            font-size: 20px;
            margin-right: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            color: #6B7280;
            font-size: 14px;
        }
        .link-text {
            color: #6B7280;
            font-size: 12px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>

        <h1>Hi {{ $customerName }},</h1>
        
        <p>Thank you for choosing {{ config('app.name') }} for your recent trip!</p>
        
        <div class="trip-details">
            <div class="trip-details-row">
                <span class="trip-details-label">Booking Number:</span>
                <span class="trip-details-value">#{{ $bookingNumber }}</span>
            </div>
            <div class="trip-details-row">
                <span class="trip-details-label">Trip Date:</span>
                <span class="trip-details-value">{{ $tripDate }}</span>
            </div>
            <div class="trip-details-row">
                <span class="trip-details-label">Fare Paid:</span>
                <span class="trip-details-value">${{ number_format($fare, 2) }}</span>
            </div>
        </div>

        <div class="optional-note">
            <span class="optional-note-icon">💡</span>
            <strong>This is completely optional!</strong><br>
            If you'd like to show appreciation for your driver's service, you can add a tip using the link below.
        </div>

        <div class="tip-suggestions">
            <p><strong>Suggested gratuity amounts:</strong></p>
            <div class="tip-amounts">
                @foreach($suggestedTips as $tip)
                <div class="tip-option">
                    <div class="tip-percentage">{{ $tip['percentage'] }}%</div>
                    <div class="tip-amount">${{ number_format($tip['amount'], 2) }}</div>
                </div>
                @endforeach
            </div>
            
            <a href="{{ $tipUrl }}" class="cta-button">Add Tip (Optional)</a>
            
            <p class="link-text">
                Or copy this link:<br>
                {{ $tipUrl }}
            </p>
        </div>

        <p>If you're satisfied with the service as is, no action is needed. Your trip has been fully paid.</p>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>{{ config('app.name') }} Team</p>
            <p style="font-size: 12px; margin-top: 20px;">
                This email was sent to {{ $booking->customer_email }}<br>
                If you have any questions, please contact our support team.
            </p>
        </div>
    </div>
</body>
</html>