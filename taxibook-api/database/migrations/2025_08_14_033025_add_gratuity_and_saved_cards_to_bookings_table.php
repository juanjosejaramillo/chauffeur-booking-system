<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Gratuity fields
            $table->decimal('gratuity_amount', 10, 2)->default(0)->after('final_fare');
            $table->timestamp('gratuity_added_at')->nullable()->after('gratuity_amount');
            
            // Tip link fields for post-trip tipping
            $table->string('tip_link_token')->nullable()->unique()->after('gratuity_added_at');
            $table->timestamp('tip_link_sent_at')->nullable()->after('tip_link_token');
            
            // Saved payment method fields
            $table->boolean('save_payment_method')->default(false)->after('stripe_payment_method_id');
            $table->string('stripe_customer_id')->nullable()->after('save_payment_method');
            
            // QR code for in-person tip collection
            $table->text('qr_code_data')->nullable()->after('stripe_customer_id');
            
            // Index for quick token lookup
            $table->index('tip_link_token');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['tip_link_token']);
            
            $table->dropColumn([
                'gratuity_amount',
                'gratuity_added_at',
                'tip_link_token',
                'tip_link_sent_at',
                'save_payment_method',
                'stripe_customer_id',
                'qr_code_data'
            ]);
        });
    }
};