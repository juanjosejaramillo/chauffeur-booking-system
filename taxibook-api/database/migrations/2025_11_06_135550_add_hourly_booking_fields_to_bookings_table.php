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
        Schema::table('bookings', function (Blueprint $table) {
            // Add booking type field (one_way or hourly)
            $table->enum('booking_type', ['one_way', 'hourly'])->default('one_way')->after('id');

            // Add duration in hours for hourly bookings
            $table->integer('duration_hours')->nullable()->after('pickup_date');

            // Make dropoff fields nullable (not required for hourly bookings)
            $table->string('dropoff_address')->nullable()->change();
            $table->decimal('dropoff_latitude', 10, 8)->nullable()->change();
            $table->decimal('dropoff_longitude', 11, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Remove booking type and duration fields
            $table->dropColumn(['booking_type', 'duration_hours']);

            // Revert dropoff fields to not nullable (requires data cleanup first in production)
            $table->string('dropoff_address')->nullable(false)->change();
            $table->decimal('dropoff_latitude', 10, 8)->nullable(false)->change();
            $table->decimal('dropoff_longitude', 11, 8)->nullable(false)->change();
        });
    }
};
