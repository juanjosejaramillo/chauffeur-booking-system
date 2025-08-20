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
        // Remove email templates with unwanted triggers
        $unwantedTriggers = [
            'driver.assigned',
            'driver.enroute', 
            'driver.arrived',
            'trip.ended',
            'payment.authorized',
            'payment.failed',
            'admin.daily_summary',
            'admin.weekly_summary',
            'admin.payment_issue',
            'custom.manual'
        ];

        // Delete templates that have any of these triggers
        foreach ($unwantedTriggers as $trigger) {
            EmailTemplate::whereJsonContains('trigger_events', $trigger)->delete();
        }

        // Also remove any templates with these names (in case they exist)
        $unwantedTemplateNames = [
            'Driver Assigned',
            'Driver En Route',
            'Driver Arrived',
            'Payment Failed Alert',
            'Daily Summary Report',
            'Weekly Summary Report',
            'Manual Email'
        ];

        EmailTemplate::whereIn('name', $unwantedTemplateNames)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration removes data, so we can't reverse it
        // The seeder would need to be run again to restore templates
    }
};