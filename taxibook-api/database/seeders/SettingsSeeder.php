<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
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
                'description' => 'This name will appear in all customer communications',
                'order' => 1,
            ],
            [
                'group' => 'business',
                'key' => 'business_tagline',
                'display_name' => 'Business Tagline',
                'value' => 'Premium Transportation Service',
                'type' => 'text',
                'description' => 'A short description of your business',
                'order' => 2,
            ],
            [
                'group' => 'business',
                'key' => 'business_address',
                'display_name' => 'Business Address',
                'value' => 'Florida, USA',
                'type' => 'textarea',
                'description' => 'Your full business address',
                'order' => 3,
            ],
            [
                'group' => 'business',
                'key' => 'business_phone',
                'display_name' => 'Business Phone',
                'value' => '+1-813-333-8680',
                'type' => 'tel',
                'description' => 'Main business phone number',
                'order' => 4,
            ],
            [
                'group' => 'business',
                'key' => 'business_email',
                'display_name' => 'Business Email',
                'value' => 'contact@luxridesuv.com',
                'type' => 'email',
                'description' => 'Main business email address',
                'order' => 5,
            ],
            [
                'group' => 'business',
                'key' => 'support_phone',
                'display_name' => 'Support Phone',
                'value' => '',
                'type' => 'tel',
                'description' => 'Customer support phone (leave empty to use business phone)',
                'order' => 6,
            ],
            [
                'group' => 'business',
                'key' => 'support_email',
                'display_name' => 'Support Email',
                'value' => '',
                'type' => 'email',
                'description' => 'Customer support email (leave empty to use business email)',
                'order' => 7,
            ],
            [
                'group' => 'business',
                'key' => 'website_url',
                'display_name' => 'Website URL',
                'value' => 'https://luxridesuv.com',
                'type' => 'url',
                'description' => 'Your business website URL',
                'order' => 8,
            ],
            
            // Administrative Settings
            [
                'group' => 'business',
                'key' => 'admin_email',
                'display_name' => 'Admin Email',
                'value' => 'admin@luxridesuv.com',
                'type' => 'email',
                'description' => 'Email address for receiving admin notifications (bookings, cancellations, etc.)',
                'order' => 9,
            ],
            [
                'group' => 'business',
                'key' => 'admin_name',
                'display_name' => 'Admin Name',
                'value' => 'LuxRide Administrator',
                'type' => 'text',
                'description' => 'Name to use for admin email notifications',
                'order' => 10,
            ],
            
            // Stripe Settings
            [
                'group' => 'stripe',
                'key' => 'stripe_mode',
                'display_name' => 'Stripe Mode',
                'value' => 'test',
                'type' => 'select',
                'options' => ['test' => 'Test Mode', 'live' => 'Live Mode'],
                'description' => 'Switch between test and live Stripe modes',
                'order' => 1,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_test_publishable_key',
                'display_name' => 'Test Publishable Key',
                'value' => env('STRIPE_TEST_PUBLISHABLE_KEY', ''),
                'type' => 'text',
                'description' => 'Stripe test publishable key (pk_test_...)',
                'order' => 2,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_test_secret_key',
                'display_name' => 'Test Secret Key',
                'value' => env('STRIPE_TEST_SECRET_KEY', ''),
                'type' => 'password',
                'description' => 'Stripe test secret key (sk_test_...)',
                'order' => 3,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_live_publishable_key',
                'display_name' => 'Live Publishable Key',
                'value' => env('STRIPE_LIVE_PUBLISHABLE_KEY', ''),
                'type' => 'text',
                'description' => 'Stripe live publishable key (pk_live_...)',
                'order' => 4,
            ],
            [
                'group' => 'stripe',
                'key' => 'stripe_live_secret_key',
                'display_name' => 'Live Secret Key',
                'value' => env('STRIPE_LIVE_SECRET_KEY', ''),
                'type' => 'password',
                'description' => 'Stripe live secret key (sk_live_...)',
                'order' => 5,
            ],
            
            // Mapbox Settings
            [
                'group' => 'mapbox',
                'key' => 'mapbox_access_token',
                'display_name' => 'Mapbox Access Token',
                'value' => env('MAPBOX_ACCESS_TOKEN', ''),
                'type' => 'text',
                'description' => 'Mapbox API access token for maps and geocoding',
                'order' => 1,
            ],
            
            // Booking Settings
            [
                'group' => 'booking',
                'key' => 'minimum_advance_booking_hours',
                'display_name' => 'Minimum Advance Booking Hours',
                'value' => '12',
                'type' => 'number',
                'description' => 'How many hours in advance customers must book (e.g., 2 = bookings must be at least 2 hours in the future)',
                'order' => 1,
            ],
            [
                'group' => 'booking',
                'key' => 'maximum_advance_booking_days',
                'display_name' => 'Maximum Advance Booking Days',
                'value' => '90',
                'type' => 'number',
                'description' => 'How far in advance customers can book (e.g., 90 = bookings up to 90 days in advance)',
                'order' => 2,
            ],
            [
                'group' => 'booking',
                'key' => 'allow_same_day_bookings',
                'display_name' => 'Allow Same Day Bookings',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Allow customers to book trips for the same day (still respects minimum booking hours)',
                'order' => 3,
            ],
            [
                'group' => 'booking',
                'key' => 'time_selection_increment',
                'display_name' => 'Time Selection Increment',
                'value' => '5',
                'type' => 'select',
                'options' => ['5' => '5 minutes', '10' => '10 minutes', '15' => '15 minutes', '30' => '30 minutes', '60' => '60 minutes'],
                'description' => 'Time increment for pickup time selection (e.g., 5 = times shown in 5-minute intervals)',
                'order' => 4,
            ],
            
            // Email Settings
            [
                'group' => 'email',
                'key' => 'from_email_address',
                'display_name' => 'From Email Address',
                'value' => env('MAIL_FROM_ADDRESS', 'noreply@luxridesuv.com'),
                'type' => 'email',
                'description' => 'Default "from" email address for system emails',
                'order' => 1,
            ],
            [
                'group' => 'email',
                'key' => 'from_name',
                'display_name' => 'From Name',
                'value' => 'LuxRide',
                'type' => 'text',
                'description' => 'Default "from" name for system emails',
                'order' => 2,
            ],
            [
                'group' => 'email',
                'key' => 'reply_to_address',
                'display_name' => 'Reply-To Address',
                'value' => 'contact@luxridesuv.com',
                'type' => 'email',
                'description' => 'Reply-to email address (leave empty to use from address)',
                'order' => 3,
            ],
            [
                'group' => 'email',
                'key' => 'enable_email_notifications',
                'display_name' => 'Enable Email Notifications',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Master switch for all email notifications',
                'order' => 4,
            ],
            [
                'group' => 'email',
                'key' => 'bcc_admin_on_all_emails',
                'display_name' => 'BCC Admin on All Emails',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Send a copy of all emails to the admin email address',
                'order' => 5,
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