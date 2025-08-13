<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_name',
        'slug',
        'description',
        'max_passengers',
        'max_luggage',
        'base_fare',
        'base_miles_included',
        'per_minute_rate',
        'minimum_fare',
        'service_fee_multiplier',
        'tax_rate',
        'tax_enabled',
        'is_active',
        'sort_order',
        'features',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'base_fare' => 'decimal:2',
            'base_miles_included' => 'decimal:2',
            'per_minute_rate' => 'decimal:2',
            'minimum_fare' => 'decimal:2',
            'service_fee_multiplier' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_enabled' => 'boolean',
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    // Create a computed attribute for the full image URL
    public function getFullImageUrlAttribute()
    {
        $value = $this->attributes['image_url'] ?? null;
        
        if (!$value) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Otherwise, generate the storage URL
        return asset('storage/' . $value);
    }

    public function pricingTiers()
    {
        return $this->hasMany(VehiclePricingTier::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_name');
    }

    public function calculateFare($distance, $duration)
    {
        $fare = 0;
        
        // Base fare covers initial miles
        $fare += $this->base_fare;
        $remainingDistance = max(0, $distance - $this->base_miles_included);
        
        // Apply tiered pricing for remaining distance
        if ($remainingDistance > 0) {
            $tiers = $this->pricingTiers()->orderBy('from_mile')->get();
            
            foreach ($tiers as $tier) {
                if ($remainingDistance <= 0) break;
                
                $tierDistance = $tier->to_mile 
                    ? min($remainingDistance, $tier->to_mile - $tier->from_mile)
                    : $remainingDistance;
                    
                $fare += $tierDistance * $tier->per_mile_rate;
                $remainingDistance -= $tierDistance;
            }
        }
        
        // Add time-based charges
        $fare += ($duration / 60) * $this->per_minute_rate;
        
        // Apply service multiplier
        $fare *= $this->service_fee_multiplier;
        
        // Apply tax if enabled
        if ($this->tax_enabled) {
            $fare *= (1 + $this->tax_rate / 100);
        }
        
        // Ensure minimum fare
        $fare = max($fare, $this->minimum_fare);
        
        return round($fare, 2);
    }
}