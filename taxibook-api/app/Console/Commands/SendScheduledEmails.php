<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledEmails extends Command
{
    protected $signature = 'emails:send-scheduled';
    protected $description = 'Send scheduled/time-based trigger emails';
    
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }
    
    public function handle()
    {
        $this->info('Checking for scheduled emails to send...');
        
        // Handle time-based triggers
        $this->sendPickupReminders();
        $this->sendPostTripEmails();
        $this->sendDailySummary();
        $this->sendWeeklySummary();
        
        $this->info('Scheduled email check complete.');
    }
    
    /**
     * Send pickup reminder emails (24h, 2h, 30min before)
     */
    protected function sendPickupReminders()
    {
        $triggers = [
            '24_hours_before' => 24 * 60,
            '2_hours_before' => 2 * 60,
            '30_minutes_before' => 30,
        ];
        
        foreach ($triggers as $triggerKey => $minutesBefore) {
            // Find templates with this trigger
            $templates = EmailTemplate::active()
                ->whereJsonContains('trigger_events', $triggerKey)
                ->get();
            
            if ($templates->isEmpty()) {
                continue;
            }
            
            // Find bookings that need this reminder
            $targetTime = Carbon::now()->addMinutes($minutesBefore);
            $bookings = Booking::where('status', 'confirmed')
                ->whereBetween('pickup_date', [
                    $targetTime->copy()->subMinutes(5),
                    $targetTime->copy()->addMinutes(5)
                ])
                ->whereDoesntHave('emailLogs', function ($query) use ($triggerKey) {
                    $query->where('template_slug', 'LIKE', "%{$triggerKey}%")
                        ->where('status', 'sent')
                        ->where('created_at', '>', Carbon::now()->subHours(25));
                })
                ->get();
            
            foreach ($bookings as $booking) {
                foreach ($templates as $template) {
                    try {
                        $this->notificationService->sendEmailNotification(
                            $template->slug,
                            $booking
                        );
                        $this->info("Sent {$triggerKey} reminder for booking {$booking->booking_number}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send reminder: {$e->getMessage()}");
                    }
                }
            }
        }
    }
    
    /**
     * Send emails 24 hours after trip completion
     */
    protected function sendPostTripEmails()
    {
        $templates = EmailTemplate::active()
            ->whereJsonContains('trigger_events', '24_hours_after_trip')
            ->get();
        
        if ($templates->isEmpty()) {
            return;
        }
        
        $targetTime = Carbon::now()->subHours(24);
        $bookings = Booking::where('status', 'completed')
            ->whereBetween('completed_at', [
                $targetTime->copy()->subMinutes(30),
                $targetTime->copy()->addMinutes(30)
            ])
            ->whereDoesntHave('emailLogs', function ($query) {
                $query->where('template_slug', 'LIKE', '%follow_up%')
                    ->where('status', 'sent')
                    ->where('created_at', '>', Carbon::now()->subHours(25));
            })
            ->get();
        
        foreach ($bookings as $booking) {
            foreach ($templates as $template) {
                try {
                    $this->notificationService->sendEmailNotification(
                        $template->slug,
                        $booking
                    );
                    $this->info("Sent post-trip email for booking {$booking->booking_number}");
                } catch (\Exception $e) {
                    Log::error("Failed to send post-trip email: {$e->getMessage()}");
                }
            }
        }
    }
    
    /**
     * Send daily summary emails
     */
    protected function sendDailySummary()
    {
        // Only run at 9 AM
        if (Carbon::now()->hour !== 9 || Carbon::now()->minute > 5) {
            return;
        }
        
        $templates = EmailTemplate::active()
            ->whereJsonContains('trigger_events', 'daily_summary')
            ->get();
        
        if ($templates->isEmpty()) {
            return;
        }
        
        // Get yesterday's stats
        $yesterday = Carbon::yesterday();
        $stats = [
            'date' => $yesterday->format('F j, Y'),
            'total_bookings' => Booking::whereDate('created_at', $yesterday)->count(),
            'completed_trips' => Booking::whereDate('completed_at', $yesterday)->count(),
            'total_revenue' => Booking::whereDate('completed_at', $yesterday)->sum('final_fare'),
            'cancelled_bookings' => Booking::whereDate('cancelled_at', $yesterday)->count(),
        ];
        
        foreach ($templates as $template) {
            // Send to admin emails
            $adminEmails = config('app.admin_emails', []);
            foreach ($adminEmails as $adminEmail) {
                try {
                    $this->notificationService->sendEmailNotification(
                        $template->slug,
                        null,
                        array_merge($stats, [
                            'recipient_email' => $adminEmail,
                            'recipient_name' => 'Admin',
                        ])
                    );
                    $this->info("Sent daily summary to {$adminEmail}");
                } catch (\Exception $e) {
                    Log::error("Failed to send daily summary: {$e->getMessage()}");
                }
            }
        }
    }
    
    /**
     * Send weekly summary emails
     */
    protected function sendWeeklySummary()
    {
        // Only run on Mondays at 9 AM
        if (!Carbon::now()->isMonday() || Carbon::now()->hour !== 9 || Carbon::now()->minute > 5) {
            return;
        }
        
        $templates = EmailTemplate::active()
            ->whereJsonContains('trigger_events', 'weekly_summary')
            ->get();
        
        if ($templates->isEmpty()) {
            return;
        }
        
        // Get last week's stats
        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();
        
        $stats = [
            'week_start' => $startOfWeek->format('F j, Y'),
            'week_end' => $endOfWeek->format('F j, Y'),
            'total_bookings' => Booking::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'completed_trips' => Booking::whereBetween('completed_at', [$startOfWeek, $endOfWeek])->count(),
            'total_revenue' => Booking::whereBetween('completed_at', [$startOfWeek, $endOfWeek])->sum('final_fare'),
            'cancelled_bookings' => Booking::whereBetween('cancelled_at', [$startOfWeek, $endOfWeek])->count(),
            'average_fare' => Booking::whereBetween('completed_at', [$startOfWeek, $endOfWeek])->avg('final_fare'),
        ];
        
        foreach ($templates as $template) {
            // Send to admin emails
            $adminEmails = config('app.admin_emails', []);
            foreach ($adminEmails as $adminEmail) {
                try {
                    $this->notificationService->sendEmailNotification(
                        $template->slug,
                        null,
                        array_merge($stats, [
                            'recipient_email' => $adminEmail,
                            'recipient_name' => 'Admin',
                        ])
                    );
                    $this->info("Sent weekly summary to {$adminEmail}");
                } catch (\Exception $e) {
                    Log::error("Failed to send weekly summary: {$e->getMessage()}");
                }
            }
        }
    }
}