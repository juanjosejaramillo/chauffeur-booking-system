@extends('emails.luxe-layout')

@section('title', 'Payment Receipt')

@section('content')
<h1 class="greeting">Payment Receipt</h1>

<div class="content">
    <p>Transaction #{{ $transaction->stripe_transaction_id }}</p>
</div>

<div class="info-box">
    <div class="info-box-title">Customer Information</div>
    <div class="info-row">
        <div class="info-label">Name</div>
        <div class="info-value">{{ $booking->customer_full_name }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Email</div>
        <div class="info-value">{{ $booking->customer_email }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Phone</div>
        <div class="info-value">{{ $booking->customer_phone }}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Booking Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{ $booking->booking_number }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date & Time</div>
        <div class="info-value">{{ $booking->pickup_date->format('F j, Y g:i A') }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{ $booking->vehicleType->display_name }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup</div>
        <div class="info-value">{{ $booking->pickup_address }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Dropoff</div>
        <div class="info-value">{{ $booking->dropoff_address }}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Payment Information</div>
    <div class="info-row">
        <div class="info-label">Payment Date</div>
        <div class="info-value">{{ $transaction->created_at->format('F j, Y g:i A') }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Payment Method</div>
        <div class="info-value">Credit/Debit Card</div>
    </div>
    <div class="info-row">
        <div class="info-label">Status</div>
        <div class="info-value">{{ ucfirst($transaction->status) }}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Fare Breakdown</div>
    <div style="background-color: #fafafa; border-radius: 8px; padding: 15px; margin-top: 15px;">
        <div style="display: flex; justify-content: space-between; padding: 8px 0;">
            <span>Base Fare:</span>
            <span>${{ number_format($booking->estimated_fare * 0.8, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 8px 0;">
            <span>Service Fee:</span>
            <span>${{ number_format($booking->estimated_fare * 0.15, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 8px 0;">
            <span>Tax:</span>
            <span>${{ number_format($booking->estimated_fare * 0.05, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-top: 2px solid #1a1a1a; padding-top: 10px; margin-top: 10px; font-weight: bold; font-size: 18px;">
            <span>Total Paid:</span>
            <span>${{ number_format($transaction->amount, 2) }}</span>
        </div>
    </div>
</div>

<div class="alert-box success">
    <div class="alert-title">PAID</div>
    <div class="alert-content">
        Payment successfully processed
    </div>
</div>

<div class="content">
    <p style="text-align: center; color: #888; font-size: 14px;">Thank you for choosing {{ config('business.name', 'LuxRide') }}!</p>
</div>
@endsection