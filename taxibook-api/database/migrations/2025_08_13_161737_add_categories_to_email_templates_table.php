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
            $table->enum('category', ['customer', 'admin', 'driver'])->default('customer')->after('name');
            $table->text('description')->nullable()->after('body');
            $table->string('cc_emails')->nullable()->after('description');
            $table->string('bcc_emails')->nullable()->after('cc_emails');
            $table->boolean('attach_receipt')->default(false)->after('bcc_emails');
            $table->boolean('attach_booking_details')->default(false)->after('attach_receipt');
            $table->integer('delay_minutes')->default(0)->after('attach_booking_details');
            $table->json('trigger_events')->nullable()->after('delay_minutes');
            $table->integer('priority')->default(0)->after('trigger_events');
            
            $table->index('category');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'description',
                'cc_emails',
                'bcc_emails',
                'attach_receipt',
                'attach_booking_details',
                'delay_minutes',
                'trigger_events',
                'priority'
            ]);
        });
    }
};
