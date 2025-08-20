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
        // No schema changes needed - just run the simplified seeder
        // This migration serves as a marker that the system has been simplified
        
        // Update the available triggers to remove confusing ones
        Schema::table('email_templates', function (Blueprint $table) {
            // Add a comment to indicate this migration simplified the system
            $table->comment('Email template system simplified on 2025-08-19 to resolve trigger/timing conflicts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the comment
        Schema::table('email_templates', function (Blueprint $table) {
            $table->comment('');
        });
    }
};