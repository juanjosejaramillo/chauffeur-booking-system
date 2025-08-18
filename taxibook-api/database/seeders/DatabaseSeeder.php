<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\VehiclePricingTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@taxibook.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
        ]);
        
        // Create vehicle types
        $this->createVehicleTypes();
        
        // Create booking form fields first (needed for dynamic shortcodes)
        $this->call(BookingFormFieldSeeder::class);
        
        // Create all email templates with dynamic shortcode support
        $this->call(ComprehensiveEmailTemplateSeeder::class);
    }
    
    private function createVehicleTypes(): void
    {
        // Economy Vehicle
        $economy = VehicleType::create([
            'display_name' => 'Economy',
            'slug' => 'economy',
            'description' => 'Affordable rides for everyday travel',
            'max_passengers' => 4,
            'max_luggage' => 2,
            'base_fare' => 10.00,
            'base_miles_included' => 2,
            'per_minute_rate' => 0.20,
            'minimum_fare' => 15.00,
            'service_fee_multiplier' => 1.00,
            'tax_rate' => 8.875,
            'tax_enabled' => true,
            'is_active' => true,
            'sort_order' => 1,
            'features' => ['Standard comfort', 'AC', 'Radio'],
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $economy->id,
            'from_mile' => 0,
            'to_mile' => 10,
            'per_mile_rate' => 2.00,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $economy->id,
            'from_mile' => 10,
            'to_mile' => 25,
            'per_mile_rate' => 1.75,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $economy->id,
            'from_mile' => 25,
            'to_mile' => null,
            'per_mile_rate' => 1.50,
        ]);
        
        // Standard Vehicle
        $standard = VehicleType::create([
            'display_name' => 'Standard',
            'slug' => 'standard',
            'description' => 'Comfortable rides with extra space',
            'max_passengers' => 4,
            'max_luggage' => 3,
            'base_fare' => 15.00,
            'base_miles_included' => 2,
            'per_minute_rate' => 0.30,
            'minimum_fare' => 20.00,
            'service_fee_multiplier' => 1.00,
            'tax_rate' => 8.875,
            'tax_enabled' => true,
            'is_active' => true,
            'sort_order' => 2,
            'features' => ['Premium comfort', 'AC', 'Phone chargers', 'Water bottles'],
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $standard->id,
            'from_mile' => 0,
            'to_mile' => 10,
            'per_mile_rate' => 2.50,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $standard->id,
            'from_mile' => 10,
            'to_mile' => 25,
            'per_mile_rate' => 2.25,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $standard->id,
            'from_mile' => 25,
            'to_mile' => null,
            'per_mile_rate' => 2.00,
        ]);
        
        // Premium Vehicle
        $premium = VehicleType::create([
            'display_name' => 'Premium',
            'slug' => 'premium',
            'description' => 'Luxury vehicles for special occasions',
            'max_passengers' => 4,
            'max_luggage' => 3,
            'base_fare' => 25.00,
            'base_miles_included' => 3,
            'per_minute_rate' => 0.50,
            'minimum_fare' => 35.00,
            'service_fee_multiplier' => 1.00,
            'tax_rate' => 8.875,
            'tax_enabled' => true,
            'is_active' => true,
            'sort_order' => 3,
            'features' => ['Luxury vehicles', 'Leather seats', 'AC', 'Phone chargers', 'Water bottles', 'WiFi'],
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $premium->id,
            'from_mile' => 0,
            'to_mile' => 10,
            'per_mile_rate' => 3.50,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $premium->id,
            'from_mile' => 10,
            'to_mile' => 25,
            'per_mile_rate' => 3.00,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $premium->id,
            'from_mile' => 25,
            'to_mile' => null,
            'per_mile_rate' => 2.75,
        ]);
        
        // SUV Vehicle
        $suv = VehicleType::create([
            'display_name' => 'SUV',
            'slug' => 'suv',
            'description' => 'Spacious vehicles for groups and luggage',
            'max_passengers' => 6,
            'max_luggage' => 5,
            'base_fare' => 30.00,
            'base_miles_included' => 2,
            'per_minute_rate' => 0.40,
            'minimum_fare' => 40.00,
            'service_fee_multiplier' => 1.00,
            'tax_rate' => 8.875,
            'tax_enabled' => true,
            'is_active' => true,
            'sort_order' => 4,
            'features' => ['Extra space', '3 rows of seats', 'AC', 'Phone chargers', 'Large trunk'],
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $suv->id,
            'from_mile' => 0,
            'to_mile' => 10,
            'per_mile_rate' => 3.00,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $suv->id,
            'from_mile' => 10,
            'to_mile' => 25,
            'per_mile_rate' => 2.75,
        ]);
        
        VehiclePricingTier::create([
            'vehicle_type_id' => $suv->id,
            'from_mile' => 25,
            'to_mile' => null,
            'per_mile_rate' => 2.50,
        ]);
    }
    
}