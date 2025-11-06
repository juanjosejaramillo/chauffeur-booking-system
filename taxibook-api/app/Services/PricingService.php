<?php

namespace App\Services;

use App\Models\VehicleType;

class PricingService
{
    private GoogleMapsService $mapsService;

    public function __construct(GoogleMapsService $mapsService)
    {
        $this->mapsService = $mapsService;
    }

    public function calculatePrices(float $fromLat, float $fromLng, float $toLat, float $toLng, ?string $pickupDateTime = null): array
    {
        // Convert pickup datetime to ISO 8601 format for Google Maps API if provided
        $departureTime = null;
        if ($pickupDateTime) {
            try {
                $departureTime = \Carbon\Carbon::parse($pickupDateTime)->toIso8601String();
            } catch (\Exception $e) {
                \Log::warning('Invalid pickup datetime provided', ['datetime' => $pickupDateTime]);
            }
        }
        
        // Get route information from Google Maps with traffic consideration
        $route = $this->mapsService->getRoute($fromLat, $fromLng, $toLat, $toLng, $departureTime);
        
        if (!$route) {
            throw new \Exception('Unable to calculate route');
        }

        $distance = $route['distance'];
        $duration = $route['duration'];

        // Get all active vehicle types
        $vehicleTypes = VehicleType::active()
            ->with('pricingTiers')
            ->ordered()
            ->get();

        $prices = [];

        foreach ($vehicleTypes as $vehicleType) {
            $fare = $vehicleType->calculateFare($distance, $duration);
            
            $prices[] = [
                'vehicle_type_id' => $vehicleType->id,
                'display_name' => $vehicleType->display_name,
                'slug' => $vehicleType->slug,
                'description' => $vehicleType->description,
                'max_passengers' => $vehicleType->max_passengers,
                'max_luggage' => $vehicleType->max_luggage,
                'features' => $vehicleType->features ?? [],
                'image_url' => $vehicleType->full_image_url,
                'estimated_fare' => $fare,
                'fare_breakdown' => $this->getFareBreakdown($vehicleType, $distance, $duration, $fare),
            ];
        }

        return [
            'route' => [
                'distance' => round($distance, 2),
                'duration' => $duration,
                'duration_minutes' => round($duration / 60),
                'polyline' => $route['polyline'],
            ],
            'vehicles' => $prices,
            'gratuity_options' => [
                ['percentage' => 0, 'label' => 'No tip'],
                ['percentage' => 15, 'label' => '15%'],
                ['percentage' => 20, 'label' => '20%'],
                ['percentage' => 25, 'label' => '25%'],
            ],
            'payment_note' => 'Fare will be charged at booking. You can optionally add a tip now or after your trip.',
        ];
    }

    private function getFareBreakdown(VehicleType $vehicleType, float $distance, int $duration, float $totalFare): array
    {
        $breakdown = [];
        
        // Base fare
        $breakdown['base_fare'] = [
            'label' => sprintf('Base fare (includes first %g miles)', $vehicleType->base_miles_included),
            'amount' => $vehicleType->base_fare,
        ];

        // Distance charges - show each tier separately
        if ($distance > $vehicleType->base_miles_included) {
            $billableDistance = $distance - $vehicleType->base_miles_included;
            $currentMile = $vehicleType->base_miles_included;
            
            $tiers = $vehicleType->pricingTiers()->orderBy('from_mile')->get();
            $tierIndex = 1;
            
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
                    $tierCharge = $milesInTier * $tier->per_mile_rate;
                    
                    // Format tier label
                    $tierLabel = $tier->to_mile 
                        ? sprintf('Miles %g-%g', $tier->from_mile, $tier->to_mile)
                        : sprintf('Miles %g+', $tier->from_mile);
                    
                    $breakdown["tier_{$tierIndex}"] = [
                        'label' => sprintf('%s: %g × $%.2f', $tierLabel, round($milesInTier, 2), $tier->per_mile_rate),
                        'amount' => round($tierCharge, 2),
                    ];
                    
                    $billableDistance -= $milesInTier;
                    $currentMile += $milesInTier;
                    $tierIndex++;
                }
            }
            
