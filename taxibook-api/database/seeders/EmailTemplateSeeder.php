<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Customer Email Templates
            [
                'slug' => 'booking-confirmation',
                'name' => 'Booking Confirmation',
                'category' => 'customer',
                'subject' => 'Booking Confirmed - {{booking_number}}',
                'body' => $this->getBookingConfirmationTemplate(),
                'description' => 'Sent to customer when booking is confirmed and payment is authorized',
                'attach_receipt' => false,
                'attach_booking_details' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.confirmed'],
                'priority' => 10,
                'available_variables' => $this->getCustomerVariables(),
                'is_active' => true,
            ],
            [
                'slug' => 'booking-reminder',
                'name' => 'Booking Reminder',
                'category' => 'customer',
                'subject' => 'Reminder: Your ride is tomorrow - {{booking_number}}',
                'body' => $this->getBookingReminderTemplate(),
                'description' => 'Sent 24 hours before pickup time',
                'attach_booking_details' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.reminder'],
                'priority' => 8,
                'available_variables' => $this->getCustomerVariables(),
                'is_active' => true,
            ],
            [
                'slug' => 'driver-assigned',
                'name' => 'Driver Assigned',
                'category' => 'customer',
                'subject' => 'Your driver is assigned - {{booking_number}}',
                'body' => $this->getDriverAssignedTemplate(),
                'description' => 'Sent when driver is assigned to the booking',
                'delay_minutes' => 0,
                'trigger_events' => ['driver.assigned'],
                'priority' => 9,
                'available_variables' => array_merge($this->getCustomerVariables(), $this->getDriverVariables()),
                'is_active' => true,
            ],
            [
                'slug' => 'driver-enroute',
                'name' => 'Driver En Route',
                'category' => 'customer',
                'subject' => 'Your driver is on the way - {{booking_number}}',
                'body' => $this->getDriverEnrouteTemplate(),
                'description' => 'Sent when driver starts journey to pickup location',
                'delay_minutes' => 0,
                'trigger_events' => ['driver.enroute'],
                'priority' => 10,
                'available_variables' => array_merge($this->getCustomerVariables(), $this->getDriverVariables()),
                'is_active' => true,
            ],
            [
                'slug' => 'trip-completed',
                'name' => 'Trip Completed',
                'category' => 'customer',
                'subject' => 'Thank you for riding with us - {{booking_number}}',
                'body' => $this->getTripCompletedTemplate(),
                'description' => 'Sent after trip completion',
                'attach_receipt' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['trip.completed'],
                'priority' => 9,
                'available_variables' => $this->getCustomerVariables(),
                'is_active' => true,
            ],
            [
                'slug' => 'booking-modified',
                'name' => 'Booking Modified',
                'category' => 'customer',
                'subject' => 'Your booking has been updated - {{booking_number}}',
                'body' => $this->getBookingModifiedTemplate(),
                'description' => 'Sent when booking details are changed',
                'attach_booking_details' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['booking.modified'],
                'priority' => 9,
                'available_variables' => array_merge($this->getCustomerVariables(), ['changes_summary']),
                'is_active' => true,
            ],
            [
                'slug' => 'booking-cancelled',
                'name' => 'Booking Cancelled',
                'category' => 'customer',
                'subject' => 'Booking Cancelled - {{booking_number}}',
                'body' => $this->getBookingCancelledTemplate(),
                'description' => 'Sent when booking is cancelled',
                'delay_minutes' => 0,
                'trigger_events' => ['booking.cancelled'],
                'priority' => 10,
                'available_variables' => array_merge($this->getCustomerVariables(), ['cancellation_reason']),
                'is_active' => true,
            ],
            [
                'slug' => 'payment-receipt',
                'name' => 'Payment Receipt',
                'category' => 'customer',
                'subject' => 'Payment Receipt - {{booking_number}}',
                'body' => $this->getPaymentReceiptTemplate(),
                'description' => 'Sent after successful payment capture',
                'attach_receipt' => true,
                'delay_minutes' => 0,
                'trigger_events' => ['payment.captured'],
                'priority' => 9,
                'available_variables' => array_merge($this->getCustomerVariables(), $this->getPaymentVariables()),
                'is_active' => true,
            ],
            [
                'slug' => 'refund-processed',
                'name' => 'Refund Processed',
                'category' => 'customer',
                'subject' => 'Refund Processed - {{booking_number}}',
                'body' => $this->getRefundProcessedTemplate(),
                'description' => 'Sent when refund is processed',
                'delay_minutes' => 0,
                'trigger_events' => ['payment.refunded'],
                'priority' => 10,
                'available_variables' => array_merge($this->getCustomerVariables(), ['refund_amount', 'refund_reason']),
                'is_active' => true,
            ],
            [
                'slug' => 'review-request',
                'name' => 'Review Request',
                'category' => 'customer',
                'subject' => 'How was your ride? - {{booking_number}}',
                'body' => $this->getReviewRequestTemplate(),
                'description' => 'Sent 24 hours after trip completion',
                'delay_minutes' => 1440, // 24 hours
                'trigger_events' => ['trip.completed'],
                'priority' => 3,
                'available_variables' => $this->getCustomerVariables(),
                'is_active' => true,
            ],

            // Admin Email Templates
            [
                'slug' => 'admin-new-booking',
                'name' => 'New Booking Alert (Admin)',
                'category' => 'admin',
                'subject' => 'New Booking: {{booking_number}} - {{pickup_date}}',
                'body' => $this->getAdminNewBookingTemplate(),
                'description' => 'Sent to admin when new booking is created',
                'cc_emails' => '',
                'bcc_emails' => '',
                'delay_minutes' => 0,
                'trigger_events' => ['booking.created', 'booking.confirmed'],
                'priority' => 10,
                'available_variables' => $this->getCustomerVariables(),
                'is_active' => true,
            ],
            [
                'slug' => 'admin-booking-reminder',
                'name' => 'Upcoming Booking Reminder (Admin)',
                'category' => 'admin',
                'subject' => 'Upcoming: {{booking_number}} in 2 hours',
                'body' => $this->getAdminBookingReminderTemplate(),
                'description' => 'Sent to admin 2 hours before pickup',
                'delay_minutes' => 0,
                'trigger_events' => ['booking.reminder.admin'],
                'priority' => 8,
                'available_variables' => $this->getCustomerVariables(),
                'is_active' => true,
            ],
            [
                'slug' => 'admin-payment-captured',
                'name' => 'Payment Captured (Admin)',
                'category' => 'admin',
                'subject' => 'Payment Captured: ${{final_fare}} - {{booking_number}}',
                'body' => $this->getAdminPaymentCapturedTemplate(),
                'description' => 'Sent to admin when payment is captured',
                'delay_minutes' => 0,
                'trigger_events' => ['payment.captured'],
                'priority' => 7,
                'available_variables' => array_merge($this->getCustomerVariables(), $this->getPaymentVariables()),
                'is_active' => true,
            ],
            [
                'slug' => 'admin-booking-cancelled',
                'name' => 'Booking Cancelled (Admin)',
                'category' => 'admin',
                'subject' => 'Cancelled: {{booking_number}} - {{customer_name}}',
                'body' => $this->getAdminBookingCancelledTemplate(),
                'description' => 'Sent to admin when booking is cancelled',
                'delay_minutes' => 0,
                'trigger_events' => ['booking.cancelled'],
                'priority' => 9,
                'available_variables' => array_merge($this->getCustomerVariables(), ['cancellation_reason']),
                'is_active' => true,
            ],
            [
                'slug' => 'admin-daily-summary',
                'name' => 'Daily Summary (Admin)',
                'category' => 'admin',
                'subject' => 'Daily Summary - {{date}}',
                'body' => $this->getAdminDailySummaryTemplate(),
                'description' => 'Daily summary of bookings and revenue',
                'delay_minutes' => 0,
                'trigger_events' => ['daily.summary'],
                'priority' => 5,
                'available_variables' => ['date', 'total_bookings', 'total_revenue', 'completed_trips', 'cancelled_trips'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }

    private function getCustomerVariables(): array
    {
        return [
            'booking_number', 'customer_name', 'customer_first_name', 'customer_last_name',
            'customer_email', 'customer_phone', 'pickup_address', 'dropoff_address',
            'pickup_date', 'pickup_time', 'vehicle_type', 'estimated_fare', 'final_fare',
            'special_instructions', 'booking_url', 'company_name', 'company_phone',
            'company_email', 'support_url'
        ];
    }

    private function getDriverVariables(): array
    {
        return [
            'driver_name', 'driver_phone', 'driver_vehicle', 'driver_license_plate'
        ];
    }

    private function getPaymentVariables(): array
    {
        return [
            'transaction_id', 'transaction_amount', 'transaction_date'
        ];
    }

    private function getBookingConfirmationTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Hello {{customer_first_name}},</h1>

<div class="content">
    <p>Great news! Your booking has been confirmed and your payment has been authorized.</p>
</div>

<div class="highlight-box">
    <div class="highlight-label">Your Booking Number</div>
    <div class="highlight-value">{{booking_number}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Pickup Date:</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup Time:</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup Address:</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Dropoff Address:</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type:</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Estimated Fare:</div>
        <div class="info-value">{{estimated_fare}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Booking Details</a>
</div>

<div class="alert-box">
    <div class="alert-title">Important Information</div>
    <div class="alert-content">
        Please be ready at your pickup location 5 minutes before the scheduled time. Your driver will wait up to 5 minutes after the scheduled pickup time.
    </div>
</div>

<div class="content">
    <p>If you need to make any changes to your booking, please contact us at least 2 hours before your pickup time.</p>
    <p>Thank you for choosing {{company_name}}!</p>
</div>
@endsection
';
    }

    private function getBookingReminderTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Hello {{customer_first_name}},</h1>

<div class="content">
    <p>This is a friendly reminder that your ride is scheduled for tomorrow.</p>
</div>

<div class="highlight-box">
    <div class="highlight-label">Booking Number</div>
    <div class="highlight-value">{{booking_number}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Tomorrow\'s Trip</div>
    <div class="info-row">
        <div class="info-label">Pickup Time:</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup Address:</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Dropoff Address:</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type:</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Full Details</a>
</div>

<div class="alert-box">
    <div class="alert-title">Reminder</div>
    <div class="alert-content">
        Please be ready at your pickup location 5 minutes before {{pickup_time}}.
    </div>
</div>
@endsection
';
    }

    private function getDriverAssignedTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Hello {{customer_first_name}},</h1>

<div class="content">
    <p>Your driver has been assigned for your upcoming trip.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Driver Information</div>
    <div class="info-row">
        <div class="info-label">Driver Name:</div>
        <div class="info-value">{{driver_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Phone:</div>
        <div class="info-value">{{driver_phone}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle:</div>
        <div class="info-value">{{driver_vehicle}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">License Plate:</div>
        <div class="info-value">{{driver_license_plate}}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Pickup Time:</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup Address:</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">Track Your Ride</a>
</div>
@endsection
';
    }

    private function getDriverEnrouteTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Your driver is on the way!</h1>

<div class="content">
    <p>{{driver_name}} is heading to your pickup location now.</p>
</div>

<div class="alert-box success">
    <div class="alert-title">Driver En Route</div>
    <div class="alert-content">
        Please be ready at your pickup location. Your driver will arrive soon.
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Driver Details</div>
    <div class="info-row">
        <div class="info-label">Name:</div>
        <div class="info-value">{{driver_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle:</div>
        <div class="info-value">{{driver_vehicle}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">License Plate:</div>
        <div class="info-value">{{driver_license_plate}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Contact:</div>
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
    <a href="{{booking_url}}" class="button">Track Driver Location</a>
</div>
@endsection
';
    }

    private function getTripCompletedTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Thank you for riding with us!</h1>

<div class="content">
    <p>We hope you had a pleasant journey with {{company_name}}.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Summary</div>
    <div class="info-row">
        <div class="info-label">Booking Number:</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date:</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">From:</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">To:</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Total Fare:</div>
        <div class="info-value">{{final_fare}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Receipt</a>
</div>

<div class="content">
    <p>Your receipt is attached to this email for your records.</p>
    <p>We value your feedback! Please take a moment to rate your experience.</p>
</div>
@endsection
';
    }

    private function getBookingModifiedTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Hello {{customer_first_name}},</h1>

<div class="content">
    <p>Your booking has been successfully updated.</p>
</div>

<div class="alert-box">
    <div class="alert-title">Changes Made</div>
    <div class="alert-content">
        {{changes_summary}}
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Updated Booking Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number:</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">New Pickup Time:</div>
        <div class="info-value">{{pickup_date}} at {{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup Address:</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Dropoff Address:</div>
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
@extends("emails.layout")

@section("content")
<h1 class="greeting">Booking Cancelled</h1>

<div class="content">
    <p>Your booking {{booking_number}} has been cancelled.</p>
</div>

<div class="alert-box error">
    <div class="alert-title">Cancellation Confirmed</div>
    <div class="alert-content">
        {{cancellation_reason}}
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Cancelled Booking Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number:</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Original Date:</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Original Time:</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
</div>

<div class="content">
    <p>If you paid for this booking, a refund will be processed within 5-7 business days.</p>
    <p>Need to book another ride? Visit our website or contact us.</p>
</div>

<div class="button-container">
    <a href="{{support_url}}" class="button">Book New Ride</a>
</div>
@endsection
';
    }

    private function getPaymentReceiptTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Payment Receipt</h1>

<div class="content">
    <p>Thank you for your payment. This email confirms that we have successfully processed your payment.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Payment Details</div>
    <div class="info-row">
        <div class="info-label">Transaction ID:</div>
        <div class="info-value">{{transaction_id}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Amount Paid:</div>
        <div class="info-value">{{transaction_amount}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date:</div>
        <div class="info-value">{{transaction_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Booking Number:</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
</div>

<div class="alert-box success">
    <div class="alert-title">Payment Successful</div>
    <div class="alert-content">
        Your payment has been processed successfully. A detailed receipt is attached to this email.
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Booking</a>
</div>
@endsection
';
    }

    private function getRefundProcessedTemplate(): string
    {
        return '
@extends("emails.layout")

@section("content")
<h1 class="greeting">Refund Processed</h1>

<div class="content">
    <p>We have processed a refund for your booking {{booking_number}}.</p>
</div>

<div class="alert-box success">
    <div class="alert-title">Refund Confirmed</div>
    <div class="alert-content">
        Amount refunded: {{refund_amount}}<br>
        Reason: {{refund_reason}}
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Refund Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number:</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Refund Amount:</div>
        <div class="info-value">{{refund_amount}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Processing Time:</div>
        <div class="info-value">5-7 business days</div>
    </div>
</div>

<div class="content">
    <p>The refund will appear on your original payment method within 5-7 business days.</p>
    <p>If you have any questions about this refund, please contact our support team.</p>
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
@extends("emails.layout")

@section("content")
<h1 class="greeting">How was your ride, {{customer_first_name}}?</h1>

<div class="content">
    <p>We hope you had a great experience with {{company_name}}. Your feedback helps us improve our service.</p>
</div>

<div class="info-box">
    <div class="info-box-title">Your Recent Trip</div>
    <div class="info-row">
        <div class="info-label">Date:</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">From:</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">To:</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}/review" class="button">Leave a Review</a>
</div>

<div class="content">
    <p>Your feedback is important to us and helps other customers make informed decisions.</p>
    <p>Thank you for choosing {{company_name}}!</p>
</div>
@endsection
';
    }

    private function getAdminNewBookingTemplate(): string
    {
        return '
<h2>New Booking Alert</h2>

<p>A new booking has been created.</p>

<h3>Booking Details:</h3>
<ul>
    <li><strong>Booking Number:</strong> {{booking_number}}</li>
    <li><strong>Customer:</strong> {{customer_name}}</li>
    <li><strong>Email:</strong> {{customer_email}}</li>
    <li><strong>Phone:</strong> {{customer_phone}}</li>
    <li><strong>Pickup Date:</strong> {{pickup_date}}</li>
    <li><strong>Pickup Time:</strong> {{pickup_time}}</li>
    <li><strong>Pickup Address:</strong> {{pickup_address}}</li>
    <li><strong>Dropoff Address:</strong> {{dropoff_address}}</li>
    <li><strong>Vehicle Type:</strong> {{vehicle_type}}</li>
    <li><strong>Estimated Fare:</strong> {{estimated_fare}}</li>
    <li><strong>Special Instructions:</strong> {{special_instructions}}</li>
</ul>

<p><a href="{{booking_url}}">View in Admin Panel</a></p>
';
    }

    private function getAdminBookingReminderTemplate(): string
    {
        return '
<h2>Upcoming Booking Reminder</h2>

<p>Reminder: The following booking is scheduled in 2 hours.</p>

<h3>Booking Details:</h3>
<ul>
    <li><strong>Booking Number:</strong> {{booking_number}}</li>
    <li><strong>Customer:</strong> {{customer_name}}</li>
    <li><strong>Phone:</strong> {{customer_phone}}</li>
    <li><strong>Pickup Time:</strong> {{pickup_time}}</li>
    <li><strong>Pickup Address:</strong> {{pickup_address}}</li>
    <li><strong>Dropoff Address:</strong> {{dropoff_address}}</li>
    <li><strong>Vehicle Type:</strong> {{vehicle_type}}</li>
</ul>

<p><strong>Action Required:</strong> Please ensure a driver is assigned.</p>

<p><a href="{{booking_url}}">View Booking</a></p>
';
    }

    private function getAdminPaymentCapturedTemplate(): string
    {
        return '
<h2>Payment Captured Successfully</h2>

<p>Payment has been captured for booking {{booking_number}}.</p>

<h3>Payment Details:</h3>
<ul>
    <li><strong>Amount:</strong> {{transaction_amount}}</li>
    <li><strong>Transaction ID:</strong> {{transaction_id}}</li>
    <li><strong>Date:</strong> {{transaction_date}}</li>
</ul>

<h3>Booking Details:</h3>
<ul>
    <li><strong>Customer:</strong> {{customer_name}}</li>
    <li><strong>Booking Number:</strong> {{booking_number}}</li>
    <li><strong>Service Date:</strong> {{pickup_date}}</li>
</ul>

<p><a href="{{booking_url}}">View Details</a></p>
';
    }

    private function getAdminBookingCancelledTemplate(): string
    {
        return '
<h2>Booking Cancelled</h2>

<p>Booking {{booking_number}} has been cancelled.</p>

<h3>Cancellation Details:</h3>
<ul>
    <li><strong>Reason:</strong> {{cancellation_reason}}</li>
    <li><strong>Customer:</strong> {{customer_name}}</li>
    <li><strong>Original Date:</strong> {{pickup_date}}</li>
    <li><strong>Original Time:</strong> {{pickup_time}}</li>
</ul>

<p>Please review if any refund needs to be processed.</p>

<p><a href="{{booking_url}}">View Booking</a></p>
';
    }

    private function getAdminDailySummaryTemplate(): string
    {
        return '
<h2>Daily Summary - {{date}}</h2>

<h3>Overview:</h3>
<ul>
    <li><strong>Total Bookings:</strong> {{total_bookings}}</li>
    <li><strong>Completed Trips:</strong> {{completed_trips}}</li>
    <li><strong>Cancelled Trips:</strong> {{cancelled_trips}}</li>
    <li><strong>Total Revenue:</strong> {{total_revenue}}</li>
</ul>

<p>This is an automated daily summary. For detailed reports, please log in to the admin panel.</p>
';
    }
}