<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class ComprehensiveEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing templates to avoid duplicates
        EmailTemplate::truncate();
        
        $templates = [
            // ========================================
            // CUSTOMER JOURNEY EMAILS
            // ========================================
            
            // 1. Booking Confirmation
            [
                'name' => 'Booking Confirmation',
                'description' => 'Sent immediately after booking is confirmed with payment authorization',
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
            
            // 2. 24-Hour Reminder
            [
                'name' => '24 Hour Reminder',
                'description' => 'Reminder sent 24 hours before pickup with all preferences',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Tomorrow: {{vehicle_type}} at {{pickup_time}}',
                'body' => $this->get24HourReminderTemplate(),
                'template_type' => 'html',
                'html_body' => $this->get24HourReminderHtml(),
                'trigger_events' => ['booking.reminder.24h'],
                'send_timing_type' => 'before_pickup',
                'send_timing_value' => 24,
                'send_timing_unit' => 'hours',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 3. 2-Hour Reminder
            [
                'name' => '2 Hour Reminder',
                'description' => 'Final reminder 2 hours before pickup',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Your ride arrives in 2 hours - {{booking_number}}',
                'body' => $this->get2HourReminderTemplate(),
                'template_type' => 'html',
                'html_body' => $this->get2HourReminderHtml(),
                'trigger_events' => ['booking.reminder.2h'],
                'send_timing_type' => 'before_pickup',
                'send_timing_value' => 2,
                'send_timing_unit' => 'hours',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 4. Driver Assigned
            [
                'name' => 'Driver Assigned',
                'description' => 'Notification when driver is assigned with their details',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Your driver {{driver_name}} has been assigned',
                'body' => $this->getDriverAssignedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getDriverAssignedHtml(),
                'trigger_events' => ['driver.assigned'],
                'send_timing_type' => 'immediate',
                'priority' => 9,
                'is_active' => true,
            ],
            
            // 5. Driver En Route
            [
                'name' => 'Driver En Route',
                'description' => 'Notification when driver starts journey to pickup',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Your driver is on the way',
                'body' => $this->getDriverEnRouteTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getDriverEnRouteHtml(),
                'trigger_events' => ['driver.enroute'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 6. Driver Arrived
            [
                'name' => 'Driver Arrived',
                'description' => 'Notification when driver arrives at pickup location',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Your driver has arrived',
                'body' => 'Your driver has arrived at {{pickup_address}}. Vehicle: {{driver_vehicle}}, License Plate: {{driver_plate}}',
                'trigger_events' => ['driver.arrived'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 7. Trip Completed & Receipt
            [
                'name' => 'Trip Completed & Receipt',
                'description' => 'Thank you message with receipt after trip completion',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Thank you for riding with {{company_name}} - Receipt #{{booking_number}}',
                'body' => $this->getTripCompletedTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getTripCompletedHtml(),
                'attach_receipt' => true,
                'trigger_events' => ['trip.completed', 'payment.captured'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 8. Booking Modified
            [
                'name' => 'Booking Modified',
                'description' => 'Confirmation of booking changes',
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
            
            // 9. Booking Cancelled
            [
                'name' => 'Booking Cancelled',
                'description' => 'Cancellation confirmation for customer',
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
            
            // 10. Refund Processed
            [
                'name' => 'Refund Processed',
                'description' => 'Refund confirmation for customer',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Refund Processed - {{refund_amount}}',
                'body' => 'Your refund of {{refund_amount}} has been processed for booking {{booking_number}}. It may take 3-5 business days to appear in your account.',
                'trigger_events' => ['payment.refunded'],
                'send_timing_type' => 'immediate',
                'priority' => 9,
                'is_active' => true,
            ],
            
            // ========================================
            // ADMIN NOTIFICATION EMAILS
            // ========================================
            
            // 11. New Booking Alert
            [
                'name' => 'New Booking Alert',
                'description' => 'Alert admin of new bookings',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'subject' => 'New Booking: {{booking_number}} - {{pickup_date}} {{pickup_time}}',
                'body' => $this->getAdminNewBookingTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getAdminNewBookingHtml(),
                'trigger_events' => ['booking.created', 'booking.confirmed'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 12. Booking Cancelled Alert
            [
                'name' => 'Booking Cancelled Alert',
                'description' => 'Alert admin when booking is cancelled',
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
            
            // 13. Payment Failed Alert
            [
                'name' => 'Payment Failed Alert',
                'description' => 'Alert admin of payment failures',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'subject' => 'PAYMENT FAILED: {{booking_number}}',
                'body' => 'Payment failed for booking {{booking_number}}. Customer: {{customer_name}} ({{customer_email}}). Amount: {{estimated_fare}}. Please follow up immediately.',
                'trigger_events' => ['payment.failed'],
                'send_timing_type' => 'immediate',
                'priority' => 10,
                'is_active' => true,
            ],
            
            // 14. Daily Summary
            [
                'name' => 'Daily Summary Report',
                'description' => 'Daily summary of bookings and revenue',
                'send_to_customer' => false,
                'send_to_admin' => true,
                'subject' => 'Daily Summary - {{date}}',
                'body' => 'Daily summary report for {{date}}. Total bookings: {{total_bookings}}. Total revenue: {{total_revenue}}. View full report in admin dashboard.',
                'trigger_events' => ['admin.daily_summary'],
                'send_timing_type' => 'immediate',
                'priority' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    private function getLuxeEmailWrapper(string $title, string $tagline, string $content): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset styles */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        
        /* Remove default styling */
        body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; }
        
        /* Main styles */
        .email-wrapper {
            background-color: #f8f9fa;
            padding: 40px 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 3px;
            margin: 0;
            text-transform: uppercase;
        }
        
        .tagline {
            color: #888;
            font-size: 12px;
            letter-spacing: 2px;
            margin-top: 8px;
            text-transform: uppercase;
        }
        
        /* Content */
        .email-body {
            padding: 48px 40px;
        }
        
        .greeting {
            font-size: 24px;
            font-weight: 300;
            color: #1a1a1a;
            margin: 0 0 24px 0;
            letter-spacing: 0.5px;
        }
        
        .content {
            font-size: 15px;
            line-height: 1.8;
            color: #4a4a4a;
            margin: 0 0 32px 0;
        }
        
        .content p {
            margin: 0 0 16px 0;
        }
        
        /* Info Box */
        .info-box {
            background-color: #fafafa;
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            padding: 24px;
            margin: 32px 0;
        }
        
        .info-box-title {
            font-size: 13px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 16px 0;
        }
        
        .info-row {
            display: table;
            width: 100%;
            padding: 12px 0;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-size: 14px;
            color: #888;
            padding-right: 16px;
        }
        
        .info-value {
            display: table-cell;
            width: 60%;
            font-size: 14px;
            color: #1a1a1a;
            font-weight: 500;
        }
        
        /* Highlight Box */
        .highlight-box {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            border-left: 3px solid #1a1a1a;
            padding: 20px 24px;
            margin: 32px 0;
        }
        
        .highlight-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 8px 0;
        }
        
        .highlight-value {
            font-size: 20px;
            color: #1a1a1a;
            font-weight: 500;
            margin: 0;
        }
        
        /* Alert Boxes */
        .alert-box {
            border-radius: 8px;
            padding: 16px 20px;
            margin: 24px 0;
        }
        
        .alert-box.info {
            background-color: #e3f2fd;
            border: 1px solid #bbdefb;
        }
        
        .alert-box.success {
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
        }
        
        .alert-box.warning {
            background-color: #fff3e0;
            border: 1px solid #ffe0b2;
        }
        
        .alert-box.error {
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
        }
        
        .alert-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 8px 0;
        }
        
        .alert-content {
            font-size: 14px;
            color: #4a4a4a;
            line-height: 1.6;
        }
        
        /* Buttons */
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button-container .button {
            margin: 0 8px;
        }
        
        .button {
            display: inline-block;
            background-color: #1a1a1a;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 16px 40px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .button:hover {
            background-color: #2d2d2d;
        }
        
        .button.secondary {
            background-color: transparent;
            color: #1a1a1a !important;
            border: 1px solid #1a1a1a;
        }
        
        .button.secondary:hover {
            background-color: #1a1a1a;
            color: #ffffff !important;
        }
        
        .secondary-button {
            display: inline-block;
            background-color: transparent;
            color: #1a1a1a !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 16px 40px;
            border: 1px solid #1a1a1a;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .secondary-button:hover {
            background-color: #1a1a1a;
            color: #ffffff !important;
        }
        
        /* Footer */
        .email-footer {
            background-color: #fafafa;
            padding: 32px 40px;
            text-align: center;
            border-top: 1px solid #f0f0f0;
        }
        
        .footer-links {
            margin: 0 0 24px 0;
        }
        
        .footer-links a {
            color: #888;
            text-decoration: none;
            font-size: 13px;
            margin: 0 12px;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #1a1a1a;
        }
        
        .footer-text {
            font-size: 12px;
            color: #aaa;
            margin: 8px 0;
            line-height: 1.6;
        }
        
        .social-links {
            margin: 24px 0 0 0;
        }
        
        .social-links a {
            display: inline-block;
            width: 32px;
            height: 32px;
            background-color: #1a1a1a;
            border-radius: 50%;
            margin: 0 8px;
            text-decoration: none;
            line-height: 32px;
            color: #ffffff;
            font-size: 14px;
        }
        
        /* Responsive */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border-radius: 0;
            }
            
            .email-body {
                padding: 32px 24px;
            }
            
            .button {
                display: block;
                width: 100%;
                margin: 8px 0 !important;
            }
            
            .button.secondary {
                margin-top: 12px !important;
            }
            
            .info-label,
            .info-value {
                display: block;
                width: 100%;
            }
            
            .info-label {
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1 class="logo">{{company_name}}</h1>
                <p class="tagline">' . $tagline . '</p>
            </div>
            
            <!-- Body -->
            <div class="email-body">
                ' . $content . '
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-links">
                    <a href="{{website_url}}/booking">Book a Ride</a>
                    <a href="{{website_url}}/support">Support</a>
                    <a href="{{website_url}}/terms">Terms</a>
                    <a href="{{website_url}}/privacy">Privacy</a>
                </div>
                
                <p class="footer-text">
                    © {{current_year}} {{company_name}}. All rights reserved.<br>
                    This email was sent to you regarding your booking with our service.
                </p>
                
                <p class="footer-text">
                    {{company_address}}<br>
                    {{company_phone}}
                </p>
            </div>
        </div>
    </div>
</body>
</html>';
    }

    // ========================================
    // CUSTOMER EMAIL TEMPLATES
    // ========================================

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
{{#if field_special_occasion_display}}Special Occasion: {{field_special_occasion_display}}{{/if}}
{{/if}}

{{#if has_special_instructions}}
Special Requests: {{special_instructions}}
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
    {{#if field_special_occasion_display}}
    <div class="info-row">
        <div class="info-label">Special Occasion</div>
        <div class="info-value">{{field_special_occasion_display}}</div>
    </div>
    {{/if}}
</div>
{{/if}}

{{#if has_special_instructions}}
<div class="highlight-box">
    <div class="highlight-label">Special Instructions</div>
    <div style="color: #4a4a4a; margin-top: 10px;">{{special_instructions}}</div>
</div>
{{/if}}

<div class="alert-box success">
    <div class="alert-title">What Happens Next?</div>
    <div class="alert-content">
        We\'ll send you a reminder 24 hours before your pickup. Your professional driver will be assigned and will arrive 5 minutes before your scheduled time.
        {{#if is_airport_transfer}}
        We\'ll track your flight in real-time for any changes.
        {{/if}}
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

{{#if has_additional_fields}}
We have your preferences ready:
{{#if field_child_seats_display}}✓ {{field_child_seats_display}}{{/if}}
{{#if field_meet_and_greet}}✓ Meet & Greet Service{{/if}}
{{#if field_number_of_bags}}✓ Space for {{field_number_of_bags}}{{/if}}
{{/if}}

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

{{#if has_additional_fields}}
<div class="alert-box success">
    <div class="alert-title">Everything is Prepared</div>
    <div class="alert-content">
        {{#if field_flight_number}}• Flight {{field_flight_number}} tracking active<br>{{/if}}
        {{#if field_child_seats_display}}• {{field_child_seats_display}} ready<br>{{/if}}
        {{#if field_meet_and_greet}}• Driver will meet you at baggage claim<br>{{/if}}
        {{#if field_number_of_bags}}• Vehicle ready for {{field_number_of_bags}}<br>{{/if}}
        {{#if field_special_occasion_display}}• Special arrangements for {{field_special_occasion_display}}{{/if}}
    </div>
</div>
{{/if}}

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Full Details</a>
</div>

<div class="alert-box warning">
    <div class="alert-title">Need to make changes?</div>
    <div class="alert-content">
        Please call us now at {{company_phone}} to modify your booking.
    </div>
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
</div>

<div class="content" style="text-align: center;">
    <p style="color: #888;">Questions? Call {{company_phone}}</p>
</div>';

        return $this->getLuxeEmailWrapper('2 Hour Reminder', 'Premium Transportation Service', $content);
    }

    private function getDriverAssignedTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your driver has been assigned!

Driver: {{driver_name}}
Vehicle: {{driver_vehicle}}
License: {{driver_plate}}

{{#if has_additional_fields}}
Your driver has been briefed on:
{{#if field_child_seats_display}}• {{field_child_seats_display}}{{/if}}
{{#if field_meet_and_greet}}• Meet & Greet service{{/if}}
{{#if field_special_occasion_display}}• {{field_special_occasion_display}}{{/if}}
{{/if}}

{{company_name}}';
    }

    private function getDriverAssignedHtml(): string
    {
        $content = '<h1 class="greeting">Hi {{customer_first_name}},</h1>

<div class="content">
    <p>Great news! Your professional driver has been assigned to your booking.</p>
</div>

<div class="highlight-box" style="text-align: center;">
    <div class="highlight-label">Your Driver</div>
    <div class="highlight-value" style="font-size: 28px;">{{driver_name}}</div>
    <div style="color: #888; font-size: 14px; margin-top: 10px;">Professional Chauffeur</div>
</div>

<div class="info-box">
    <div class="info-box-title">Vehicle Details</div>
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{driver_vehicle}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">License Plate</div>
        <div class="info-value">{{driver_plate}}</div>
    </div>
</div>

{{#if has_additional_fields}}
<div class="alert-box success">
    <div class="alert-title">Driver Briefed On Your Preferences</div>
    <div class="alert-content">
        {{#if field_child_seats_display}}• {{field_child_seats_display}} prepared<br>{{/if}}
        {{#if field_meet_and_greet}}• Will meet you at baggage claim with name sign<br>{{/if}}
        {{#if field_number_of_bags}}• Ready for {{field_number_of_bags}}<br>{{/if}}
        {{#if field_special_occasion_display}}• Aware of special occasion: {{field_special_occasion_display}}{{/if}}
    </div>
</div>
{{/if}}

<div class="content" style="text-align: center;">
    <p style="color: #888;">Your driver will arrive 5 minutes before pickup time</p>
</div>';

        return $this->getLuxeEmailWrapper('Driver Assigned', 'Premium Transportation Service', $content);
    }

    private function getDriverEnRouteTemplate(): string
    {
        return '{{customer_first_name}}, your driver is on the way!

Driver: {{driver_name}}
Vehicle: {{driver_vehicle}}
License: {{driver_plate}}
ETA: {{driver_eta}} minutes

Track your driver: {{tracking_url}}

{{company_name}}';
    }

    private function getDriverEnRouteHtml(): string
    {
        $content = '<h1 class="greeting">{{customer_first_name}}, your driver is on the way!</h1>

<div class="highlight-box" style="text-align: center; background: #fff3e0; border-left-color: #f59e0b;">
    <div class="highlight-label" style="color: #92400e;">Arriving In</div>
    <div class="highlight-value" style="font-size: 48px; color: #f59e0b;">{{driver_eta}}</div>
    <div style="color: #92400e; font-size: 14px; margin-top: 10px;">MINUTES</div>
</div>

<div class="info-box">
    <div class="info-box-title">Driver Information</div>
    <div class="info-row">
        <div class="info-label">Driver</div>
        <div class="info-value">{{driver_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Vehicle</div>
        <div class="info-value">{{driver_vehicle}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">License Plate</div>
        <div class="info-value">{{driver_plate}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{tracking_url}}" class="button">Track Driver Location</a>
</div>';

        return $this->getLuxeEmailWrapper('Driver En Route', 'Premium Transportation Service', $content);
    }

    private function getTripCompletedTemplate(): string
    {
        return 'Thank you {{customer_first_name}}!

Your trip is complete.
Total Fare: {{final_fare}}

{{#if has_additional_fields}}
We hope we met your expectations:
{{#if field_child_seats_display}}✓ {{field_child_seats_display}} provided{{/if}}
{{#if field_special_occasion_display}}✓ Made your {{field_special_occasion_display}} special{{/if}}
{{/if}}

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
</div>

{{#if has_additional_fields}}
<div class="alert-box success">
    <div class="alert-title">Services Provided</div>
    <div class="alert-content">
        {{#if field_child_seats_display}}• {{field_child_seats_display}}<br>{{/if}}
        {{#if field_meet_and_greet}}• Airport Meet & Greet Service<br>{{/if}}
        {{#if field_special_occasion_display}}• Special {{field_special_occasion_display}} Service{{/if}}
    </div>
</div>
{{/if}}

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
    <div class="info-row">
        <div class="info-label">Vehicle</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
</div>

<div class="button-container">
    <a href="{{booking_url}}" class="button">View Updated Booking</a>
</div>';

        return $this->getLuxeEmailWrapper('Booking Updated', 'Premium Transportation Service', $content);
    }

    private function getBookingCancelledTemplate(): string
    {
        return 'Hi {{customer_first_name}},

Your booking {{booking_number}} has been cancelled.

{{#if cancellation_reason}}
Reason: {{cancellation_reason}}
{{/if}}

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

<div class="info-box">
    <div class="info-box-title">Cancellation Details</div>
    <div class="info-row">
        <div class="info-label">Original Date</div>
        <div class="info-value">{{pickup_date}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Original Time</div>
        <div class="info-value">{{pickup_time}}</div>
    </div>
    {{#if cancellation_reason}}
    <div class="info-row">
        <div class="info-label">Reason</div>
        <div class="info-value">{{cancellation_reason}}</div>
    </div>
    {{/if}}
</div>

{{#if refund_amount}}
<div class="alert-box success">
    <div class="alert-title">Refund Information</div>
    <div class="alert-content">
        A refund of {{refund_amount}} will be processed within 3-5 business days.
    </div>
</div>
{{/if}}

<div class="content" style="text-align: center;">
    <p>We\'re sorry to see this booking cancelled.</p>
    <p style="color: #888;">Need to book again? Call {{company_phone}}</p>
</div>';

        return $this->getLuxeEmailWrapper('Booking Cancelled', 'Premium Transportation Service', $content);
    }

    // ========================================
    // ADMIN EMAIL TEMPLATES
    // ========================================

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

{{#if has_additional_fields}}
Customer Preferences:
{{#if field_flight_number}}Flight: {{field_flight_number}}{{/if}}
{{#if field_child_seats_display}}Child Seats: {{field_child_seats_display}}{{/if}}
{{#if field_meet_and_greet}}Meet & Greet: Required{{/if}}
{{#if field_special_occasion_display}}Occasion: {{field_special_occasion_display}}{{/if}}
{{/if}}

{{#if has_special_instructions}}
Special Instructions: {{special_instructions}}
{{/if}}

View in admin: {{admin_url}}/bookings/{{booking_id}}';
    }

    private function getAdminNewBookingHtml(): string
    {
        $content = '<h1 class="greeting">New Booking Alert</h1>

<div class="content">
    <p>A new booking has been received and requires your attention.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #fff3e0; border-left-color: #f59e0b;">
    <div class="highlight-label" style="color: #92400e;">Booking Reference</div>
    <div class="highlight-value" style="font-size: 28px; color: #f59e0b;">{{booking_number}}</div>
    <div style="color: #92400e; font-size: 14px; margin-top: 10px;">{{pickup_date}} at {{pickup_time}}</div>
</div>

<div class="info-box" style="background: #e3f2fd; border: 1px solid #90caf9;">
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

<div class="info-box">
    <div class="info-box-title">Trip Details</div>
    <div class="info-row">
        <div class="info-label">Date & Time</div>
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
    <div class="info-row">
        <div class="info-label">Vehicle Type</div>
        <div class="info-value">{{vehicle_type}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Estimated Fare</div>
        <div class="info-value" style="font-weight: 600; color: #059669;">{{estimated_fare}}</div>
    </div>
</div>

{{#if has_additional_fields}}
<div class="alert-box warning">
    <div class="alert-title">Customer Preferences</div>
    <div class="alert-content">
        {{#if field_flight_number}}• Flight: {{field_flight_number}}<br>{{/if}}
        {{#if field_child_seats_display}}• Child Seats: {{field_child_seats_display}}<br>{{/if}}
        {{#if field_meet_and_greet}}• Meet & Greet Service Required<br>{{/if}}
        {{#if field_special_occasion_display}}• Special Occasion: {{field_special_occasion_display}}<br>{{/if}}
        {{#if field_number_of_bags}}• Luggage: {{field_number_of_bags}}{{/if}}
    </div>
</div>
{{/if}}

{{#if has_special_instructions}}
<div class="highlight-box">
    <div class="highlight-label">Special Instructions</div>
    <div style="color: #4a4a4a; margin-top: 10px; font-style: italic;">{{special_instructions}}</div>
</div>
{{/if}}

<div class="button-container">
    <a href="{{admin_url}}/bookings/{{booking_id}}" class="button">View Full Details</a>
</div>

<div class="content" style="text-align: center;">
    <p style="color: #888; font-size: 12px;">This is an automated notification from your booking system.</p>
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

{{#if cancellation_reason}}
Reason: {{cancellation_reason}}
{{/if}}

{{#if refund_amount}}
Refund Required: {{refund_amount}}
{{/if}}

Action Required: Process refund if applicable
View details: {{admin_url}}/bookings/{{booking_id}}';
    }

    private function getAdminCancelledHtml(): string
    {
        $content = '<h1 class="greeting">Booking Cancellation Alert</h1>

<div class="content">
    <p>A booking has been cancelled and requires your attention.</p>
</div>

<div class="highlight-box" style="text-align: center; background: #ffebee; border-left-color: #ef4444;">
    <div class="highlight-label" style="color: #dc2626;">CANCELLED BOOKING</div>
    <div class="highlight-value" style="font-size: 28px; color: #dc2626;">{{booking_number}}</div>
</div>

<div class="info-box" style="background: #fef2f2; border: 1px solid #fecaca;">
    <div class="info-box-title">Customer Information</div>
    <div class="info-row">
        <div class="info-label">Name</div>
        <div class="info-value">{{customer_name}}</div>
    </div>
    <div class="info-row">
        <div class="info-label">Phone</div>
        <div class="info-value">{{customer_phone}}</div>
    </div>
</div>

<div class="info-box">
    <div class="info-box-title">Original Trip Details</div>
    <div class="info-row">
        <div class="info-label">Date & Time</div>
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
    <div class="info-row">
        <div class="info-label">Original Fare</div>
        <div class="info-value" style="font-weight: 600;">{{estimated_fare}}</div>
    </div>
</div>

{{#if cancellation_reason}}
<div class="alert-box warning">
    <div class="alert-title">Cancellation Reason</div>
    <div class="alert-content">{{cancellation_reason}}</div>
</div>
{{/if}}

{{#if refund_amount}}
<div class="alert-box error">
    <div class="alert-title">Refund Required</div>
    <div class="alert-content">
        <strong>Amount to Refund: {{refund_amount}}</strong><br>
        Please process this refund immediately through your payment system.
    </div>
</div>
{{/if}}

<div class="button-container">
    <a href="{{admin_url}}/bookings/{{booking_id}}" class="button">View Booking Details</a>
</div>

<div class="content" style="text-align: center;">
    <p style="color: #dc2626; font-weight: 600;">Action Required: Process refund if applicable</p>
    <p style="color: #888; font-size: 12px;">This is an automated notification from your booking system.</p>
</div>';

        return $this->getLuxeEmailWrapper('Booking Cancelled', 'Admin Notification', $content);
    }
}