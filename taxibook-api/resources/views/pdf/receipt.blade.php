<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $booking->booking_number }}</title>
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
            padding: 30px 20px;
            text-align: center;
        }
        
        .company-name {
            color: #ffffff;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .receipt-title {
            color: #888;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        /* Main content wrapper */
        .content-wrapper {
            padding: 30px 25px;
        }
        
        /* Confirmation Number Box */
        .confirmation-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .confirmation-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .confirmation-number {
            font-size: 24px;
            font-weight: 500;
            color: #1a1a1a;
            letter-spacing: 2px;
        }
        
        /* Receipt Info Section */
        .receipt-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .receipt-info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .receipt-info-column.right {
            text-align: right;
        }
        
        .receipt-date-label {
            font-size: 10px;
            color: #888;
            margin-bottom: 3px;
        }
        
        .receipt-date-value {
            font-size: 12px;
            font-weight: 500;
            color: #1a1a1a;
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
            width: 35%;
            font-size: 10px;
            color: #888;
            padding-right: 10px;
        }
        
        .info-value {
            display: table-cell;
            width: 65%;
            font-size: 10px;
            color: #1a1a1a;
            font-weight: 500;
        }
        
        /* Service Details Table */
        .services-section {
            margin: 20px 0;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .services-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .services-table th {
            background: #f8f8f8;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .services-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 10px;
        }
        
        .services-table .amount {
            text-align: right;
            font-weight: 500;
        }
        
        /* Highlight Box for totals */
        .highlight-box {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            border-left: 4px solid #1a1a1a;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        
        .total-label {
            display: table-cell;
            text-align: right;
            padding-right: 15px;
            font-size: 10px;
            color: #666;
        }
        
        .total-value {
            display: table-cell;
            width: 100px;
            text-align: right;
            font-size: 10px;
            font-weight: 500;
        }
        
        .total-row.grand-total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #1a1a1a;
        }
        
        .total-row.grand-total .total-label {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .total-row.grand-total .total-value {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
        }
        
        /* Alert Box for payment status */
        .alert-box {
            border-radius: 8px;
            padding: 16px 20px;
            margin: 20px 0;
        }
        
        .alert-box.success {
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
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
            font-size: 10px;
            color: #4a4a4a;
            line-height: 1.4;
        }
        
        .payment-status {
            display: inline-block;
            padding: 3px 8px;
            background: #28a745;
            color: white;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .payment-status.refunded {
            background: #fd7e14;
        }
        
        /* Thank You Message */
        .thank-you {
            text-align: center;
            padding: 25px;
            margin: 25px 0;
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            border-radius: 8px;
        }
        
        .thank-you-text {
            font-size: 12px;
            color: #4a4a4a;
            font-style: italic;
            margin-bottom: 8px;
        }
        
        .thank-you-subtext {
            font-size: 10px;
            color: #888;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f0f0 100%);
            padding: 20px;
            text-align: center;
            border-top: 2px solid #e8e8e8;
            margin-top: 30px;
        }
        
        .footer-company {
            font-size: 12px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .footer-contact {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .footer-note {
            font-size: 9px;
            color: #999;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e0e0e0;
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
            <h1 class="company-name">{{ $settings['business_name'] ?? 'LuxRide' }}</h1>
            <div class="receipt-title">Official Receipt</div>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Confirmation Number -->
            <div class="confirmation-box">
                <div class="confirmation-label">Your Confirmation Number</div>
                <div class="confirmation-number">{{ $booking->booking_number }}</div>
            </div>
            
            <!-- Receipt Info -->
            <div class="receipt-info">
                <div class="receipt-info-column">
                    <div class="receipt-date-label">Booking Date</div>
                    <div class="receipt-date-value">{{ $booking->created_at->format('F j, Y g:i A') }}</div>
                </div>
                <div class="receipt-info-column right">
                    <div class="receipt-date-label">Receipt Date</div>
                    <div class="receipt-date-value">{{ now()->format('F j, Y') }}</div>
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
            
            <!-- Trip Details -->
            <div class="info-box">
                <div class="info-box-title">Trip Details</div>
                <div class="info-row">
                    <span class="info-label">Date & Time</span>
                    <span class="info-value">{{ $booking->pickup_date->format('F j, Y') }} at {{ $booking->pickup_date->format('g:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vehicle</span>
                    <span class="info-value">{{ $booking->vehicleType->name ?? 'Premium Vehicle' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pickup</span>
                    <span class="info-value">{{ $booking->pickup_address }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dropoff</span>
                    <span class="info-value">{{ $booking->dropoff_address }}</span>
                </div>
            </div>
            
            <!-- Service Details -->
            <div class="services-section">
                <div class="section-title">Charges</div>
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>Transportation Service</strong><br>
                                <span style="color: #666; font-size: 9px;">
                                    {{ $booking->vehicleType->name ?? 'Premium Vehicle' }}
                                    @if($booking->estimated_distance)
                                    • {{ number_format($booking->estimated_distance, 1) }} miles
                                    @endif
                                </span>
                            </td>
                            <td class="amount">${{ number_format($booking->subtotal ?? $booking->estimated_fare, 2) }}</td>
                        </tr>
                        
                        @if($booking->gratuity_amount > 0)
                        <tr>
                            <td>
                                <strong>Gratuity</strong>
                                @if($booking->gratuity_type === 'percentage')
                                <span style="color: #666; font-size: 9px;">({{ $booking->gratuity_percentage }}%)</span>
                                @endif
                            </td>
                            <td class="amount">${{ number_format($booking->gratuity_amount, 2) }}</td>
                        </tr>
                        @endif
                        
                        @if($booking->discount_amount > 0)
                        <tr>
                            <td>
                                <strong>Discount</strong>
                                @if($booking->discount_code)
                                <span style="color: #666; font-size: 9px;">(Code: {{ $booking->discount_code }})</span>
                                @endif
                            </td>
                            <td class="amount" style="color: #dc3545;">-${{ number_format($booking->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Totals in Highlight Box -->
            <div class="highlight-box">
                <div class="total-row">
                    <div class="total-label">Service Total:</div>
                    <div class="total-value">${{ number_format($booking->subtotal ?? $booking->estimated_fare, 2) }}</div>
                </div>
                
                @if($booking->gratuity_amount > 0)
                <div class="total-row">
                    <div class="total-label">Gratuity:</div>
                    <div class="total-value">${{ number_format($booking->gratuity_amount, 2) }}</div>
                </div>
                @endif
                
                @if($booking->discount_amount > 0)
                <div class="total-row">
                    <div class="total-label">Discount:</div>
                    <div class="total-value" style="color: #dc3545;">-${{ number_format($booking->discount_amount, 2) }}</div>
                </div>
                @endif
                
                <div class="total-row grand-total">
                    <div class="total-label">Total Charged</div>
                    @php
                        $totalCharged = ($booking->final_fare ?? $booking->estimated_fare) + $booking->gratuity_amount;
                    @endphp
                    <div class="total-value">${{ number_format($totalCharged, 2) }}</div>
                </div>
                
                @if($booking->total_refunded > 0)
                <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #888;">
                    <div class="total-row" style="color: #dc3545;">
                        <div class="total-label">Refunded:</div>
                        <div class="total-value">-${{ number_format($booking->total_refunded, 2) }}</div>
                    </div>
                    <div class="total-row">
                        <div class="total-label" style="font-size: 11px; font-weight: 600;">Net Amount:</div>
                        <div class="total-value" style="font-size: 12px; font-weight: 600;">${{ number_format($booking->net_amount, 2) }}</div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Payment Information Box -->
            <div class="info-box">
                <div class="info-box-title">Payment Information</div>
                <div class="info-row">
                    <span class="info-label">Method</span>
                    <span class="info-value">Credit Card</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="payment-status @if($booking->total_refunded > 0) refunded @endif">
                            @if($booking->payment_status === 'captured' && $booking->total_refunded > 0)
                                Partially Refunded
                            @else
                                {{ ucfirst($booking->payment_status) }}
                            @endif
                        </span>
                    </span>
                </div>
                @if($booking->stripe_payment_intent_id)
                <div class="info-row">
                    <span class="info-label">Transaction ID</span>
                    <span class="info-value" style="font-size: 9px;">{{ $booking->stripe_payment_intent_id }}</span>
                </div>
                @endif
            </div>
            
            @if($booking->total_refunded > 0 && $booking->transactions)
            <!-- Refund History -->
            <div class="info-box">
                <div class="info-box-title">Refund History</div>
                @foreach($booking->transactions->whereIn('type', ['refund', 'partial_refund'])->where('status', 'succeeded')->take(3) as $refund)
                <div class="info-row">
                    <span class="info-label">{{ $refund->created_at->format('M j, Y') }}</span>
                    <span class="info-value">${{ number_format($refund->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif
            
            <!-- Thank You Message -->
            <div class="thank-you">
                <div class="thank-you-text">Thank you for choosing {{ $settings['business_name'] ?? 'LuxRide' }}</div>
                <div class="thank-you-subtext">We appreciate your business and look forward to serving you again</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-company">{{ $settings['business_name'] ?? 'LuxRide' }}</div>
            <div class="footer-contact">{{ $settings['business_address'] ?? 'Florida, USA' }}</div>
            <div class="footer-contact">{{ $settings['business_phone'] ?? '+1-813-333-8680' }} • {{ $settings['business_email'] ?? 'contact@luxridesuv.com' }}</div>
            <div class="footer-note">
                This is an official receipt for your records<br>
                Receipt #{{ $booking->booking_number }} • Generated on {{ now()->format('F j, Y g:i A') }}
            </div>
        </div>
    </div>
</body>
</html>