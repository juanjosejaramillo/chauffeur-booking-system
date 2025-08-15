<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\BookingFormField;

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
        
        // Get enabled form fields
        $formFields = BookingFormField::enabled()
            ->ordered()
            ->get()
            ->map(function ($field) {
                return [
                    'key' => $field->key,
                    'label' => $field->label,
                    'placeholder' => $field->placeholder,
                    'type' => $field->type,
                    'required' => $field->required,
                    'options' => $field->options,
                    'validation_rules' => $field->validation_rules,
                    'conditions' => $field->conditions,
                    'helper_text' => $field->helper_text,
                    'group' => $field->group,
                ];
            });
        
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
            'form_fields' => $formFields,
        ]);
    }
}