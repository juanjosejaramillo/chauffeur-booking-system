<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'template_slug',
        'recipient_email',
        'recipient_name',
        'cc_emails',
        'bcc_emails',
        'subject',
        'body',
        'variables_used',
        'attachments',
        'status',
        'message_id',
        'error_message',
        'sent_at',
        'opened_at',
        'open_count',
    ];

    protected function casts(): array
    {
        return [
            'variables_used' => 'array',
            'attachments' => 'array',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function markAsSent($messageId = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'message_id' => $messageId,
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsOpened()
    {
        $this->update([
            'opened_at' => $this->opened_at ?? now(),
            'open_count' => $this->open_count + 1,
        ]);
    }
}
