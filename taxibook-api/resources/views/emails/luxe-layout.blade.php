<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Chauffeur Service')</title>
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
        body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
        
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
                <h1 class="logo">{{ $company_name ?? config('business.name', 'LuxRide') }}</h1>
                <p class="tagline">Premium Transportation Service</p>
            </div>
            
            <!-- Body -->
            <div class="email-body">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-links">
                    <a href="https://book.luxridesuv.com">Book a Ride</a>
                    <a href="https://luxridesuv.com/contact/">Contact Us</a>
                </div>
                
                <p class="footer-text">
                    © {{ date('Y') }} {{ $company_name ?? config('business.name', 'LuxRide') }}. All rights reserved.<br>
                    This email was sent to you regarding your booking with our service.
                </p>
                
                <p class="footer-text">
                    {{ config('app.company_address', 'Florida, USA') }}<br>
                    {{ config('business.phone', '') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>