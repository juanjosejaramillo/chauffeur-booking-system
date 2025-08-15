<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Business Information
            [
                'group' => 'business',
                'key' => 'business_name',
                'display_name' => 'Business Name',
                'value' => 'LuxRide',
                'type' => 'text',
                'description' => 'Your business name',
                'order' => 1,
            ],
            [
                'group' => 'business',
                'key' => 'business_tagline',
                'display_name' => 'Business Tagline',
                'value' => 'Premium Transportation Service',
                'type' => 'text',
                'description' => 'Business tagline or slogan',
                'order' => 2,
            ],
            [
                'group' => 'business',
                'key' => 'business_address',
                'display_name' => 'Business Address',
                'value' => '123 Business Ave, Suite 100, New York, NY 10001',
                'type' => 'text',
                'description' => 'Full business address',
                'order' => 3,
            ],
            [
                'group' => 'business',
                'key' => 'business_phone',
                'display_name' => 'Business Phone',
                'value' => '1-800-LUXRIDE',
                'type' => 'tel',
                'description' => 'Main business phone number',
                'order' => 4,
            ],
            [
                'group' => 'business',
                'key' => 'business_email',
                'display_name' => 'Business Email',
                'value' => 'info@luxride.com',
                'type' => 'email',
                'description' => 'Main business email',
                'order' => 5,
            ],
            [
                'group' => 'business',
                'key' => 'support_phone',
                'display_name' => 'Support Phone',
                'value' => '',
                'type' => 'tel',
                'description' => 'Customer support phone',
                'order' => 6,
            ],
            [
                'group' => 'business',
                'key' => 'support_email',
                'display_name' => 'Support Email',
                'value' => '',
                'type' => 'email',
                'description' => 'Customer support email',
                'order' => 7,
            ],
            [
                'group' => 'business',
                'key' => 'website_url',
                'display_name' => 'Website URL',
                'value' => config('app.url'),
                'type' => 'url',
                'description' => 'Business website URL',
                'order' => 8,
            ],
            
            // Stripe Settings
            [
                'group' => 'stripe',
                'key' => 'stripe_enabled',
                'display_name' => 'Stripe Enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable Stripe payments',
                'order' => 1,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_mode',
                'display_name' => 'Stripe Mode',
                'value' => 'test',
                'type' => 'text',
                'description' => 'Stripe mode (test/live)',
                'order' => 2,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_test_publishable_key',
                'display_name' => 'Test Publishable Key',
                'value' => env('STRIPE_PUBLISHABLE_KEY', ''),
                'type' => 'password',
                'description' => 'Stripe test publishable key',
                'is_encrypted' => true,
                'order' => 3,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_test_secret_key',
                'display_name' => 'Test Secret Key',
                'value' => env('STRIPE_SECRET_KEY', ''),
                'type' => 'password',
                'description' => 'Stripe test secret key',
                'is_encrypted' => true,
                'order' => 4,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_live_publishable_key',
                'display_name' => 'Live Publishable Key',
                'value' => '',
                'type' => 'password',
                'description' => 'Stripe live publishable key',
                'is_encrypted' => true,
                'order' => 5,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_live_secret_key',
                'display_name' => 'Live Secret Key',
                'value' => '',
                'type' => 'password',
                'description' => 'Stripe live secret key',
                'is_encrypted' => true,
                'order' => 6,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_webhook_secret',
                'display_name' => 'Webhook Secret',
                'value' => env('STRIPE_WEBHOOK_SECRET', ''),
                'type' => 'password',
                'description' => 'Stripe webhook secret',
                'is_encrypted' => true,
                'order' => 7,
            ],
            
            // Mapbox Settings
            [
                'group' => 'mapbox',
                'key' => 'mapbox_enabled',
                'display_name' => 'Mapbox Enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable Mapbox integration',
                'order' => 1,
            ],
            [
                'group' => 'mapbox',
                'key' => 'mapbox_public_token',
                'display_name' => 'Public Token',
                'value' => env('MAPBOX_PUBLIC_TOKEN', ''),
                'type' => 'password',
                'description' => 'Mapbox public access token',
                'is_encrypted' => true,
                'order' => 2,
            ],
            [
                'group' => 'mapbox',
                'key' => 'mapbox_secret_token',
                'display_name' => 'Secret Token',
                'value' => env('MAPBOX_SECRET_TOKEN', ''),
                'type' => 'password',
                'description' => 'Mapbox secret access token',
                'is_encrypted' => true,
                'order' => 3,
            ],
            [
                'group' => 'mapbox',
                'key' => 'mapbox_map_style',
                'display_name' => 'Map Style',
                'value' => 'mapbox://styles/mapbox/streets-v12',
                'type' => 'text',
                'description' => 'Default map style',
                'order' => 4,
            ],
            [
                'group' => 'mapbox',
                'key' => 'mapbox_default_latitude',
                'display_name' => 'Default Latitude',
                'value' => '40.7128',
                'type' => 'number',
                'description' => 'Default map center latitude',
                'order' => 5,
            ],
            [
                'group' => 'mapbox',
                'key' => 'mapbox_default_longitude',
                'display_name' => 'Default Longitude',
                'value' => '-74.0060',
                'type' => 'number',
                'description' => 'Default map center longitude',
                'order' => 6,
            ],
            [
                'group' => 'mapbox',
                'key' => 'mapbox_default_zoom',
                'display_name' => 'Default Zoom',
                'value' => '12',
                'type' => 'number',
                'description' => 'Default map zoom level',
                'order' => 7,
            ],
            
            // Email Settings
            [
                'group' => 'email',
                'key' => 'mail_from_address',
                'display_name' => 'From Address',
                'value' => 'noreply@luxride.com',
                'type' => 'email',
                'description' => 'Default from email address',
                'order' => 1,
            ],
            [
                'group' => 'email',
                'key' => 'mail_from_name',
                'display_name' => 'From Name',
                'value' => 'LuxRide',
                'type' => 'text',
                'description' => 'Default from name',
                'order' => 2,
            ],
            [
                'group' => 'email',
                'key' => 'mail_reply_to',
                'display_name' => 'Reply-To Address',
                'value' => '',
                'type' => 'email',
                'description' => 'Reply-to email address',
                'order' => 3,
            ],
            [
                'group' => 'email',
                'key' => 'email_notifications_enabled',
                'display_name' => 'Notifications Enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable email notifications',
                'order' => 4,
            ],
            [
                'group' => 'email',
                'key' => 'email_bcc_admin',
                'display_name' => 'BCC Admin',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'BCC admin on all emails',
                'order' => 5,
            ],
            
            // System Settings
            [
                'group' => 'system',
                'key' => 'app_timezone',
                'display_name' => 'Timezone',
                'value' => 'America/New_York',
                'type' => 'text',
                'description' => 'Application timezone',
                'order' => 1,
            ],
            [
                'group' => 'system',
                'key' => 'app_locale',
                'display_name' => 'Locale',
                'value' => 'en',
                'type' => 'text',
                'description' => 'Application locale',
                'order' => 2,
            ],
            [
                'group' => 'system',
                'key' => 'currency',
                'display_name' => 'Currency',
                'value' => 'USD',
                'type' => 'text',
                'description' => 'Default currency',
                'order' => 3,
            ],
            [
                'group' => 'system',
                'key' => 'maintenance_mode',
                'display_name' => 'Maintenance Mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'order' => 4,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}