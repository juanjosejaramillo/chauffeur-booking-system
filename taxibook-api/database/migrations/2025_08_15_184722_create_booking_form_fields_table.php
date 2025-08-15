<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Unique identifier like 'flight_number'
            $table->string('label'); // Display label
            $table->string('placeholder')->nullable(); // Input placeholder
            $table->enum('type', ['text', 'number', 'email', 'tel', 'select', 'checkbox', 'textarea', 'date', 'time']);
            $table->boolean('required')->default(false);
            $table->boolean('enabled')->default(true);
            $table->json('options')->nullable(); // For select fields
            $table->json('validation_rules')->nullable(); // Custom validation rules
            $table->json('conditions')->nullable(); // Conditional display rules
            $table->integer('order')->default(0); // Field ordering
            $table->string('helper_text')->nullable(); // Help text below field
            $table->string('group')->nullable(); // Group fields together
            $table->timestamps();
            
            $table->index('enabled');
            $table->index('order');
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_form_fields');
    }
};