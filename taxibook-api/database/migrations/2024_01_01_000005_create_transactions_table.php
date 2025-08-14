<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['authorization', 'capture', 'refund', 'partial_refund', 'void', 'payment', 'tip']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'succeeded', 'failed']);
            $table->string('stripe_transaction_id')->nullable();
            $table->json('stripe_response')->nullable();
            $table->text('notes')->nullable();
            $table->string('processed_by')->nullable();
            $table->timestamps();
            
            $table->index(['booking_id', 'type']);
            $table->index('stripe_transaction_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};