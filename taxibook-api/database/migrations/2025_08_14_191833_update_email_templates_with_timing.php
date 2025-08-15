<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EmailTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing templates with appropriate timing configurations
        $timingConfigs = [
            // 24 Hour reminders
            '24 Hour Booking Reminder' => [
                'send_timing_type' => 'before_pickup',
                'send_timing_value' => 24,
                'send_timing_unit' => 'hours',
            ],
            
            // 2 Hour reminders
            '2 Hour Booking Reminder' => [
                'send_timing_type' => 'before_pickup',
                'send_timing_value' => 2,
                'send_timing_unit' => 'hours',
            ],
            'Upcoming Booking Alert (Admin)' => [
                'send_timing_type' => 'before_pickup',
                'send_timing_value' => 2,
                'send_timing_unit' => 'hours',
            ],
            
            // After completion emails
            'Review Request' => [
                'send_timing_type' => 'after_completion',
                'send_timing_value' => 24,
                'send_timing_unit' => 'hours',
            ],
            
            // Immediate emails (default for all others)
            'Booking Confirmation' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Driver Assigned Notification' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Driver En Route' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Driver Arrived' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Trip Completed' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Booking Modified' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Booking Cancelled' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Payment Receipt' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Payment Failed' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Refund Processed' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'New Booking Alert (Admin)' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Booking Cancelled (Admin)' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Payment Captured (Admin)' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Payment Failed (Admin)' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
            'Refund Processed (Admin)' => [
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ],
        ];
        
        foreach ($timingConfigs as $templateName => $config) {
            EmailTemplate::where('name', $templateName)
                ->update($config);
        }
        
        // Set default for any templates not specifically configured
        EmailTemplate::whereNull('send_timing_type')
            ->update([
                'send_timing_type' => 'immediate',
                'send_timing_value' => 0,
                'send_timing_unit' => 'minutes',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all timing to defaults
        EmailTemplate::query()->update([
            'send_timing_type' => 'immediate',
            'send_timing_value' => 0,
            'send_timing_unit' => 'minutes',
        ]);
    }
};