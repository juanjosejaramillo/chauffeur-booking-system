<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('from_mile', 8, 2);
            $table->decimal('to_mile', 8, 2)->nullable();
            $table->decimal('per_mile_rate', 8, 2);
            $table->timestamps();
            
            $table->index(['vehicle_type_id', 'from_mile']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_pricing_tiers');
    }
};