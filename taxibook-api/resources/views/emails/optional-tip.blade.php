@extends('emails.luxe-layout')

@section('title', 'Add a Tip for Your Trip')

@section('content')
<h1 class="greeting">Hi {{ $customerName }},</h1>

<div class="content">
    <p>Thank you for choosing {{ config('business.name', 'LuxRide') }} for your recent trip!</p>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">#{{ $bookingNumber }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Trip Date</div>
        <div class="info-value">{{ $tripDate }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Fare Paid</div>
        <div class="info-value">${{ number_format($fare, 2) }}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{ $tipUrl }}" class="button">Add Tip</a>
</div>

<div class="content" style="text-align: center;">
    <p style="font-size: 12px; color: #888; margin-top: 20px;">
        Or copy this link:<br>
        <span style="word-break: break-all;">{{ $tipUrl }}</span>
    </p>
</div>

@endsection