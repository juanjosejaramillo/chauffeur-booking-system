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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index();
            $table->string('key')->unique();
            $table->string('display_name');
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, number, boolean, json, email, tel, url, password
            $table->text('description')->nullable();
            $table->json('options')->nullable(); // For select fields
            $table->json('validation_rules')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
            
            $table->index(['group', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};