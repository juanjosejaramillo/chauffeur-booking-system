<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'category',
        'subject',
        'body',
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
    ];

    protected function casts(): array
    {
        return [
            'available_variables' => 'array',
            'trigger_events' => 'array',
            'available_triggers' => 'array',
            'recipient_config' => 'array',
            'attach_receipt' => 'boolean',
            'attach_booking_details' => 'boolean',
            'is_active' => 'boolean',
        ];
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
        $body = $this->body;

        foreach ($variables as $key => $value) {
            $subject = str_replace("{{{$key}}}", $value, $subject);
            $body = str_replace("{{{$key}}}", $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
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
        return [
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
            'support_url' => 'Support URL',
            'booking_url' => 'Direct link to booking',
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
            'driver.trip_started' => 'When trip starts',
            'driver.trip_ended' => 'When trip ends',
            
            // Scheduled Events
            'booking.reminder.24h' => '24 hours before pickup',
            'booking.reminder.2h' => '2 hours before pickup',
            'booking.reminder.30m' => '30 minutes before pickup',
            'trip.review.24h' => '24 hours after trip completion',
            
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
}