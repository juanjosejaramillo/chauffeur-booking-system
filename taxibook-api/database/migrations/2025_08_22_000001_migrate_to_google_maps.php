<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove Mapbox settings
        Setting::whereIn('key', [
            'mapbox_token',
            'mapbox_public_token',
            'mapbox_api_key'
        ])->delete();

        // Add Google Maps settings
        $googleSettings = [
            [
                'key' => 'google_maps_api_key',
                'display_name' => 'Google Maps API Key',
                'value' => env('GOOGLE_MAPS_API_KEY', ''),
                'type' => 'password',
                'group' => 'maps',
                'description' => 'Your Google Maps API key for maps, places, and directions',
            ],
            [
                'key' => 'google_maps_enabled',
                'display_name' => 'Enable Google Maps',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'maps',
                'description' => 'Enable Google Maps for geocoding and routing',
            ],
            [
                'key' => 'google_places_enabled',
                'display_name' => 'Enable Google Places',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'maps',
                'description' => 'Enable Google Places for autocomplete and venue search',
            ],
            [
                'key' => 'google_traffic_model',
                'display_name' => 'Traffic Model',
                'value' => 'best_guess',
                'type' => 'select',
                'group' => 'maps',
                'description' => 'Traffic prediction model: best_guess (default), optimistic, or pessimistic',
            ],
        ];

        foreach ($googleSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Google Maps settings
        Setting::whereIn('key', [
            'google_maps_api_key',
            'google_maps_enabled',
            'google_places_enabled',
            'google_traffic_model'
        ])->delete();

        // Restore Mapbox settings (for rollback)
        $mapboxSettings = [
            [
                'key' => 'mapbox_token',
                'value' => env('MAPBOX_TOKEN', ''),
                'type' => 'string',
                'group' => 'maps',
                'label' => 'Mapbox Access Token',
                'description' => 'Your Mapbox access token for maps and geocoding',
            ],
        ];

        foreach ($mapboxSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
};