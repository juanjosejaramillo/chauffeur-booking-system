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
        // Update existing templates based on their category
        \App\Models\EmailTemplate::where('category', 'customer')
            ->update(['send_to_customer' => true]);
            
        \App\Models\EmailTemplate::where('category', 'admin')
            ->update(['send_to_admin' => true]);
            
        \App\Models\EmailTemplate::where('category', 'driver')
            ->update(['send_to_driver' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all recipient flags
        \App\Models\EmailTemplate::query()->update([
            'send_to_customer' => false,
            'send_to_admin' => false,
            'send_to_driver' => false,
        ]);
    }
};
