<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first vehicle type (or create one if needed)
        $vehicleType = VehicleType::first();
        
        if (!$vehicleType) {
            $this->command->error('No vehicle types found. Please run VehicleType seeder first.');
            return;
        }

        // Create a single booking
        Booking::create([
            'booking_number' => 'BK-' . strtoupper(uniqid()),
            'user_id' => null, // Guest booking
            'vehicle_type_id' => $vehicleType->id,
            
            // Customer information
            'customer_first_name' => 'John',
            'customer_last_name' => 'Smith',
            'customer_email' => 'john.smith@example.com',
            'customer_phone' => '+1-813-555-0123',
            
            // Trip details
            'pickup_address' => 'Tampa International Airport (TPA), Tampa, FL 33607, USA',
            'pickup_latitude' => 27.9755,
            'pickup_longitude' => -82.5333,
            'dropoff_address' => '123 Main Street, Tampa, FL 33602, USA',
            'dropoff_latitude' => 27.9506,
            'dropoff_longitude' => -82.4572,
            'pickup_date' => Carbon::now()->addDays(3)->setHour(14)->setMinute(30)->setSecond(0),
            'estimated_distance' => 15.5,
            'estimated_duration' => 25,
            
            // Pricing
            'estimated_fare' => 85.00,
            'final_fare' => null,
            'fare_breakdown' => [
                'base_fare' => 30.00,
                'distance_charge' => 45.00,
                'service_fee' => 10.00,
                'tax' => 0.00,
                'total' => 85.00
            ],
            
            // Status
            'status' => 'pending',
            'payment_status' => 'pending',
            'stripe_payment_intent_id' => null,
            'stripe_payment_method_id' => null,
            
            // Additional info
            'special_instructions' => 'Please wait at arrivals gate. Flight AA1234 arriving at 2:00 PM.',
            'admin_notes' => null,
            'cancellation_reason' => null,
            'cancelled_at' => null,
            
            // Additional fields (dynamic form data)
            'additional_data' => [
                'flight_number' => 'AA1234',
                'number_of_bags' => '2',
                'child_seats_required' => 'no',
                'meet_and_greet_service' => 'yes',
                'special_occasion' => 'no'
            ],
            
            // Email verification
            'email_verified_at' => null,
            'email_verification_code' => null,
            
            // Gratuity
            'gratuity_amount' => 0.00,
            'gratuity_added_at' => null,
            
            // Refunds
            'total_refunded' => 0,
            
            // Timestamps
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $this->command->info('✅ Created 1 sample booking with status: pending');
    }
}