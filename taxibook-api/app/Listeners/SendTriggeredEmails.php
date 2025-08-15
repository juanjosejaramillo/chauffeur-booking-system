<?php

namespace App\Listeners;

use App\Models\EmailTemplate;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SendTriggeredEmails
{
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    /**
     * Handle any event and send emails for templates with matching triggers
     */
    public function handle($event)
    {
        $eventClass = get_class($event);
        $triggerKey = $this->mapEventToTrigger($eventClass);
        
        if (!$triggerKey) {
            return;
        }
        
        // Find all active templates with this trigger enabled
        $templates = EmailTemplate::active()
            ->whereJsonContains('trigger_events', $triggerKey)
            ->get();
        
        foreach ($templates as $template) {
            try {
                // Skip templates that have timing configuration (not immediate)
                // These will be handled by the scheduled command
                if ($template->send_timing_type !== 'immediate') {
                    Log::info("Skipping scheduled email: {$template->slug} (will send {$template->getTimingDescription()})");
                    continue;
                }
                
                // Get booking from event if available
                $booking = null;
                if (property_exists($event, 'booking')) {
                    $booking = $event->booking;
                }
                
                // Prepare additional variables based on event type
                $additionalVariables = $this->getEventVariables($event);
                
                // Send the email
                $this->notificationService->sendEmailNotification(
                    $template->slug,
                    $booking,
                    $additionalVariables
                );
                
                Log::info("Sent immediate email for trigger: {$triggerKey}, template: {$template->slug}");
            } catch (\Exception $e) {
                Log::error("Failed to send triggered email: {$e->getMessage()}", [
                    'template' => $template->slug,
                    'trigger' => $triggerKey,
                    'event' => $eventClass
                ]);
            }
        }
    }
    
    /**
     * Map event classes to trigger keys
     */
    protected function mapEventToTrigger(string $eventClass): ?string
    {
        $mapping = [
            'App\Events\BookingCreated' => 'booking.created',
            'App\Events\BookingConfirmed' => 'booking.confirmed',
            'App\Events\BookingModified' => 'booking.modified',
            'App\Events\BookingCancelled' => 'booking.cancelled',
            'App\Events\BookingCompleted' => 'booking.completed',
            'App\Events\PaymentAuthorized' => 'payment.authorized',
            'App\Events\PaymentCaptured' => 'payment.captured',
            'App\Events\PaymentRefunded' => 'payment.refunded',
            'App\Events\PaymentFailed' => 'payment.failed',
        ];
        
        return $mapping[$eventClass] ?? null;
    }
    
    /**
     * Extract additional variables from event
     */
    protected function getEventVariables($event): array
    {
        $variables = [];
        
        // Add transaction info if available
        if (property_exists($event, 'transaction')) {
            $transaction = $event->transaction;
            $variables['transaction_id'] = $transaction->stripe_transaction_id;
            $variables['transaction_amount'] = '$' . number_format($transaction->amount, 2);
            $variables['transaction_date'] = $transaction->created_at->format('F j, Y g:i A');
        }
        
        // Add refund info if available
        if (property_exists($event, 'refundAmount')) {
            $variables['refund_amount'] = '$' . number_format($event->refundAmount, 2);
            $variables['refund_reason'] = $event->reason ?? 'No reason provided';
        }
        
        // Add changes summary if available
        if (property_exists($event, 'changes')) {
            $changesSummary = [];
            foreach ($event->changes as $field => $value) {
                $changesSummary[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $value;
            }
            $variables['changes_summary'] = implode("\n", $changesSummary);
        }
        
        return $variables;
    }
}