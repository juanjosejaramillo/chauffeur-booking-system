<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookingEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $templateSlug;

    public function __construct(Booking $booking, string $templateSlug)
    {
        $this->booking = $booking;
        $this->templateSlug = $templateSlug;
    }

    public function handle()
    {
        $template = EmailTemplate::active()
            ->where('slug', $this->templateSlug)
            ->first();
            
        if (!$template) {
            return;
        }
        
        $variables = $this->getTemplateVariables();
        $rendered = $template->render($variables);
        
        Mail::send([], [], function ($message) use ($rendered) {
            $message->to($this->booking->customer_email, $this->booking->customer_full_name)
                ->subject($rendered['subject'])
                ->html($rendered['body']);
        });
    }
    
    private function getTemplateVariables(): array
    {
        return [
            'booking_number' => $this->booking->booking_number,
            'customer_name' => $this->booking->customer_full_name,
            'pickup_address' => $this->booking->pickup_address,
            'dropoff_address' => $this->booking->dropoff_address,
            'pickup_date' => $this->booking->pickup_date->format('F j, Y'),
            'pickup_time' => $this->booking->pickup_date->format('g:i A'),
            'vehicle_type' => $this->booking->vehicleType->display_name,
            'estimated_fare' => number_format($this->booking->estimated_fare, 2),
            'special_instructions' => $this->booking->special_instructions ?? 'None',
        ];
    }
}