@extends('emails.luxe-layout')

@section('title', 'Verification Code')

@section('content')
<h1 class="greeting">Hello {{ $customerName }},</h1>

<div class="content">
    <p>Thank you for choosing {{ config('business.name', 'LuxRide') }}. To complete your booking, please enter the verification code below:</p>
</div>

<div class="highlight-box" style="text-align: center;">
    <div class="highlight-label">Your verification code is:</div>
    <div class="highlight-value" style="font-size: 36px; letter-spacing: 8px;">{{ $code }}</div>
    <div style="color: #888; font-size: 14px; margin-top: 10px;">This code expires in 10 minutes</div>
</div>

<div class="info-box">
    <div class="info-box-title">Booking Preview</div>
    <div class="info-row">
        <div class="info-label">Pickup</div>
        <div class="info-value">{{ $pickupAddress }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Dropoff</div>
        <div class="info-value">{{ $dropoffAddress }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date & Time</div>
        <div class="info-value">{{ $pickupDate }}</div>
    </div>
</div>

<div class="alert-box warning">
    <div class="alert-title">Didn't receive the code?</div>
    <div class="alert-content">
        Please check your spam folder. If you still can't find it, you can request a new code.
    </div>
</div>

<div class="content">
    <p style="color: #888;">If you didn't request this booking, please ignore this email.</p>
</div>
@endsection