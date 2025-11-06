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
        'hourly_enabled',
        'hourly_rate',
        'minimum_hours',
        'maximum_hours',
        'miles_included_per_hour',
        'excess_mile_rate',
    ];

    // Handle features as comma-separated list for the form
    public function setFeaturesAttribute($value)
    {
        if (is_string($value)) {
            // Convert comma-separated string to array
            $this->attributes['features'] = json_encode(
                array_map('trim', array_filter(explode(',', $value)))
            );
        } elseif (is_array($value)) {
            $this->attributes['features'] = json_encode($value);
        } else {
            $this->attributes['features'] = $value;
        }
    }

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
            'hourly_enabled' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'minimum_hours' => 'integer',
            'maximum_hours' => 'integer',
            'miles_included_per_hour' => 'integer',
            'excess_mile_rate' => 'decimal:2',
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
        $baseFare = $this->base_fare;
        $distanceCharges = 0;
        $timeCharges = 0;
        
        // Calculate distance charges beyond base miles
        if ($distance > $this->base_miles_included) {
            $billableDistance = $distance - $this->base_miles_included;
            $currentMile = $this->base_miles_included;
            
            // Get tiers ordered by from_mile
            $tiers = $this->pricingTiers()->orderBy('from_mile')->get();
            
            foreach ($tiers as $tier) {
                if ($billableDistance <= 0) break;
                
                // Determine the start and end of this tier
                $tierStart = max($tier->from_mile, $currentMile);
                $tierEnd = $tier->to_mile ?? PHP_FLOAT_MAX;
                
                // Skip this tier if we haven't reached it yet
                if ($currentMile >= $tierEnd) continue;
                
                // Calculate miles in this tier
                $milesInTier = min($tierEnd - $tierStart, $billableDistance);
                
                if ($milesInTier > 0) {
                    $distanceCharges += $milesInTier * $tier->per_mile_rate;
                    $billableDistance -= $milesInTier;
                    $currentMile += $milesInTier;
                }
            }
            
            // If there's still distance left and no unlimited tier, charge at the last tier's rate
            if ($billableDistance > 0 && $tiers->isNotEmpty()) {
                $lastTier = $tiers->last();
                if ($lastTier->to_mile !== null) {
                    // No unlimited tier defined, use last tier's rate
                    $distanceCharges += $billableDistance * $lastTier->per_mile_rate;
                }
            }
        }
        
        // Calculate time charges
        $timeCharges = ($duration / 60) * $this->per_minute_rate;
        
        // Calculate subtotal
        $subtotal = $baseFare + $distanceCharges + $timeCharges;
        
        // Apply service multiplier if different from 1
        if ($this->service_fee_multiplier != 1) {
            $subtotal *= $this->service_fee_multiplier;
        }
        
        // Apply tax if enabled (on subtotal)
        $total = $subtotal;
        if ($this->tax_enabled && $this->tax_rate > 0) {
            $total = $subtotal * (1 + $this->tax_rate / 100);
        }
        
        // Ensure minimum fare
        $total = max($total, $this->minimum_fare);

        return round($total, 2);
    }

    /**
     * Calculate fare for hourly bookings
     *
     * @param int $hours Number of hours
     * @return float Total fare
     */
    public function calculateHourlyFare($hours)
    {
        // Simple calculation: hours × hourly_rate
        $total = $hours * $this->hourly_rate;

        // Note: Tax/service fees not applied per user requirements
        // Future enhancement: Apply excess mile charges if actual miles exceed included miles

        return round($total, 2);
    }
}