<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('flight_number')->nullable()->after('special_instructions');
            $table->boolean('is_airport_pickup')->default(false)->after('flight_number');
            $table->boolean('is_airport_dropoff')->default(false)->after('is_airport_pickup');
            $table->json('additional_data')->nullable()->after('is_airport_dropoff');
            
            $table->index('is_airport_pickup');
            $table->index('is_airport_dropoff');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'flight_number',
                'is_airport_pickup', 
                'is_airport_dropoff',
                'additional_data'
            ]);
        });
    }
};