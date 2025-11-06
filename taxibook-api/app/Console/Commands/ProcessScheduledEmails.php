<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send scheduled emails based on timing configuration';

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled emails...');
        
        // Get all active email templates with timing configuration
        $templates = EmailTemplate::where('is_active', true)
            ->where('send_timing_type', '!=', 'immediate')
            ->get();
        
        $emailsSent = 0;
        
        foreach ($templates as $template) {
            $this->info("Checking template: {$template->name}");
            
            // Process based on timing type
            switch ($template->send_timing_type) {
                case 'before_pickup':
                case 'after_pickup':
                    $emailsSent += $this->processPickupBasedEmails($template);
                    break;
                    
                case 'after_booking':
                    $emailsSent += $this->processBookingBasedEmails($template);
                    break;
                    
                case 'after_completion':
                    $emailsSent += $this->processCompletionBasedEmails($template);
                    break;
            }
        }
        
        $this->info("Scheduled email processing complete. {$emailsSent} emails queued for sending.");
        
        return Command::SUCCESS;
    }
    
    /**
     * Process emails based on pickup time
     */
    protected function processPickupBasedEmails(EmailTemplate $template): int
    {
        $emailsSent = 0;
        $minutesThreshold = $template->getTimingInMinutes();

        // Get bookings that match the timing criteria
        $query = Booking::where('status', 'confirmed');
        
        if ($template->send_timing_type === 'before_pickup') {
            // Find bookings where pickup is X minutes in the future
            $targetTime = now()->addMinutes($minutesThreshold);
            $query->whereBetween('pickup_date', [
                $targetTime->copy()->subMinutes(15), // 15-minute window
                $targetTime->copy()->addMinutes(15)
            ]);
        } else {
            // Find bookings where pickup was X minutes ago
            $targetTime = now()->subMinutes($minutesThreshold);
            $query->whereBetween('pickup_date', [
                $targetTime->copy()->subMinutes(15),
                $targetTime->copy()->addMinutes(15)
            ]);
        }
        
        $bookings = $query->get();
        
        foreach ($bookings as $booking) {
            if ($this->shouldSendEmail($template, $booking)) {
                $this->notificationService->sendEmailNotification(
                    $template->slug,
                    $booking
                );
                $emailsSent++;
                $this->info("  - Queued email for booking {$booking->booking_number}");
            }
        }
        
        return $emailsSent;
    }
    
    /**
     * Process emails based on booking creation time
     */
    protected function processBookingBasedEmails(EmailTemplate $template): int
    {
        $emailsSent = 0;
        $minutesThreshold = $template->getTimingInMinutes();

        // Find bookings created X minutes ago
        $targetTime = now()->subMinutes($minutesThreshold);

        $bookings = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [
                $targetTime->copy()->subMinutes(15),
                $targetTime->copy()->addMinutes(15)
            ])
            ->get();
        
        foreach ($bookings as $booking) {
            if ($this->shouldSendEmail($template, $booking)) {
                $this->notificationService->sendEmailNotification(
                    $template->slug,
                    $booking
                );
                $emailsSent++;
                $this->info("  - Queued email for booking {$booking->booking_number}");
            }
        }
        
        return $emailsSent;
    }
    
    /**
     * Process emails based on trip completion
     */
    protected function processCompletionBasedEmails(EmailTemplate $template): int
    {
        $emailsSent = 0;
        $minutesThreshold = $template->getTimingInMinutes();
        
        // Find completed bookings from X minutes ago
        $targetTime = now()->subMinutes($minutesThreshold);
        
        $bookings = Booking::where('status', 'completed')
            ->whereBetween('updated_at', [
                $targetTime->copy()->subMinutes(15),
                $targetTime->copy()->addMinutes(15)
            ])
            ->get();
        
        foreach ($bookings as $booking) {
            if ($this->shouldSendEmail($template, $booking)) {
                $this->notificationService->sendEmailNotification(
                    $template->slug,
                    $booking
                );
                $emailsSent++;
                $this->info("  - Queued email for booking {$booking->booking_number}");
            }
        }
        
        return $emailsSent;
    }
    
    /**
     * Check if we should send this email (avoid duplicates)
     */
    protected function shouldSendEmail(EmailTemplate $template, Booking $booking): bool
    {
        // Check if we've already sent this template for this booking recently
        $recentlySent = EmailLog::where('booking_id', $booking->id)
            ->where('template_slug', $template->slug)
            ->where('created_at', '>', now()->subHours(24))
            ->exists();
        
        return !$recentlySent;
    }
}