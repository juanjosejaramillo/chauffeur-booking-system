<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\EmailTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update templates to use proper event triggers instead of time-based ones
        $updates = [
            '24 Hour Booking Reminder' => ['booking.confirmed'],
            '2 Hour Booking Reminder' => ['booking.confirmed'],
            'Upcoming Booking Alert (Admin)' => ['booking.confirmed', 'booking.created'],
            'Review Request' => ['booking.completed', 'trip.ended'],
        ];
        
        foreach ($updates as $templateName => $newTriggers) {
            $template = EmailTemplate::where('name', $templateName)->first();
            if ($template) {
                // Remove old time-based triggers and add proper event triggers
                $currentTriggers = $template->trigger_events ?? [];
                
                // Remove time-based triggers
                $timeTriggers = ['booking.reminder.24h', 'booking.reminder.2h', 'booking.reminder.30m', 'trip.review.24h'];
                $cleanedTriggers = array_diff($currentTriggers, $timeTriggers);
                
                // Add the proper event triggers
                $finalTriggers = array_unique(array_merge($cleanedTriggers, $newTriggers));
                
                $template->update(['trigger_events' => array_values($finalTriggers)]);
            }
        }
        
        // Clean up all other templates to remove time-based triggers
        EmailTemplate::all()->each(function ($template) {
            $currentTriggers = $template->trigger_events ?? [];
            $timeTriggers = ['booking.reminder.24h', 'booking.reminder.2h', 'booking.reminder.30m', 'trip.review.24h'];
            $cleanedTriggers = array_diff($currentTriggers, $timeTriggers);
            
            if (count($cleanedTriggers) !== count($currentTriggers)) {
                $template->update(['trigger_events' => array_values($cleanedTriggers)]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a cleanup migration, no need to reverse
    }
};