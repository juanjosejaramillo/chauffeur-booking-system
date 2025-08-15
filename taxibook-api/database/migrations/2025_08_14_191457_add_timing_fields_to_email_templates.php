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
        Schema::table('email_templates', function (Blueprint $table) {
            // Add timing configuration fields
            $table->enum('send_timing_type', ['immediate', 'before_pickup', 'after_pickup', 'after_booking', 'after_completion'])
                ->default('immediate')
                ->after('delay_minutes');
            
            $table->integer('send_timing_value')->default(0)->after('send_timing_type');
            
            $table->enum('send_timing_unit', ['minutes', 'hours', 'days'])
                ->default('hours')
                ->after('send_timing_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn(['send_timing_type', 'send_timing_value', 'send_timing_unit']);
        });
    }
};