            // If there's still distance left and no unlimited tier
            if ($billableDistance > 0 && $tiers->isNotEmpty()) {
                $lastTier = $tiers->last();
                if ($lastTier->to_mile !== null) {
                    $tierCharge = $billableDistance * $lastTier->per_mile_rate;
                    $breakdown["tier_overflow"] = [
                        'label' => sprintf('Miles %g+: %g × $%.2f', $lastTier->to_mile + 1, round($billableDistance, 2), $lastTier->per_mile_rate),
                        'amount' => round($tierCharge, 2),
                    ];
                }
            }
        }

        // Time charges
        $timeCharge = ($duration / 60) * $vehicleType->per_minute_rate;
        if ($timeCharge > 0) {
            $breakdown['time_charge'] = [
                'label' => sprintf('Time: %d × $%.2f', round($duration / 60), $vehicleType->per_minute_rate),
                'amount' => round($timeCharge, 2),
            ];
        }

        // Calculate subtotal (before service fee and tax)
        $subtotal = array_sum(array_column($breakdown, 'amount'));

        // Service fee multiplier (if different from 1)
        if ($vehicleType->service_fee_multiplier != 1) {
            $serviceFee = $subtotal * ($vehicleType->service_fee_multiplier - 1);
            $breakdown['service_fee'] = [
                'label' => sprintf('Service fee (%.0f%%)', ($vehicleType->service_fee_multiplier - 1) * 100),
                'amount' => round($serviceFee, 2),
            ];
            $subtotal += $serviceFee;
        }

        // Add subtotal line before tax
        $breakdown['subtotal'] = [
            'label' => 'Subtotal',
            'amount' => round($subtotal, 2),
            'is_subtotal' => true,
        ];

        // Tax (optional)
        if ($vehicleType->tax_enabled && $vehicleType->tax_rate > 0) {
            $tax = $subtotal * ($vehicleType->tax_rate / 100);
            $breakdown['tax'] = [
                'label' => sprintf('Tax (%.2f%%)', $vehicleType->tax_rate),
                'amount' => round($tax, 2),
            ];
        }

        // Total
        $breakdown['total'] = [
            'label' => 'Total',
            'amount' => $totalFare,
        ];

        return $breakdown;
    }

    /**
     * Calculate prices for hourly bookings
     *
     * @param int $hours Number of hours
     * @return array Array of vehicle prices with hourly information
     */
    public function calculateHourlyPrices(int $hours): array
    {
        // Get all active vehicle types that have hourly booking enabled
        $vehicleTypes = VehicleType::active()
            ->where('hourly_enabled', true)
            ->where('hourly_rate', '>', 0)
            ->ordered()
            ->get();

        $prices = [];

        foreach ($vehicleTypes as $vehicleType) {
            // Validate hours against vehicle constraints
            if ($hours < $vehicleType->minimum_hours || $hours > $vehicleType->maximum_hours) {
                continue; // Skip this vehicle if hours don't meet requirements
            }

            $fare = $vehicleType->calculateHourlyFare($hours);

            $prices[] = [
                'vehicle_type_id' => $vehicleType->id,
                'display_name' => $vehicleType->display_name,
                'slug' => $vehicleType->slug,
                'description' => $vehicleType->description,
                'max_passengers' => $vehicleType->max_passengers,
                'max_luggage' => $vehicleType->max_luggage,
                'features' => $vehicleType->features ?? [],
                'image_url' => $vehicleType->full_image_url,
                'estimated_fare' => $fare,
                'hourly_rate' => $vehicleType->hourly_rate,
                'hours' => $hours,
                'miles_included_per_hour' => $vehicleType->miles_included_per_hour,
                'total_miles_included' => $hours * $vehicleType->miles_included_per_hour,
                'excess_mile_rate' => $vehicleType->excess_mile_rate,
                'minimum_hours' => $vehicleType->minimum_hours,
                'maximum_hours' => $vehicleType->maximum_hours,
                'fare_breakdown' => $this->getHourlyFareBreakdown($vehicleType, $hours, $fare),
            ];
        }

        return [
            'vehicles' => $prices,
            'gratuity_options' => [
                ['percentage' => 0, 'label' => 'No tip'],
                ['percentage' => 15, 'label' => '15%'],
                ['percentage' => 20, 'label' => '20%'],
                ['percentage' => 25, 'label' => '25%'],
            ],
            'payment_note' => 'Fare will be charged at booking. You can optionally add a tip now or after your trip.',
        ];
    }

    /**
     * Get fare breakdown for hourly bookings
     */
    private function getHourlyFareBreakdown(VehicleType $vehicleType, int $hours, float $totalFare): array
    {
        $breakdown = [];

        // Hourly rate calculation
        $breakdown['hourly_rate'] = [
            'label' => sprintf('%d hours × $%.2f per hour', $hours, $vehicleType->hourly_rate),
            'amount' => $totalFare,
        ];

        // Miles included info
        $totalMilesIncluded = $hours * $vehicleType->miles_included_per_hour;
        $breakdown['miles_included'] = [
            'label' => sprintf('Includes %d miles (%d miles per hour)', $totalMilesIncluded, $vehicleType->miles_included_per_hour),
            'amount' => 0,
            'is_info' => true,
        ];

        // Excess mile rate info
        if ($vehicleType->excess_mile_rate > 0) {
            $breakdown['excess_miles'] = [
                'label' => sprintf('Extra miles charged at $%.2f per mile', $vehicleType->excess_mile_rate),
                'amount' => 0,
                'is_info' => true,
            ];
        }

        // Total
        $breakdown['total'] = [
            'label' => 'Total',
            'amount' => $totalFare,
        ];

        return $breakdown;
    }
}