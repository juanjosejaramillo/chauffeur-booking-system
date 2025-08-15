<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // CUSTOMER EMAILS - Booking Lifecycle
            [
                'name' => 'Booking Confirmation',
                'description' => 'Sent to customer when booking is confirmed and payment is authorized',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Booking Confirmed - {{booking_number}}',
                'body' => $this->getBookingConfirmationTemplate(),
                'attach_receipt' => false,
                'attach_booking_details' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.confirmed'],
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => '24 Hour Booking Reminder',
                'description' => 'Reminder sent to customer 24 hours before pickup',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Tomorrow\'s Ride - {{booking_number}}',
                'body' => $this->getBookingReminderTemplate(),
                'attach_booking_details' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.reminder.24h'],
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => '2 Hour Booking Reminder',
                'description' => 'Final reminder sent 2 hours before pickup',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Your Ride is Coming Soon - {{booking_number}}',
                'body' => $this->getTwoHourReminderTemplate(),
                'attach_booking_details' => false,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.reminder.2h'],
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Driver Assigned Notification',
                'description' => 'Notifies customer when driver is assigned',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Your Driver Has Been Assigned - {{booking_number}}',
                'body' => $this->getDriverAssignedTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['driver.assigned'],
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Driver En Route',
                'description' => 'Sent when driver starts journey to pickup location',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Your Driver is On the Way - {{booking_number}}',
                'body' => $this->getDriverEnrouteTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['driver.enroute'],
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Driver Arrived',
                'description' => 'Notification when driver arrives at pickup location',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Your Driver Has Arrived',
                'body' => $this->getDriverArrivedTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['driver.arrived'],
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Trip Completed',
                'description' => 'Thank you message after trip completion',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Thank You for Riding With Us',
                'body' => $this->getTripCompletedTemplate(),
                'attach_receipt' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.completed'],
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Booking Modified',
                'description' => 'Sent when booking details are changed',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Your Booking Has Been Updated - {{booking_number}}',
                'body' => $this->getBookingModifiedTemplate(),
                'attach_booking_details' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.modified'],
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Booking Cancelled',
                'description' => 'Confirmation of booking cancellation',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Booking Cancelled - {{booking_number}}',
                'body' => $this->getBookingCancelledTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['booking.cancelled'],
                'priority' => 10,
                'is_active' => true,
            ],
            
            // CUSTOMER EMAILS - Payment Related
            [
                'name' => 'Payment Receipt',
                'description' => 'Receipt sent after successful payment capture',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Payment Receipt - {{booking_number}}',
                'body' => $this->getPaymentReceiptTemplate(),
                'attach_receipt' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['payment.captured'],
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Payment Failed',
                'description' => 'Notification when payment fails',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Payment Issue - Action Required',
                'body' => $this->getPaymentFailedTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['payment.failed'],
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Refund Processed',
                'description' => 'Confirmation when refund is processed',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'Refund Processed - {{booking_number}}',
                'body' => $this->getRefundProcessedTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['payment.refunded'],
                'priority' => 10,
                'is_active' => true,
            ],
            
            // CUSTOMER EMAILS - Follow-up
            [
                'name' => 'Review Request',
                'description' => 'Request for feedback 24 hours after trip',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'subject' => 'How Was Your Experience?',
                'body' => $this->getReviewRequestTemplate(),
                'delay_minutes' => 1440, // 24 hours
                'trigger_events' => ['trip.review.24h'],
                'priority' => 3,
                'is_active' => true,
            ],
            
            // ADMIN EMAILS - Booking Notifications
            [
                'name' => 'New Booking Alert (Admin)',
                'description' => 'Alert sent to admin for new bookings',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'New Booking: {{booking_number}} - {{pickup_date}}',
                'body' => $this->getAdminNewBookingTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['booking.created', 'booking.confirmed'],
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Upcoming Booking Alert (Admin)',
                'description' => 'Reminder for admin 2 hours before pickup',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'Upcoming: {{booking_number}} in 2 hours',
                'body' => $this->getAdminUpcomingBookingTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['booking.reminder.2h'],
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Booking Cancelled (Admin)',
                'description' => 'Admin notification when booking is cancelled',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'Cancelled: {{booking_number}} - {{customer_name}}',
                'body' => $this->getAdminBookingCancelledTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['booking.cancelled'],
                'priority' => 9,
                'is_active' => true,
            ],
            
            // ADMIN EMAILS - Payment Notifications
            [
                'name' => 'Payment Captured (Admin)',
                'description' => 'Admin notification for successful payment',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'Payment Captured: {{final_fare}} - {{booking_number}}',
                'body' => $this->getAdminPaymentCapturedTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['payment.captured'],
                'priority' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Payment Failed (Admin)',
                'description' => 'Admin alert for failed payments',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'Payment Failed: {{booking_number}}',
                'body' => $this->getAdminPaymentFailedTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['payment.failed', 'admin.payment_issue'],
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Refund Processed (Admin)',
                'description' => 'Admin notification for refunds',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'Refund Issued: {{refund_amount}} - {{booking_number}}',
                'body' => $this->getAdminRefundTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['payment.refunded'],
                'priority' => 8,
                'is_active' => true,
            ],
            
            // ADMIN EMAILS - Reports
            [
                'name' => 'Daily Summary Report',
                'description' => 'Daily summary of bookings and revenue',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'Daily Summary - {{date}}',
                'body' => $this->getAdminDailySummaryTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['admin.daily_summary'],
                'priority' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Weekly Summary Report',
                'description' => 'Weekly business summary',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'send_to_driver' => false,
                'subject' => 'Weekly Summary - Week of {{date}}',
                'body' => $this->getAdminWeeklySummaryTemplate(),
                'delay_minutes' => 0,
                'trigger_events' => ['admin.weekly_summary'],
                'priority' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }

    // CUSTOMER EMAIL TEMPLATES - Using Luxe Layout

    private function getBookingConfirmationTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Hello {{customer_first_name}},</h1>

