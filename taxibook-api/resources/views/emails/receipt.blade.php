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
        .receipt-title {
            font-size: 20px;
            margin-top: 10px;
            color: #666;
        }
        .receipt-number {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #eee;
        }
        .info-label {
            color: #666;
        }
        .info-value {
            font-weight: 500;
            color: #333;
        }
        .total-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        .total-row.grand-total {
            border-top: 2px solid #4F46E5;
            padding-top: 15px;
            margin-top: 10px;
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .stamp {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border: 2px solid #28a745;
            border-radius: 8px;
            color: #28a745;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">TaxiBook</div>
            <div class="receipt-title">Payment Receipt</div>
            <div class="receipt-number">Transaction #{{ $transaction->stripe_transaction_id }}</div>
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
            <div class="section-title">Booking Details</div>
            <div class="info-row">
                <span class="info-label">Booking Number:</span>
                <span class="info-value">{{ $booking->booking_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date & Time:</span>
                <span class="info-value">{{ $booking->pickup_date->format('F j, Y g:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vehicle Type:</span>
                <span class="info-value">{{ $booking->vehicleType->display_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pickup:</span>
                <span class="info-value">{{ $booking->pickup_address }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dropoff:</span>
                <span class="info-value">{{ $booking->dropoff_address }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Payment Information</div>
            <div class="info-row">
                <span class="info-label">Payment Date:</span>
                <span class="info-value">{{ $transaction->created_at->format('F j, Y g:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value">Credit/Debit Card</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ ucfirst($transaction->status) }}</span>
            </div>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span>Base Fare:</span>
                <span>${{ number_format($booking->estimated_fare * 0.8, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Service Fee:</span>
                <span>${{ number_format($booking->estimated_fare * 0.15, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Tax:</span>
                <span>${{ number_format($booking->estimated_fare * 0.05, 2) }}</span>
            </div>
            <div class="total-row grand-total">
                <span>Total Paid:</span>
                <span>${{ number_format($transaction->amount, 2) }}</span>
            </div>
        </div>

        <div class="stamp">
            PAID
        </div>

        <div class="footer">
            <p>{{ $company['company_name'] }}</p>
            <p>{{ $company['company_phone'] }} | {{ $company['company_email'] }}</p>
            <p>Thank you for choosing TaxiBook!</p>
        </div>
    </div>
</body>
</html>