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
            'label' => "Base fare (includes {$vehicleType->base_miles_included} miles)",
            'amount' => $vehicleType->base_fare,
        ];

        // Distance charges
        $remainingDistance = max(0, $distance - $vehicleType->base_miles_included);
        if ($remainingDistance > 0) {
            $distanceCharge = 0;
            $tiers = $vehicleType->pricingTiers()->orderBy('from_mile')->get();
            
            foreach ($tiers as $tier) {
                if ($remainingDistance <= 0) break;
                
                $tierDistance = $tier->to_mile 
                    ? min($remainingDistance, $tier->to_mile - $tier->from_mile)
                    : $remainingDistance;
                    
                $distanceCharge += $tierDistance * $tier->per_mile_rate;
                $remainingDistance -= $tierDistance;
            }
            
            $breakdown['distance_charge'] = [
                'label' => sprintf('Distance charge (%.2f miles)', $distance - $vehicleType->base_miles_included),
                'amount' => round($distanceCharge, 2),
            ];
        }

        // Time charges
        $timeCharge = ($duration / 60) * $vehicleType->per_minute_rate;
        if ($timeCharge > 0) {
            $breakdown['time_charge'] = [
                'label' => sprintf('Time charge (%d minutes)', round($duration / 60)),
                'amount' => round($timeCharge, 2),
            ];
        }

        // Service fee multiplier
        if ($vehicleType->service_fee_multiplier != 1) {
            $subtotal = array_sum(array_column($breakdown, 'amount'));
            $serviceFee = $subtotal * ($vehicleType->service_fee_multiplier - 1);
            
            $breakdown['service_fee'] = [
                'label' => 'Service fee',
                'amount' => round($serviceFee, 2),
            ];
        }

        // Tax
        if ($vehicleType->tax_enabled) {
            $subtotal = array_sum(array_column($breakdown, 'amount'));
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