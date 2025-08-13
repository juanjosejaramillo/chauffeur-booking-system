<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'type',
        'amount',
        'status',
        'stripe_transaction_id',
        'stripe_response',
        'notes',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'stripe_response' => 'array',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'succeeded');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}