<?php

namespace App\Events;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCaptured
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $transaction;

    public function __construct(Booking $booking, Transaction $transaction)
    {
        $this->booking = $booking;
        $this->transaction = $transaction;
    }
}