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
        
        // Stripe settings
        $stripeEnabled = filter_var(Setting::get('stripe_enabled', true), FILTER_VALIDATE_BOOLEAN);
        $stripeMode = Setting::get('stripe_mode', 'test');
        $stripePublicKey = null;
        
        if ($stripeEnabled) {
            // Get the appropriate public key based on mode
            if ($stripeMode === 'live') {
                $stripePublicKey = Setting::get('stripe_live_publishable_key');
            } else {
                $stripePublicKey = Setting::get('stripe_test_publishable_key');
            }
            
            // Fallback to environment variable if not set in settings
            if (!$stripePublicKey) {
                $stripePublicKey = env('STRIPE_PUBLIC_KEY');
            }
        }
        
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
        
        // Legal URLs
        $termsUrl = Setting::get('terms_url', 'https://luxridesuv.com/terms');
        $cancellationPolicyUrl = Setting::get('cancellation_policy_url', 'https://luxridesuv.com/cancellation-policy');
        
        // Google Maps settings
        $googleMapsApiKey = Setting::get('google_maps_api_key', env('GOOGLE_MAPS_API_KEY'));
        
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
            'stripe' => [
                'enabled' => $stripeEnabled,
                'mode' => $stripeMode,
                'public_key' => $stripePublicKey,
            ],
            'google_maps' => [
                'api_key' => $googleMapsApiKey,
            ],
            'legal' => [
                'terms_url' => $termsUrl,
                'cancellation_policy_url' => $cancellationPolicyUrl,
            ],
            'form_fields' => $formFields,
        ]);
    }
}