<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplatesEnhancedSeeder extends Seeder
{
    public function run()
    {
        // Booking Confirmation - HTML Template
        EmailTemplate::updateOrCreate(
            ['slug' => 'booking-confirmation-html'],
            [
                'name' => 'Booking Confirmation (HTML)',
                'category' => 'customer',
                'subject' => 'Booking Confirmed - {{booking_number}}',
                'body' => '',
                'html_body' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 40px; text-align: center;">
            <h1 style="color: #ffffff; font-size: 28px; font-weight: 300; letter-spacing: 3px; margin: 0; text-transform: uppercase;">{{company_name}}</h1>
            <p style="color: #888; font-size: 12px; letter-spacing: 2px; margin-top: 8px; text-transform: uppercase;">Premium Transportation Service</p>
        </div>
        
        <!-- Content -->
        <div style="padding: 40px;">
            <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Dear {{customer_first_name}},</h2>
            
            <p style="color: #666; font-size: 16px; line-height: 1.6;">
                Your booking has been confirmed! We look forward to providing you with exceptional service.
            </p>
            
            <!-- Booking Details Box -->
            <div style="background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 8px; padding: 24px; margin: 30px 0;">
                <h3 style="color: #333; font-size: 18px; margin-top: 0;">Booking Details</h3>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-size: 14px;">Booking Number:</td>
                        <td style="padding: 10px 0; color: #333; font-size: 14px; font-weight: bold;">{{booking_number}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-size: 14px;">Pickup Date:</td>
                        <td style="padding: 10px 0; color: #333; font-size: 14px;">{{pickup_date}} at {{pickup_time}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-size: 14px;">From:</td>
                        <td style="padding: 10px 0; color: #333; font-size: 14px;">{{pickup_address}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-size: 14px;">To:</td>
                        <td style="padding: 10px 0; color: #333; font-size: 14px;">{{dropoff_address}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-size: 14px;">Vehicle:</td>
                        <td style="padding: 10px 0; color: #333; font-size: 14px;">{{vehicle_type}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #666; font-size: 14px;">Estimated Fare:</td>
                        <td style="padding: 10px 0; color: #333; font-size: 14px; font-weight: bold;">${{estimated_fare}}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Call to Action -->
            <div style="text-align: center; margin: 40px 0;">
                <a href="{{booking_url}}" style="display: inline-block; background-color: #1a1a1a; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 4px; font-size: 16px;">View Booking</a>
            </div>
            
            <!-- Info Alert -->
            <div style="background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 30px 0;">
                <p style="color: #1976d2; font-weight: bold; margin: 0 0 5px 0;">Important Information</p>
                <p style="color: #666; font-size: 14px; margin: 0;">
                    Your driver will contact you 15 minutes before arrival. Please ensure your phone is available.
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="background-color: #f9f9f9; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
            <p style="color: #999; font-size: 12px; margin: 5px 0;">
                © {{current_year}} {{company_name}}. All rights reserved.
            </p>
            <p style="color: #999; font-size: 12px; margin: 5px 0;">
                {{company_phone}} | {{company_email}}
            </p>
        </div>
    </div>
</body>
</html>
                ',
                'css_styles' => '',
                'template_type' => 'html',
                'description' => 'HTML template for booking confirmation emails',
                'is_active' => true,
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'send_timing_type' => 'immediate',
                'trigger_events' => ['booking.confirmed'],
                'available_variables' => array_keys(EmailTemplate::getAvailableVariables()),
                'priority' => 100,
            ]
        );

        // Welcome Email - Components Based
        EmailTemplate::updateOrCreate(
            ['slug' => 'welcome-email-components'],
            [
                'name' => 'Welcome Email (Component Based)',
                'category' => 'customer',
                'subject' => 'Welcome to {{company_name}}!',
                'body' => 'Welcome email using components',
                'html_body' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Using header component -->
        <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 40px; text-align: center;">
            <h1 style="color: #ffffff; font-size: 28px; font-weight: 300; letter-spacing: 3px; margin: 0; text-transform: uppercase;">{{company_name}}</h1>
            <p style="color: #888; font-size: 12px; letter-spacing: 2px; margin-top: 8px; text-transform: uppercase;">Premium Transportation Service</p>
        </div>
        
        <!-- Using greeting component -->
        <div style="padding: 40px;">
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 24px; font-weight: 300; color: #1a1a1a; margin: 0 0 16px 0; letter-spacing: 0.5px;">Welcome {{customer_first_name}}!</h2>
                <p style="font-size: 15px; line-height: 1.8; color: #4a4a4a; margin: 0;">
                    Thank you for choosing {{company_name}} for your luxury transportation needs. We are committed to providing you with exceptional service and comfort on every journey.
                </p>
            </div>
            
            <!-- Using two-column component -->
            <table style="width: 100%; margin: 30px 0;">
                <tr>
                    <td style="width: 50%; padding-right: 15px; vertical-align: top;">
                        <h3 style="font-size: 16px; color: #333; margin: 0 0 10px 0;">Why Choose Us?</h3>
                        <p style="font-size: 14px; line-height: 1.6; color: #666; margin: 0;">
                            Professional drivers, luxury vehicles, and 24/7 customer support ensure your journey is always comfortable and stress-free.
                        </p>
                    </td>
                    <td style="width: 50%; padding-left: 15px; vertical-align: top;">
                        <h3 style="font-size: 16px; color: #333; margin: 0 0 10px 0;">Book with Confidence</h3>
                        <p style="font-size: 14px; line-height: 1.6; color: #666; margin: 0;">
                            Easy online booking, transparent pricing, and instant confirmation make planning your travel simple and convenient.
                        </p>
                    </td>
                </tr>
            </table>
            
            <!-- Using button components -->
            <div style="text-align: center; margin: 40px 0;">
                <a href="{{booking_url}}" style="display: inline-block; background-color: #1a1a1a; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; padding: 16px 40px; border-radius: 4px;">Book Your First Ride</a>
            </div>
            
            <!-- Using divider component -->
            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
            
            <!-- Using info alert component -->
            <div style="background-color: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 8px; padding: 16px 20px; margin: 24px 0;">
                <p style="font-size: 14px; font-weight: 600; color: #2e7d32; margin: 0 0 8px 0;">Special Offer!</p>
                <p style="font-size: 14px; color: #4a4a4a; line-height: 1.6; margin: 0;">
                    Use code WELCOME10 for 10% off your first booking!
                </p>
            </div>
        </div>
        
        <!-- Using footer component -->
        <div style="background-color: #fafafa; padding: 32px 40px; text-align: center; border-top: 1px solid #f0f0f0;">
            <div style="margin: 0 0 24px 0;">
                <a href="{{booking_url}}" style="color: #888; text-decoration: none; font-size: 13px; margin: 0 12px;">Book a Ride</a>
                <a href="{{support_url}}" style="color: #888; text-decoration: none; font-size: 13px; margin: 0 12px;">Support</a>
            </div>
            <p style="font-size: 12px; color: #aaa; margin: 8px 0; line-height: 1.6;">
                © {{current_year}} {{company_name}}. All rights reserved.<br>
                {{company_phone}}
            </p>
        </div>
    </div>
</body>
</html>
                ',
                'template_type' => 'html',
                'template_components' => [
                    'header' => 'simple',
                    'greeting' => 'greeting',
                    'content' => 'two_column',
                    'buttons' => ['primary'],
                    'alerts' => ['success'],
                    'footer' => 'complete',
                ],
                'description' => 'Welcome email built with reusable components',
                'is_active' => true,
                'send_to_customer' => true,
                'send_timing_type' => 'immediate',
                'trigger_events' => ['customer.registered'],
                'available_variables' => array_keys(EmailTemplate::getAvailableVariables()),
                'priority' => 90,
            ]
        );

        // Receipt Email - Mixed Template
        EmailTemplate::updateOrCreate(
            ['slug' => 'payment-receipt'],
            [
                'name' => 'Payment Receipt',
                'category' => 'customer',
                'subject' => 'Receipt for Booking {{booking_number}}',
                'body' => '@extends(\'emails.luxe-layout\')

@section(\'content\')
    <h2 class="greeting">Thank you for your payment!</h2>
    
    <div class="content">
        <p>Dear {{customer_name}},</p>
        <p>We have successfully received your payment for booking {{booking_number}}.</p>
    </div>
    
    <div class="info-box">
        <h3 class="info-box-title">Payment Details</h3>
        <div class="info-row">
            <span class="info-label">Amount Paid:</span>
            <span class="info-value">${{final_fare}}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Date:</span>
            <span class="info-value">{{payment_date}}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Transaction ID:</span>
            <span class="info-value">{{transaction_id}}</span>
        </div>
    </div>
    
    <div class="button-container">
        <a href="{{receipt_url}}" class="button">Download Receipt</a>
    </div>
@endsection',
                'template_type' => 'blade',
                'description' => 'Payment receipt using Blade template',
                'is_active' => true,
                'send_to_customer' => true,
                'send_timing_type' => 'immediate',
                'trigger_events' => ['payment.captured'],
                'available_variables' => array_keys(EmailTemplate::getAvailableVariables()),
                'priority' => 95,
            ]
        );
    }
}