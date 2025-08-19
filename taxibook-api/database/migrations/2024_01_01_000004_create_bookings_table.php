<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_type_id')->constrained();
            
            // Customer information (stored denormalized for historical accuracy)
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            
            // Trip details
            $table->string('pickup_address');
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->string('dropoff_address');
            $table->decimal('dropoff_latitude', 10, 8);
            $table->decimal('dropoff_longitude', 11, 8);
            $table->dateTime('pickup_date');
            $table->decimal('estimated_distance', 8, 2);
            $table->integer('estimated_duration');
            
            // Pricing
            $table->decimal('estimated_fare', 10, 2);
            $table->decimal('final_fare', 10, 2)->nullable();
            $table->json('fare_breakdown')->nullable();
            
            // Status and payment
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'failed']);
            $table->enum('payment_status', ['pending', 'authorized', 'captured', 'refunded', 'failed', 'cancelled'])->default('pending');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            
            // Additional info
            $table->text('special_instructions')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('booking_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index('pickup_date');
            $table->index(['user_id', 'status']);
            $table->index('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};