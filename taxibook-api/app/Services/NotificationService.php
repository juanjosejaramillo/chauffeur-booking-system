<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class NotificationService
{
    protected $companyConfig;
    
    public function __construct()
    {
        try {
            // Try to get settings from database
            $businessName = Setting::get('business_name', config('app.name', 'LuxRide'));
            $businessEmail = Setting::get('business_email', config('mail.from.address', 'noreply@luxride.com'));
            $businessPhone = Setting::get('business_phone', '1-800-LUXRIDE');
            
            $this->companyConfig = [
                'name' => $businessName, // Add 'name' for email templates
                'company_name' => $businessName,
                'company_email' => $businessEmail,
                'company_phone' => $businessPhone,
                'support_url' => Setting::get('website_url', config('app.url')) . '/support',
                'support_email' => Setting::get('support_email', $businessEmail),
                'support_phone' => Setting::get('support_phone', $businessPhone),
                'business_address' => Setting::get('business_address', ''),
            ];
        } catch (\Exception $e) {
            // Fallback to config values if settings table doesn't exist
            $this->companyConfig = [
                'name' => config('app.name', 'LuxRide'), // Add 'name' for email templates
                'company_name' => config('app.name', 'LuxRide'),
                'company_email' => config('mail.from.address', 'noreply@luxride.com'),
                'company_phone' => '1-800-LUXRIDE',
                'support_url' => config('app.url') . '/support',
                'support_email' => config('mail.from.address', 'noreply@luxride.com'),
                'support_phone' => '1-800-LUXRIDE',
                'business_address' => '',
            ];
        }
    }

    /**
     * Send email notification based on template
     */
    public function sendEmailNotification(
        string $templateSlug,
        Booking $booking = null,
        array $additionalVariables = [],
        array $attachments = []
    ): bool {
        try {
            $template = EmailTemplate::active()
                ->where('slug', $templateSlug)
                ->first();

            if (!$template) {
                Log::warning("Email template not found: {$templateSlug}");
                return false;
            }

            $variables = $this->prepareVariables($booking, $additionalVariables);
            $rendered = $template->render($variables);

            // Determine recipients based on template settings
            $recipients = $this->getRecipientsForTemplate($template, $booking, $additionalVariables);
            
            if (empty($recipients)) {
                Log::warning("No recipients configured for template: {$templateSlug}");
                return false;
            }

            // Prepare attachments first so we can log them
            $emailAttachments = $this->prepareAttachments($template, $booking, $attachments);
            
            // Log attachment generation for debugging
            if (!empty($emailAttachments)) {
                Log::info("Generated attachments for {$templateSlug}", [
                    'count' => count($emailAttachments),
                    'attachments' => array_map(function($att) {
                        return $att['name'] ?? 'unknown';
                    }, $emailAttachments)
                ]);
            }
            
            // Create email log
            $emailLog = EmailLog::create([
                'booking_id' => $booking?->id,
                'user_id' => $booking?->user_id,
                'template_slug' => $templateSlug,
                'recipient_email' => $recipients['to'][0] ?? '',
                'recipient_name' => $recipients['to_names'][0] ?? '',
                'cc_emails' => implode(',', array_merge($recipients['cc'], $template->getRecipientEmails('cc'))),
                'bcc_emails' => implode(',', array_merge($recipients['bcc'], $template->getRecipientEmails('bcc'))),
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'variables_used' => $variables,
                'attachments' => array_map(function($att) {
                    if (!is_array($att)) {
                        return ['name' => 'unknown', 'mime' => 'application/octet-stream'];
                    }
                    return [
                        'name' => isset($att['name']) ? $att['name'] : 'unknown', 
                        'mime' => isset($att['mime']) ? $att['mime'] : 'application/octet-stream'
                    ];
                }, $emailAttachments),
                'status' => 'pending',
            ]);

            // Send email with delay if configured
            $delay = $template->delay_minutes > 0 ? now()->addMinutes($template->delay_minutes) : null;
            
            $this->dispatchEmail(
                $emailLog,
                $rendered,
                $template,
                $emailAttachments,
                $recipients,
                $delay
            );

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email notification: {$e->getMessage()}", [
                'template' => $templateSlug,
                'booking_id' => $booking?->id,
            ]);
            
            if (isset($emailLog)) {
                $emailLog->markAsFailed($e->getMessage());
            }
            
            return false;
        }
    }

    /**
     * Get recipients for a template based on its configuration
     */
    protected function getRecipientsForTemplate(EmailTemplate $template, Booking $booking = null, array $additionalVariables = []): array
    {
        $recipients = [
            'to' => [],
            'to_names' => [],
            'cc' => [],
            'bcc' => [],
        ];

        // Send to customer
        if ($template->send_to_customer && $booking) {
            $recipients['to'][] = $booking->customer_email;
            $recipients['to_names'][] = $booking->customer_full_name;
        }

        // Send to admin
        if ($template->send_to_admin) {
            // Get admin email from settings, fall back to business email, then to config
            $adminEmail = Setting::get('admin_email', Setting::get('business_email', config('mail.from.address', 'admin@luxride.com')));
            $adminName = Setting::get('admin_name', 'Administrator');
            
            if ($adminEmail) {
                if (empty($recipients['to'])) {
                    $recipients['to'][] = $adminEmail;
                    $recipients['to_names'][] = $adminName;
                } else {
                    $recipients['cc'][] = $adminEmail;
                }
            }
        }

        // Send to driver (future implementation)
        if ($template->send_to_driver && $booking) {
            // TODO: Implement when driver functionality is added
            // if ($booking->driver) {
            //     $recipients['to'][] = $booking->driver->email;
            //     $recipients['to_names'][] = $booking->driver->name;
            // }
        }

        // Override with additional variables if provided (for manual sends)
        if (isset($additionalVariables['recipient_email'])) {
            $recipients['to'] = [$additionalVariables['recipient_email']];
            $recipients['to_names'] = [$additionalVariables['recipient_name'] ?? 'Recipient'];
        }

        return $recipients;
    }

    /**
     * Prepare variables for email template
     */
    protected function prepareVariables(Booking $booking = null, array $additionalVariables = []): array
    {
        // Add common variables that are always available
        $commonVariables = [
            'current_year' => date('Y'),
            'current_date' => date('F j, Y'),
            'current_time' => date('g:i A'),
            'website_url' => Setting::get('website_url', config('app.url')),
            'company_address' => Setting::get('business_address', '123 Business Ave, Suite 100, City, State 12345'),
        ];
        
        $variables = array_merge($this->companyConfig, $commonVariables, $additionalVariables);

        if ($booking) {
            $variables = array_merge($variables, [
                'booking_number' => $booking->booking_number,
                'customer_name' => $booking->customer_full_name,
                'customer_first_name' => $booking->customer_first_name,
                'customer_last_name' => $booking->customer_last_name,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'pickup_address' => $booking->pickup_address,
                'dropoff_address' => $booking->dropoff_address,
                'pickup_date' => $booking->pickup_date->format('F j, Y'),
                'pickup_time' => $booking->pickup_date->format('g:i A'),
                'vehicle_type' => $booking->vehicleType?->display_name ?? 'N/A',
                'estimated_fare' => '$' . number_format($booking->estimated_fare, 2),
                'estimated_distance' => $booking->estimated_distance ? number_format($booking->estimated_distance, 1) : null,
                'estimated_duration' => $booking->estimated_duration ? round($booking->estimated_duration / 60) : null,
                'final_fare' => $booking->final_fare ? '$' . number_format($booking->final_fare, 2) : '$' . number_format($booking->estimated_fare, 2),
                'total_refunded' => $booking->total_refunded > 0 ? '$' . number_format($booking->total_refunded, 2) : null,
                'net_amount' => '$' . number_format($booking->net_amount, 2),
                'has_refund' => $booking->total_refunded > 0,
                'is_partially_refunded' => $booking->isPartiallyRefunded(),
                'is_fully_refunded' => $booking->isFullyRefunded(),
                'special_instructions' => $booking->special_instructions ?? 'None',
                'admin_notes' => $booking->admin_notes ?? '',
                'cancellation_reason' => $booking->cancellation_reason ?? '',
                'booking_url' => config('app.url') . '/booking/' . $booking->booking_number,
                'receipt_url' => config('app.url') . '/booking/' . $booking->booking_number . '/receipt',
                'status' => ucfirst($booking->status),
                'payment_status' => ucfirst($booking->payment_status),
            ]);

            // Add dynamic form fields from additional_data
            if ($booking->additional_data && is_array($booking->additional_data)) {
                foreach ($booking->additional_data as $fieldKey => $fieldValue) {
                    // Add field with 'field_' prefix to avoid conflicts
                    $variables['field_' . $fieldKey] = $this->formatFieldValue($fieldKey, $fieldValue);
                    
                    // Also add human-readable versions for common fields
                    $variables['field_' . $fieldKey . '_display'] = $this->getFieldDisplayValue($fieldKey, $fieldValue);
                }
            }

            // Add flight number separately if it exists (for backward compatibility)
            if ($booking->flight_number) {
                $variables['flight_number'] = $booking->flight_number;
                $variables['field_flight_number'] = $booking->flight_number;
            }

            // Add conditional flags for template logic
            $variables['has_flight_number'] = !empty($booking->flight_number) || !empty($booking->additional_data['flight_number']);
            $variables['has_special_instructions'] = !empty($booking->special_instructions);
            $variables['has_additional_fields'] = !empty($booking->additional_data);
            $variables['is_airport_transfer'] = $booking->is_airport_pickup || $booking->is_airport_dropoff;

            // Add booking type variables for hourly vs one-way bookings
            $variables['booking_type'] = $booking->booking_type;
            $variables['duration_hours'] = $booking->duration_hours;
            $variables['is_hourly_booking'] = $booking->booking_type === 'hourly';
        }

        return $variables;
    }

    /**
     * Format field value for display
     */
    protected function formatFieldValue($key, $value)
    {
        if (is_null($value) || $value === '') {
            return '';
        }

        // Special formatting for known field types
        switch ($key) {
            case 'number_of_bags':
                return $value . ' bag' . ($value != 1 ? 's' : '');
            case 'meet_and_greet':
                return $value ? 'Yes' : 'No';
            default:
                return is_string($value) ? $value : json_encode($value);
        }
    }

    /**
     * Get human-readable display value for field
     */
    protected function getFieldDisplayValue($key, $value)
    {
        if (is_null($value) || $value === '') {
            return '';
        }

        // Load field configuration to get proper labels
        $field = \App\Models\BookingFormField::where('key', $key)->first();
        
        if ($field && $field->type === 'select' && $field->options) {
            foreach ($field->options as $option) {
                if ($option['value'] === $value) {
                    return $option['label'];
                }
            }
        }

        if ($field && $field->type === 'checkbox') {
            return $value ? 'Yes' : 'No';
        }

        return $this->formatFieldValue($key, $value);
    }

    /**
     * Prepare attachments for email
     */
    protected function prepareAttachments(EmailTemplate $template, Booking $booking = null, array $additionalAttachments = []): array
    {
        $attachments = $additionalAttachments;

        // Attach receipt if configured and payment has been processed
        if ($template->attach_receipt && $booking && in_array($booking->payment_status, ['authorized', 'captured', 'partial'])) {
            $receipt = $this->generateReceipt($booking);
            if (!empty($receipt)) {
                $attachments[] = $receipt;
            }
        }

        // Attach booking details if configured
        if ($template->attach_booking_details && $booking) {
            $details = $this->generateBookingDetails($booking);
            if (!empty($details)) {
                $attachments[] = $details;
            }
        }

        return array_filter($attachments);
    }

    /**
     * Generate PDF receipt
     */
    protected function generateReceipt(Booking $booking): array
    {
        try {
            // Check if payment has been made
            if (!in_array($booking->payment_status, ['authorized', 'captured', 'partial'])) {
                Log::info("Skipping receipt generation - payment not processed for booking {$booking->booking_number}");
                return [];
            }

            // Get business settings for receipt
            $settings = [
                'business_name' => Setting::get('business_name', 'LuxRide'),
                'business_address' => Setting::get('business_address', 'Florida, USA'),
                'business_phone' => Setting::get('business_phone', '+1-813-333-8680'),
                'business_email' => Setting::get('business_email', 'contact@luxridesuv.com'),
            ];
            
            $pdf = Pdf::loadView('pdf.receipt', [
                'booking' => $booking,
                'settings' => $settings,
            ]);

            $fileName = "receipt_{$booking->booking_number}.pdf";
            $path = storage_path("app/temp/{$fileName}");
            
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            $pdf->save($path);

            return [
                'path' => $path,
                'name' => $fileName,
                'mime' => 'application/pdf',
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate receipt: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Generate PDF booking details
     */
    protected function generateBookingDetails(Booking $booking): array
    {
        try {
            // Ensure vehicleType is loaded
            if (!$booking->relationLoaded('vehicleType')) {
                $booking->load('vehicleType');
            }

            $pdf = Pdf::loadView('pdf.booking-details', [
                'booking' => $booking,
                'company' => $this->companyConfig,
            ]);

            $fileName = "booking_{$booking->booking_number}.pdf";
            $path = storage_path("app/temp/{$fileName}");
            
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            $pdf->save($path);

            return [
                'path' => $path,
                'name' => $fileName,
                'mime' => 'application/pdf',
            ];
        } catch (\Exception $e) {
            Log::error("Failed to generate booking details: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Dispatch email to queue
     */
    protected function dispatchEmail(
        EmailLog $emailLog,
        array $rendered,
        EmailTemplate $template,
        array $attachments,
        array $recipients,
        $delay = null
    ): void {
        $job = function () use ($emailLog, $rendered, $template, $attachments, $recipients) {
            try {
                Mail::send([], [], function ($message) use ($emailLog, $rendered, $template, $attachments, $recipients) {
                    // Add primary recipients
                    if (!empty($recipients['to'])) {
                        foreach ($recipients['to'] as $index => $email) {
                            $name = $recipients['to_names'][$index] ?? '';
                            if ($index === 0) {
                                $message->to($email, $name);
                            } else {
                                $message->cc($email, $name);
                            }
                        }
                    }
                    
                    $message->subject($rendered['subject'])
                        ->html($rendered['body']);

                    // Add CC recipients
                    foreach (array_merge($recipients['cc'], $template->getRecipientEmails('cc')) as $ccEmail) {
                        if (!empty($ccEmail)) {
                            $message->cc($ccEmail);
                        }
                    }

                    // Add BCC recipients
                    foreach (array_merge($recipients['bcc'], $template->getRecipientEmails('bcc')) as $bccEmail) {
                        if (!empty($bccEmail)) {
                            $message->bcc($bccEmail);
                        }
                    }

                    // Add attachments
                    foreach ($attachments as $attachment) {
                        if (is_array($attachment) && isset($attachment['path']) && file_exists($attachment['path'])) {
                            $message->attach($attachment['path'], [
                                'as' => isset($attachment['name']) ? $attachment['name'] : basename($attachment['path']),
                                'mime' => isset($attachment['mime']) ? $attachment['mime'] : 'application/octet-stream',
                            ]);
                        }
                    }
                });

                $emailLog->markAsSent();

                // Clean up temporary files
                foreach ($attachments as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path']) && str_contains($attachment['path'], '/temp/')) {
                        unlink($attachment['path']);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Email dispatch failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'template' => $emailLog->template_slug,
                    'recipient' => $emailLog->recipient_email
                ]);
                $emailLog->markAsFailed($e->getMessage());
                // Don't rethrow to prevent breaking the flow
                // throw $e;
            }
        };

        if ($delay) {
            dispatch($job)->delay($delay);
        } else {
            // Execute immediately since we're using sync queue
            $job();
        }
    }

    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmation(Booking $booking): bool
    {
        return $this->sendEmailNotification('booking-confirmation', $booking);
    }

    /**
     * Send booking reminder email
     */
    public function sendBookingReminder(Booking $booking): bool
    {
        return $this->sendEmailNotification('booking-reminder', $booking);
    }

    /**
     * Send booking cancellation email
     */
    public function sendBookingCancellation(Booking $booking): bool
    {
        return $this->sendEmailNotification('booking-cancelled', $booking);
    }

    /**
     * Send payment receipt email
     */
    public function sendPaymentReceipt(Booking $booking, Transaction $transaction): bool
    {
        return $this->sendEmailNotification('payment-receipt', $booking, [
            'transaction_id' => $transaction->stripe_transaction_id,
            'transaction_amount' => '$' . number_format($transaction->amount, 2),
            'transaction_date' => $transaction->created_at->format('F j, Y g:i A'),
        ]);
    }

    /**
     * Send refund notification email
     */
    public function sendRefundNotification(Booking $booking, float $refundAmount, string $reason = null): bool
    {
        return $this->sendEmailNotification('refund-processed', $booking, [
            'refund_amount' => '$' . number_format($refundAmount, 2),
            'refund_reason' => $reason ?? 'No reason provided',
        ]);
    }

    /**
     * Send admin notification for new booking
     */
    public function sendAdminNewBookingNotification(Booking $booking): bool
    {
        // Get admin email from settings, fall back to business email, then to config
        $adminEmail = Setting::get('admin_email', Setting::get('business_email', config('mail.from.address', 'admin@luxride.com')));
        $adminName = Setting::get('admin_name', 'Administrator');
        
        if ($adminEmail) {
            $this->sendEmailNotification('admin-new-booking', $booking, [
                'recipient_email' => $adminEmail,
                'recipient_name' => $adminName,
            ]);
        }
        
        return true;
    }

    /**
     * Send driver assignment notification
     */
    public function sendDriverAssignmentNotification(Booking $booking, array $driverInfo): bool
    {
        return $this->sendEmailNotification('driver-assigned', $booking, [
            'driver_name' => $driverInfo['name'] ?? 'Your driver',
            'driver_phone' => $driverInfo['phone'] ?? '',
            'driver_vehicle' => $driverInfo['vehicle'] ?? '',
            'driver_license_plate' => $driverInfo['license_plate'] ?? '',
        ]);
    }

    /**
     * Send booking modification notification
     */
    public function sendBookingModificationNotification(Booking $booking, array $changes): bool
    {
        $changesSummary = [];
        
        if (isset($changes['pickup_date'])) {
            $changesSummary[] = "New pickup date/time: " . $changes['pickup_date'];
        }
        if (isset($changes['pickup_address'])) {
            $changesSummary[] = "New pickup address: " . $changes['pickup_address'];
        }
        if (isset($changes['dropoff_address'])) {
            $changesSummary[] = "New dropoff address: " . $changes['dropoff_address'];
        }
        
        return $this->sendEmailNotification('booking-modified', $booking, [
            'changes_summary' => implode("\n", $changesSummary),
        ]);
    }
}