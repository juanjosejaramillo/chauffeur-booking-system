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
        // Add the payment mode setting
        Setting::firstOrCreate(
            ['key' => 'payment_mode'],
            [
                'value' => 'immediate', // Default: charge immediately (current behavior)
                'display_name' => 'Payment Mode',
                'description' => 'When to charge the customer: immediately at booking or after service is completed',
                'type' => 'select',
                'group' => 'stripe',
                'order' => 8,
                'is_visible' => true,
                'is_encrypted' => false
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('key', 'payment_mode')->delete();
    }
};
