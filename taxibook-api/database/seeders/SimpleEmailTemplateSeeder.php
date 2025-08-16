<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class SimpleEmailTemplateSeeder extends Seeder
{
    public function run()
    {
        // Beautiful Minimalist Booking Confirmation
        EmailTemplate::updateOrCreate(
            ['slug' => 'booking-confirmed-minimal'],
            [
                'name' => 'Booking Confirmation (Minimal)',
                'category' => 'customer',
                'subject' => 'Booking Confirmed - {{booking_number}}',
                'body' => '',
                'html_body' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f7f7f7;">
    
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f7f7f7;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #000000; padding: 40px 40px 30px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 300; letter-spacing: 2px; text-transform: uppercase;">{{company_name}}</h1>
                        </td>
                    </tr>
                    
                    <!-- Success Icon & Title -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center;">
                            <div style="display: inline-block; width: 60px; height: 60px; border-radius: 50%; background-color: #22c55e; margin-bottom: 20px; line-height: 60px; color: white; font-size: 32px;">✓</div>
                            <h2 style="margin: 0; color: #000000; font-size: 28px; font-weight: 300;">Booking Confirmed</h2>
                            <p style="margin: 10px 0 0 0; color: #666666; font-size: 14px;">Reference: {{booking_number}}</p>
                        </td>
                    </tr>
                    
                    <!-- Customer Greeting -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <p style="margin: 0; color: #333333; font-size: 16px;">Dear {{customer_first_name}},</p>
                            <p style="margin: 10px 0 0 0; color: #666666; font-size: 15px; line-height: 1.6;">
                                Your luxury transportation has been successfully booked. We look forward to providing you with an exceptional travel experience.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Booking Details Card -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fafafa; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 30px;">
                                        
                                        <!-- Date and Time -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-bottom: 25px;">
                                            <tr>
                                                <td style="text-align: center;">
                                                    <span style="color: #000000; font-size: 24px; font-weight: 300;">{{pickup_date}}</span>
                                                    <span style="color: #666666; font-size: 20px; font-weight: 300; margin-left: 10px;">at {{pickup_time}}</span>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <hr style="border: none; border-top: 1px solid #e5e5e5; margin: 20px 0;">
                                        
                                        <!-- Route Details -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding-bottom: 15px;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td width="30" valign="top">
                                                                <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #22c55e; margin-top: 5px;"></div>
                                                            </td>
                                                            <td>
                                                                <p style="margin: 0 0 3px 0; color: #999999; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Pickup</p>
                                                                <p style="margin: 0; color: #333333; font-size: 15px;">{{pickup_address}}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <td style="padding-bottom: 15px;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td width="30" valign="top">
                                                                <div style="width: 10px; height: 10px; border-radius: 50%; background-color: #ef4444; margin-top: 5px;"></div>
                                                            </td>
                                                            <td>
                                                                <p style="margin: 0 0 3px 0; color: #999999; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Dropoff</p>
                                                                <p style="margin: 0; color: #333333; font-size: 15px;">{{dropoff_address}}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <hr style="border: none; border-top: 1px solid #e5e5e5; margin: 25px 0 20px 0;">
                                        
                                        <!-- Vehicle and Fare -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td width="50%" style="padding-right: 10px;">
                                                    <p style="margin: 0 0 5px 0; color: #999999; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Vehicle</p>
                                                    <p style="margin: 0; color: #333333; font-size: 16px; font-weight: 500;">{{vehicle_type}}</p>
                                                </td>
                                                <td width="50%" style="text-align: right; padding-left: 10px;">
                                                    <p style="margin: 0 0 5px 0; color: #999999; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Estimated Fare</p>
                                                    <p style="margin: 0; color: #333333; font-size: 20px; font-weight: 500;">${{estimated_fare}}</p>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Action Button -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <a href="{{booking_url}}" style="display: inline-block; background-color: #000000; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 4px; font-size: 14px; font-weight: 500; letter-spacing: 0.5px;">VIEW BOOKING DETAILS</a>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; text-align: center; border-top: 1px solid #e5e5e5;">
                            <p style="margin: 0 0 10px 0; color: #999999; font-size: 13px;">
                                Need help? Contact us anytime
                            </p>
                            <p style="margin: 0; color: #666666; font-size: 14px;">
                                {{company_phone}} | {{company_email}}
                            </p>
                            <p style="margin: 20px 0 0 0; color: #cccccc; font-size: 12px;">
                                © {{company_name}}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>',
                'template_type' => 'html',
                'description' => 'Beautiful minimalist booking confirmation email',
                'is_active' => true,
                'send_to_customer' => true,
                'send_to_admin' => false,
                'send_to_driver' => false,
                'send_timing_type' => 'immediate',
                'trigger_events' => ['booking.confirmed'],
                'priority' => 100,
            ]
        );
    }
}