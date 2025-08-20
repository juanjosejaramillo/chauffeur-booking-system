<?php

namespace App\Observers;

use App\Models\Booking;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCompleted;
use App\Events\BookingCreated;
use App\Events\BookingModified;
use App\Events\TripStarted;

class BookingObserver
{
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
        // Check if status changed to cancelled
        if ($booking->isDirty('status') && $booking->status === 'cancelled') {
            // Get the original status
            $originalStatus = $booking->getOriginal('status');
            
            // Only fire event if it wasn't already cancelled
            if ($originalStatus !== 'cancelled') {
                event(new BookingCancelled(
                    $booking, 
                    $booking->cancellation_reason ?? 'Booking cancelled by admin'
                ));
            }
        }
        
        // Check if status changed to confirmed
        if ($booking->isDirty('status') && $booking->status === 'confirmed') {
            $originalStatus = $booking->getOriginal('status');
            
            // Only fire event if it wasn't already confirmed
            if ($originalStatus !== 'confirmed') {
                event(new BookingConfirmed($booking));
            }
        }
        
        // Check if status changed to in_progress (trip started)
        if ($booking->isDirty('status') && $booking->status === 'in_progress') {
            $originalStatus = $booking->getOriginal('status');
            
            // Only fire event if it wasn't already in_progress
            if ($originalStatus !== 'in_progress') {
                event(new TripStarted($booking));
            }
        }
        
        // Check if status changed to completed
        if ($booking->isDirty('status') && $booking->status === 'completed') {
            $originalStatus = $booking->getOriginal('status');
            
            // Only fire event if it wasn't already completed
            if ($originalStatus !== 'completed') {
                event(new BookingCompleted($booking));
            }
        }
        
        // Check if key booking details changed (but not status changes)
        if (!$booking->isDirty('status') && 
            ($booking->isDirty('pickup_date') || 
             $booking->isDirty('pickup_address') || 
             $booking->isDirty('dropoff_address'))) {
            
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
}