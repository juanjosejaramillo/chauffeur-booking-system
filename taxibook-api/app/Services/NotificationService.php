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
        $variables = array_merge($this->companyConfig, $additionalVariables);

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
                'final_fare' => $booking->final_fare ? '$' . number_format($booking->final_fare, 2) : '$' . number_format($booking->estimated_fare, 2),
                'special_instructions' => $booking->special_instructions ?? 'None',
                'admin_notes' => $booking->admin_notes ?? '',
                'cancellation_reason' => $booking->cancellation_reason ?? '',
                'booking_url' => config('app.url') . '/booking/' . $booking->booking_number,
                'receipt_url' => config('app.url') . '/booking/' . $booking->booking_number . '/receipt',
                'status' => ucfirst($booking->status),
                'payment_status' => ucfirst($booking->payment_status),
            ]);
        }

        return $variables;
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

            $pdf = Pdf::loadView('pdf.receipt', [
                'booking' => $booking,
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

            $pdf = Pdf::loadView('emails.booking-details', [
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