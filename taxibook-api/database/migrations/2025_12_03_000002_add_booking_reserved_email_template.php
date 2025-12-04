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
        // Add the Booking Reserved email template for post_service payment mode
        EmailTemplate::firstOrCreate(
            ['name' => 'Booking Reserved - Card on File'],
            [
                'description' => 'Sent when booking is confirmed with card saved (post_service payment mode) - card not charged yet',
                'send_to_customer' => true,
                'send_to_admin' => false,
                'subject' => 'Booking Reserved - {{booking_number}} | {{company_name}}',
                'body' => $this->getPlainTextTemplate(),
                'template_type' => 'html',
                'html_body' => $this->getHtmlTemplate(),
                'attach_booking_details' => true,
                'trigger_events' => ['booking.confirmed'],
                'send_timing_type' => 'immediate',
                'priority' => 9, // Slightly lower than regular confirmation so it can be overridden
                'is_active' => false, // Disabled by default - admin enables when using post_service mode
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        EmailTemplate::where('name', 'Booking Reserved - Card on File')->delete();
    }

    private function getPlainTextTemplate(): string
    {
        return 'Dear {{customer_first_name}},

Your booking has been reserved!

CONFIRMATION: {{booking_number}}
Date: {{pickup_date}}
Time: {{pickup_time}}
Vehicle: {{vehicle_type}}

From: {{pickup_address}}
To: {{dropoff_address}}

PAYMENT INFORMATION:
Your card ending in ****{{card_last4}} is securely on file.
Estimated Fare: {{estimated_fare}}

IMPORTANT: Your card will NOT be charged now.
Payment will be processed after your ride is completed.

Please review our Cancellation Policy before your trip.

We look forward to serving you.

{{company_name}}
{{company_phone}}';
    }

    private function getHtmlTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Reserved</title>
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
                <p class="tagline">Premium Transportation Service</p>
            </div>

            <div class="email-body">
                <h1 class="greeting">Hello {{customer_first_name}},</h1>

                <div class="content">
                    <p>Thank you for choosing {{company_name}}. Your booking has been reserved!</p>
                </div>

                <div class="highlight-box" style="text-align: center;">
                    <div class="highlight-label">Your confirmation number is:</div>
                    <div class="highlight-value" style="font-size: 36px; letter-spacing: 8px;">{{booking_number}}</div>
                </div>

                <div class="alert-box info" style="background: #e3f2fd; border: 1px solid #90caf9;">
                    <div class="alert-title" style="color: #1565c0;">
                        <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 8px;" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        Card on File - Not Charged Yet
                    </div>
                    <div class="alert-content" style="color: #1976d2;">
                        Your card is securely saved. <strong>You will NOT be charged until your ride is completed.</strong><br>
                        Estimated Fare: <strong>{{estimated_fare}}</strong>
                    </div>
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
                    {{#if is_hourly_booking}}
                    <div class="info-row">
                        <div class="info-label">Service Type</div>
                        <div class="info-value">Hourly</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Duration</div>
                        <div class="info-value">{{duration_hours}} hours</div>
                    </div>
                    {{/if}}
                    <div class="info-row">
                        <div class="info-label">Pickup</div>
                        <div class="info-value">{{pickup_address}}</div>
                    </div>
                    {{#if dropoff_address}}
                    <div class="info-row">
                        <div class="info-label">Dropoff</div>
                        <div class="info-value">{{dropoff_address}}</div>
                    </div>
                    {{/if}}
                    <div class="info-row">
                        <div class="info-label">Estimated Fare</div>
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

                <div class="alert-box warning" style="background: #fff8e1; border: 1px solid #ffecb3;">
                    <div class="alert-title" style="color: #f57c00;">Cancellation Policy</div>
                    <div class="alert-content" style="color: #ef6c00;">
                        By reserving this ride, you authorize {{company_name}} to charge your card for the fare once your trip is completed. Please review our <a href="{{cancellation_policy_url}}" style="color: #e65100; text-decoration: underline;">Cancellation Policy</a> before your trip.
                    </div>
                </div>

                <div class="alert-box success">
                    <div class="alert-title">What Happens Next?</div>
                    <div class="alert-content">
                        We\'ll send you reminders before your pickup. Your professional driver will arrive 5 minutes before your scheduled time. After your ride is completed, your card will be charged and you\'ll receive a receipt.
                    </div>
                </div>

                <div class="button-container">
                    <a href="{{booking_url}}" class="button">View Booking Details</a>
                </div>
            </div>

            <div class="email-footer">
                <div class="footer-links">
                    <a href="{{website_url}}/booking">Book a Ride</a>
                    <a href="{{website_url}}/support">Support</a>
                    <a href="{{cancellation_policy_url}}">Cancellation Policy</a>
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
};
