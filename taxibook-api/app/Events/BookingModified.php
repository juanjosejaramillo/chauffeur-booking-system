<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $changes;

    public function __construct(Booking $booking, array $changes)
    {
        $this->booking = $booking;
        $this->changes = $changes;
    }
}