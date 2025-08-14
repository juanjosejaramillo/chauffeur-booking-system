<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('branding.name', 'TaxiBook'))</title>
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        {{-- Reset Styles --}}
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        {{-- Base Styles with Luxury Theme --}}
        body {
            font-family: {!! config('branding.fonts.body') !!};
            line-height: 1.6;
            color: {{ config('branding.colors.charcoal') }};
            background-color: {{ config('branding.colors.light_gray') }};
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        {{-- Main Container --}}
        .email-wrapper {
            max-width: {{ config('branding.layout.max_width') }};
            margin: 0 auto;
            background-color: {{ config('branding.colors.light_gray') }};
            padding: 40px 20px;
        }
        
        .email-container {
            background-color: {{ config('branding.colors.white') }};
            border-radius: {{ config('branding.layout.border_radius') }};
            overflow: hidden;
            box-shadow: {{ config('branding.layout.shadow') }};
        }
        
        {{-- Luxury Header --}}
        .email-header {
            background: linear-gradient(135deg, {{ config('branding.colors.black') }} 0%, {{ config('branding.colors.charcoal') }} 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            border-bottom: 2px solid {{ config('branding.colors.gold') }};
        }
        
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(201, 169, 97, 0.03) 10px,
                rgba(201, 169, 97, 0.03) 20px
            );
            pointer-events: none;
        }
        
        .logo {
            font-family: {!! config('branding.fonts.display') !!};
            font-size: 36px;
            font-weight: 700;
            color: {{ config('branding.colors.gold') }};
            text-decoration: none;
            display: inline-block;
            margin-bottom: 8px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            position: relative;
        }
        
        .tagline {
            color: {{ config('branding.colors.gold_light') }};
            font-size: 13px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            font-weight: 300;
        }
        
        {{-- Email Body --}}
        .email-body {
            padding: 50px 40px;
            background-color: {{ config('branding.colors.cream') }};
        }
        
        .greeting {
            font-family: {!! config('branding.fonts.display') !!};
            font-size: 28px;
            font-weight: 400;
            color: {{ config('branding.colors.black') }};
            margin-bottom: 25px;
            letter-spacing: 0.02em;
        }
        
        .content {
            color: {{ config('branding.colors.charcoal') }};
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        
        .content p {
            margin-bottom: 16px;
        }
        
        {{-- Luxury Info Box --}}
        .info-box {
            background-color: {{ config('branding.colors.white') }};
            border: 1px solid {{ config('branding.colors.gold_light') }};
            border-radius: {{ config('branding.layout.border_radius_soft') }};
            padding: 25px;
            margin: 30px 0;
            position: relative;
        }
        
        .info-box::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 30px;
            right: 30px;
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                {{ config('branding.colors.gold') }} 50%, 
                transparent 100%);
        }
        
        .info-box-title {
            font-family: {!! config('branding.fonts.display') !!};
            font-weight: 600;
            color: {{ config('branding.colors.black') }};
            margin-bottom: 20px;
            font-size: 18px;
            letter-spacing: 0.05em;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid {{ config('branding.colors.light_gray') }};
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: {{ config('branding.colors.gray') }};
            width: 140px;
            flex-shrink: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .info-value {
            color: {{ config('branding.colors.black') }};
            flex: 1;
            font-weight: 400;
        }
        
        {{-- Luxury Highlight Box --}}
        .highlight-box {
            background: linear-gradient(135deg, {{ config('branding.colors.cream') }} 0%, {{ config('branding.colors.white') }} 100%);
            border: 2px solid {{ config('branding.colors.gold') }};
            border-radius: {{ config('branding.layout.border_radius_soft') }};
            padding: 30px;
            margin: 35px 0;
            text-align: center;
            position: relative;
        }
        
        .highlight-box::after {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 1px solid {{ config('branding.colors.gold_light') }};
            border-radius: {{ config('branding.layout.border_radius_soft') }};
            pointer-events: none;
        }
        
        .highlight-label {
            color: {{ config('branding.colors.charcoal') }};
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .highlight-value {
            font-family: {!! config('branding.fonts.display') !!};
            font-size: 36px;
            font-weight: 700;
            color: {{ config('branding.colors.gold_dark') }};
            letter-spacing: 0.1em;
        }
        
        {{-- Luxury Buttons --}}
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, {{ config('branding.colors.gold') }} 0%, {{ config('branding.colors.gold_dark') }} 100%);
            color: {{ config('branding.colors.white') }};
            text-decoration: none;
            border-radius: {{ config('branding.layout.border_radius_soft') }};
            font-weight: 500;
            font-size: 15px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(201, 169, 97, 0.3);
            transition: all 0.3s ease;
        }
        
        .button:hover {
            background: linear-gradient(135deg, {{ config('branding.colors.gold_dark') }} 0%, {{ config('branding.colors.gold') }} 100%);
            box-shadow: 0 6px 20px rgba(201, 169, 97, 0.4);
        }
        
        .secondary-button {
            display: inline-block;
            padding: 14px 32px;
            background-color: transparent;
            color: {{ config('branding.colors.gold_dark') }};
            text-decoration: none;
            border: 2px solid {{ config('branding.colors.gold') }};
            border-radius: {{ config('branding.layout.border_radius_soft') }};
            font-weight: 500;
            font-size: 14px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        
        .secondary-button:hover {
            background-color: {{ config('branding.colors.gold') }};
            color: {{ config('branding.colors.white') }};
        }
        
        {{-- Alert Boxes --}}
        .alert-box {
            background-color: {{ config('branding.colors.cream') }};
            border: 1px solid {{ config('branding.colors.gold_light') }};
            border-radius: {{ config('branding.layout.border_radius_soft') }};
            padding: 18px;
            margin: 25px 0;
            border-left: 4px solid {{ config('branding.colors.gold') }};
        }
        
        .alert-box.success {
            background-color: #D1FAE5;
            border-color: #6EE7B7;
            border-left-color: {{ config('branding.colors.success') }};
        }
        
        .alert-box.error {
            background-color: #FEE2E2;
            border-color: #FCA5A5;
            border-left-color: {{ config('branding.colors.error') }};
        }
        
        .alert-title {
            font-weight: 600;
            color: {{ config('branding.colors.charcoal') }};
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 13px;
        }
        
        .alert-content {
            color: {{ config('branding.colors.gray') }};
            font-size: 14px;
            line-height: 1.5;
        }
        
        {{-- Luxury Divider --}}
        .divider {
            height: 1px;
            background: linear-gradient(90deg, 
                transparent 0%, 
                {{ config('branding.colors.gold_light') }} 20%,
                {{ config('branding.colors.gold') }} 50%,
                {{ config('branding.colors.gold_light') }} 80%,
                transparent 100%);
            margin: 40px 0;
        }
        
        {{-- Luxury Footer --}}
        .email-footer {
            background: linear-gradient(135deg, {{ config('branding.colors.charcoal') }} 0%, {{ config('branding.colors.black') }} 100%);
            padding: 40px 30px;
            text-align: center;
            border-top: 1px solid {{ config('branding.colors.gold') }};
        }
        
        .footer-links {
            margin-bottom: 25px;
        }
        
        .footer-links a {
            color: {{ config('branding.colors.gold_light') }};
            text-decoration: none;
            margin: 0 15px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: {{ config('branding.colors.gold') }};
        }
        
        .footer-social {
            margin-bottom: 25px;
        }
        
        .social-icon {
            display: inline-block;
            width: 36px;
            height: 36px;
            background-color: transparent;
            border: 1px solid {{ config('branding.colors.gold') }};
            border-radius: 50%;
            margin: 0 8px;
            text-decoration: none;
            line-height: 34px;
            color: {{ config('branding.colors.gold') }};
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background-color: {{ config('branding.colors.gold') }};
            color: {{ config('branding.colors.black') }};
        }
        
        .footer-text {
            color: {{ config('branding.colors.gold_light') }};
            font-size: 12px;
            line-height: 1.6;
            opacity: 0.8;
        }
        
        .footer-text p {
            margin-bottom: 6px;
        }
        
        {{-- Mobile Responsive --}}
        @media (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            
            .email-header {
                padding: 30px 20px;
            }
            
            .logo {
                font-size: 28px;
            }
            
            .email-body {
                padding: 35px 25px;
            }
            
            .greeting {
                font-size: 24px;
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
            
            .footer-links a {
                display: block;
                margin: 10px 0;
            }
        }
        
        {{-- Dark Mode Support --}}
        @media (prefers-color-scheme: dark) {
            body {
                background-color: {{ config('branding.colors.black') }} !important;
            }
            
            .email-wrapper {
                background-color: {{ config('branding.colors.black') }} !important;
            }
            
            .email-container {
                background-color: {{ config('branding.colors.charcoal') }} !important;
            }
            
            .email-body {
                background-color: {{ config('branding.colors.charcoal') }} !important;
            }
            
            .greeting {
                color: {{ config('branding.colors.gold') }} !important;
            }
            
            .content {
                color: {{ config('branding.colors.light_gray') }} !important;
            }
            
            .info-box {
                background-color: {{ config('branding.colors.black') }} !important;
                border-color: {{ config('branding.colors.gold_dark') }} !important;
            }
            
            .info-box-title {
                color: {{ config('branding.colors.gold') }} !important;
            }
            
            .info-label {
                color: {{ config('branding.colors.gold_light') }} !important;
            }
            
            .info-value {
                color: {{ config('branding.colors.white') }} !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            {{-- Luxury Header --}}
            <div class="email-header">
                <a href="{{ config('branding.website_url') }}" class="logo">
                    {{ config('branding.name') }}
                </a>
                <div class="tagline">{{ config('branding.tagline') }}</div>
            </div>
            
            {{-- Email Content --}}
            <div class="email-body">
                @yield('content')
            </div>
            
            {{-- Luxury Footer --}}
            <div class="email-footer">
                @if(config('branding.features.show_support_links'))
                <div class="footer-links">
                    <a href="{{ config('branding.company.support_url') }}">Support</a>
                    <a href="{{ config('branding.company.terms_url') }}">Terms</a>
                    <a href="{{ config('branding.company.privacy_url') }}">Privacy</a>
                </div>
                @endif
                
                @if(config('branding.features.show_social_links'))
                <div class="footer-social">
                    @if(config('branding.social.facebook'))
                    <a href="{{ config('branding.social.facebook') }}" class="social-icon">f</a>
                    @endif
                    @if(config('branding.social.twitter'))
                    <a href="{{ config('branding.social.twitter') }}" class="social-icon">t</a>
                    @endif
                    @if(config('branding.social.instagram'))
                    <a href="{{ config('branding.social.instagram') }}" class="social-icon">i</a>
                    @endif
                    @if(config('branding.social.linkedin'))
                    <a href="{{ config('branding.social.linkedin') }}" class="social-icon">in</a>
                    @endif
                </div>
                @endif
                
                <div class="footer-text">
                    <p>© {{ date('Y') }} {{ config('branding.name') }}. All rights reserved.</p>
                    @if(config('branding.features.show_company_address'))
                    <p>{{ config('branding.company.address') }}</p>
                    @endif
                    <p>This is an automated message, please do not reply to this email.</p>
                    @if(config('branding.features.show_unsubscribe') && isset($unsubscribe_url))
                    <p style="margin-top: 10px;">
                        <a href="{{ $unsubscribe_url }}" style="color: {{ config('branding.colors.gold_light') }}; opacity: 0.7; text-decoration: underline;">
                            Unsubscribe from these emails
                        </a>
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>