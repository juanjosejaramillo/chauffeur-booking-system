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
        
        // Create email templates
        $this->createEmailTemplates();
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
    
    private function createEmailTemplates(): void
    {
        EmailTemplate::create([
            'slug' => 'booking-confirmation',
            'name' => 'Booking Confirmation',
            'subject' => 'Your TaxiBook Booking Confirmation - {{booking_number}}',
            'body' => '<h2>Booking Confirmed!</h2>
            <p>Dear {{customer_name}},</p>
            <p>Your booking has been confirmed. Here are your trip details:</p>
            <ul>
                <li><strong>Booking Number:</strong> {{booking_number}}</li>
                <li><strong>Pickup:</strong> {{pickup_address}}</li>
                <li><strong>Dropoff:</strong> {{dropoff_address}}</li>
                <li><strong>Date:</strong> {{pickup_date}}</li>
                <li><strong>Time:</strong> {{pickup_time}}</li>
                <li><strong>Vehicle:</strong> {{vehicle_type}}</li>
                <li><strong>Estimated Fare:</strong> ${{estimated_fare}}</li>
                <li><strong>Special Instructions:</strong> {{special_instructions}}</li>
            </ul>
            <p>Your driver will arrive at the pickup location at the scheduled time. Please be ready 5 minutes before.</p>
            <p>Thank you for choosing TaxiBook!</p>',
            'available_variables' => [
                'booking_number', 'customer_name', 'pickup_address', 'dropoff_address',
                'pickup_date', 'pickup_time', 'vehicle_type', 'estimated_fare', 'special_instructions'
            ],
            'is_active' => true,
        ]);
        
        EmailTemplate::create([
            'slug' => 'trip-reminder',
            'name' => 'Trip Reminder',
            'subject' => 'Reminder: Your TaxiBook Trip Tomorrow - {{booking_number}}',
            'body' => '<h2>Trip Reminder</h2>
            <p>Dear {{customer_name}},</p>
            <p>This is a reminder about your upcoming trip tomorrow:</p>
            <ul>
                <li><strong>Pickup:</strong> {{pickup_address}}</li>
                <li><strong>Time:</strong> {{pickup_time}}</li>
                <li><strong>Vehicle:</strong> {{vehicle_type}}</li>
            </ul>
            <p>Please be ready at the pickup location 5 minutes before the scheduled time.</p>
            <p>Safe travels!</p>',
            'available_variables' => [
                'booking_number', 'customer_name', 'pickup_address', 'pickup_time', 'vehicle_type'
            ],
            'is_active' => true,
        ]);
    }
}