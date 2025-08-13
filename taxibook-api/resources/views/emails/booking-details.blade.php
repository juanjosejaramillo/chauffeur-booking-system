<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
        }
        .document-title {
            font-size: 20px;
            margin-top: 10px;
            color: #666;
        }
        .booking-number-large {
            background-color: #f0f0ff;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            letter-spacing: 2px;
        }
        .section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4F46E5;
        }
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px dotted #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #666;
            width: 150px;
            flex-shrink: 0;
        }
        .info-value {
            font-weight: 500;
            color: #333;
            flex: 1;
        }
        .map-placeholder {
            background-color: #f8f9fa;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
            color: #999;
            margin: 20px 0;
        }
        .important-note {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .important-note-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }
        .qr-code {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .fare-breakdown {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .fare-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .fare-total {
            border-top: 2px solid #4F46E5;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TaxiBook</div>
            <div class="document-title">Booking Confirmation</div>
        </div>

        <div class="booking-number-large">
            {{ $booking->booking_number }}
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <span class="status-badge status-{{ $booking->status }}">
                {{ ucfirst($booking->status) }}
            </span>
        </div>

        <div class="section">
            <div class="section-title">Trip Details</div>
            <div class="info-row">
                <span class="info-label">Pickup Date:</span>
                <span class="info-value">{{ $booking->pickup_date->format('l, F j, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pickup Time:</span>
                <span class="info-value">{{ $booking->pickup_date->format('g:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vehicle Type:</span>
                <span class="info-value">{{ $booking->vehicleType->display_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estimated Duration:</span>
                <span class="info-value">{{ round($booking->estimated_duration / 60) }} minutes</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estimated Distance:</span>
                <span class="info-value">{{ number_format($booking->estimated_distance, 1) }} miles</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Pickup Location</div>
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value">{{ $booking->pickup_address }}</span>
            </div>
            @if($booking->special_instructions)
            <div class="info-row">
                <span class="info-label">Instructions:</span>
                <span class="info-value">{{ $booking->special_instructions }}</span>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Dropoff Location</div>
            <div class="info-row">
                <span class="info-label">Address:</span>
                <span class="info-value">{{ $booking->dropoff_address }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Customer Information</div>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $booking->customer_full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $booking->customer_email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $booking->customer_phone }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Fare Information</div>
            <div class="fare-breakdown">
                <div class="fare-row">
                    <span>Base Fare:</span>
                    <span>${{ number_format($booking->estimated_fare * 0.8, 2) }}</span>
                </div>
                <div class="fare-row">
                    <span>Service Fee:</span>
                    <span>${{ number_format($booking->estimated_fare * 0.15, 2) }}</span>
                </div>
                <div class="fare-row">
                    <span>Estimated Tax:</span>
                    <span>${{ number_format($booking->estimated_fare * 0.05, 2) }}</span>
                </div>
                <div class="fare-row fare-total">
                    <span>Total Estimated Fare:</span>
                    <span>${{ number_format($booking->estimated_fare, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="important-note">
            <div class="important-note-title">Important Information</div>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Please be ready at your pickup location 5 minutes before the scheduled time</li>
                <li>Your driver will wait up to 5 minutes after the scheduled pickup time</li>
                <li>The fare shown is an estimate and may vary based on actual route and traffic conditions</li>
                <li>For any changes or cancellations, please contact us at least 2 hours before pickup</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>{{ $company['company_name'] }}</strong></p>
            <p>{{ $company['company_phone'] }} | {{ $company['company_email'] }}</p>
            <p>{{ $company['support_url'] }}</p>
            <p style="margin-top: 20px; font-size: 10px;">
                This document was generated on {{ now()->format('F j, Y g:i A') }}
            </p>
        </div>
    </div>
</body>
</html>