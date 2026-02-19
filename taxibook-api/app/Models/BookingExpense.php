<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingExpense extends Model
{
    protected $fillable = [
        'booking_id',
        'description',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
