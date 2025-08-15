<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column already exists before adding
        if (!Schema::hasColumn('bookings', 'total_refunded')) {
            Schema::table('bookings', function (Blueprint $table) {
                // Add total_refunded field to track cumulative refunds
                $table->decimal('total_refunded', 10, 2)->default(0)->after('gratuity_added_at');
            });
        }
        
        // SQLite doesn't support ENUM modification directly, so we'll use a check constraint
        // The payment_status field already exists, we just document the new 'partial' status
        // The application will handle the 'partial' status in code
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('total_refunded');
        });
    }
};
