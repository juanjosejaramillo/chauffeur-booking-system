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
            // Make estimated_distance and estimated_duration nullable for hourly bookings
            $table->decimal('estimated_distance', 8, 2)->nullable()->change();
            $table->integer('estimated_duration')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert to not nullable (requires data cleanup first in production)
            $table->decimal('estimated_distance', 8, 2)->nullable(false)->change();
            $table->integer('estimated_duration')->nullable(false)->change();
        });
    }
};
