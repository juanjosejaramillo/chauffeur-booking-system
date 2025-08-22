<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only load settings if the database is available and settings table exists
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            if (Schema::hasTable('settings')) {
                $this->loadSettings();
            }
        } catch (\Exception $e) {
            // Database might not be available yet (during migrations)
            return;
        }
    }

    /**
     * Load settings from database and override config values.
     */
    protected function loadSettings(): void
    {
        // Cache settings for performance
        $settings = Cache::remember('app_settings_config', 3600, function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });

        // Override mail configuration
        if (isset($settings['mail_from_address'])) {
            Config::set('mail.from.address', $settings['mail_from_address']);
        }
        if (isset($settings['mail_from_name'])) {
            Config::set('mail.from.name', $settings['mail_from_name']);
        }
        if (isset($settings['mail_reply_to']) && !empty($settings['mail_reply_to'])) {
            Config::set('mail.reply_to', [
                'address' => $settings['mail_reply_to'],
                'name' => $settings['mail_from_name'] ?? config('app.name')
            ]);
        }

        // Override Stripe configuration
        if (isset($settings['stripe_enabled']) && $settings['stripe_enabled']) {
            $mode = $settings['stripe_mode'] ?? 'test';
            
            if ($mode === 'live') {
                if (isset($settings['stripe_live_publishable_key'])) {
                    Config::set('services.stripe.key', $settings['stripe_live_publishable_key']);
                    Config::set('stripe.publishable_key', $settings['stripe_live_publishable_key']);
                }
                if (isset($settings['stripe_live_secret_key'])) {
                    Config::set('services.stripe.secret', $settings['stripe_live_secret_key']);
                    Config::set('stripe.secret_key', $settings['stripe_live_secret_key']);
                    Config::set('cashier.secret', $settings['stripe_live_secret_key']);
                }
            } else {
                // Test mode
                if (isset($settings['stripe_test_publishable_key'])) {
                    Config::set('services.stripe.key', $settings['stripe_test_publishable_key']);
                    Config::set('stripe.publishable_key', $settings['stripe_test_publishable_key']);
                }
                if (isset($settings['stripe_test_secret_key'])) {
                    Config::set('services.stripe.secret', $settings['stripe_test_secret_key']);
                    Config::set('stripe.secret_key', $settings['stripe_test_secret_key']);
                    Config::set('cashier.secret', $settings['stripe_test_secret_key']);
                }
            }
            
            if (isset($settings['stripe_webhook_secret'])) {
                Config::set('services.stripe.webhook_secret', $settings['stripe_webhook_secret']);
                Config::set('stripe.webhook_secret', $settings['stripe_webhook_secret']);
                Config::set('cashier.webhook.secret', $settings['stripe_webhook_secret']);
            }
        }

        // Override Google Maps configuration
        if (isset($settings['google_maps_api_key'])) {
            Config::set('services.google.maps_api_key', $settings['google_maps_api_key']);
            Config::set('google.api_key', $settings['google_maps_api_key']);
        }
        if (isset($settings['google_traffic_model'])) {
            Config::set('google.traffic_model', $settings['google_traffic_model']);
        }

        // Override app configuration
        if (isset($settings['business_name'])) {
            Config::set('app.name', $settings['business_name']);
        }
        if (isset($settings['app_timezone'])) {
            Config::set('app.timezone', $settings['app_timezone']);
        }
        if (isset($settings['app_locale'])) {
            Config::set('app.locale', $settings['app_locale']);
        }
        if (isset($settings['currency'])) {
            Config::set('app.currency', $settings['currency']);
        }
        if (isset($settings['website_url'])) {
            Config::set('app.url', $settings['website_url']);
        }

        // Set custom config values for easy access
        Config::set('business.name', $settings['business_name'] ?? config('app.name'));
        Config::set('business.email', $settings['business_email'] ?? config('mail.from.address'));
        Config::set('business.phone', $settings['business_phone'] ?? '1-800-LUXRIDE');
        Config::set('business.address', $settings['business_address'] ?? '');
        Config::set('business.support_email', $settings['support_email'] ?? $settings['business_email'] ?? config('mail.from.address'));
        Config::set('business.support_phone', $settings['support_phone'] ?? $settings['business_phone'] ?? '1-800-LUXRIDE');
        Config::set('business.admin_email', $settings['admin_email'] ?? $settings['business_email'] ?? config('mail.from.address'));
        Config::set('business.admin_name', $settings['admin_name'] ?? 'Administrator');
        
        // Validate critical settings are configured
        $this->validateCriticalSettings($settings);
        
        // Store settings mode for reference
        Config::set('settings.stripe_mode', $settings['stripe_mode'] ?? 'test');
        Config::set('settings.stripe_enabled', $settings['stripe_enabled'] ?? false);
        Config::set('settings.google_maps_enabled', $settings['google_maps_enabled'] ?? true);
        Config::set('settings.maintenance_mode', $settings['maintenance_mode'] ?? false);
    }
    
    /**
     * Validate that critical settings are configured.
     */
    protected function validateCriticalSettings(array $settings): void
    {
        $missingSettings = [];
        
        // Check for critical business settings
        if (empty($settings['business_name'])) {
            $missingSettings[] = 'Business Name';
        }
        
        if (empty($settings['business_email'])) {
            $missingSettings[] = 'Business Email';
        }
        
        if (empty($settings['admin_email'])) {
            $missingSettings[] = 'Admin Email';
        }
        
        if (empty($settings['admin_name'])) {
            $missingSettings[] = 'Admin Name';
        }
        
        // Log warning if critical settings are missing
        if (!empty($missingSettings)) {
            \Log::warning('Critical settings are not configured: ' . implode(', ', $missingSettings), [
                'settings' => $missingSettings,
                'action' => 'Please configure these settings in the admin panel at /admin/settings'
            ]);
            
            // In production, you might want to throw an exception or redirect to settings
            // For now, we'll just log the warning
            if (app()->environment('production') && !app()->runningInConsole()) {
                // Could add a notification to admin users here
                Cache::put('settings_warning', 'Critical settings missing: ' . implode(', ', $missingSettings), 3600);
            }
        }
    }
}