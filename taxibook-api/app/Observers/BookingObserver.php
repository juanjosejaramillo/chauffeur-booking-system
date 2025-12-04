<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Setting;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCompleted;
use App\Events\BookingCreated;
use App\Events\BookingModified;
use App\Events\TripStarted;
use App\Services\StripeService;

class BookingObserver
{
    /**
     * Track which events have been fired to prevent duplicates within the same request
     */
    protected static array $firedEvents = [];

    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        // Fire event when booking is created with pending status
        if ($booking->status === 'pending') {
            event(new BookingCreated($booking));
        }
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        $bookingId = $booking->id;

        // Check if status changed to cancelled
        if ($booking->isDirty('status') && $booking->status === 'cancelled') {
            $originalStatus = $booking->getOriginal('status');
            $eventKey = "cancelled_{$bookingId}";

            // Only fire event if it wasn't already cancelled AND not already fired
            if ($originalStatus !== 'cancelled' && !isset(self::$firedEvents[$eventKey])) {
                self::$firedEvents[$eventKey] = true;
                event(new BookingCancelled(
                    $booking,
                    $booking->cancellation_reason ?? 'Booking cancelled by admin'
                ));
            }
        }

        // Check if status changed to confirmed
        if ($booking->isDirty('status') && $booking->status === 'confirmed') {
            $originalStatus = $booking->getOriginal('status');
            $eventKey = "confirmed_{$bookingId}";

            // Only fire event if it wasn't already confirmed AND not already fired
            if ($originalStatus !== 'confirmed' && !isset(self::$firedEvents[$eventKey])) {
                self::$firedEvents[$eventKey] = true;
                event(new BookingConfirmed($booking));
            }
        }

        // Check if status changed to in_progress (trip started)
        if ($booking->isDirty('status') && $booking->status === 'in_progress') {
            $originalStatus = $booking->getOriginal('status');
            $eventKey = "in_progress_{$bookingId}";

            // Only fire event if it wasn't already in_progress AND not already fired
            if ($originalStatus !== 'in_progress' && !isset(self::$firedEvents[$eventKey])) {
                self::$firedEvents[$eventKey] = true;
                event(new TripStarted($booking));
            }
        }

        // Check if status changed to completed
        if ($booking->isDirty('status') && $booking->status === 'completed') {
            $originalStatus = $booking->getOriginal('status');
            $eventKey = "completed_{$bookingId}";

            // Only fire event if it wasn't already completed AND not already fired
            if ($originalStatus !== 'completed' && !isset(self::$firedEvents[$eventKey])) {
                self::$firedEvents[$eventKey] = true;

                // Auto-charge saved card if payment_mode is 'post_service' and payment is pending
                $this->autoChargeSavedCardOnCompletion($booking);

                event(new BookingCompleted($booking));
            }
        }

        // Check if key booking details changed (but not status changes)
        if (!$booking->isDirty('status') &&
            ($booking->isDirty('pickup_date') ||
             $booking->isDirty('pickup_address') ||
             $booking->isDirty('dropoff_address'))) {

            $eventKey = "modified_{$bookingId}_" . md5(json_encode($booking->getDirty()));

            // Prevent duplicate modified events
            if (!isset(self::$firedEvents[$eventKey])) {
                self::$firedEvents[$eventKey] = true;

                $changes = [];

                if ($booking->isDirty('pickup_date')) {
                    $changes['pickup_date'] = $booking->pickup_date->format('F j, Y g:i A');
                }
                if ($booking->isDirty('pickup_address')) {
                    $changes['pickup_address'] = $booking->pickup_address;
                }
                if ($booking->isDirty('dropoff_address')) {
                    $changes['dropoff_address'] = $booking->dropoff_address;
                }

                event(new BookingModified($booking, $changes));
            }
        }
    }
    
    /**
     * Handle the Booking "creating" event.
     */
    public function creating(Booking $booking): void
    {
        // Set cancelled_at timestamp when creating a cancelled booking
        if ($booking->status === 'cancelled' && !$booking->cancelled_at) {
            $booking->cancelled_at = now();
        }
    }
    
    /**
     * Handle the Booking "updating" event.
     */
    public function updating(Booking $booking): void
    {
        // Set cancelled_at timestamp when status changes to cancelled
        if ($booking->isDirty('status') && $booking->status === 'cancelled' && !$booking->cancelled_at) {
            $booking->cancelled_at = now();
        }

        // Clear cancelled_at if status changes from cancelled to something else
        if ($booking->isDirty('status') && $booking->status !== 'cancelled' && $booking->cancelled_at) {
            $booking->cancelled_at = null;
        }
    }

    /**
     * Auto-charge saved card when booking is marked as completed.
     * Only runs when payment_mode is 'post_service' and payment is pending.
     */
    protected function autoChargeSavedCardOnCompletion(Booking $booking): void
    {
        // Check if payment mode is post_service
        $paymentMode = Setting::get('payment_mode', 'immediate');
        if ($paymentMode !== 'post_service') {
            return;
        }

        // Check if booking has a saved payment method
        if (!$booking->hasSavedPaymentMethod()) {
            \Log::warning('Auto-charge skipped: No saved payment method', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
            ]);
            return;
        }

        // CRITICAL: Check if payment is still pending (prevent double-charge)
        if ($booking->payment_status !== 'pending') {
            \Log::info('Auto-charge skipped: Payment already processed', [
                'booking_id' => $booking->id,
                'payment_status' => $booking->payment_status,
            ]);
            return;
        }

        // Additional safeguard: Check for existing successful payment transactions
        $hasSuccessfulPayment = $booking->transactions()
            ->whereIn('type', ['payment', 'capture'])
            ->where('status', 'succeeded')
            ->exists();

        if ($hasSuccessfulPayment) {
            \Log::warning('Auto-charge BLOCKED: Previous successful payment exists', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'payment_status' => $booking->payment_status,
            ]);
            return;
        }

        // Calculate total amount to charge
        $amount = ($booking->final_fare ?? $booking->estimated_fare) + ($booking->gratuity_amount ?? 0);

        if ($amount <= 0) {
            \Log::warning('Auto-charge skipped: Invalid amount', [
                'booking_id' => $booking->id,
                'amount' => $amount,
            ]);
            return;
        }

        try {
            $stripeService = app(StripeService::class);
            $result = $stripeService->chargeWithSavedCard($booking, $amount, 'Ride completed');

            if ($result['success']) {
                \Log::info('Auto-charge successful', [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'amount' => $amount,
                    'payment_intent_id' => $result['payment_intent_id'],
                ]);

                // Send notification to admin about successful charge
                // Note: The payment_status is already updated by chargeWithSavedCard
            } else {
                \Log::error('Auto-charge failed', [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'amount' => $amount,
                    'error' => $result['error'],
                ]);

                // TODO: Optionally send notification to admin about failed charge
                // The admin will need to manually retry the charge
            }
        } catch (\Exception $e) {
            \Log::error('Auto-charge exception', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}