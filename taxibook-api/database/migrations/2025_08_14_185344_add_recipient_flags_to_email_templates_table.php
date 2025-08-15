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
            $table->boolean('send_to_customer')->default(false)->after('is_active');
            $table->boolean('send_to_admin')->default(false)->after('send_to_customer');
            $table->boolean('send_to_driver')->default(false)->after('send_to_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn(['send_to_customer', 'send_to_admin', 'send_to_driver']);
        });
    }
};
