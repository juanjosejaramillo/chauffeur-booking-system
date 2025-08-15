<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function getPublicSettings()
    {
        $supportPhone = Setting::get('support_phone') ?: Setting::get('business_phone', '1-800-LUXRIDE');
        $businessEmail = Setting::get('business_email', 'info@luxride.com');
        $businessName = Setting::get('business_name', 'LuxRide');
        
        // Booking settings with defaults
        $minimumBookingHours = (int) Setting::get('minimum_booking_hours', 2);
        $maximumBookingDays = (int) Setting::get('maximum_booking_days', 90);
        $allowSameDayBooking = filter_var(Setting::get('allow_same_day_booking', true), FILTER_VALIDATE_BOOLEAN);
        $bookingTimeIncrement = (int) Setting::get('booking_time_increment', 5);
        
        return response()->json([
            'support_phone' => $supportPhone,
            'business_email' => $businessEmail,
            'business_name' => $businessName,
            'booking' => [
                'minimum_hours' => $minimumBookingHours,
                'maximum_days' => $maximumBookingDays,
                'allow_same_day' => $allowSameDayBooking,
                'time_increment' => $bookingTimeIncrement,
            ],
        ]);
    }
}