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
        
        .receipt-title {
            color: #888;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        /* Main content wrapper */
        .content-wrapper {
            padding: 25px;
        }
        
        /* Receipt Info Section */
        .receipt-info {
            background-color: #fafafa;
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .receipt-info-row {
            display: table;
            width: 100%;
        }
        
        .receipt-info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .receipt-info-column.right {
            text-align: right;
        }
        
        .receipt-number {
            font-size: 16px;
            font-weight: 500;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        
        .receipt-date {
            font-size: 10px;
            color: #888;
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
            margin: 15px 0;
        }
        
        .section-title {
            font-size: 10px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e8e8e8;
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
            border-left: 3px solid #1a1a1a;
            padding: 12px 15px;
            margin: 15px 0;
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
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #1a1a1a;
        }
        
        .total-row.grand-total .total-label {
            font-size: 12px;
            font-weight: 600;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .total-row.grand-total .total-value {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        /* Alert Box for payment status */
        .alert-box {
            border-radius: 6px;
            padding: 10px 12px;
            margin: 15px 0;
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
            padding: 15px;
            margin: 15px 0;
        }
        
        .thank-you-text {
            font-size: 11px;
            color: #666;
            font-style: italic;
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
            <h1 class="company-name">{{ $settings['business_name'] ?? 'LuxRide' }}</h1>
            <div class="receipt-title">Official Receipt</div>
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Receipt Info -->
            <div class="receipt-info">
                <div class="receipt-info-row">
                    <div class="receipt-info-column">
                        <div class="receipt-number">#{{ $booking->booking_number }}</div>
                        <div class="receipt-date">Booking Date: {{ $booking->created_at->format('F j, Y g:i A') }}</div>
                    </div>
                    <div class="receipt-info-column right">
                        <div style="font-size: 10px; color: #888; margin-bottom: 3px;">Receipt Date</div>
                        <div style="font-size: 12px; font-weight: 500;">{{ now()->format('F j, Y') }}</div>
                    </div>
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
            
            <!-- Service Details -->
            <div class="services-section">
                <div class="section-title">Service Details</div>
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
                                <strong>{{ $booking->vehicleType->name ?? 'Premium Transportation' }}</strong><br>
                                <span style="color: #666; font-size: 9px;">
                                    {{ $booking->pickup_date->format('F j, Y') }} at {{ $booking->pickup_date->format('g:i A') }}<br>
                                    <strong>From:</strong> {{ $booking->pickup_address }}<br>
                                    <strong>To:</strong> {{ $booking->dropoff_address }}
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
                @if($booking->gratuity_amount > 0 || $booking->discount_amount > 0)
                <div class="total-row">
                    <div class="total-label">Subtotal:</div>
                    <div class="total-value">${{ number_format($booking->subtotal ?? $booking->estimated_fare, 2) }}</div>
                </div>
                @endif
                
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
                    <div class="total-label">Total Amount</div>
                    <div class="total-value">${{ number_format($booking->final_fare ?? $booking->estimated_fare, 2) }}</div>
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
            
            <!-- Payment Information Alert Box -->
            <div class="alert-box @if($booking->payment_status === 'captured' && $booking->total_refunded == 0) success @else warning @endif">
                <div class="alert-title">Payment Information</div>
                <div class="alert-content">
                    <strong>Method:</strong> Credit Card<br>
                    <strong>Status:</strong> 
                    <span class="payment-status @if($booking->total_refunded > 0) refunded @endif">
                        @if($booking->payment_status === 'captured' && $booking->total_refunded > 0)
                            Partially Refunded
                        @else
                            {{ ucfirst($booking->payment_status) }}
                        @endif
                    </span>
                    @if($booking->stripe_payment_intent_id)
                    <br><span style="font-size: 9px; color: #666; margin-top: 5px; display: block;">
                        Transaction ID: {{ $booking->stripe_payment_intent_id }}
                    </span>
                    @endif
                </div>
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
                <div class="thank-you-text">Thank you for choosing our premium transportation service</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-company">{{ $settings['business_name'] ?? 'LuxRide' }}</div>
            <div class="footer-contact">{{ $settings['business_address'] ?? 'Florida, USA' }}</div>
            <div class="footer-contact">{{ $settings['business_phone'] ?? '+1-813-333-8680' }} • {{ $settings['business_email'] ?? 'contact@luxridesuv.com' }}</div>
            <div class="footer-note">This is an official receipt for your records</div>
        </div>
    </div>
</body>
</html>