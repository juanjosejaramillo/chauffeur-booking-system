<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\EmailTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $template = EmailTemplate::where('slug', 'trip-completed')->first();
        
        if ($template) {
            $template->body = '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Thank You, {{customer_first_name}}</h1>

<div class="content">
    <p>We hope you enjoyed your journey with us. It was our pleasure to serve you.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Summary</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">From</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">To</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Total Fare</div>
        <div class="info-value">{{final_fare}}</div>
    </div>
    @if({{has_refund}})
    <div class="info-row" style="color: #dc3545;">
        <div class="info-label">Refunded Amount</div>
        <div class="info-value">{{total_refunded}}</div>
    </div>
    <div class="info-row" style="font-weight: bold;">
        <div class="info-label">Net Amount</div>
        <div class="info-value">{{net_amount}}</div>
    </div>
    @endif
</div>

@if({{has_refund}})
<div class="alert-box warning">
    <div class="alert-title">⚠ Refund Processed</div>
    <div class="alert-content">
        @if({{is_partially_refunded}})
        A partial refund of {{total_refunded}} has been processed. The net amount charged is {{net_amount}}.
        @else
        A full refund of {{total_refunded}} has been processed.
        @endif
    </div>
</div>
@endif

<div class="alert-box success">
    <div class="alert-title">✓ Receipt Attached</div>
    <div class="alert-content">
        Your payment receipt is attached to this email as a PDF for your records.
        You can also view or download it online anytime.
    </div>
</div>

<div class="button-container">
    <a href="{{receipt_url}}" class="button">View Receipt Online</a>
    <a href="{{receipt_url}}/download" class="button secondary">Download Another Copy</a>
</div>

<div class="content">
    <p>We value your feedback and would appreciate hearing about your experience.</p>
</div>
@endsection
';
            $template->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $template = EmailTemplate::where('slug', 'trip-completed')->first();
        
        if ($template) {
            $template->body = '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Thank You, {{customer_first_name}}</h1>

<div class="content">
    <p>We hope you enjoyed your journey with us. It was our pleasure to serve you.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Summary</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">From</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">To</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Total Fare</div>
        <div class="info-value">{{final_fare}}</div>
    </div>
</div>

<div class="alert-box success">
    <div class="alert-title">✓ Receipt Attached</div>
    <div class="alert-content">
        Your payment receipt is attached to this email as a PDF for your records.
        You can also view or download it online anytime.
    </div>
</div>

<div class="button-container">
    <a href="{{receipt_url}}" class="button">View Receipt Online</a>
    <a href="{{receipt_url}}/download" class="button secondary">Download Another Copy</a>
</div>

<div class="content">
    <p>We value your feedback and would appreciate hearing about your experience.</p>
</div>
@endsection
';
            $template->save();
        }
    }
};