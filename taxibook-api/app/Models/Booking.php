<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'user_id',
        'vehicle_type_id',
        'customer_first_name',
        'customer_last_name',
        'customer_email',
        'customer_phone',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'pickup_date',
        'estimated_distance',
        'estimated_duration',
        'route_polyline',
        'estimated_fare',
        'final_fare',
        'fare_breakdown',
        'status',
        'payment_status',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'special_instructions',
        'admin_notes',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_latitude' => 'decimal:8',
            'pickup_longitude' => 'decimal:8',
            'dropoff_latitude' => 'decimal:8',
            'dropoff_longitude' => 'decimal:8',
            'pickup_date' => 'datetime',
            'estimated_distance' => 'decimal:2',
            'estimated_fare' => 'decimal:2',
            'final_fare' => 'decimal:2',
            'fare_breakdown' => 'array',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = static::generateBookingNumber();
            }
        });
    }

    public static function generateBookingNumber(): string
    {
        do {
            $number = 'TB' . strtoupper(Str::random(8));
        } while (static::where('booking_number', $number)->exists());

        return $number;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('pickup_date', '>', now())
                    ->whereIn('status', ['confirmed', 'pending']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('pickup_date', today());
    }

    public function isWithin7Days(): bool
    {
        return $this->pickup_date->diffInDays(now()) <= 7;
    }

    public function canBeAuthorized(): bool
    {
        return $this->isWithin7Days() && 
               in_array($this->status, ['pending', 'confirmed']) &&
               $this->payment_status === 'pending';
    }

    public function getCustomerFullNameAttribute(): string
    {
        return "{$this->customer_first_name} {$this->customer_last_name}";
    }
}