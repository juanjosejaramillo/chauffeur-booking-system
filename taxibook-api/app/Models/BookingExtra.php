<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingExtra extends Model
{
    protected $fillable = [
        'booking_id',
        'extra_id',
        'name',
        'unit_price',
        'quantity',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function extra()
    {
        return $this->belongsTo(Extra::class);
    }
}
