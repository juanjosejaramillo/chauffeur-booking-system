<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - {{ $booking->booking_number }}</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.4;
            background: white;
            font-size: 11px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
        }
        
        /* Header with luxe gradient */
        .header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 20px;
            text-align: center;
        }
        
        .company-name {
            color: #ffffff;
            font-size: 24px;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .document-title {
            color: #888;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        /* Main content wrapper */
        .content-wrapper {
            padding: 25px;
        }
        
        /* Highlight Box for booking number */
        .highlight-box {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            border-left: 3px solid #1a1a1a;
            padding: 12px 15px;
            margin: 0 0 15px 0;
        }
        
        .highlight-label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 5px 0;
        }
        
        .highlight-value {
            font-size: 18px;
            color: #1a1a1a;
            font-weight: 500;
            margin: 0;
        }
        
        /* Status Badge */
        .status-badge {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-default {
            background-color: #f8f9fa;
            color: #666;
        }
        
        /* Info Box styling from luxe template */
        .info-box {
            background-color: #fafafa;
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .info-box-title {
            font-size: 10px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 10px 0;
        }
        
        .info-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-size: 10px;
            color: #888;
            padding-right: 10px;
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
            font-size: 10px;
            color: #1a1a1a;
            font-weight: 500;
        }
        
        /* Fare Box */
        .fare-box {
            background-color: #f8f8f8;
            border-radius: 6px;
            padding: 12px;
            margin-top: 10px;
        }
        
        .fare-row {
            display: table;
            width: 100%;
            padding: 6px 0;
        }
        
        .fare-label {
            display: table-cell;
            font-size: 10px;
            color: #666;
        }
        
        .fare-value {
            display: table-cell;
            text-align: right;
            font-size: 10px;
            color: #1a1a1a;
        }
        
        .fare-total {
            border-top: 2px solid #1a1a1a;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .fare-total .fare-label {
            font-weight: 600;
            font-size: 12px;
            color: #1a1a1a;
        }
        
        .fare-total .fare-value {
            font-weight: 600;
            font-size: 14px;
            color: #1a1a1a;
        }
        
        /* Alert Box */
        .alert-box {
            border-radius: 6px;
            padding: 10px 12px;
            margin: 15px 0;
        }
        
        .alert-box.warning {
            background-color: #fff3e0;
            border: 1px solid #ffe0b2;
        }
        
        .alert-title {
            font-size: 10px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 5px 0;
        }
        
        .alert-content {
            font-size: 9px;
            color: #4a4a4a;
            line-height: 1.4;
        }
        
        .alert-content ul {
            margin: 0;
            padding-left: 15px;
        }
        
        .alert-content li {
            margin-bottom: 3px;
        }
        
        /* Footer */
        .footer {
            background-color: #fafafa;
            padding: 15px;
            text-align: center;
            border-top: 1px solid #f0f0f0;
            margin-top: 20px;
        }
        
        .footer-company {
            font-size: 10px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .footer-contact {
            font-size: 9px;
            color: #888;
            margin-bottom: 3px;
        }
        
        .footer-note {
            font-size: 8px;
            color: #aaa;
            margin-top: 8px;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with luxe gradient -->
        <div class="header">
            <h1 class="company-name">{{ $company['name'] ?? 'LuxRide' }}</h1>
            <div class="document-title">Booking Confirmation</div>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Booking Number Highlight -->
            <div class="highlight-box">
                <div class="highlight-label">Confirmation Number</div>
                <div class="highlight-value">{{ $booking->booking_number }}</div>
            </div>
            
            <!-- Status Badge -->
            <div class="status-badge">
                <span class="status-pill 
                    @if($booking->status == 'confirmed') status-confirmed
                    @elseif($booking->status == 'pending') status-pending
                    @else status-default
                    @endif">
                    {{ ucfirst($booking->status) }}
                </span>
            </div>
            
            <!-- Trip Details -->
            <div class="info-box">
                <div class="info-box-title">Trip Details</div>
                <div class="info-row">
                    <span class="info-label">Pickup Date</span>
                    <span class="info-value">{{ $booking->pickup_date->format('l, F j, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pickup Time</span>
                    <span class="info-value">{{ $booking->pickup_date->format('g:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vehicle Type</span>
                    <span class="info-value">{{ $booking->vehicleType->display_name ?? $booking->vehicleType->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estimated Duration</span>
                    <span class="info-value">{{ round($booking->estimated_duration / 60) }} minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estimated Distance</span>
                    <span class="info-value">{{ number_format($booking->estimated_distance, 1) }} miles</span>
                </div>
            </div>
            
            <!-- Pickup Location -->
            <div class="info-box">
                <div class="info-box-title">Pickup Location</div>
                <div class="info-row">
                    <span class="info-label">Address</span>
                    <span class="info-value">{{ $booking->pickup_address }}</span>
                </div>
                @if($booking->special_instructions)
                <div class="info-row">
                    <span class="info-label">Instructions</span>
                    <span class="info-value">{{ $booking->special_instructions }}</span>
                </div>
                @endif
            </div>
            
            <!-- Dropoff Location -->
            <div class="info-box">
                <div class="info-box-title">Dropoff Location</div>
                <div class="info-row">
                    <span class="info-label">Address</span>
                    <span class="info-value">{{ $booking->dropoff_address }}</span>
                </div>
            </div>
            
            <!-- Customer Information -->
            <div class="info-box">
                <div class="info-box-title">Customer Information</div>
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value">{{ $booking->customer_full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $booking->customer_email }}</span>
                </div>
                @if($booking->customer_phone)
                <div class="info-row">
                    <span class="info-label">Phone</span>
                    <span class="info-value">{{ $booking->customer_phone }}</span>
                </div>
                @endif
            </div>
            
            <!-- Fare Information -->
            <div class="info-box">
                <div class="info-box-title">Fare Information</div>
                <div class="fare-box">
                    <div class="fare-row">
                        <span class="fare-label">Base Fare:</span>
                        <span class="fare-value">${{ number_format($booking->subtotal ?? $booking->estimated_fare, 2) }}</span>
                    </div>
                    @if($booking->gratuity_amount > 0)
                    <div class="fare-row">
                        <span class="fare-label">Gratuity:</span>
                        <span class="fare-value">${{ number_format($booking->gratuity_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($booking->discount_amount > 0)
                    <div class="fare-row">
                        <span class="fare-label">Discount:</span>
                        <span class="fare-value" style="color: #dc3545;">-${{ number_format($booking->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="fare-row fare-total">
                        <span class="fare-label">Total Estimated Fare:</span>
                        <span class="fare-value">${{ number_format($booking->final_fare ?? $booking->estimated_fare, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Important Information -->
            <div class="alert-box warning">
                <div class="alert-title">Important Information</div>
                <div class="alert-content">
                    <ul>
                        <li>Please be ready at your pickup location 5 minutes before the scheduled time</li>
                        <li>Your driver will wait up to 5 minutes after the scheduled pickup time</li>
                        <li>The fare shown is an estimate and may vary based on actual route and traffic conditions</li>
                        <li>For changes or cancellations, please contact us at least 2 hours before pickup</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-company">{{ $company['name'] ?? 'LuxRide' }}</div>
            <div class="footer-contact">{{ $company['address'] ?? 'Florida, USA' }}</div>
            <div class="footer-contact">{{ $company['phone'] ?? '+1-813-333-8680' }} • {{ $company['email'] ?? 'contact@luxridesuv.com' }}</div>
            <div class="footer-note">This document contains your booking confirmation details</div>
        </div>
    </div>
</body>
</html>