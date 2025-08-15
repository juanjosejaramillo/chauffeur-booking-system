<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // Critical Business Settings
            [
                'key' => 'business_name',
                'value' => 'LuxRide',
                'group' => 'business',
                'display_name' => 'Business Name',
                'type' => 'text',
                'description' => 'Your business name',
                'order' => 1,
            ],
            [
                'key' => 'business_email',
                'value' => 'info@luxride.com',
                'group' => 'business',
                'display_name' => 'Business Email',
                'type' => 'email',
                'description' => 'Main business email',
                'order' => 2,
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@luxride.com',
                'group' => 'business',
                'display_name' => 'Admin Email',
                'type' => 'email',
                'description' => 'Administrator email for system notifications',
                'order' => 3,
            ],
            [
                'key' => 'admin_name',
                'value' => 'LuxRide Administrator',
                'group' => 'business',
                'display_name' => 'Admin Name',
                'type' => 'text',
                'description' => 'Administrator name',
                'order' => 4,
            ],
            [
                'key' => 'business_phone',
                'value' => '1-800-LUXRIDE',
                'group' => 'business',
                'display_name' => 'Business Phone',
                'type' => 'tel',
                'description' => 'Main business phone number',
                'order' => 5,
            ],
            [
                'key' => 'business_address',
                'value' => '123 Business Ave, Suite 100, New York, NY 10001',
                'group' => 'business',
                'display_name' => 'Business Address',
                'type' => 'text',
                'description' => 'Full business address',
                'order' => 6,
            ],
            
            // Email Settings
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@luxride.com',
                'group' => 'email',
                'display_name' => 'From Email Address',
                'type' => 'email',
                'description' => 'Default from email address for system emails',
                'order' => 1,
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'LuxRide',
                'group' => 'email',
                'display_name' => 'From Name',
                'type' => 'text',
                'description' => 'Default from name for system emails',
                'order' => 2,
            ],
            [
                'key' => 'email_notifications_enabled',
                'value' => true,
                'group' => 'email',
                'display_name' => 'Enable Email Notifications',
                'type' => 'boolean',
                'description' => 'Master switch for all email notifications',
                'order' => 3,
            ],
            
            // System Settings
            [
                'key' => 'app_timezone',
                'value' => 'America/New_York',
                'group' => 'system',
                'display_name' => 'Timezone',
                'type' => 'text',
                'description' => 'System timezone',
                'order' => 1,
            ],
            [
                'key' => 'app_locale',
                'value' => 'en',
                'group' => 'system',
                'display_name' => 'Locale',
                'type' => 'text',
                'description' => 'Application locale',
                'order' => 2,
            ],
            [
                'key' => 'currency',
                'value' => 'USD',
                'group' => 'system',
                'display_name' => 'Currency',
                'type' => 'text',
                'description' => 'Default currency',
                'order' => 3,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Default settings have been seeded.');
        $this->command->warn('IMPORTANT: Please update the following critical settings in the admin panel:');
        $this->command->warn('- Business Name (currently: LuxRide)');
        $this->command->warn('- Business Email (currently: info@luxride.com)');
        $this->command->warn('- Admin Email (currently: admin@luxride.com)');
        $this->command->warn('- Admin Name (currently: LuxRide Administrator)');
        $this->command->info('Navigate to /admin/settings to configure these settings.');
    }
}