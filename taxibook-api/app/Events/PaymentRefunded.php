<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRefunded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $transaction;
    public $reason;

    public function __construct(Booking $booking, Transaction $transaction, string $reason = null)
    {
        $this->booking = $booking;
        $this->transaction = $transaction;
        $this->reason = $reason;
    }
}