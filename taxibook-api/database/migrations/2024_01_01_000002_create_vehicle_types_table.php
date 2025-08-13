<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('max_passengers');
            $table->integer('max_luggage');
            $table->decimal('base_fare', 10, 2);
            $table->decimal('base_miles_included', 8, 2);
            $table->decimal('per_minute_rate', 8, 2);
            $table->decimal('minimum_fare', 10, 2);
            $table->decimal('service_fee_multiplier', 5, 2)->default(1.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->boolean('tax_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('features')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
            
            $table->index('slug');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};