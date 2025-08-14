<?php

namespace App\Services;

use App\Models\VehicleType;

class PricingService
{
    private MapboxService $mapboxService;

    public function __construct(MapboxService $mapboxService)
    {
        $this->mapboxService = $mapboxService;
    }

    public function calculatePrices(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        // Get route information from Mapbox
        $route = $this->mapboxService->getRoute($fromLat, $fromLng, $toLat, $toLng);
        
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
}