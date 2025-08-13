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
            $table->string('email_verification_code', 6)->nullable()->after('customer_email');
            $table->timestamp('email_verified_at')->nullable()->after('email_verification_code');
            $table->timestamp('verification_expires_at')->nullable()->after('email_verified_at');
            $table->integer('verification_attempts')->default(0)->after('verification_expires_at');
            
            $table->index('email_verification_code');
            $table->index('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_code',
                'email_verified_at',
                'verification_expires_at',
                'verification_attempts'
            ]);
        });
    }
};