<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'display_name',
        'value',
        'type',
        'description',
        'options',
        'validation_rules',
        'order',
        'is_visible',
        'is_encrypted'
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_visible' => 'boolean',
        'is_encrypted' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are saved
        static::saved(function ($setting) {
            Cache::forget('settings');
            Cache::forget("setting_{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget('settings');
            Cache::forget("setting_{$setting->key}");
        });
    }

    /**
     * Get the value attribute.
     */
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Cast value based on type
        switch ($this->type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (float) $value : 0;
            case 'json':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }

    /**
     * Set the value attribute.
     */
    public function setValueAttribute($value)
    {
        // Handle JSON type
        if ($this->type === 'json' && is_array($value)) {
            $value = json_encode($value);
        }

        // Handle boolean type
        if ($this->type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        // Encrypt if needed
        if ($this->is_encrypted && $value) {
            $value = Crypt::encryptString($value);
        }

        $this->attributes['value'] = $value;
    }

    /**
     * Get all settings grouped by group.
     */
    public static function getAllGrouped()
    {
        return Cache::remember('settings', 3600, function () {
            return static::orderBy('group')
                ->orderBy('order')
                ->get()
                ->groupBy('group');
        });
    }

    /**
     * Get a specific setting value by key.
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a specific setting value by key.
     */
    public static function set($key, $value)
    {
        $setting = static::where('key', $key)->first();
        
        if ($setting) {
            $setting->value = $value;
            $setting->save();
            return $setting;
        }

        return null;
    }

    /**
     * Get settings for email templates.
     */
    public static function getEmailVariables()
    {
        return [
            'business_name' => self::get('business_name', config('app.name')),
            'business_email' => self::get('business_email', config('mail.from.address')),
            'business_phone' => self::get('business_phone', '1-800-LUXRIDE'),
            'business_address' => self::get('business_address', ''),
            'support_email' => self::get('support_email', self::get('business_email')),
            'support_phone' => self::get('support_phone', self::get('business_phone')),
            'website_url' => self::get('website_url', config('app.url')),
        ];
    }
}