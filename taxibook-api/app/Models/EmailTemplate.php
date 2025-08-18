<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'category',
        'subject',
        'body',
        'html_body',
        'css_styles',
        'template_type',
        'version_history',
        'template_components',
        'test_recipients',
        'parent_template',
        'meta_data',
        'description',
        'cc_emails',
        'bcc_emails',
        'recipient_config',
        'attach_receipt',
        'attach_booking_details',
        'delay_minutes',
        'trigger_events',
        'available_triggers',
        'priority',
        'available_variables',
        'is_active',
        'send_to_customer',
        'send_to_admin',
        'send_to_driver',
        'send_timing_type',
        'send_timing_value',
        'send_timing_unit',
    ];

    protected function casts(): array
    {
        return [
            'available_variables' => 'array',
            'trigger_events' => 'array',
            'available_triggers' => 'array',
            'recipient_config' => 'array',
            'version_history' => 'array',
            'template_components' => 'array',
            'test_recipients' => 'array',
            'meta_data' => 'array',
            'attach_receipt' => 'boolean',
            'attach_booking_details' => 'boolean',
            'is_active' => 'boolean',
            'send_to_customer' => 'boolean',
            'send_to_admin' => 'boolean',
            'send_to_driver' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && !$model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->name, $model->id);
            }
        });
    }

    public static function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function render(array $variables = []): array
    {
        $subject = $this->subject;
        $body = $this->getBodyContent();

        // Replace shortcodes in subject - templates use {{var}} format
        foreach ($variables as $key => $value) {
            // Replace double braces {{variable}}
            $subject = str_replace("{{" . $key . "}}", $value, $subject);
            // Also try triple braces for compatibility
            $subject = str_replace("{{{" . $key . "}}}", $value, $subject);
        }

        // Render based on template type
        switch ($this->template_type) {
            case 'html':
                $body = $this->renderHtmlTemplate($body, $variables);
                break;
            case 'wysiwyg':
                $body = $this->renderWysiwygTemplate($body, $variables);
                break;
            case 'blade':
            default:
                $body = $this->renderBladeTemplate($body, $variables);
                break;
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    protected function getBodyContent(): string
    {
        // Use HTML body if available and template type is HTML
        if ($this->template_type === 'html' && $this->html_body) {
            return $this->html_body;
        }
        
        return $this->body;
    }

    protected function renderHtmlTemplate(string $body, array $variables): string
    {
        // Apply CSS styles if available
        if ($this->css_styles) {
            $body = $this->inlineCss($body, $this->css_styles);
        }
        
        // Process conditional blocks first (simple implementation)
        $body = $this->processConditionals($body, $variables);
        
        // Replace variables
        foreach ($variables as $key => $value) {
            // Skip boolean values used for conditionals
            if (is_bool($value)) {
                continue;
            }
            $body = str_replace("{{" . $key . "}}", $value ?? '', $body);
            $body = str_replace("{{{" . $key . "}}}", $value ?? '', $body);
        }
        
        return $body;
    }
    
    /**
     * Process conditional blocks in templates
     */
    protected function processConditionals(string $body, array $variables): string
    {
        // Process {{#if variable}} ... {{/if}} blocks including those checking field values
        // First handle field checks like {{#if field_flight_number}}
        $pattern = '/\{\{#if\s+([a-zA-Z_]+)\}\}(.*?)\{\{\/if\}\}/s';
        
        // Keep processing until no more conditionals are found (handles nested conditionals)
        $maxIterations = 10;
        $iteration = 0;
        
        while (preg_match($pattern, $body) && $iteration < $maxIterations) {
            $body = preg_replace_callback($pattern, function($matches) use ($variables) {
                $varName = $matches[1];
                $content = $matches[2];
                
                // Check if variable exists and is truthy
                // Special handling for field_ variables - check if they have non-empty values
                if (str_starts_with($varName, 'field_')) {
                    if (isset($variables[$varName]) && !empty($variables[$varName])) {
                        return $content;
                    }
                } else {
                    // For boolean flags and other variables
                    if (isset($variables[$varName]) && $variables[$varName]) {
                        return $content;
                    }
                }
                
                // Variable is falsy or empty, remove the entire block
                return '';
            }, $body);
            
            $iteration++;
        }
        
        // Also handle inline conditionals like {{/if}} that might be left over
        $body = preg_replace('/\{\{\/if\}\}/', '', $body);
        
        return $body;
    }

    protected function renderWysiwygTemplate(string $body, array $variables): string
    {
        // Replace variables in WYSIWYG content
        foreach ($variables as $key => $value) {
            $body = str_replace("{{" . $key . "}}", $value, $body);
            $body = str_replace("{{{" . $key . "}}}", $value, $body);
        }
        
        return $body;
    }

    protected function renderBladeTemplate(string $body, array $variables): string
    {
        // Process conditional blocks first
        $body = $this->processConditionals($body, $variables);
        
        // Process Blade template syntax if present
        if (str_contains($body, '@extends') || str_contains($body, '@section')) {
            try {
                // For Blade templates, replace {{variable}} shortcodes with actual values
                $processedBody = $body;
                foreach ($variables as $key => $value) {
                    if (is_bool($value)) continue;
                    // Replace {{variable}} format (double braces)
                    $processedBody = str_replace("{{" . $key . "}}", $value ?? '', $processedBody);
                    // Also replace {{{variable}}} format (triple braces) for compatibility
                    $processedBody = str_replace("{{{" . $key . "}}}", $value ?? '', $processedBody);
                }
                
                // Now render with Blade
                $body = \Illuminate\Support\Facades\Blade::render($processedBody, $variables);
            } catch (\Exception $e) {
                // If Blade rendering fails, log and use original body with manual replacement
                \Illuminate\Support\Facades\Log::warning("Failed to render Blade template for {$this->slug}: " . $e->getMessage());
                
                // Fallback to manual replacement
                foreach ($variables as $key => $value) {
                    if (is_bool($value)) continue;
                    $body = str_replace("{{" . $key . "}}", $value ?? '', $body);
                    $body = str_replace("{{{" . $key . "}}}", $value ?? '', $body);
                }
            }
        } else {
            // For non-Blade templates, replace shortcodes manually
            foreach ($variables as $key => $value) {
                if (is_bool($value)) continue;
                // Replace double braces {{variable}}
                $body = str_replace("{{" . $key . "}}", $value ?? '', $body);
                // Also replace triple braces {{{variable}}} for compatibility
                $body = str_replace("{{{" . $key . "}}}", $value ?? '', $body);
            }
        }
        
        return $body;
    }

    protected function inlineCss(string $html, string $css): string
    {
        // Simple CSS inlining for email compatibility
        // In production, you might want to use a library like Emogrifier
        $style = "<style>{$css}</style>";
        
        // If the HTML already has a <head> section, insert styles there
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $style . '</head>', $html);
        } else {
            // Otherwise, prepend the styles
            $html = $style . $html;
        }
        
        return $html;
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForEvent($query, $event)
    {
        return $query->whereJsonContains('trigger_events', $event);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'template_slug', 'slug');
    }

    public function getRecipientEmails($type = 'cc'): array
    {
        $field = $type === 'cc' ? 'cc_emails' : 'bcc_emails';
        
        if (empty($this->$field)) {
            return [];
        }

        return array_map('trim', explode(',', $this->$field));
    }

    public static function getAvailableVariables(): array
    {
        $coreVariables = [
            'booking_number' => 'Unique booking reference number',
            'customer_name' => 'Customer full name',
            'customer_first_name' => 'Customer first name',
            'customer_last_name' => 'Customer last name',
            'customer_email' => 'Customer email address',
            'customer_phone' => 'Customer phone number',
            'pickup_address' => 'Pickup location address',
            'dropoff_address' => 'Dropoff location address',
            'pickup_date' => 'Pickup date (formatted)',
            'pickup_time' => 'Pickup time (formatted)',
            'vehicle_type' => 'Selected vehicle type',
            'estimated_fare' => 'Estimated fare amount',
            'final_fare' => 'Final fare amount',
            'special_instructions' => 'Special instructions from customer',
            'admin_notes' => 'Admin notes',
            'cancellation_reason' => 'Reason for cancellation',
            'refund_amount' => 'Refund amount',
            'company_name' => 'Company name',
            'company_phone' => 'Company contact phone',
            'company_email' => 'Company contact email',
            'company_address' => 'Company business address',
            'support_url' => 'Support URL',
            'website_url' => 'Main website URL',
            'booking_url' => 'Direct link to booking',
            'receipt_url' => 'Direct link to receipt PDF',
            'current_year' => 'Current year (e.g., 2025)',
            'current_date' => 'Current date (e.g., January 18, 2025)',
            'current_time' => 'Current time (e.g., 3:45 PM)',
            'flight_number' => 'Flight number (if provided)',
            'is_airport_transfer' => 'Boolean flag for airport transfers',
            'has_special_instructions' => 'Boolean flag if special instructions exist',
            'has_flight_number' => 'Boolean flag if flight number exists',
            'has_additional_fields' => 'Boolean flag if dynamic fields exist',
        ];

        // Add dynamic field variables
        $dynamicVariables = self::getDynamicFieldVariables();
        
        return array_merge($coreVariables, $dynamicVariables);
    }

    /**
     * Get available dynamic field variables from BookingFormField
     */
    public static function getDynamicFieldVariables(): array
    {
        $variables = [];
        
        try {
            $fields = \App\Models\BookingFormField::enabled()->get();
            
            foreach ($fields as $field) {
                // Add the field variable
                $variables['field_' . $field->key] = $field->label . ' (Dynamic Field)';
                
                // Add display version for select/checkbox fields
                if (in_array($field->type, ['select', 'checkbox'])) {
                    $variables['field_' . $field->key . '_display'] = $field->label . ' (Display Value)';
                }
            }
        } catch (\Exception $e) {
            // If table doesn't exist or error occurs, return empty array
            \Illuminate\Support\Facades\Log::warning('Could not load dynamic field variables: ' . $e->getMessage());
        }
        
        return $variables;
    }

    /**
     * Get grouped available variables for UI display
     */
    public static function getGroupedAvailableVariables(): array
    {
        return [
            'System & Date' => [
                'current_year' => 'Current year (e.g., 2025)',
                'current_date' => 'Current date (e.g., January 18, 2025)',
                'current_time' => 'Current time (e.g., 3:45 PM)',
            ],
            'Customer Information' => [
                'customer_name' => 'Customer full name',
                'customer_first_name' => 'Customer first name',
                'customer_last_name' => 'Customer last name',
                'customer_email' => 'Customer email address',
                'customer_phone' => 'Customer phone number',
            ],
            'Booking Details' => [
                'booking_number' => 'Unique booking reference number',
                'pickup_address' => 'Pickup location address',
                'dropoff_address' => 'Dropoff location address',
                'pickup_date' => 'Pickup date (formatted)',
                'pickup_time' => 'Pickup time (formatted)',
                'vehicle_type' => 'Selected vehicle type',
                'special_instructions' => 'Special instructions from customer',
                'flight_number' => 'Flight number (if provided)',
            ],
            'Pricing' => [
                'estimated_fare' => 'Estimated fare amount',
                'final_fare' => 'Final fare amount',
                'refund_amount' => 'Refund amount',
            ],
            'Company Information' => [
                'company_name' => 'Company name',
                'company_phone' => 'Company contact phone',
                'company_email' => 'Company contact email',
                'company_address' => 'Company business address',
                'support_url' => 'Support URL',
            ],
            'Links' => [
                'website_url' => 'Main website URL',
                'booking_url' => 'Direct link to booking',
                'receipt_url' => 'Direct link to receipt PDF',
            ],
            'Conditional Flags' => [
                'is_airport_transfer' => 'True if airport pickup or dropoff',
                'has_special_instructions' => 'True if special instructions exist',
                'has_flight_number' => 'True if flight number provided',
                'has_additional_fields' => 'True if any dynamic fields filled',
            ],
            'Dynamic Fields' => self::getDynamicFieldVariables(),
        ];
    }

    public static function getAvailableTriggers(): array
    {
        return [
            // Booking Events
            'booking.created' => 'When a new booking is created',
            'booking.confirmed' => 'When booking is confirmed (payment authorized)',
            'booking.modified' => 'When booking details are changed',
            'booking.cancelled' => 'When booking is cancelled',
            'booking.completed' => 'When booking/trip is completed',
            
            // Payment Events
            'payment.authorized' => 'When payment is authorized',
            'payment.captured' => 'When payment is captured',
            'payment.refunded' => 'When payment is refunded',
            'payment.failed' => 'When payment fails',
            
            // Driver Events
            'driver.assigned' => 'When driver is assigned to booking',
            'driver.enroute' => 'When driver starts journey to pickup',
            'driver.arrived' => 'When driver arrives at pickup location',
            
            // Trip Events
            'trip.started' => 'When trip starts',
            'trip.ended' => 'When trip ends',
            
            // Admin Events
            'admin.daily_summary' => 'Daily summary report',
            'admin.weekly_summary' => 'Weekly summary report',
            'admin.payment_issue' => 'When payment issue occurs',
            
            // Custom Events
            'custom.manual' => 'Manually triggered email',
        ];
    }

    public static function getRecipientTypes(): array
    {
        return [
            'customer' => 'Customer (booking email)',
            'admin' => 'All admin emails',
            'specific_admin' => 'Specific admin email(s)',
            'driver' => 'Assigned driver',
            'custom' => 'Custom email address',
            'role' => 'Users with specific role',
            'department' => 'Department email',
        ];
    }

    public function getRecipientsForBooking($booking = null): array
    {
        $recipients = [];
        $config = $this->recipient_config ?? [];

        foreach ($config as $recipientType => $settings) {
            switch ($recipientType) {
                case 'customer':
                    if ($booking && $settings['enabled'] ?? false) {
                        $recipients[] = [
                            'email' => $booking->customer_email,
                            'name' => $booking->customer_full_name,
                            'type' => 'to'
                        ];
                    }
                    break;
                    
                case 'admin':
                    if ($settings['enabled'] ?? false) {
                        $adminEmails = config('app.admin_emails', []);
                        foreach ($adminEmails as $email) {
                            $recipients[] = [
                                'email' => $email,
                                'name' => 'Admin',
                                'type' => $settings['send_as'] ?? 'to'
                            ];
                        }
                    }
                    break;
                    
                case 'specific_admin':
                    if ($settings['enabled'] ?? false) {
                        $emails = $settings['emails'] ?? [];
                        foreach ($emails as $email) {
                            $recipients[] = [
                                'email' => $email,
                                'name' => 'Admin',
                                'type' => $settings['send_as'] ?? 'cc'
                            ];
                        }
                    }
                    break;
                    
                case 'driver':
                    if ($booking && ($settings['enabled'] ?? false)) {
                        // If driver relationship exists
                        if (method_exists($booking, 'driver') && $booking->driver) {
                            $recipients[] = [
                                'email' => $booking->driver->email,
                                'name' => $booking->driver->name,
                                'type' => 'to'
                            ];
                        }
                    }
                    break;
                    
                case 'custom':
                    if ($settings['enabled'] ?? false) {
                        $email = $settings['email'] ?? null;
                        if ($email) {
                            $recipients[] = [
                                'email' => $email,
                                'name' => $settings['name'] ?? 'Recipient',
                                'type' => $settings['send_as'] ?? 'to'
                            ];
                        }
                    }
                    break;
            }
        }

        return $recipients;
    }

    /**
     * Get when this email should be sent as a Carbon datetime
     */
    public function getSendTimeFor($booking): ?\Carbon\Carbon
    {
        if ($this->send_timing_type === 'immediate') {
            return now();
        }

        $baseTime = null;
        
        switch ($this->send_timing_type) {
            case 'before_pickup':
                $baseTime = $booking->pickup_date;
                break;
            case 'after_pickup':
                $baseTime = $booking->pickup_date;
                break;
            case 'after_booking':
                $baseTime = $booking->created_at;
                break;
            case 'after_completion':
                // Assuming we have a completed_at field or use updated_at when status is completed
                $baseTime = $booking->completed_at ?? $booking->updated_at;
                break;
        }

        if (!$baseTime) {
            return null;
        }

        $minutes = $this->getTimingInMinutes();
        
        if ($this->send_timing_type === 'before_pickup') {
            return $baseTime->copy()->subMinutes($minutes);
        } else {
            return $baseTime->copy()->addMinutes($minutes);
        }
    }

    /**
     * Convert timing value and unit to minutes
     */
    public function getTimingInMinutes(): int
    {
        $value = $this->send_timing_value;
        
        switch ($this->send_timing_unit) {
            case 'minutes':
                return $value;
            case 'hours':
                return $value * 60;
            case 'days':
                return $value * 60 * 24;
            default:
                return 0;
        }
    }

    /**
     * Get human-readable timing description
     */
    public function getTimingDescription(): string
    {
        if ($this->send_timing_type === 'immediate') {
            return 'Send immediately';
        }

        $value = $this->send_timing_value;
        $unit = $value === 1 ? rtrim($this->send_timing_unit, 's') : $this->send_timing_unit;
        
        switch ($this->send_timing_type) {
            case 'before_pickup':
                return "Send {$value} {$unit} before pickup";
            case 'after_pickup':
                return "Send {$value} {$unit} after pickup";
            case 'after_booking':
                return "Send {$value} {$unit} after booking created";
            case 'after_completion':
                return "Send {$value} {$unit} after trip completed";
            default:
                return 'Send immediately';
        }
    }

    /**
     * Save current version to history
     */
    public function saveVersion(string $changeNote = null): void
    {
        $history = $this->version_history ?? [];
        
        $version = [
            'version' => count($history) + 1,
            'subject' => $this->subject,
            'body' => $this->body,
            'html_body' => $this->html_body,
            'css_styles' => $this->css_styles,
            'template_type' => $this->template_type,
            'saved_at' => now()->toIso8601String(),
            'saved_by' => auth()->user()->name ?? 'System',
            'change_note' => $changeNote,
        ];
        
        // Keep only last 10 versions
        array_unshift($history, $version);
        $this->version_history = array_slice($history, 0, 10);
        $this->save();
    }

    /**
     * Restore from a specific version
     */
    public function restoreVersion(int $versionNumber): bool
    {
        $history = $this->version_history ?? [];
        
        foreach ($history as $version) {
            if ($version['version'] == $versionNumber) {
                // Save current as a version first
                $this->saveVersion('Before restoring to version ' . $versionNumber);
                
                // Restore the version
                $this->subject = $version['subject'];
                $this->body = $version['body'];
                $this->html_body = $version['html_body'] ?? null;
                $this->css_styles = $version['css_styles'] ?? null;
                $this->template_type = $version['template_type'] ?? 'blade';
                $this->save();
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * Duplicate this template
     */
    public function duplicate(string $newName = null): self
    {
        $duplicate = $this->replicate();
        $duplicate->name = $newName ?? $this->name . ' (Copy)';
        $duplicate->slug = self::generateUniqueSlug($duplicate->name);
        $duplicate->is_active = false; // Start inactive
        $duplicate->version_history = []; // Start fresh history
        $duplicate->save();
        
        return $duplicate;
    }

    /**
     * Export template to JSON
     */
    public function export(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'subject' => $this->subject,
            'body' => $this->body,
            'html_body' => $this->html_body,
            'css_styles' => $this->css_styles,
            'template_type' => $this->template_type,
            'description' => $this->description,
            'available_variables' => $this->available_variables,
            'trigger_events' => $this->trigger_events,
            'template_components' => $this->template_components,
            'meta_data' => $this->meta_data,
            'exported_at' => now()->toIso8601String(),
            'exported_from' => config('app.url'),
        ];
    }

    /**
     * Import template from JSON
     */
    public static function import(array $data, string $namePrefix = null): self
    {
        $template = new self();
        $template->name = ($namePrefix ?? '') . $data['name'];
        $template->slug = self::generateUniqueSlug($template->name);
        $template->category = $data['category'] ?? 'imported';
        $template->subject = $data['subject'];
        $template->body = $data['body'];
        $template->html_body = $data['html_body'] ?? null;
        $template->css_styles = $data['css_styles'] ?? null;
        $template->template_type = $data['template_type'] ?? 'blade';
        $template->description = $data['description'] ?? null;
        $template->available_variables = $data['available_variables'] ?? [];
        $template->trigger_events = $data['trigger_events'] ?? [];
        $template->template_components = $data['template_components'] ?? [];
        $template->is_active = false; // Start inactive
        $template->meta_data = array_merge($data['meta_data'] ?? [], [
            'imported_at' => now()->toIso8601String(),
            'imported_from' => $data['exported_from'] ?? 'unknown',
        ]);
        $template->save();
        
        return $template;
    }

    /**
     * Scope to get templates that should be sent now
     */
    public function scopeDueForSending($query)
    {
        return $query->where('is_active', true)
            ->where('send_timing_type', '!=', 'immediate');
    }
}