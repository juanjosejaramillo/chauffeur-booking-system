<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brand Identity
    |--------------------------------------------------------------------------
    |
    | Core brand information used across all email templates and communications.
    |
    */
    
    'name' => env('BRAND_NAME', 'TaxiBook'),
    'tagline' => env('BRAND_TAGLINE', 'Premium Chauffeur Service'),
    'logo_url' => env('BRAND_LOGO_URL', null),
    'website_url' => env('APP_URL', 'https://taxibook.com'),
    
    /*
    |--------------------------------------------------------------------------
    | Color Palette
    |--------------------------------------------------------------------------
    |
    | Luxury color scheme matching the React frontend design.
    | These colors are used throughout all email templates.
    |
    */
    
    'colors' => [
        // Primary luxury colors
        'gold' => '#C9A961',
        'gold_light' => '#E4D4A8',
        'gold_dark' => '#A08A4F',
        
        // Neutral luxury colors
        'black' => '#0A0A0A',
        'charcoal' => '#1A1A1A',
        'gray' => '#2A2A2A',
        'light_gray' => '#F8F7F4',
        'cream' => '#FAF9F6',
        'white' => '#FFFFFF',
        
        // Accent colors
        'success' => '#10B981',
        'error' => '#EF4444',
        'warning' => '#F59E0B',
        'info' => '#3B82F6',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Typography
    |--------------------------------------------------------------------------
    |
    | Font families and sizes for different text elements.
    |
    */
    
    'fonts' => [
        'display' => "'Playfair Display', Georgia, serif",
        'body' => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
        'mono' => "'SF Mono', Monaco, Consolas, 'Courier New', monospace",
    ],
    
    'font_sizes' => [
        'hero' => '36px',
        'h1' => '28px',
        'h2' => '24px',
        'h3' => '20px',
        'body' => '16px',
        'small' => '14px',
        'tiny' => '12px',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Layout Settings
    |--------------------------------------------------------------------------
    |
    | Spacing, borders, and other layout configurations.
    |
    */
    
    'layout' => [
        'max_width' => '600px',
        'padding' => '40px',
        'padding_mobile' => '20px',
        'border_radius' => '0px', // Sharp edges for luxury feel
        'border_radius_soft' => '4px', // Subtle rounding for buttons
        'shadow' => '0 10px 40px rgba(0, 0, 0, 0.1)',
        'shadow_lg' => '0 20px 60px rgba(0, 0, 0, 0.15)',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Themes
    |--------------------------------------------------------------------------
    |
    | Different theme presets that can be selected.
    |
    */
    
    'themes' => [
        'luxury' => [
            'primary' => '#C9A961',
            'secondary' => '#0A0A0A',
            'background' => '#FAF9F6',
            'text' => '#1A1A1A',
            'accent' => '#A08A4F',
        ],
        'modern' => [
            'primary' => '#4F46E5',
            'secondary' => '#7C3AED',
            'background' => '#F3F4F6',
            'text' => '#1F2937',
            'accent' => '#6366F1',
        ],
        'classic' => [
            'primary' => '#1E40AF',
            'secondary' => '#1E293B',
            'background' => '#FFFFFF',
            'text' => '#334155',
            'accent' => '#2563EB',
        ],
    ],
    
    'active_theme' => env('EMAIL_THEME', 'luxury'),
    
    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | Contact details and legal information for email footers.
    |
    */
    
    'company' => [
        'address' => env('COMPANY_ADDRESS', '123 Luxury Lane, Beverly Hills, CA 90210'),
        'phone' => env('COMPANY_PHONE', '+1 (555) 123-4567'),
        'email' => env('COMPANY_EMAIL', 'support@taxibook.com'),
        'support_url' => env('SUPPORT_URL', 'https://taxibook.com/support'),
        'privacy_url' => env('PRIVACY_URL', 'https://taxibook.com/privacy'),
        'terms_url' => env('TERMS_URL', 'https://taxibook.com/terms'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Social Media
    |--------------------------------------------------------------------------
    |
    | Social media links for email footers.
    |
    */
    
    'social' => [
        'facebook' => env('SOCIAL_FACEBOOK', ''),
        'twitter' => env('SOCIAL_TWITTER', ''),
        'instagram' => env('SOCIAL_INSTAGRAM', ''),
        'linkedin' => env('SOCIAL_LINKEDIN', ''),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Email Features
    |--------------------------------------------------------------------------
    |
    | Toggle various email features and components.
    |
    */
    
    'features' => [
        'show_social_links' => true,
        'show_unsubscribe' => true,
        'show_company_address' => true,
        'show_support_links' => true,
        'enable_dark_mode' => true,
    ],
];