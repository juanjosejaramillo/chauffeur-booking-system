@extends('emails.luxe-layout')

@section('title', 'Booking Confirmation')

@section('content')
<h1 class="greeting">Booking Confirmation</h1>

<div class="highlight-box">
    <div class="highlight-label">Confirmation Number</div>
    <div class="highlight-value">{{ $booking->booking_number }}</div>
</div>

<div style="text-align: center; margin-bottom: 20px;">
    <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; text-transform: uppercase; font-size: 12px;
        @if($booking->status == 'confirmed')
            background-color: #d4edda; color: #155724;
        @elseif($booking->status == 'pending')
            background-color: #fff3cd; color: #856404;
        @else
            background-color: #f8f9fa; color: #666;
        @endif">
        {{ ucfirst($booking->status) }}
    </span>
    <span style="display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; text-transform: uppercase; font-size: 12px; margin-left: 10px;
        @if($booking->booking_type == 'hourly')
            background-color: #e7f5ff; color: #1971c2;
        @else
            background-color: #f3f4f6; color: #6b7280;
        @endif">
        {{ $booking->booking_type == 'hourly' ? 'Hourly' : 'One Way' }}
    </span>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Pickup Date</div>
        <div class="info-value">{{ $booking->pickup_date->format('l, F j, Y') }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup Time</div>
        <div class="info-value">{{ $booking->pickup_date->format('g:i A') }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{ $booking->vehicleType->display_name }}</div>
    </div>
    @if($booking->booking_type == 'hourly')
    <div class="info-row">
        <div class="info-label">Duration</div>
        <div class="info-value">{{ $booking->duration_hours }} hour{{ $booking->duration_hours > 1 ? 's' : '' }}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Miles Included</div>
        <div class="info-value">{{ $booking->duration_hours * $booking->vehicleType->miles_included_per_hour }} miles ({{ $booking->vehicleType->miles_included_per_hour }} miles per hour)</div>
    </div>
    @if($booking->vehicleType->excess_mile_rate > 0)
    <div class="info-row">
        <div class="info-label">Extra Miles</div>
        <div class="info-value">${{ number_format($booking->vehicleType->excess_mile_rate, 2) }} per mile</div>
    </div>
    @endif
    @else
    <div class="info-row">
        <div class="info-label">Estimated Duration</div>
        <div class="info-value">{{ round($booking->estimated_duration / 60) }} minutes</div>
    </div>
    <div class="info-row">
        <div class="info-label">Estimated Distance</div>
        <div class="info-value">{{ number_format($booking->estimated_distance, 1) }} miles</div>
    </div>
    @endif
</div>

<div class="info-box">
    <div class="info-box-title">Pickup Location</div>
    <div class="info-row">
        <div class="info-label">Address</div>
        <div class="info-value">{{ $booking->pickup_address }}</div>
    </div>
    @if($booking->special_instructions)
    <div class="info-row">
        <div class="info-label">Instructions</div>
        <div class="info-value">{{ $booking->special_instructions }}</div>
    </div>
    @endif
</div>

@if($booking->booking_type == 'one_way' && $booking->dropoff_address)
<div class="info-box">
    <div class="info-box-title">Dropoff Location</div>
    <div class="info-row">
        <div class="info-label">Address</div>
        <div class="info-value">{{ $booking->dropoff_address }}</div>
    </div>
</div>
@endif

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
    <div class="info-box-title">Payment Information</div>
    <div style="background-color: #fafafa; border-radius: 8px; padding: 15px; margin-top: 15px;">
        <div style="display: flex; justify-content: space-between; padding: 8px 0;">
            <span>Estimated Fare:</span>
            <span style="font-weight: bold; font-size: 18px;">${{ number_format($booking->estimated_fare + ($booking->extras_total ?? 0) + ($booking->tax_amount ?? 0), 2) }}</span>
        </div>
        @if(($booking->extras_total ?? 0) > 0)
        <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; color: #666;">
            <span>Extras:</span>
            <span>${{ number_format($booking->extras_total, 2) }}</span>
        </div>
        @endif
        @if(($booking->tax_amount ?? 0) > 0)
        <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; color: #666;">
            <span>Tax:</span>
            <span>${{ number_format($booking->tax_amount, 2) }}</span>
        </div>
        @endif
        <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; color: #666;">
            <span>Payment Status:</span>
            <span>{{ ucfirst($booking->payment_status) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; color: #666;">
            <span>Booking Status:</span>
            <span>{{ ucfirst($booking->status) }}</span>
        </div>
    </div>
</div>

<div class="alert-box warning">
    <div class="alert-title">Important Information</div>
    <div class="alert-content">
        <ul style="margin: 0; padding-left: 20px;">
            <li>Please be ready at your pickup location 5 minutes before the scheduled time</li>
            <li>Your driver will wait up to 5 minutes after the scheduled pickup time</li>
            <li>The fare shown is an estimate and may vary based on actual route and traffic conditions</li>
            <li>For any changes or cancellations, please contact us at least 2 hours before pickup</li>
        </ul>
    </div>
</div>
@endsection