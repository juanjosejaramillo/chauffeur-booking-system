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
        
        return response()->json([
            'support_phone' => $supportPhone,
            'business_email' => $businessEmail,
            'business_name' => $businessName,
        ]);
    }
}