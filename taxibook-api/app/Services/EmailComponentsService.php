<?php

namespace App\Services;

class EmailComponentsService
{
    /**
     * Get all available email components
     */
    public static function getComponents(): array
    {
        return [
            'headers' => self::getHeaders(),
            'footers' => self::getFooters(),
            'buttons' => self::getButtons(),
            'info_boxes' => self::getInfoBoxes(),
            'alerts' => self::getAlerts(),
            'dividers' => self::getDividers(),
            'social_links' => self::getSocialLinks(),
            'content_blocks' => self::getContentBlocks(),
        ];
    }

    /**
     * Header components
     */
    public static function getHeaders(): array
    {
        return [
            'simple' => [
                'name' => 'Simple Header',
                'html' => '
                    <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 40px; text-align: center;">
                        <h1 style="color: #ffffff; font-size: 28px; font-weight: 300; letter-spacing: 3px; margin: 0; text-transform: uppercase;">{{company_name}}</h1>
                        <p style="color: #888; font-size: 12px; letter-spacing: 2px; margin-top: 8px; text-transform: uppercase;">Premium Transportation Service</p>
                    </div>
                ',
                'variables' => ['company_name'],
            ],
            'with_logo' => [
                'name' => 'Header with Logo',
                'html' => '
                    <div style="background: #ffffff; padding: 30px; text-align: center; border-bottom: 2px solid #f0f0f0;">
                        <img src="{{logo_url}}" alt="{{company_name}}" style="max-width: 200px; height: auto;">
                        <p style="color: #666; font-size: 14px; margin-top: 10px;">{{tagline}}</p>
                    </div>
                ',
                'variables' => ['logo_url', 'company_name', 'tagline'],
            ],
            'minimal' => [
                'name' => 'Minimal Header',
                'html' => '
                    <div style="padding: 20px 0; border-bottom: 1px solid #e0e0e0; text-align: center;">
                        <h2 style="color: #333; font-size: 24px; font-weight: 400; margin: 0;">{{company_name}}</h2>
                    </div>
                ',
                'variables' => ['company_name'],
            ],
        ];
    }

    /**
     * Footer components
     */
    public static function getFooters(): array
    {
        return [
            'complete' => [
                'name' => 'Complete Footer',
                'html' => '
                    <div style="background-color: #fafafa; padding: 32px 40px; text-align: center; border-top: 1px solid #f0f0f0;">
                        <div style="margin: 0 0 24px 0;">
                            <a href="{{booking_url}}" style="color: #888; text-decoration: none; font-size: 13px; margin: 0 12px;">Book a Ride</a>
                            <a href="{{support_url}}" style="color: #888; text-decoration: none; font-size: 13px; margin: 0 12px;">Support</a>
                            <a href="{{terms_url}}" style="color: #888; text-decoration: none; font-size: 13px; margin: 0 12px;">Terms</a>
                            <a href="{{privacy_url}}" style="color: #888; text-decoration: none; font-size: 13px; margin: 0 12px;">Privacy</a>
                        </div>
                        <p style="font-size: 12px; color: #aaa; margin: 8px 0; line-height: 1.6;">
                            © {{current_year}} {{company_name}}. All rights reserved.<br>
                            {{company_address}}<br>
                            {{company_phone}}
                        </p>
                    </div>
                ',
                'variables' => ['booking_url', 'support_url', 'terms_url', 'privacy_url', 'current_year', 'company_name', 'company_address', 'company_phone'],
            ],
            'simple' => [
                'name' => 'Simple Footer',
                'html' => '
                    <div style="padding: 20px; text-align: center; color: #999; font-size: 12px;">
                        <p>© {{current_year}} {{company_name}}. All rights reserved.</p>
                        <p>{{company_phone}} | {{company_email}}</p>
                    </div>
                ',
                'variables' => ['current_year', 'company_name', 'company_phone', 'company_email'],
            ],
        ];
    }

