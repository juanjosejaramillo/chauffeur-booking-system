<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the email verification toggle setting
        Setting::firstOrCreate(
            ['key' => 'require_email_verification'],
            [
                'value' => 'true',
                'display_name' => 'Require Email Verification',
                'description' => 'Require email verification code before payment',
                'type' => 'boolean',
                'group' => 'booking',
                'order' => 5,
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
        Setting::where('key', 'require_email_verification')->delete();
    }
};