<div class="content">
    <p>Your premium transportation has been confirmed. We\'re delighted to be serving you.</p>
</div>

<div class="highlight-box">
    <div class="highlight-label">Confirmation Number</div>
    <div class="highlight-value">{{booking_number}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Date</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Time</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Destination</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Estimated Fare</div>
        <div class="info-value">{{estimated_fare}}</div>
    </div>
</div>

<div class="alert-box info">
    <div class="alert-title">What\'s Next</div>
    <div class="alert-content">
        We\'ll send you a reminder 24 hours before your pickup time. Your professional chauffeur will arrive 5 minutes before your scheduled time.
    </div>
</div>

<div class="alert-box success">
    <div class="alert-title">📎 Booking Details Attached</div>
    <div class="alert-content">
        Your complete booking details are attached to this email as a PDF for your records.
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Booking Online</a>
    <a href="{{receipt_url}}" class="button secondary">Get Receipt</a>
</div>

<div class="content">
    <p>Should you need to make any changes, please contact us at least 2 hours before your scheduled pickup time.</p>
</div>
@endsection
';
    }

    private function getBookingReminderTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Good Day {{customer_first_name}},</h1>

<div class="content">
    <p>This is a courtesy reminder of your scheduled transportation tomorrow.</p>
</div>

<div class="highlight-box">
    <div class="highlight-label">Tomorrow\'s Booking</div>
    <div class="highlight-value">{{pickup_time}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Information</div>
    <div class="info-row">
        <div class="info-label">Pickup Location</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Destination</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Confirmation</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
</div>

<div class="alert-box info">
    <div class="alert-title">Preparation Reminder</div>
    <div class="alert-content">
        Please be ready at your pickup location 5 minutes before {{pickup_time}}. Your chauffeur will wait up to 5 minutes after the scheduled time.
    </div>
</div>

<div class="alert-box success">
    <div class="alert-title">📎 Booking Details Attached</div>
    <div class="alert-content">
        Your complete trip details are attached to this email as a PDF for easy reference.
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Details Online</a>
    <a href="{{receipt_url}}" class="button secondary">Get Receipt</a>
</div>
@endsection
';
    }

    private function getTwoHourReminderTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">{{customer_first_name}}, Your Ride is Coming Soon</h1>

<div class="content">
    <p>Your chauffeur will arrive in approximately 2 hours.</p>
