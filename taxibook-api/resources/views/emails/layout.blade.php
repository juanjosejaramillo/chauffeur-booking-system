<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TaxiBook')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1F2937;
            background-color: #F3F4F6;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #F3F4F6;
            padding: 20px;
        }
        
        .email-container {
            background-color: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }
        
        .email-header {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            padding: 30px;
            text-align: center;
        }
        
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #FFFFFF;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 20px;
        }
        
        .content {
            color: #4B5563;
            margin-bottom: 30px;
        }
        
        .content p {
            margin-bottom: 15px;
        }
        
        .info-box {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .info-box-title {
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: #6B7280;
            width: 140px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #1F2937;
            flex: 1;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%);
            border: 2px solid #7C3AED;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        
        .highlight-label {
            color: #5B21B6;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .highlight-value {
            font-size: 32px;
            font-weight: bold;
            color: #5B21B6;
            letter-spacing: 2px;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
        }
        
        .button:hover {
            background: linear-gradient(135deg, #4338CA 0%, #6D28D9 100%);
        }
        
        .secondary-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #FFFFFF;
            color: #4F46E5;
            text-decoration: none;
            border: 2px solid #4F46E5;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .alert-box {
            background-color: #FEF3C7;
            border: 1px solid #FCD34D;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .alert-box.success {
            background-color: #D1FAE5;
            border-color: #6EE7B7;
        }
        
        .alert-box.error {
            background-color: #FEE2E2;
            border-color: #FCA5A5;
        }
        
        .alert-title {
            font-weight: 600;
            color: #92400E;
            margin-bottom: 5px;
        }
        
        .alert-box.success .alert-title {
            color: #065F46;
        }
        
        .alert-box.error .alert-title {
            color: #991B1B;
        }
        
        .alert-content {
            color: #78350F;
            font-size: 14px;
        }
        
        .alert-box.success .alert-content {
            color: #047857;
        }
        
        .alert-box.error .alert-content {
            color: #B91C1C;
        }
        
        .divider {
            height: 1px;
            background-color: #E5E7EB;
            margin: 30px 0;
        }
        
        .email-footer {
            background-color: #F9FAFB;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }
        
        .footer-links {
            margin-bottom: 20px;
        }
        
        .footer-links a {
            color: #4F46E5;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .footer-social {
            margin-bottom: 20px;
        }
        
        .social-icon {
            display: inline-block;
            width: 32px;
            height: 32px;
            background-color: #E5E7EB;
            border-radius: 50%;
            margin: 0 5px;
            text-decoration: none;
            line-height: 32px;
            color: #4B5563;
        }
        
        .social-icon:hover {
            background-color: #4F46E5;
            color: #FFFFFF;
        }
        
        .footer-text {
            color: #6B7280;
            font-size: 12px;
            line-height: 1.5;
        }
        
        .footer-text p {
            margin-bottom: 5px;
        }
        
        @media (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            
            .email-header {
                padding: 20px;
            }
            
            .email-body {
                padding: 25px 20px;
            }
            
            .info-row {
                flex-direction: column;
            }
            
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .button {
                display: block;
                width: 100%;
            }
        }
        
        @media (prefers-color-scheme: dark) {
            /* Dark mode support for email clients that support it */
            body {
                background-color: #1F2937 !important;
            }
            
            .email-wrapper {
                background-color: #1F2937 !important;
            }
            
            .email-container {
                background-color: #111827 !important;
            }
            
            .email-body {
                background-color: #111827 !important;
            }
            
            .greeting {
                color: #F9FAFB !important;
            }
            
            .content {
                color: #D1D5DB !important;
            }
            
            .info-box {
                background-color: #1F2937 !important;
                border-color: #374151 !important;
            }
            
            .info-box-title {
                color: #F9FAFB !important;
            }
            
            .info-label {
                color: #9CA3AF !important;
            }
            
            .info-value {
                color: #F3F4F6 !important;
            }
            
            .email-footer {
                background-color: #1F2937 !important;
                border-color: #374151 !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <a href="{{ config('app.url') }}" class="logo">TaxiBook</a>
                <div class="tagline">Premium Chauffeur Service</div>
            </div>
            
            <div class="email-body">
                @yield('content')
            </div>
            
            <div class="email-footer">
                <div class="footer-links">
                    <a href="{{ config('app.url') }}/support">Support</a>
                    <a href="{{ config('app.url') }}/terms">Terms of Service</a>
                    <a href="{{ config('app.url') }}/privacy">Privacy Policy</a>
                </div>
                
                <div class="footer-text">
                    <p>© {{ date('Y') }} TaxiBook. All rights reserved.</p>
                    <p>{{ config('app.company_address', '123 Main Street, City, State 12345') }}</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                    @if(isset($unsubscribe_url))
                    <p style="margin-top: 10px;">
                        <a href="{{ $unsubscribe_url }}" style="color: #6B7280; text-decoration: underline;">Unsubscribe from these emails</a>
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>