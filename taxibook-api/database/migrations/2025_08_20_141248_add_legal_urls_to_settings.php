<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Terms and Conditions URL setting
        Setting::firstOrCreate(
            ['key' => 'terms_url'],
            [
                'group' => 'legal',
                'display_name' => 'Terms and Conditions URL',
                'value' => 'https://luxridesuv.com/terms',
                'type' => 'text',
                'description' => 'URL for Terms and Conditions page (opens when customer clicks the link)',
                'order' => 1,
                'is_visible' => true,
            ]
        );

        // Add Cancellation Policy URL setting
        Setting::firstOrCreate(
            ['key' => 'cancellation_policy_url'],
            [
                'group' => 'legal',
                'display_name' => 'Cancellation Policy URL',
                'value' => 'https://luxridesuv.com/cancellation-policy',
                'type' => 'text',
                'description' => 'URL for Cancellation Policy page (opens when customer clicks the link)',
                'order' => 2,
                'is_visible' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('key', 'terms_url')->delete();
        Setting::where('key', 'cancellation_policy_url')->delete();
    }
};