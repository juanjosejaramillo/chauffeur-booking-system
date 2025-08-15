<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $booking->booking_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.6;
            background: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 30px;
            margin-bottom: 40px;
        }
        
        .company-name {
            font-size: 32px;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .receipt-title {
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #666;
            margin-top: 10px;
        }
        
        /* Receipt Details */
        .receipt-info {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }
        
        .receipt-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .receipt-column.right {
            text-align: right;
        }
        
        .receipt-number {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .receipt-date {
            font-size: 14px;
            color: #666;
        }
        
        /* Customer Info */
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .info-row {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .info-label {
            display: inline-block;
            width: 120px;
            color: #666;
        }
        
        .info-value {
            color: #1a1a1a;
            font-weight: 500;
        }
        
        /* Service Details Table */
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .services-table th {
            background: #f8f8f8;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            border-bottom: 2px solid #e8e8e8;
        }
        
        .services-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .services-table .amount {
            text-align: right;
            font-weight: 500;
        }
        
        /* Totals */
        .totals {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e8e8e8;
        }
        
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .total-label {
            display: table-cell;
            text-align: right;
            padding-right: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .total-value {
            display: table-cell;
            width: 150px;
            text-align: right;
            font-size: 14px;
            font-weight: 500;
        }
        
        .total-row.grand-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #1a1a1a;
        }
        
        .total-row.grand-total .total-label {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .total-row.grand-total .total-value {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        /* Payment Info */
        .payment-info {
            background: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .payment-method {
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .payment-status {
            display: inline-block;
            padding: 4px 12px;
            background: #28a745;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Footer */
        .footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #e8e8e8;
            text-align: center;
            color: #888;
            font-size: 12px;
        }
        
        .footer-company {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .footer-contact {
            margin-bottom: 3px;
        }
        
        .thank-you {
            margin-top: 40px;
            padding: 30px;
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            border-left: 3px solid #1a1a1a;
            text-align: center;
        }
        
        .thank-you-text {
            font-size: 18px;
            font-weight: 300;
            color: #1a1a1a;
            letter-spacing: 0.5px;
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
        <!-- Header -->
        <div class="header">
            <h1 class="company-name">{{ config('app.name', 'Chauffeur') }}</h1>
            <div class="receipt-title">Official Receipt</div>
        </div>
        
        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="receipt-column">
                <div class="receipt-number">{{ $booking->booking_number }}</div>
                <div class="receipt-date">{{ $booking->created_at->format('F j, Y g:i A') }}</div>
            </div>
            <div class="receipt-column right">
                <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Receipt Date</div>
                <div style="font-size: 16px; font-weight: 500;">{{ now()->format('F j, Y') }}</div>
            </div>
        </div>
        
        <!-- Customer Information -->
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
            @if($booking->customer_phone)
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $booking->customer_phone }}</span>
            </div>
            @endif
        </div>
        
        <!-- Service Details -->
        <div class="section">
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
                            <span style="color: #666; font-size: 13px;">
                                {{ $booking->pickup_date->format('F j, Y') }} at {{ $booking->pickup_date->format('g:i A') }}<br>
                                From: {{ $booking->pickup_address }}<br>
                                To: {{ $booking->dropoff_address }}
                            </span>
                        </td>
                        <td class="amount">${{ number_format($booking->subtotal ?? $booking->estimated_fare, 2) }}</td>
                    </tr>
                    
                    @if($booking->gratuity_amount > 0)
                    <tr>
                        <td>
                            <strong>Gratuity</strong><br>
                            <span style="color: #666; font-size: 13px;">
                                {{ $booking->gratuity_type === 'percentage' ? $booking->gratuity_percentage . '%' : 'Custom amount' }}
                            </span>
                        </td>
                        <td class="amount">${{ number_format($booking->gratuity_amount, 2) }}</td>
                    </tr>
                    @endif
                    
                    @if($booking->discount_amount > 0)
                    <tr>
                        <td>
                            <strong>Discount</strong>
                            @if($booking->discount_code)
                            <br><span style="color: #666; font-size: 13px;">Code: {{ $booking->discount_code }}</span>
                            @endif
                        </td>
                        <td class="amount">-${{ number_format($booking->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="totals">
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
                <div class="total-value">-${{ number_format($booking->discount_amount, 2) }}</div>
            </div>
            @endif
            
            <div class="total-row grand-total">
                <div class="total-label">Total Amount:</div>
                <div class="total-value">${{ number_format($booking->final_fare ?? $booking->estimated_fare, 2) }}</div>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="payment-info">
            <div class="payment-method">
                <strong>Payment Method:</strong> Credit Card
            </div>
            <div class="payment-method">
                <strong>Status:</strong> <span class="payment-status">{{ ucfirst($booking->payment_status) }}</span>
            </div>
            @if($booking->stripe_payment_intent_id)
            <div class="payment-method" style="margin-top: 10px; font-size: 12px; color: #666;">
                Transaction ID: {{ $booking->stripe_payment_intent_id }}
            </div>
            @endif
        </div>
        
        <!-- Thank You Message -->
        <div class="thank-you">
            <div class="thank-you-text">Thank you for choosing our premium transportation service</div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-company">{{ config('app.name', 'Chauffeur Service') }}</div>
            <div class="footer-contact">{{ config('app.company_address', '123 Business Ave, Suite 100, City, State 12345') }}</div>
            <div class="footer-contact">{{ config('app.company_phone', '1-800-CHAUFFEUR') }} | {{ config('app.company_email', 'info@chauffeur.com') }}</div>
            <div style="margin-top: 15px;">
                This is an official receipt for your records
            </div>
        </div>
    </div>
</body>
</html>