</div>

<div class="highlight-box">
    <div class="highlight-label">Pickup Time</div>
    <div class="highlight-value">{{pickup_time}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Quick Reference</div>
    <div class="info-row">
        <div class="info-label">Pickup</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Booking #</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
</div>

<div class="alert-box success">
    <div class="alert-title">Be Ready</div>
    <div class="alert-content">
        Your professional chauffeur will arrive promptly. We\'ll notify you when they\'re on the way.
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">Track Status</a>
</div>
@endsection
';
    }

    private function getDriverAssignedTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">{{customer_first_name}}, Your Chauffeur Has Been Assigned</h1>

<div class="content">
    <p>We\'re pleased to confirm your professional chauffeur for your upcoming journey.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Chauffeur Information</div>
    <div class="info-row">
        <div class="info-label">Name</div>
        <div class="info-value">{{driver_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Contact</div>
        <div class="info-value">{{driver_phone}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle</div>
        <div class="info-value">{{driver_vehicle}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">License Plate</div>
        <div class="info-value">{{driver_license_plate}}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Pickup Time</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup Location</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Details</a>
</div>
@endsection
';
    }

    private function getDriverEnrouteTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Your Chauffeur is On the Way</h1>

<div class="content">
    <p>{{driver_name}} is en route to your pickup location.</p>
</div>

<div class="alert-box success">
    <div class="alert-title">Driver En Route</div>
    <div class="alert-content">
        Please be ready at your pickup location. Your chauffeur will arrive shortly.
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Chauffeur Details</div>
    <div class="info-row">
        <div class="info-label">Name</div>
        <div class="info-value">{{driver_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle</div>
        <div class="info-value">{{driver_vehicle}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">License Plate</div>
        <div class="info-value">{{driver_license_plate}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Contact</div>
        <div class="info-value">{{driver_phone}}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Pickup Location</div>
    <div class="info-row">
        <div class="info-value">{{pickup_address}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">Track Driver</a>
</div>
@endsection
';
    }

    private function getDriverArrivedTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Your Chauffeur Has Arrived</h1>

<div class="alert-box success">
    <div class="alert-title">Driver Waiting</div>
    <div class="alert-content">
        {{driver_name}} has arrived at your pickup location and is waiting for you.
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Vehicle Information</div>
    <div class="info-row">
        <div class="info-label">Vehicle</div>
        <div class="info-value">{{driver_vehicle}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">License Plate</div>
        <div class="info-value">{{driver_license_plate}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Chauffeur</div>
        <div class="info-value">{{driver_name}}</div>
    </div>
</div>

<div class="content">
    <p>Please proceed to your pickup location. Your chauffeur will wait for up to 5 minutes.</p>
</div>
@endsection
';
    }

    private function getTripCompletedTemplate(): string
    {
        return '
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
    }

    private function getBookingModifiedTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Booking Update Confirmation</h1>

<div class="content">
    <p>{{customer_first_name}}, your booking has been successfully updated as requested.</p>
</div>

<div class="alert-box info">
    <div class="alert-title">Changes Applied</div>
    <div class="alert-content">
        {{changes_summary}}
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Updated Booking Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">New Date & Time</div>
        <div class="info-value">{{pickup_date}} at {{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Destination</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Updated Booking</a>
</div>
@endsection
';
    }

    private function getBookingCancelledTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Booking Cancellation Confirmed</h1>

<div class="content">
    <p>Your booking {{booking_number}} has been cancelled as requested.</p>
</div>

<div class="alert-box error">
    <div class="alert-title">Cancellation Details</div>
    <div class="alert-content">
        {{cancellation_reason}}
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Cancelled Booking Information</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Original Date</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Original Time</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
</div>

<div class="alert-box info">
    <div class="alert-title">Refund Information</div>
    <div class="alert-content">
        If you had made a payment, a refund will be processed within 5-7 business days to your original payment method.
    </div>
</div>

<div class="content">
    <p>We hope to serve you again in the future.</p>
</div>

<div class="button-container">
    <a href="{{support_url}}" class="secondary-button">Book New Ride</a>
</div>
@endsection
';
    }

    private function getPaymentReceiptTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Payment Receipt</h1>

<div class="content">
    <p>Thank you for your payment. This confirms successful processing of your transaction.</p>
</div>

<div class="highlight-box">
    <div class="highlight-label">Amount Paid</div>
    <div class="highlight-value">{{transaction_amount}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Payment Details</div>
    <div class="info-row">
        <div class="info-label">Transaction ID</div>
        <div class="info-value">{{transaction_id}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date</div>
        <div class="info-value">{{transaction_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Payment Method</div>
        <div class="info-value">Credit Card</div>
    </div>
</div>

<div class="alert-box success">
    <div class="alert-title">Payment Successful</div>
    <div class="alert-content">
        Your payment has been processed successfully. A detailed receipt is attached for your records.
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Booking</a>
</div>
@endsection
';
    }

    private function getPaymentFailedTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Payment Issue - Action Required</h1>

<div class="content">
    <p>{{customer_first_name}}, we were unable to process your payment for booking {{booking_number}}.</p>
</div>

<div class="alert-box error">
    <div class="alert-title">Payment Failed</div>
    <div class="alert-content">
        Your payment could not be processed. Please update your payment information to secure your booking.
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Booking at Risk</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Scheduled Date</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Amount Due</div>
        <div class="info-value">{{estimated_fare}}</div>
    </div>
</div>

<div class="alert-box warning">
    <div class="alert-title">Immediate Action Required</div>
    <div class="alert-content">
        Please update your payment information within 24 hours to avoid automatic cancellation of your booking.
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">Update Payment</a>
</div>

<div class="content">
    <p>If you need assistance, please contact our support team immediately.</p>
</div>
@endsection
';
    }

    private function getRefundProcessedTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">Refund Confirmation</h1>

<div class="content">
    <p>We have processed a refund for your booking {{booking_number}}.</p>
</div>

<div class="highlight-box">
    <div class="highlight-label">Refund Amount</div>
    <div class="highlight-value">{{refund_amount}}</div>
</div>

<div class="alert-box success">
    <div class="alert-title">Refund Processed</div>
    <div class="alert-content">
        Reason: {{refund_reason}}
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Refund Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Refund Amount</div>
        <div class="info-value">{{refund_amount}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Processing Time</div>
        <div class="info-value">5-7 business days</div>
    </div>
    <div class="info-row">
        <div class="info-label">Refund Method</div>
        <div class="info-value">Original payment method</div>
    </div>
</div>

<div class="content">
    <p>The refund will appear on your statement within 5-7 business days.</p>
    <p>If you have any questions, please contact our support team.</p>
</div>

<div class="button-container">
    <a href="{{support_url}}" class="secondary-button">Contact Support</a>
</div>
@endsection
';
    }

    private function getReviewRequestTemplate(): string
    {
        return '
@extends("emails.luxe-layout")

@section("content")
<h1 class="greeting">How Was Your Experience, {{customer_first_name}}?</h1>

<div class="content">
    <p>Your feedback helps us maintain the exceptional service you deserve.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Your Recent Journey</div>
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
</div>

<div class="content">
    <p>We\'d appreciate it if you could take a moment to share your experience.</p>
</div>

<div class="button-container">
    <a href="{{booking_url}}/review" class="button">Leave Review</a>
</div>

<div class="content">
    <p>Thank you for choosing our premium transportation service.</p>
</div>
@endsection
';
    }

    // ADMIN EMAIL TEMPLATES - Clean Professional Format

    private function getAdminNewBookingTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #1a1a1a; color: white; padding: 20px; }
        .content { padding: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
        .alert { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 10px 20px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - New Booking Alert</h2>
    </div>
    
    <div class="content">
        <div class="alert">
            <strong>New booking received - Driver assignment required</strong>
        </div>
        
        <table class="info-table">
            <tr>
                <td>Booking Number</td>
                <td>{{booking_number}}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{customer_name}}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{customer_email}}</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>{{customer_phone}}</td>
            </tr>
            <tr>
                <td>Pickup Date/Time</td>
                <td>{{pickup_date}} at {{pickup_time}}</td>
            </tr>
            <tr>
                <td>Pickup Address</td>
                <td>{{pickup_address}}</td>
            </tr>
            <tr>
                <td>Dropoff Address</td>
                <td>{{dropoff_address}}</td>
            </tr>
            <tr>
                <td>Vehicle Type</td>
                <td>{{vehicle_type}}</td>
            </tr>
            <tr>
                <td>Estimated Fare</td>
                <td>{{estimated_fare}}</td>
            </tr>
            <tr>
                <td>Special Instructions</td>
                <td>{{special_instructions}}</td>
            </tr>
            <tr>
                <td>Payment Status</td>
                <td>{{payment_status}}</td>
            </tr>
        </table>
        
        <p><a href="{{booking_url}}" class="button">View in Admin Panel</a></p>
    </div>
</body>
</html>
';
    }

    private function getAdminUpcomingBookingTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #ff9800; color: white; padding: 20px; }
        .content { padding: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
        .alert { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 10px 20px; background: #1a1a1a; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - ⏰ Upcoming Booking - 2 Hours</h2>
    </div>
    
    <div class="content">
        <div class="alert">
            <strong>Action Required: Ensure driver is assigned and ready</strong>
        </div>
        
        <table class="info-table">
            <tr>
                <td>Booking Number</td>
                <td>{{booking_number}}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{customer_name}}</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>{{customer_phone}}</td>
            </tr>
            <tr>
                <td>Pickup Time</td>
                <td>{{pickup_time}}</td>
            </tr>
            <tr>
                <td>Pickup Address</td>
                <td>{{pickup_address}}</td>
            </tr>
            <tr>
                <td>Dropoff Address</td>
                <td>{{dropoff_address}}</td>
            </tr>
            <tr>
                <td>Vehicle Type</td>
                <td>{{vehicle_type}}</td>
            </tr>
            <tr>
                <td>Driver Status</td>
                <td>Check assignment</td>
            </tr>
        </table>
        
        <p><strong>Checklist:</strong></p>
        <ul>
            <li>✓ Driver assigned and confirmed</li>
            <li>✓ Vehicle ready and clean</li>
            <li>✓ Driver has customer contact info</li>
            <li>✓ Route planned</li>
        </ul>
        
        <p><a href="{{booking_url}}" class="button">Manage Booking</a></p>
    </div>
</body>
</html>
';
    }

    private function getAdminBookingCancelledTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #dc3545; color: white; padding: 20px; }
        .content { padding: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
        .alert { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - Booking Cancelled</h2>
    </div>
    
    <div class="content">
        <div class="alert">
            <strong>Cancellation Reason: {{cancellation_reason}}</strong>
        </div>
        
        <table class="info-table">
            <tr>
                <td>Booking Number</td>
                <td>{{booking_number}}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{customer_name}}</td>
            </tr>
            <tr>
                <td>Original Date</td>
                <td>{{pickup_date}}</td>
            </tr>
            <tr>
                <td>Original Time</td>
                <td>{{pickup_time}}</td>
            </tr>
            <tr>
                <td>Refund Status</td>
                <td>Check if refund needed</td>
            </tr>
        </table>
        
        <p><strong>Action Items:</strong></p>
        <ul>
            <li>Notify assigned driver (if applicable)</li>
            <li>Process refund if payment was captured</li>
            <li>Update schedule</li>
        </ul>
    </div>
</body>
</html>
';
    }

    private function getAdminPaymentCapturedTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #28a745; color: white; padding: 20px; }
        .content { padding: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
        .success { background: #d4edda; border: 1px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - 💰 Payment Captured Successfully</h2>
    </div>
    
    <div class="content">
        <div class="success">
            <strong>Payment processed successfully</strong>
        </div>
        
        <table class="info-table">
            <tr>
                <td>Amount</td>
                <td><strong>{{transaction_amount}}</strong></td>
            </tr>
            <tr>
                <td>Transaction ID</td>
                <td>{{transaction_id}}</td>
            </tr>
            <tr>
                <td>Date</td>
                <td>{{transaction_date}}</td>
            </tr>
            <tr>
                <td>Booking Number</td>
                <td>{{booking_number}}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{customer_name}}</td>
            </tr>
            <tr>
                <td>Service Date</td>
                <td>{{pickup_date}}</td>
            </tr>
        </table>
    </div>
</body>
</html>
';
    }

    private function getAdminPaymentFailedTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #dc3545; color: white; padding: 20px; }
        .content { padding: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
        .alert { background: #f8d7da; border: 1px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - ⚠️ Payment Failed</h2>
    </div>
    
    <div class="content">
        <div class="alert">
            <strong>Payment processing failed - Customer follow-up required</strong>
        </div>
        
        <table class="info-table">
            <tr>
                <td>Booking Number</td>
                <td>{{booking_number}}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{customer_name}}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{customer_email}}</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>{{customer_phone}}</td>
            </tr>
            <tr>
                <td>Amount</td>
                <td>{{estimated_fare}}</td>
            </tr>
            <tr>
                <td>Service Date</td>
                <td>{{pickup_date}}</td>
            </tr>
        </table>
        
        <p><strong>Required Actions:</strong></p>
        <ul>
            <li>Contact customer immediately</li>
            <li>Request updated payment information</li>
            <li>Consider booking status if payment not resolved</li>
        </ul>
        
        <p><a href="{{booking_url}}" class="button">View Booking</a></p>
    </div>
</body>
</html>
';
    }

    private function getAdminRefundTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #17a2b8; color: white; padding: 20px; }
        .content { padding: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 30%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - Refund Processed</h2>
    </div>
    
    <div class="content">
        <table class="info-table">
            <tr>
                <td>Refund Amount</td>
                <td><strong>{{refund_amount}}</strong></td>
            </tr>
            <tr>
                <td>Reason</td>
                <td>{{refund_reason}}</td>
            </tr>
            <tr>
                <td>Booking Number</td>
                <td>{{booking_number}}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{customer_name}}</td>
            </tr>
            <tr>
                <td>Original Amount</td>
                <td>{{final_fare}}</td>
            </tr>
            <tr>
                <td>Processing Date</td>
                <td>{{transaction_date}}</td>
            </tr>
        </table>
        
        <p>Refund will appear on customer\'s statement within 5-7 business days.</p>
    </div>
</body>
</html>
';
    }

    private function getAdminDailySummaryTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #1a1a1a; color: white; padding: 20px; }
        .content { padding: 20px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #1a1a1a; }
        .stat-label { color: #666; margin-top: 5px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - Daily Summary - {{date}}</h2>
    </div>
    
    <div class="content">
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number">{{total_bookings}}</div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{completed_trips}}</div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{cancelled_trips}}</div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{total_revenue}}</div>
                <div class="stat-label">Revenue</div>
            </div>
        </div>
        
        <p>This is an automated daily summary. For detailed reports, please log in to the admin panel.</p>
    </div>
</body>
</html>
';
    }

    private function getAdminWeeklySummaryTemplate(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #1a1a1a; color: white; padding: 20px; }
        .content { padding: 20px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #1a1a1a; }
        .stat-label { color: #666; margin-top: 5px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table th { background: #f8f9fa; padding: 10px; text-align: left; }
        .info-table td { padding: 10px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LuxRide - Weekly Summary - Week of {{date}}</h2>
    </div>
    
    <div class="content">
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number">{{total_bookings}}</div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{completed_trips}}</div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">{{total_revenue}}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        
        <h3>Performance Metrics</h3>
        <table class="info-table">
            <tr>
                <th>Metric</th>
                <th>This Week</th>
                <th>Last Week</th>
                <th>Change</th>
            </tr>
            <tr>
                <td>Bookings</td>
                <td>{{total_bookings}}</td>
                <td>{{last_week_bookings}}</td>
                <td>{{booking_change}}%</td>
            </tr>
            <tr>
                <td>Revenue</td>
                <td>{{total_revenue}}</td>
                <td>{{last_week_revenue}}</td>
                <td>{{revenue_change}}%</td>
            </tr>
            <tr>
                <td>Avg Fare</td>
                <td>{{avg_fare}}</td>
                <td>{{last_week_avg_fare}}</td>
                <td>{{fare_change}}%</td>
            </tr>
        </table>
        
        <p>For detailed analytics and reports, please access the admin dashboard.</p>
    </div>
</body>
</html>
';
    }
}