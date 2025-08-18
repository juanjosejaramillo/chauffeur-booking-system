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
        // Clear all existing email templates
        DB::table('email_templates')->truncate();
        
        // Run the comprehensive seeder
        \Artisan::call('db:seed', ['--class' => 'ComprehensiveEmailTemplateSeeder']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reliably restore previous templates
        // This migration is intentionally one-way
    }
};