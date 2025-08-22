<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private string $apiKey;
    private string $baseUrl = 'https://maps.googleapis.com/maps/api';

    public function __construct()
    {
        // Get Google Maps API key from settings (configured by SettingsServiceProvider)
        $this->apiKey = config('services.google.maps_api_key') 
            ?: config('google.api_key')
            ?: env('GOOGLE_MAPS_API_KEY');
            
        if (!$this->apiKey) {
            throw new \Exception('Google Maps API key not configured. Please configure it in Settings.');
        }
    }

    /**
     * Geocode an address using Google Geocoding API
     */
    public function geocode(string $query): ?array
    {
        $cacheKey = 'google_geocode_' . md5($query);
        
        return Cache::remember($cacheKey, 3600, function () use ($query) {
            $response = Http::get("{$this->baseUrl}/geocode/json", [
                'address' => $query,
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    return array_map(function ($result) {
                        return [
                            'address' => $result['formatted_address'],
                            'latitude' => $result['geometry']['location']['lat'],
                            'longitude' => $result['geometry']['location']['lng'],
                            'place_id' => $result['place_id'] ?? null,
                        ];
                    }, $data['results']);
                }
            }

            return null;
        });
    }

    /**
     * Get route information using Google Directions API with traffic data
     */
    public function getRoute(float $fromLat, float $fromLng, float $toLat, float $toLng, ?string $departureTime = null): ?array
    {
        // Include departure time in cache key if provided
        $cacheKey = "google_route_{$fromLat}_{$fromLng}_{$toLat}_{$toLng}";
        if ($departureTime) {
            $cacheKey .= "_" . md5($departureTime);
        }
        
        Log::info('GoogleMapsService::getRoute called', [
            'from' => [$fromLat, $fromLng],
            'to' => [$toLat, $toLng],
            'departure_time' => $departureTime,
            'cache_key' => $cacheKey
        ]);
        
        return Cache::remember($cacheKey, 3600, function () use ($fromLat, $fromLng, $toLat, $toLng, $departureTime) {
            $origin = "{$fromLat},{$fromLng}";
            $destination = "{$toLat},{$toLng}";
            
            Log::info('Making Google Directions API request', [
                'origin' => $origin,
                'destination' => $destination,
                'has_api_key' => !empty($this->apiKey)
            ]);
            
            $startTime = microtime(true);
            
            try {
                $params = [
                    'origin' => $origin,
                    'destination' => $destination,
                    'key' => $this->apiKey,
                    'mode' => 'driving',
                    'units' => 'imperial', // Use miles for US
                ];
                
                // Add departure time for traffic-aware routing
                if ($departureTime) {
                    // Convert ISO 8601 to Unix timestamp for Google
                    $timestamp = strtotime($departureTime);
                    if ($timestamp > time()) {
                        $params['departure_time'] = $timestamp;
                        $params['traffic_model'] = 'best_guess'; // or 'pessimistic' for worst-case
                    }
                }
                
                $response = Http::timeout(10)->get("{$this->baseUrl}/directions/json", $params);
                
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                Log::info('Google Directions API response received', [
                    'duration_ms' => $duration,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'OK' && !empty($data['routes'])) {
                        $route = $data['routes'][0];
                        $leg = $route['legs'][0];
                        
                        // Use duration_in_traffic if available (when departure_time is set)
                        $duration = isset($leg['duration_in_traffic']) 
                            ? $leg['duration_in_traffic']['value']
                            : $leg['duration']['value'];
                        
                        $result = [
                            'distance' => $leg['distance']['value'] / 1609.344, // Convert meters to miles
                            'duration' => $duration, // Duration in seconds (with traffic if available)
                            'polyline' => $route['overview_polyline']['points'] ?? null, // Encoded polyline
                            'distance_text' => $leg['distance']['text'],
                            'duration_text' => isset($leg['duration_in_traffic']) 
                                ? $leg['duration_in_traffic']['text']
                                : $leg['duration']['text'],
                        ];
                        
                        Log::info('Route calculated successfully', [
                            'distance_miles' => $result['distance'],
                            'duration_seconds' => $result['duration'],
                            'has_traffic_data' => isset($leg['duration_in_traffic'])
                        ]);
                        
                        return $result;
                    } else {
                        Log::warning('No routes found in Google response', [
                            'status' => $data['status'],
                            'error_message' => $data['error_message'] ?? null
                        ]);
                    }
                } else {
                    Log::error('Google Directions API request failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Exception in Google Directions API request', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return null;
        });
    }

    /**
     * Reverse geocode coordinates to get address
     */
    public function reverseGeocode(float $lat, float $lng): ?string
    {
        $cacheKey = "google_reverse_{$lat}_{$lng}";
        
        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            $response = Http::get("{$this->baseUrl}/geocode/json", [
                'latlng' => "{$lat},{$lng}",
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    return $data['results'][0]['formatted_address'];
                }
            }

            return null;
        });
    }

    /**
     * Autocomplete search for addresses and places
     * Simple and direct use of Google Places Autocomplete API
     */
    public function autocomplete(string $input, ?float $lat = null, ?float $lng = null): ?array
    {
        $cacheKey = 'google_autocomplete_' . md5($input . $lat . $lng);
        
        return Cache::remember($cacheKey, 300, function () use ($input, $lat, $lng) {
            // Simple autocomplete parameters
            $params = [
                'input' => $input,
                'key' => $this->apiKey,
            ];
            
            // Add location bias if provided
            if ($lat && $lng) {
                $params['location'] = "{$lat},{$lng}";
                $params['radius'] = 50000; // 50km radius
            }
            
            // First try without type restrictions to get all results
            $response = Http::get("{$this->baseUrl}/place/autocomplete/json", $params);
            
            if (!$response->successful()) {
                return [];
            }
            
            $data = $response->json();
            
            if ($data['status'] !== 'OK' || empty($data['predictions'])) {
                return [];
            }
            
            $results = [];
            foreach ($data['predictions'] as $prediction) {
                $types = $prediction['types'] ?? [];
                $mainText = $prediction['structured_formatting']['main_text'] ?? '';
                $secondaryText = $prediction['structured_formatting']['secondary_text'] ?? '';
                $description = $prediction['description'] ?? '';
                
                // Determine if it's a business/venue vs an address
                $isVenue = !in_array('street_address', $types) && 
                          !in_array('route', $types) &&
                          !in_array('political', $types);
                
                $results[] = [
                    'place_id' => $prediction['place_id'],
                    'name' => $mainText ?: explode(',', $description)[0],
                    'address' => $secondaryText ?: $description,
                    'full_description' => $description,
                    'is_venue' => $isVenue,
                    'is_airport' => in_array('airport', $types),
                    'types' => $types,
                    'latitude' => null,
                    'longitude' => null,
                ];
            }
            
            return $results;
        });
    }
    
    /**
     * Extract a meaningful place name from geocoding result
     */
    private function extractPlaceName(array $result): string
    {
        // Try to get a meaningful name from address components
        $components = $result['address_components'] ?? [];
        
        foreach ($components as $component) {
            $types = $component['types'] ?? [];
            
            // Look for establishment, point_of_interest, or premise
            if (in_array('establishment', $types) || 
                in_array('point_of_interest', $types) ||
                in_array('premise', $types)) {
                return $component['long_name'];
            }
        }
        
        // Fall back to first part of formatted address
        return explode(',', $result['formatted_address'])[0] ?? 'Unknown Location';
    }

    /**
     * Get detailed place information by place ID
     */
    public function getPlaceDetails(string $placeId): ?array
    {
        $cacheKey = 'google_place_' . $placeId;
        
        return Cache::remember($cacheKey, 3600, function () use ($placeId) {
            $response = Http::get("{$this->baseUrl}/place/details/json", [
                'place_id' => $placeId,
                'key' => $this->apiKey,
                'fields' => 'name,formatted_address,geometry,types',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'OK' && isset($data['result'])) {
                    $result = $data['result'];
                    return [
                        'name' => $result['name'] ?? '',
                        'address' => $result['formatted_address'] ?? '',
                        'latitude' => $result['geometry']['location']['lat'] ?? null,
                        'longitude' => $result['geometry']['location']['lng'] ?? null,
                        'types' => $result['types'] ?? [],
                    ];
                }
            }

            // Fallback to geocoding if place details fails
            $geocodeResponse = Http::get("{$this->baseUrl}/geocode/json", [
                'place_id' => $placeId,
                'key' => $this->apiKey,
            ]);
            
            if ($geocodeResponse->successful()) {
                $geocodeData = $geocodeResponse->json();
                
                if ($geocodeData['status'] === 'OK' && !empty($geocodeData['results'])) {
                    $result = $geocodeData['results'][0];
                    return [
                        'name' => explode(',', $result['formatted_address'])[0] ?? '',
                        'address' => $result['formatted_address'] ?? '',
                        'latitude' => $result['geometry']['location']['lat'] ?? null,
                        'longitude' => $result['geometry']['location']['lng'] ?? null,
                        'types' => $result['types'] ?? [],
                    ];
                }
            }

            return null;
        });
    }
}