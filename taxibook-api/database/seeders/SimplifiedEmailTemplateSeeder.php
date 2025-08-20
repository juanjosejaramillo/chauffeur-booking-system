<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class SimplifiedEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing templates to avoid duplicates
        EmailTemplate::truncate();
        
        $templates = [
            // ========================================
            // IMMEDIATE EMAILS (Event-triggered)
            // ========================================
            
            // 1. Booking Created - sent when booking is created with pending status
            [
                'name' => 'New Booking Pending (Admin)',
                'description' => 'Sent to admin when new booking is created (pending status)',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'subject' => 'NEW BOOKING PENDING - {{booking_number}} | {{customer_name}}',
                'body' => $this->getBookingCreatedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getBookingCreatedHtml(),
                'attach_booking_details' => true,
                'trigger_events' => ['booking.created'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 2. Booking Confirmation - sent when payment is authorized
            [
                'name' => 'Booking Confirmation',
                'description' => 'Sent immediately when booking is confirmed with payment',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Booking Confirmed - {{booking_number}} | {{company_name}}',
                'body' => $this->getBookingConfirmationTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getBookingConfirmationHtml(),
                'attach_booking_details' => true,
                'trigger_events' => ['booking.confirmed'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 3. Trip Started - sent when trip starts
            [
                'name' => 'Trip Started',
                'description' => 'Sent immediately when trip starts',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Your trip has started - {{booking_number}}',
                'body' => $this->getTripStartedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getTripStartedHtml(),
                'trigger_events' => ['trip.started'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 4. Trip Completed - sent when trip ends
            [
                'name' => 'Trip Completed & Receipt',
                'description' => 'Sent immediately when trip is completed',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Thank you for riding with {{company_name}} - Receipt #{{booking_number}}',
                'body' => $this->getTripCompletedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getTripCompletedHtml(),
                'attach_receipt' => true,
                'trigger_events' => ['booking.completed'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 5. Booking Modified - sent when booking details change
            [
                'name' => 'Booking Modified',
                'description' => 'Sent immediately when booking is modified',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Booking Updated - {{booking_number}}',
                'body' => $this->getBookingModifiedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getBookingModifiedHtml(),
                'trigger_events' => ['booking.modified'],
                'send_timing_type' => 'immediate',
                'priority' => 9,
                'is_active' => true,
            ],
            
            // 6. Booking Cancelled - sent when booking is cancelled
            [
                'name' => 'Booking Cancelled',
                'description' => 'Sent immediately when booking is cancelled',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Booking Cancelled - {{booking_number}}',
                'body' => $this->getBookingCancelledTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getBookingCancelledHtml(),
                'trigger_events' => ['booking.cancelled'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // ========================================
            // SCHEDULED EMAILS (Time-based)
            // ========================================
            
            // 7. 24-Hour Reminder - sent 24 hours before pickup
            [
                'name' => '24 Hour Reminder',
                'description' => 'Sent 24 hours before pickup time - no trigger events needed',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Tomorrow: {{vehicle_type}} at {{pickup_time}}',
                'body' => $this->get24HourReminderTemplate(),
                'template_type' => 'html',
                'html_body' => $this->get24HourReminderHtml(),
                'trigger_events' => [], // NO TRIGGERS - purely time-based
                'send_timing_type' => 'before_pickup',
                'send_timing_value' => 24,
                'send_timing_unit' => 'hours',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 8. 2-Hour Reminder - sent 2 hours before pickup
            [
                'name' => '2 Hour Reminder',
                'description' => 'Sent 2 hours before pickup time - no trigger events needed',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Your ride arrives in 2 hours - {{booking_number}}',
                'body' => $this->get2HourReminderTemplate(),
                'template_type' => 'html',
                'html_body' => $this->get2HourReminderHtml(),
                'trigger_events' => [], // NO TRIGGERS - purely time-based
                'send_timing_type' => 'before_pickup',
                'send_timing_value' => 2,
                'send_timing_unit' => 'hours',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 9. Follow-up Email - sent 1 day after trip completion
            [
                'name' => 'Trip Follow-up',
                'description' => 'Sent 1 day after trip completion for feedback',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'How was your ride with {{company_name}}?',
                'body' => $this->getFollowupTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getFollowupHtml(),
                'trigger_events' => [], // NO TRIGGERS - purely time-based
                'send_timing_type' => 'after_completion',
                'send_timing_value' => 24,
                'send_timing_unit' => 'hours',
                'priority' => 5,
                'is_active' => true,
            ],
            
            // ========================================
            // ADMIN NOTIFICATION EMAILS
            // ========================================
            
            // 10. New Booking Alert - immediate admin notification
            [
                'name' => 'New Booking Alert',
                'description' => 'Alert admin immediately when new booking is created',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'subject' => 'New Booking: {{booking_number}} - {{pickup_date}} {{pickup_time}}',
                'body' => $this->getAdminNewBookingTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getAdminNewBookingHtml(),
                'trigger_events' => ['booking.confirmed'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 11. Booking Cancelled Alert - immediate admin notification
            [
                'name' => 'Booking Cancelled Alert',
                'description' => 'Alert admin immediately when booking is cancelled',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'subject' => 'CANCELLED: {{booking_number}} - {{customer_name}}',
                'body' => $this->getAdminCancelledTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getAdminCancelledHtml(),
                'trigger_events' => ['booking.cancelled'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 12. Payment Captured - sent when payment is captured
            [
                'name' => 'Payment Captured',
                'description' => 'Sent when payment is successfully captured',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Payment Received - {{booking_number}}',
                'body' => $this->getPaymentCapturedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getPaymentCapturedHtml(),
                'trigger_events' => ['payment.captured'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 13. Payment Refunded - sent when payment is refunded
            [
                'name' => 'Payment Refunded',
                'description' => 'Sent when payment is refunded',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Refund Processed - {{booking_number}}',
                'body' => $this->getPaymentRefundedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getPaymentRefundedHtml(),
                'trigger_events' => ['payment.refunded'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }

    // ========================================
    // TEMPLATE CONTENT METHODS
    // ========================================

    private function getLuxeEmailWrapper(string $title, string $tagline, string $content): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        
        body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; }
        
        .email-wrapper { background-color: #f8f9fa; padding: 40px 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
        
        .email-header { background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 40px; text-align: center; }
        .logo { color: #ffffff; font-size: 28px; font-weight: 300; letter-spacing: 3px; margin: 0; text-transform: uppercase; }
        .tagline { color: #888; font-size: 12px; letter-spacing: 2px; margin-top: 8px; text-transform: uppercase; }
        
        .email-body { padding: 48px 40px; }
        .greeting { font-size: 24px; font-weight: 300; color: #1a1a1a; margin: 0 0 24px 0; letter-spacing: 0.5px; }
        .content { font-size: 15px; line-height: 1.8; color: #4a4a4a; margin: 0 0 32px 0; }
        .content p { margin: 0 0 16px 0; }
        
        .info-box { background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 8px; padding: 24px; margin: 32px 0; }
        .info-box-title { font-size: 13px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 16px 0; }
        .info-row { display: table; width: 100%; padding: 12px 0; border-bottom: 1px solid #e8e8e8; }
        .info-row:last-child { border-bottom: none; }
        .info-label { display: table-cell; width: 40%; font-size: 14px; color: #888; padding-right: 16px; }
        .info-value { display: table-cell; width: 60%; font-size: 14px; color: #1a1a1a; font-weight: 500; }
        
        .highlight-box { background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%); border-left: 3px solid #1a1a1a; padding: 20px 24px; margin: 32px 0; }
        .highlight-label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px 0; }
        .highlight-value { font-size: 20px; color: #1a1a1a; font-weight: 500; margin: 0; }
        
        .alert-box { border-radius: 8px; padding: 16px 20px; margin: 24px 0; }
        .alert-box.info { background-color: #e3f2fd; border: 1px solid #bbdefb; }
        .alert-box.success { background-color: #e8f5e9; border: 1px solid #c8e6c9; }
        .alert-box.warning { background-color: #fff3e0; border: 1px solid #ffe0b2; }
        .alert-title { font-size: 14px; font-weight: 600; color: #1a1a1a; margin: 0 0 8px 0; }
        .alert-content { font-size: 14px; color: #4a4a4a; line-height: 1.6; }
        
        .button-container { text-align: center; margin: 40px 0; }
        .button { display: inline-block; background-color: #1a1a1a; color: #ffffff !important; text-decoration: none; font-size: 14px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; padding: 16px 40px; border-radius: 4px; margin: 0 8px; }
        .button:hover { background-color: #2d2d2d; }
        .button.secondary { background-color: transparent; color: #1a1a1a !important; border: 1px solid #1a1a1a; }
        .button.secondary:hover { background-color: #1a1a1a; color: #ffffff !important; }
        
        .email-footer { background-color: #fafafa; padding: 32px 40px; text-align: center; border-top: 1px solid #f0f0f0; }
        .footer-links { margin: 0 0 24px 0; }
        .footer-links a { color: #888; text-decoration: none; font-size: 13px; margin: 0 12px; }
        .footer-links a:hover { color: #1a1a1a; }
        .footer-text { font-size: 12px; color: #aaa; margin: 8px 0; line-height: 1.6; }
        
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; border-radius: 0; }
            .email-body { padding: 32px 24px; }
            .button { display: block; width: 100%; margin: 8px 0 !important; }
            .info-label, .info-value { display: block; width: 100%; }
            .info-label { margin-bottom: 4px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="logo">{{company_name}}</h1>
                <p class="tagline">' . $tagline . '</p>
            </div>
            
            <div class="email-body">
                ' . $content . '
            </div>
            
            <div class="email-footer">
                <div class="footer-links">
                    <a href="{{website_url}}/booking">Book a Ride</a>
                    <a href="{{website_url}}/support">Support</a>
                    <a href="{{website_url}}/terms">Terms</a>
                </div>
                
                <p class="footer-text">
                    © {{current_year}} {{company_name}}. All rights reserved.<br>
                    {{company_address}} | {{company_phone}}
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
    }

    private function getBookingCreatedTemplate(): string
    {
        return 'NEW BOOKING PENDING APPROVAL

BOOKING: {{booking_number}}
STATUS: Pending Payment Authorization

CUSTOMER INFORMATION:
Name: {{customer_name}}
Email: {{customer_email}}
Phone: {{customer_phone}}

TRIP DETAILS:
Date: {{pickup_date}}
Time: {{pickup_time}}
Vehicle: {{vehicle_type}}
Estimated Fare: {{estimated_fare}}

ROUTE:
From: {{pickup_address}}
To: {{dropoff_address}}
Distance: {{estimated_distance}}
Duration: {{estimated_duration}}

Flight Number: {{field_flight_number}}
Number of Bags: {{field_number_of_bags}}
Child Seats: {{field_child_seats_required}}
Meet & Greet: {{field_meet_and_greet_service}}
Special Occasion: {{field_special_occasion}}

Special Instructions: {{special_instructions}}

Please monitor this booking for payment authorization.';
    }

    private function getBookingCreatedHtml(): string
    {
        $content = '<h1 class="greeting">New Booking Pending</h1>

<div class="content">
    <p>A new booking has been created and is awaiting payment authorization.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #fef3c7; border-left-color: #f59e0b;">
    <div class="highlight-label" style="color: #92400e;">PENDING BOOKING</div>
    <div class="highlight-value" style="font-size: 36px; letter-spacing: 8px; color: #d97706;">{{booking_number}}</div>
    <div style="color: #92400e; font-size: 14px; margin-top: 10px;">Awaiting Payment Authorization</div>
</div>

<div class="info-box">
    <div class="info-box-title">Customer Information</div>
    <div class="info-row">
        <div class="info-label">Name</div>
        <div class="info-value">{{customer_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Email</div>
        <div class="info-value"><a href="mailto:{{customer_email}}" style="color: #6366f1;">{{customer_email}}</a></div>
    </div>
    <div class="info-row">
        <div class="info-label">Phone</div>
        <div class="info-value"><a href="tel:{{customer_phone}}" style="color: #6366f1;">{{customer_phone}}</a></div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Date & Time</div>
        <div class="info-value">{{pickup_date}} at {{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Estimated Fare</div>
        <div class="info-value" style="font-weight: bold; color: #059669;">{{estimated_fare}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Distance</div>
        <div class="info-value">{{estimated_distance}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Duration</div>
        <div class="info-value">{{estimated_duration}}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Route Information</div>
    <div class="info-row">
        <div class="info-label">Pickup Location</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Dropoff Location</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Additional Information</div>
    <div class="info-row">
        <div class="info-label">Flight Number</div>
        <div class="info-value">{{field_flight_number_display}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Number of Bags</div>
        <div class="info-value">{{field_number_of_bags_display}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Child Seats Required</div>
        <div class="info-value">{{field_child_seats_required_display}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Meet & Greet Service</div>
        <div class="info-value">{{field_meet_and_greet_service_display}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Special Occasion</div>
        <div class="info-value">{{field_special_occasion_display}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Special Instructions</div>
        <div class="info-value">{{special_instructions}}</div>
    </div>
</div>

<div class="alert-box warning" style="background: #fef3c7; border-left-color: #f59e0b;">
    <div class="alert-title" style="color: #92400e;">Action Required</div>
    <div class="alert-content" style="color: #78350f;">
        This booking is pending payment authorization. The customer will complete payment to confirm the booking.
    </div>
</div>

<div class="button-container">
    <a href="{{admin_url}}/bookings" class="button">View All Bookings</a>
</div>';

        return $this->getLuxeEmailWrapper('New Booking Pending', 'Admin Notification', $content);
    }

    private function getBookingConfirmationTemplate(): string
    {
        return 'Dear {{customer_first_name}},

Your booking has been confirmed!

CONFIRMATION: {{booking_number}}
Date: {{pickup_date}}
Time: {{pickup_time}}
Vehicle: {{vehicle_type}}

From: {{pickup_address}}
To: {{dropoff_address}}

{{#if has_additional_fields}}
YOUR PREFERENCES:
{{#if field_flight_number}}Flight: {{field_flight_number}}{{/if}}
{{#if field_number_of_bags}}Luggage: {{field_number_of_bags}}{{/if}}
{{#if field_child_seats_display}}Child Seats: {{field_child_seats_display}}{{/if}}
{{#if field_meet_and_greet}}✓ Meet & Greet Service{{/if}}
{{/if}}

Total: {{estimated_fare}}

We look forward to serving you.

{{company_name}}
{{company_phone}}';
    }

    private function getBookingConfirmationHtml(): string
    {
        $content = '<h1 class="greeting">Hello {{customer_first_name}},</h1>

<div class="content">
    <p>Thank you for choosing {{company_name}}. Your booking has been confirmed!</p>
</div>

<div class="highlight-box" style="text-align: center;">
    <div class="highlight-label">Your confirmation number is:</div>
    <div class="highlight-value" style="font-size: 36px; letter-spacing: 8px;">{{booking_number}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Date & Time</div>
        <div class="info-value">{{pickup_date}} at {{pickup_time}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Pickup</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Dropoff</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Total Fare</div>
        <div class="info-value">{{estimated_fare}}</div>
    </div>
</div>

{{#if has_additional_fields}}
<div class="info-box">
    <div class="info-box-title">Your Preferences</div>
    {{#if field_flight_number}}
    <div class="info-row">
        <div class="info-label">Flight Number</div>
        <div class="info-value">{{field_flight_number}}</div>
    </div>
    {{/if}}
    {{#if field_number_of_bags}}
    <div class="info-row">
        <div class="info-label">Luggage</div>
        <div class="info-value">{{field_number_of_bags}}</div>
    </div>
    {{/if}}
    {{#if field_child_seats_display}}
    <div class="info-row">
        <div class="info-label">Child Seats</div>
        <div class="info-value">{{field_child_seats_display}}</div>
    </div>
    {{/if}}
    {{#if field_meet_and_greet}}
    <div class="info-row">
        <div class="info-label">Meet & Greet</div>
        <div class="info-value">Yes, at baggage claim</div>
    </div>
    {{/if}}
</div>
{{/if}}

<div class="alert-box success">
    <div class="alert-title">What Happens Next?</div>
    <div class="alert-content">
        We\'ll send you reminders 24 hours and 2 hours before your pickup. Your professional driver will arrive 5 minutes before your scheduled time.
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Booking</a>
    <a href="{{receipt_url}}" class="button secondary">Download Receipt</a>
</div>';

        return $this->getLuxeEmailWrapper('Booking Confirmed', 'Premium Transportation Service', $content);
    }

    private function get24HourReminderTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your ride is tomorrow!

⏰ {{pickup_time}}
📍 {{pickup_address}}
🚗 {{vehicle_type}}

Your driver will arrive 5 minutes early.
See you tomorrow!

{{company_name}}';
    }

    private function get24HourReminderHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Your ride is tomorrow! We\'re all set for your premium transportation service.</p>
</div>

<div class="highlight-box" style="text-align: center;">
    <div class="highlight-label">Your pickup is scheduled for</div>
    <div class="highlight-value" style="font-size: 28px;">Tomorrow at {{pickup_time}}</div>
    <div style="color: #888; font-size: 14px; margin-top: 10px;">Your driver will arrive 5 minutes early</div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
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
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Full Details</a>
</div>';

        return $this->getLuxeEmailWrapper('24 Hour Reminder', 'Premium Transportation Service', $content);
    }

    private function get2HourReminderTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your driver will arrive in 2 hours!

Time: {{pickup_time}}
Location: {{pickup_address}}
Vehicle: {{vehicle_type}}

Your driver will arrive 5 minutes before pickup time.

{{company_name}}';
    }

    private function get2HourReminderHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Your driver will arrive in 2 hours! Please be ready for your pickup.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #ffebee; border-left-color: #ef4444;">
    <div class="highlight-label" style="color: #dc2626;">Time Remaining</div>
    <div class="highlight-value" style="font-size: 48px; color: #dc2626;">2 HOURS</div>
    <div style="color: #991b1b; font-size: 14px; margin-top: 10px;">Until your pickup at {{pickup_time}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Quick Reminder</div>
    <div class="info-row">
        <div class="info-label">Pickup Location</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Driver Arrival</div>
        <div class="info-value">5 minutes before {{pickup_time}}</div>
    </div>
</div>

<div class="alert-box info">
    <div class="alert-title">Please Be Ready</div>
    <div class="alert-content">
        Your driver will arrive 5 minutes before your scheduled pickup time. Please be ready at your pickup location.
    </div>
</div>';

        return $this->getLuxeEmailWrapper('2 Hour Reminder', 'Premium Transportation Service', $content);
    }

    private function getTripStartedTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your trip has started!

Booking: {{booking_number}}
Vehicle: {{vehicle_type}}

We hope you enjoy your ride.

{{company_name}}';
    }

    private function getTripStartedHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Your trip has started! We hope you enjoy your ride with {{company_name}}.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #e8f5e9; border-left-color: #10b981;">
    <div class="highlight-label" style="color: #059669;">Trip In Progress</div>
    <div class="highlight-value" style="font-size: 28px; color: #059669;">{{booking_number}}</div>
    <div style="color: #059669; font-size: 14px; margin-top: 10px;">Safe travels!</div>
</div>

<div class="info-box">
    <div class="info-box-title">Trip Information</div>
    <div class="info-row">
        <div class="info-label">From</div>
        <div class="info-value">{{pickup_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">To</div>
        <div class="info-value">{{dropoff_address}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
</div>';

        return $this->getLuxeEmailWrapper('Trip Started', 'Premium Transportation Service', $content);
    }

    private function getTripCompletedTemplate(): string
    {
        return 'Thank you {{customer_first_name}}!

Your trip is complete.
Total Fare: {{final_fare}}

Receipt attached.
We hope to serve you again soon!

{{company_name}}';
    }

    private function getTripCompletedHtml(): string
    {
        $content = '<h1 class="greeting">Thank you {{customer_first_name}}!</h1>

<div class="content">
    <p>We hope you enjoyed your ride with {{company_name}}. Your trip has been completed successfully.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #e8f5e9; border-left-color: #10b981;">
    <div class="highlight-label" style="color: #059669;">Total Fare Charged</div>
    <div class="highlight-value" style="font-size: 36px; color: #059669;">{{final_fare}}</div>
</div>

<div class="button-container">
    <a href="{{receipt_url}}" class="button">Download Receipt</a>
</div>

<div class="alert-box info">
    <div class="alert-title">Book Your Next Ride</div>
    <div class="alert-content">
        We\'d love to serve you again! Call {{company_phone}} or email {{company_email}} to book your next premium transportation.
    </div>
</div>';

        return $this->getLuxeEmailWrapper('Trip Completed', 'Premium Transportation Service', $content);
    }

    private function getBookingModifiedTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your booking has been updated.

New Details:
Date: {{pickup_date}}
Time: {{pickup_time}}
From: {{pickup_address}}
To: {{dropoff_address}}

Confirmation: {{booking_number}}

{{company_name}}';
    }

    private function getBookingModifiedHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Your booking has been successfully updated. Please review the updated details below.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #e3f2fd; border-left-color: #2196f3;">
    <div class="highlight-label" style="color: #1565c0;">Confirmation Number</div>
    <div class="highlight-value" style="color: #1976d2;">{{booking_number}}</div>
    <div style="color: #1976d2; font-size: 14px; margin-top: 10px;">Changes Confirmed</div>
</div>

<div class="info-box">
    <div class="info-box-title">Updated Trip Details</div>
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
</div>';

        return $this->getLuxeEmailWrapper('Booking Updated', 'Premium Transportation Service', $content);
    }

    private function getBookingCancelledTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your booking {{booking_number}} has been cancelled.

{{#if refund_amount}}
Refund of {{refund_amount}} will be processed within 3-5 business days.
{{/if}}

We hope to serve you in the future.

{{company_name}}';
    }

    private function getBookingCancelledHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Your booking has been cancelled as requested.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #ffebee; border-left-color: #ef4444;">
    <div class="highlight-label" style="color: #dc2626;">Booking Cancelled</div>
    <div class="highlight-value" style="color: #dc2626;">{{booking_number}}</div>
</div>

{{#if refund_amount}}
<div class="alert-box success">
    <div class="alert-title">Refund Information</div>
    <div class="alert-content">
        A refund of {{refund_amount}} will be processed within 3-5 business days.
    </div>
</div>
{{/if}}';

        return $this->getLuxeEmailWrapper('Booking Cancelled', 'Premium Transportation Service', $content);
    }

    private function getFollowupTemplate(): string
    {
        return 'Hi {{customer_first_name}},

How was your recent ride with {{company_name}}?

We hope you had a great experience and would love to hear your feedback.

Thank you for choosing us for your transportation needs.

{{company_name}}';
    }

    private function getFollowupHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>How was your recent ride with {{company_name}}? We hope you had a great experience!</p>
    <p>Your feedback helps us continue providing excellent service.</p>
</div>

<div class="alert-box info">
    <div class="alert-title">Book Your Next Ride</div>
    <div class="alert-content">
        Ready for your next premium transportation experience? Call {{company_phone}} or visit our website to book again.
    </div>
</div>';

        return $this->getLuxeEmailWrapper('How Was Your Ride?', 'Premium Transportation Service', $content);
    }

    private function getAdminNewBookingTemplate(): string
    {
        return 'NEW BOOKING ALERT

Booking: {{booking_number}}
Customer: {{customer_name}}
Email: {{customer_email}}
Phone: {{customer_phone}}

Trip Details:
Date: {{pickup_date}}
Time: {{pickup_time}}
From: {{pickup_address}}
To: {{dropoff_address}}
Vehicle: {{vehicle_type}}
Fare: {{estimated_fare}}

View in admin: {{admin_url}}/bookings/{{booking_id}}';
    }

    private function getAdminNewBookingHtml(): string
    {
        $content = '<h1 class="greeting">New Booking Alert</h1>

<div class="highlight-box" style="text-align: center; background: #fff3e0; border-left-color: #f59e0b;">
    <div class="highlight-label" style="color: #92400e;">Booking Reference</div>
    <div class="highlight-value" style="font-size: 28px; color: #f59e0b;">{{booking_number}}</div>
    <div style="color: #92400e; font-size: 14px; margin-top: 10px;">{{pickup_date}} at {{pickup_time}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Customer Information</div>
    <div class="info-row">
        <div class="info-label">Name</div>
        <div class="info-value">{{customer_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Email</div>
        <div class="info-value">{{customer_email}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Phone</div>
        <div class="info-value">{{customer_phone}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{admin_url}}/bookings/{{booking_id}}" class="button">View Full Details</a>
</div>';

        return $this->getLuxeEmailWrapper('New Booking Alert', 'Admin Notification', $content);
    }

    private function getAdminCancelledTemplate(): string
    {
        return 'BOOKING CANCELLED

Booking: {{booking_number}}
Customer: {{customer_name}} ({{customer_phone}})

Original Trip:
Date: {{pickup_date}} at {{pickup_time}}
Route: {{pickup_address}} to {{dropoff_address}}
Fare: {{estimated_fare}}

{{#if refund_amount}}
Refund Required: {{refund_amount}}
{{/if}}

Action Required: Process refund if applicable
View details: {{admin_url}}/bookings/{{booking_id}}';
    }

    private function getAdminCancelledHtml(): string
    {
        $content = '<h1 class="greeting">Booking Cancellation Alert</h1>

<div class="highlight-box" style="text-align: center; background: #ffebee; border-left-color: #ef4444;">
    <div class="highlight-label" style="color: #dc2626;">CANCELLED BOOKING</div>
    <div class="highlight-value" style="font-size: 28px; color: #dc2626;">{{booking_number}}</div>
</div>

{{#if refund_amount}}
<div class="alert-box warning">
    <div class="alert-title">Refund Required</div>
    <div class="alert-content">
        <strong>Amount to Refund: {{refund_amount}}</strong><br>
        Please process this refund immediately through your payment system.
    </div>
</div>
{{/if}}

<div class="button-container">
    <a href="{{admin_url}}/bookings/{{booking_id}}" class="button">View Booking Details</a>
</div>';

        return $this->getLuxeEmailWrapper('Booking Cancelled', 'Admin Notification', $content);
    }

    private function getPaymentCapturedTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your payment has been successfully processed.

Booking: {{booking_number}}
Amount Charged: {{final_fare}}

Thank you for your business!

{{company_name}}';
    }

    private function getPaymentCapturedHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Your payment has been successfully processed for your recent trip.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #e8f5e9; border-left-color: #10b981;">
    <div class="highlight-label" style="color: #059669;">Payment Successful</div>
    <div class="highlight-value" style="font-size: 36px; color: #059669;">{{final_fare}}</div>
    <div style="color: #059669; font-size: 14px; margin-top: 10px;">Booking #{{booking_number}}</div>
</div>

<div class="info-box">
    <div class="info-box-title">Payment Details</div>
    <div class="info-row">
        <div class="info-label">Booking Number</div>
        <div class="info-value">{{booking_number}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Amount Charged</div>
        <div class="info-value">{{final_fare}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Date</div>
        <div class="info-value">{{current_date}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{receipt_url}}" class="button">Download Receipt</a>
</div>';

        return $this->getLuxeEmailWrapper('Payment Captured', 'Premium Transportation Service', $content);
    }

    private function getPaymentRefundedTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your refund has been processed.

Booking: {{booking_number}}
Refund Amount: {{refund_amount}}

The refund will appear in your account within 3-5 business days.

{{company_name}}';
    }

    private function getPaymentRefundedHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Your refund has been successfully processed.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #e3f2fd; border-left-color: #2196f3;">
    <div class="highlight-label" style="color: #1565c0;">Refund Processed</div>
    <div class="highlight-value" style="font-size: 36px; color: #1976d2;">{{refund_amount}}</div>
    <div style="color: #1976d2; font-size: 14px; margin-top: 10px;">Booking #{{booking_number}}</div>
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
        <div class="info-value">3-5 business days</div>
    </div>
</div>

<div class="alert-box info">
    <div class="alert-title">Refund Information</div>
    <div class="alert-content">
        Your refund of {{refund_amount}} has been initiated and will appear in your account within 3-5 business days, depending on your bank\'s processing time.
    </div>
</div>';

        return $this->getLuxeEmailWrapper('Payment Refunded', 'Premium Transportation Service', $content);
    }
}