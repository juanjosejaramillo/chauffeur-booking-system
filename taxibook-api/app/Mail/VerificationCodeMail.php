<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $customerName;
    public string $pickupAddress;
    public string $dropoffAddress;
    public string $pickupDate;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $code,
        string $customerName,
        string $pickupAddress,
        string $dropoffAddress,
        string $pickupDate
    ) {
        $this->code = $code;
        $this->customerName = $customerName;
        $this->pickupAddress = $pickupAddress;
        $this->dropoffAddress = $dropoffAddress;
        $this->pickupDate = $pickupDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your TaxiBook Verification Code: ' . $this->code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}