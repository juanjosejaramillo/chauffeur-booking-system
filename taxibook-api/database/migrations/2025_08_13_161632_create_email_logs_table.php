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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('template_slug')->nullable();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('cc_emails')->nullable();
            $table->string('bcc_emails')->nullable();
            $table->string('subject');
            $table->longText('body');
            $table->json('variables_used')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced', 'complained']);
            $table->string('message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->timestamps();
            
            $table->index(['booking_id', 'status']);
            $table->index('recipient_email');
            $table->index('template_slug');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
