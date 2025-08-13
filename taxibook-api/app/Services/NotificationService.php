<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class NotificationService
{
    protected $companyConfig;
    
    public function __construct()
    {
        $this->companyConfig = [
            'company_name' => config('app.name', 'TaxiBook'),
            'company_email' => config('mail.from.address', 'noreply@taxibook.com'),
            'company_phone' => config('app.company_phone', '1-800-TAXIBOOK'),
            'support_url' => config('app.url') . '/support',
        ];
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

            // Create email log
            $emailLog = EmailLog::create([
                'booking_id' => $booking?->id,
                'user_id' => $booking?->user_id,
                'template_slug' => $templateSlug,
                'recipient_email' => $booking?->customer_email ?? $additionalVariables['recipient_email'] ?? '',
                'recipient_name' => $booking?->customer_full_name ?? $additionalVariables['recipient_name'] ?? '',
                'cc_emails' => implode(',', $template->getRecipientEmails('cc')),
                'bcc_emails' => implode(',', $template->getRecipientEmails('bcc')),
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
                'variables_used' => $variables,
                'attachments' => $attachments,
                'status' => 'pending',
            ]);

            // Prepare attachments
            $emailAttachments = $this->prepareAttachments($template, $booking, $attachments);

            // Send email with delay if configured
            $delay = $template->delay_minutes > 0 ? now()->addMinutes($template->delay_minutes) : null;
            
            $this->dispatchEmail(
                $emailLog,
                $rendered,
                $template,
                $emailAttachments,
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

        if ($template->attach_receipt && $booking && $booking->payment_status === 'captured') {
            $attachments[] = $this->generateReceipt($booking);
        }

        if ($template->attach_booking_details && $booking) {
            $attachments[] = $this->generateBookingDetails($booking);
        }

        return array_filter($attachments);
    }

    /**
     * Generate PDF receipt
     */
    protected function generateReceipt(Booking $booking): array
    {
        try {
            $transaction = $booking->transactions()
                ->where('type', 'capture')
                ->where('status', 'succeeded')
                ->latest()
                ->first();

            if (!$transaction) {
                return [];
            }

            $pdf = Pdf::loadView('emails.receipt', [
                'booking' => $booking,
                'transaction' => $transaction,
                'company' => $this->companyConfig,
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
        $delay = null
    ): void {
        $job = function () use ($emailLog, $rendered, $template, $attachments) {
            try {
                Mail::send([], [], function ($message) use ($emailLog, $rendered, $template, $attachments) {
                    $message->to($emailLog->recipient_email, $emailLog->recipient_name)
                        ->subject($rendered['subject'])
                        ->html($rendered['body']);

                    // Add CC recipients
                    foreach ($template->getRecipientEmails('cc') as $ccEmail) {
                        $message->cc($ccEmail);
                    }

                    // Add BCC recipients
                    foreach ($template->getRecipientEmails('bcc') as $bccEmail) {
                        $message->bcc($bccEmail);
                    }

                    // Add attachments
                    foreach ($attachments as $attachment) {
                        if (isset($attachment['path']) && file_exists($attachment['path'])) {
                            $message->attach($attachment['path'], [
                                'as' => $attachment['name'] ?? basename($attachment['path']),
                                'mime' => $attachment['mime'] ?? 'application/octet-stream',
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
                $emailLog->markAsFailed($e->getMessage());
                throw $e;
            }
        };

        if ($delay) {
            dispatch($job)->delay($delay);
        } else {
            dispatch($job);
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
        $adminEmails = config('app.admin_emails', []);
        
        foreach ($adminEmails as $adminEmail) {
            $this->sendEmailNotification('admin-new-booking', $booking, [
                'recipient_email' => $adminEmail,
                'recipient_name' => 'Admin',
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