    /**
     * Button components
     */
    public static function getButtons(): array
    {
        return [
            'primary' => [
                'name' => 'Primary Button',
                'html' => '
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{button_url}}" style="display: inline-block; background-color: #1a1a1a; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; padding: 16px 40px; border-radius: 4px;">{{button_text}}</a>
                    </div>
                ',
                'variables' => ['button_url', 'button_text'],
            ],
            'secondary' => [
                'name' => 'Secondary Button',
                'html' => '
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{button_url}}" style="display: inline-block; background-color: transparent; color: #1a1a1a; text-decoration: none; font-size: 14px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; padding: 15px 39px; border: 1px solid #1a1a1a; border-radius: 4px;">{{button_text}}</a>
                    </div>
                ',
                'variables' => ['button_url', 'button_text'],
            ],
            'success' => [
                'name' => 'Success Button',
                'html' => '
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{button_url}}" style="display: inline-block; background-color: #4CAF50; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: 500; padding: 16px 40px; border-radius: 4px;">{{button_text}}</a>
                    </div>
                ',
                'variables' => ['button_url', 'button_text'],
            ],
        ];
    }

    /**
     * Info box components
     */
    public static function getInfoBoxes(): array
    {
        return [
            'booking_details' => [
                'name' => 'Booking Details Box',
                'html' => '
                    <div style="background-color: #fafafa; border: 1px solid #f0f0f0; border-radius: 8px; padding: 24px; margin: 32px 0;">
                        <h3 style="font-size: 13px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 16px 0;">Booking Details</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #e8e8e8;">
                                <td style="padding: 12px 0; font-size: 14px; color: #888;">Booking Number:</td>
                                <td style="padding: 12px 0; font-size: 14px; color: #1a1a1a; font-weight: 500;">{{booking_number}}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e8e8e8;">
                                <td style="padding: 12px 0; font-size: 14px; color: #888;">Pickup Location:</td>
                                <td style="padding: 12px 0; font-size: 14px; color: #1a1a1a; font-weight: 500;">{{pickup_address}}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e8e8e8;">
                                <td style="padding: 12px 0; font-size: 14px; color: #888;">Dropoff Location:</td>
                                <td style="padding: 12px 0; font-size: 14px; color: #1a1a1a; font-weight: 500;">{{dropoff_address}}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e8e8e8;">
                                <td style="padding: 12px 0; font-size: 14px; color: #888;">Date & Time:</td>
                                <td style="padding: 12px 0; font-size: 14px; color: #1a1a1a; font-weight: 500;">{{pickup_date}} at {{pickup_time}}</td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 0; font-size: 14px; color: #888;">Vehicle Type:</td>
                                <td style="padding: 12px 0; font-size: 14px; color: #1a1a1a; font-weight: 500;">{{vehicle_type}}</td>
                            </tr>
                        </table>
                    </div>
                ',
                'variables' => ['booking_number', 'pickup_address', 'dropoff_address', 'pickup_date', 'pickup_time', 'vehicle_type'],
            ],
            'highlight' => [
                'name' => 'Highlight Box',
                'html' => '
                    <div style="background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%); border-left: 3px solid #1a1a1a; padding: 20px 24px; margin: 32px 0;">
                        <p style="font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px 0;">{{label}}</p>
                        <p style="font-size: 20px; color: #1a1a1a; font-weight: 500; margin: 0;">{{value}}</p>
                    </div>
                ',
                'variables' => ['label', 'value'],
            ],
        ];
    }

    /**
     * Alert components
     */
    public static function getAlerts(): array
    {
        return [
            'info' => [
                'name' => 'Info Alert',
                'html' => '
                    <div style="background-color: #e3f2fd; border: 1px solid #bbdefb; border-radius: 8px; padding: 16px 20px; margin: 24px 0;">
                        <p style="font-size: 14px; font-weight: 600; color: #1976d2; margin: 0 0 8px 0;">{{alert_title}}</p>
                        <p style="font-size: 14px; color: #4a4a4a; line-height: 1.6; margin: 0;">{{alert_content}}</p>
                    </div>
                ',
                'variables' => ['alert_title', 'alert_content'],
            ],
            'success' => [
                'name' => 'Success Alert',
                'html' => '
                    <div style="background-color: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 8px; padding: 16px 20px; margin: 24px 0;">
                        <p style="font-size: 14px; font-weight: 600; color: #2e7d32; margin: 0 0 8px 0;">{{alert_title}}</p>
                        <p style="font-size: 14px; color: #4a4a4a; line-height: 1.6; margin: 0;">{{alert_content}}</p>
                    </div>
                ',
                'variables' => ['alert_title', 'alert_content'],
            ],
            'warning' => [
                'name' => 'Warning Alert',
                'html' => '
                    <div style="background-color: #fff3e0; border: 1px solid #ffe0b2; border-radius: 8px; padding: 16px 20px; margin: 24px 0;">
                        <p style="font-size: 14px; font-weight: 600; color: #f57c00; margin: 0 0 8px 0;">{{alert_title}}</p>
                        <p style="font-size: 14px; color: #4a4a4a; line-height: 1.6; margin: 0;">{{alert_content}}</p>
                    </div>
                ',
                'variables' => ['alert_title', 'alert_content'],
            ],
        ];
    }

    /**
     * Divider components
     */
    public static function getDividers(): array
    {
        return [
            'simple' => [
                'name' => 'Simple Divider',
                'html' => '<hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">',
                'variables' => [],
            ],
            'dotted' => [
                'name' => 'Dotted Divider',
                'html' => '<hr style="border: none; border-top: 2px dotted #ccc; margin: 30px 0;">',
                'variables' => [],
            ],
            'with_text' => [
                'name' => 'Divider with Text',
                'html' => '
                    <div style="text-align: center; margin: 30px 0;">
                        <div style="display: inline-block; position: relative; width: 100%;">
                            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 0; position: absolute; top: 50%; width: 100%;">
                            <span style="background: #ffffff; padding: 0 20px; position: relative; color: #999; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">{{divider_text}}</span>
                        </div>
                    </div>
                ',
                'variables' => ['divider_text'],
            ],
        ];
    }

    /**
     * Social link components
     */
    public static function getSocialLinks(): array
    {
        return [
            'icons' => [
                'name' => 'Social Media Icons',
                'html' => '
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{facebook_url}}" style="display: inline-block; margin: 0 8px;"><img src="https://img.icons8.com/ios-filled/32/000000/facebook-new.png" alt="Facebook" style="width: 32px; height: 32px;"></a>
                        <a href="{{twitter_url}}" style="display: inline-block; margin: 0 8px;"><img src="https://img.icons8.com/ios-filled/32/000000/twitter.png" alt="Twitter" style="width: 32px; height: 32px;"></a>
                        <a href="{{instagram_url}}" style="display: inline-block; margin: 0 8px;"><img src="https://img.icons8.com/ios-filled/32/000000/instagram-new.png" alt="Instagram" style="width: 32px; height: 32px;"></a>
                        <a href="{{linkedin_url}}" style="display: inline-block; margin: 0 8px;"><img src="https://img.icons8.com/ios-filled/32/000000/linkedin.png" alt="LinkedIn" style="width: 32px; height: 32px;"></a>
                    </div>
                ',
                'variables' => ['facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url'],
            ],
        ];
    }

    /**
     * Content block components
     */
    public static function getContentBlocks(): array
    {
        return [
            'greeting' => [
                'name' => 'Greeting Block',
                'html' => '
                    <div style="margin-bottom: 30px;">
                        <h2 style="font-size: 24px; font-weight: 300; color: #1a1a1a; margin: 0 0 16px 0; letter-spacing: 0.5px;">Dear {{customer_name}},</h2>
                        <p style="font-size: 15px; line-height: 1.8; color: #4a4a4a; margin: 0;">{{greeting_message}}</p>
                    </div>
                ',
                'variables' => ['customer_name', 'greeting_message'],
            ],
            'paragraph' => [
                'name' => 'Paragraph Block',
                'html' => '
                    <p style="font-size: 15px; line-height: 1.8; color: #4a4a4a; margin: 0 0 16px 0;">{{paragraph_content}}</p>
                ',
                'variables' => ['paragraph_content'],
            ],
            'two_column' => [
                'name' => 'Two Column Layout',
                'html' => '
                    <table style="width: 100%; margin: 20px 0;">
                        <tr>
                            <td style="width: 50%; padding-right: 15px; vertical-align: top;">
                                <h3 style="font-size: 16px; color: #333; margin: 0 0 10px 0;">{{column1_title}}</h3>
                                <p style="font-size: 14px; line-height: 1.6; color: #666; margin: 0;">{{column1_content}}</p>
                            </td>
                            <td style="width: 50%; padding-left: 15px; vertical-align: top;">
                                <h3 style="font-size: 16px; color: #333; margin: 0 0 10px 0;">{{column2_title}}</h3>
                                <p style="font-size: 14px; line-height: 1.6; color: #666; margin: 0;">{{column2_content}}</p>
                            </td>
                        </tr>
                    </table>
                ',
                'variables' => ['column1_title', 'column1_content', 'column2_title', 'column2_content'],
            ],
        ];
    }

    /**
     * Get CSS for email templates
     */
    public static function getEmailCss(): string
    {
        return '
            /* Email-safe CSS */
            body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
            table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
            img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
            a { text-decoration: none; }
            
            /* Container */
            .email-wrapper { background-color: #f8f9fa; padding: 40px 20px; }
            .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }
            
            /* Typography */
            h1, h2, h3, h4, h5, h6 { margin: 0; padding: 0; }
            p { margin: 0 0 16px 0; }
            
            /* Responsive */
            @media screen and (max-width: 600px) {
                .email-container { width: 100% !important; border-radius: 0; }
                .email-body { padding: 32px 24px !important; }
                table td { display: block !important; width: 100% !important; }
            }
        ';
    }
}