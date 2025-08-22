<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get or set a setting value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key = null, $default = null)
    {
        if (is_null($key)) {
            return app(Setting::class);
        }

        return Setting::get($key, $default);
    }
}

if (!function_exists('business_name')) {
    /**
     * Get the business name.
     *
     * @return string
     */
    function business_name()
    {
        return setting('business_name', config('app.name'));
    }
}

if (!function_exists('business_email')) {
    /**
     * Get the business email.
     *
     * @return string
     */
    function business_email()
    {
        return setting('business_email', config('mail.from.address'));
    }
}

if (!function_exists('business_phone')) {
    /**
     * Get the business phone.
     *
     * @return string
     */
    function business_phone()
    {
        return setting('business_phone', '1-800-LUXRIDE');
    }
}

if (!function_exists('support_email')) {
    /**
     * Get the support email.
     *
     * @return string
     */
    function support_email()
    {
        return setting('support_email') ?: business_email();
    }
}

if (!function_exists('support_phone')) {
    /**
     * Get the support phone.
     *
     * @return string
     */
    function support_phone()
    {
        return setting('support_phone') ?: business_phone();
    }
}

if (!function_exists('stripe_key')) {
    /**
     * Get the appropriate Stripe key based on mode.
     *
     * @param string $type 'publishable' or 'secret'
     * @return string|null
     */
    function stripe_key($type = 'publishable')
    {
        if (!setting('stripe_enabled', true)) {
            return null;
        }

        $mode = setting('stripe_mode', 'test');
        
        if ($type === 'publishable') {
            return $mode === 'live' 
                ? setting('stripe_live_publishable_key')
                : setting('stripe_test_publishable_key');
        }
        
        return $mode === 'live'
            ? setting('stripe_live_secret_key')
            : setting('stripe_test_secret_key');
    }
}

if (!function_exists('google_maps_api_key')) {
    /**
     * Get the Google Maps API key.
     *
     * @return string|null
     */
    function google_maps_api_key()
    {
        if (!setting('google_maps_enabled', true)) {
            return null;
        }

        return setting('google_maps_api_key');
    }
}