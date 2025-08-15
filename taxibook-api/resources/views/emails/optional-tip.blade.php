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

<div class="alert-box info">
    <div class="alert-title">This is completely optional!</div>
    <div class="alert-content">
        If you'd like to show appreciation for your driver's service, you can add a tip using the link below.
    </div>
</div>

<div style="text-align: center; margin: 40px 0;">
    <p style="font-size: 16px; font-weight: 500; margin-bottom: 24px;">Suggested gratuity amounts:</p>
    
    <div style="display: flex; justify-content: center; gap: 20px; margin: 30px 0;">
        @foreach($suggestedTips as $tip)
        <div style="text-align: center; padding: 15px; background-color: #fafafa; border-radius: 8px; min-width: 100px; border: 1px solid #f0f0f0;">
            <div style="font-size: 13px; color: #888; margin-bottom: 8px;">{{ $tip['percentage'] }}%</div>
            <div style="font-size: 20px; font-weight: 600; color: #1a1a1a;">${{ number_format($tip['amount'], 2) }}</div>
        </div>
        @endforeach
    </div>
</div>

<div class="button-container">
    <a href="{{ $tipUrl }}" class="button">Add Tip (Optional)</a>
</div>

<div class="content" style="text-align: center;">
    <p style="font-size: 12px; color: #888; margin-top: 20px;">
        Or copy this link:<br>
        <span style="word-break: break-all;">{{ $tipUrl }}</span>
    </p>
</div>

<div class="content">
    <p>If you're satisfied with the service as is, no action is needed. Your trip has been fully paid.</p>
</div>
@endsection