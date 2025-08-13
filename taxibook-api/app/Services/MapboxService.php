<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MapboxService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.mapbox.com';

    public function __construct()
    {
        $this->apiKey = config('services.mapbox.api_key');
    }

    public function geocode(string $query): ?array
    {
        $cacheKey = 'mapbox_geocode_' . md5($query);
        
        return Cache::remember($cacheKey, 3600, function () use ($query) {
            $response = Http::get("{$this->baseUrl}/geocoding/v5/mapbox.places/{$query}.json", [
                'access_token' => $this->apiKey,
                'limit' => 5,
                'types' => 'address',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['features'])) {
                    return array_map(function ($feature) {
                        return [
                            'address' => $feature['place_name'],
                            'latitude' => $feature['center'][1],
                            'longitude' => $feature['center'][0],
                        ];
                    }, $data['features']);
                }
            }

            return null;
        });
    }

    public function getRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): ?array
    {
        $cacheKey = "mapbox_route_{$fromLat}_{$fromLng}_{$toLat}_{$toLng}";
        
        \Log::info('MapboxService::getRoute called', [
            'from' => [$fromLat, $fromLng],
            'to' => [$toLat, $toLng],
            'cache_key' => $cacheKey
        ]);
        
        return Cache::remember($cacheKey, 3600, function () use ($fromLat, $fromLng, $toLat, $toLng) {
            $coordinates = "{$fromLng},{$fromLat};{$toLng},{$toLat}";
            $url = "{$this->baseUrl}/directions/v5/mapbox/driving/{$coordinates}";
            
            \Log::info('Making Mapbox API request', [
                'url' => $url,
                'has_api_key' => !empty($this->apiKey)
            ]);
            
            $startTime = microtime(true);
            
            try {
                $response = Http::timeout(10)->get($url, [
                    'access_token' => $this->apiKey,
                    'geometries' => 'polyline',
                    'overview' => 'full',
                ]);
                
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                \Log::info('Mapbox API response received', [
                    'duration_ms' => $duration,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (!empty($data['routes'])) {
                        $route = $data['routes'][0];
                        
                        $result = [
                            'distance' => $route['distance'] / 1609.344, // Convert meters to miles
                            'duration' => $route['duration'], // Duration in seconds
                            'polyline' => $route['geometry'],
                        ];
                        
                        \Log::info('Route calculated successfully', [
                            'distance_miles' => $result['distance'],
                            'duration_seconds' => $result['duration']
                        ]);
                        
                        return $result;
                    } else {
                        \Log::warning('No routes found in Mapbox response', ['data' => $data]);
                    }
                } else {
                    \Log::error('Mapbox API request failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Exception in Mapbox API request', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            return null;
        });
    }

    public function reverseGeocode(float $lat, float $lng): ?string
    {
        $cacheKey = "mapbox_reverse_{$lat}_{$lng}";
        
        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            $response = Http::get("{$this->baseUrl}/geocoding/v5/mapbox.places/{$lng},{$lat}.json", [
                'access_token' => $this->apiKey,
                'types' => 'address',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['features'])) {
                    return $data['features'][0]['place_name'];
                }
            }

            return null;
        });
    }
}