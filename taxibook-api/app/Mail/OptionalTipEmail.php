<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OptionalTipEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $tipUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $tipUrl)
    {
        $this->booking = $booking;
        $this->tipUrl = $tipUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Add a Tip for Your Recent Trip - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.optional-tip',
            with: [
                'booking' => $this->booking,
                'tipUrl' => $this->tipUrl,
                'customerName' => $this->booking->customer_first_name,
                'bookingNumber' => $this->booking->booking_number,
                'tripDate' => $this->booking->pickup_date->format('F j, Y'),
                'fare' => $this->booking->final_fare,
                'suggestedTips' => [
                    ['percentage' => 15, 'amount' => round($this->booking->final_fare * 0.15, 2)],
                    ['percentage' => 20, 'amount' => round($this->booking->final_fare * 0.20, 2)],
                    ['percentage' => 25, 'amount' => round($this->booking->final_fare * 0.25, 2)],
                ],
            ]
        );
    }
}