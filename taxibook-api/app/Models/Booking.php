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
        'email_verification_code',
        'email_verified_at',
        'verification_expires_at',
        'verification_attempts',
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
        'estimated_fare',
        'final_fare',
        'fare_breakdown',
        'gratuity_amount',
        'gratuity_added_at',
        'tip_link_token',
        'tip_link_sent_at',
        'total_refunded',
        'status',
        'payment_status',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'save_payment_method',
        'stripe_customer_id',
        'qr_code_data',
        'special_instructions',
        'flight_number',
        'is_airport_pickup',
        'is_airport_dropoff',
        'additional_data',
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
            'gratuity_amount' => 'decimal:2',
            'gratuity_added_at' => 'datetime',
            'tip_link_sent_at' => 'datetime',
            'total_refunded' => 'decimal:2',
            'fare_breakdown' => 'array',
            'is_airport_pickup' => 'boolean',
            'is_airport_dropoff' => 'boolean',
            'additional_data' => 'array',
            'save_payment_method' => 'boolean',
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
        // Characters to use: A-Z (excluding O, I, L) and 0-9 (excluding 0, 1)
        // This avoids confusion between O/0, I/1, L/1
        $characters = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        
        do {
            // Generate a 6-character alphanumeric booking number
            $number = '';
            for ($i = 0; $i < 6; $i++) {
                $number .= $characters[random_int(0, strlen($characters) - 1)];
            }
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

    public function hasTipped(): bool
    {
        return $this->gratuity_amount > 0;
    }

    public function canAddTip(): bool
    {
        return $this->status === 'completed' && !$this->hasTipped();
    }
    
    public function hasSavedPaymentMethod(): bool
    {
        return !empty($this->stripe_payment_method_id) && !empty($this->stripe_customer_id);
    }

    public function getTotalAmountAttribute(): float
    {
        return $this->final_fare + $this->gratuity_amount;
    }

    public function generateTipToken(): string
    {
        $this->tip_link_token = Str::random(40);
        $this->save();
        
        return $this->tip_link_token;
    }

    /**
     * Calculate total refunded amount from transactions
     */
    public function calculateTotalRefunded(): float
    {
        return $this->transactions()
            ->whereIn('type', ['refund', 'partial_refund'])
            ->where('status', 'succeeded')
            ->sum('amount');
    }

    /**
     * Get the net amount after refunds (what customer actually paid)
     */
    public function getNetAmountAttribute(): float
    {
        $chargedAmount = ($this->final_fare ?? $this->estimated_fare) + $this->gratuity_amount;
        return $chargedAmount - $this->total_refunded;
    }

    /**
     * Check if booking has been partially refunded
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->total_refunded > 0 && 
               $this->total_refunded < ($this->final_fare ?? $this->estimated_fare);
    }

    /**
     * Check if booking has been fully refunded
     */
    public function isFullyRefunded(): bool
    {
        return $this->total_refunded >= ($this->final_fare ?? $this->estimated_fare);
    }

    /**
     * Get the amount that was actually charged to the customer
     */
    public function getChargedAmountAttribute(): float
    {
        if ($this->payment_status === 'captured' || 
            $this->payment_status === 'refunded') {
            return ($this->final_fare ?? $this->estimated_fare) + $this->gratuity_amount;
        }
        return 0;
    }

    /**
     * Sync total refunded amount from transactions
     */
    public function syncTotalRefunded(): void
    {
        $this->total_refunded = $this->calculateTotalRefunded();
        $this->save();
    }
}