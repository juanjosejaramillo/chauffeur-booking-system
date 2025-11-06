<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            // Enable/disable hourly bookings for this vehicle type
            $table->boolean('hourly_enabled')->default(false)->after('is_active');

            // Price per hour for hourly bookings
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('hourly_enabled');

            // Minimum hours required for hourly booking
            $table->integer('minimum_hours')->default(2)->after('hourly_rate');

            // Maximum hours allowed for hourly booking
            $table->integer('maximum_hours')->default(12)->after('minimum_hours');

            // Miles included per hour
            $table->integer('miles_included_per_hour')->default(20)->after('maximum_hours');

            // Charge per mile over included miles
            $table->decimal('excess_mile_rate', 10, 2)->nullable()->after('miles_included_per_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            // Drop hourly booking fields
            $table->dropColumn([
                'hourly_enabled',
                'hourly_rate',
                'minimum_hours',
                'maximum_hours',
                'miles_included_per_hour',
                'excess_mile_rate'
            ]);
        });
    }
};
