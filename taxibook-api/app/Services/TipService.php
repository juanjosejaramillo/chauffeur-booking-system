<?php

namespace App\Services;

use App\Models\Booking;
use App\Mail\OptionalTipEmail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TipService
{
    private StripeService $stripeService;
    
    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }
    
    /**
     * Generate and send tip link to customer
     */
    public function sendTipLink(Booking $booking): array
    {
        // Check if already tipped
        if ($booking->hasTipped()) {
            return [
                'success' => false,
                'message' => 'This booking has already been tipped.',
            ];
        }
        
        // Check if booking is completed
        if ($booking->status !== 'completed') {
            return [
                'success' => false,
                'message' => 'Tips can only be added after trip completion.',
            ];
        }
        
        // Generate unique token if not exists
        if (!$booking->tip_link_token) {
            $booking->tip_link_token = Str::random(40);
        }
        
        // Generate QR code
        // Use frontend URL for React app
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $tipUrl = "{$frontendUrl}/tip/{$booking->tip_link_token}";
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($tipUrl);
        
        // Store QR code as base64
        $booking->qr_code_data = 'data:image/svg+xml;base64,' . base64_encode($qrCode);
        $booking->tip_link_sent_at = now();
        $booking->save();
        
        // Send email
        Mail::to($booking->customer_email)->send(new OptionalTipEmail($booking, $tipUrl));
        
        return [
            'success' => true,
            'message' => 'Tip link sent successfully.',
            'url' => $tipUrl,
            'qr_code' => $booking->qr_code_data,
        ];
    }
    
    /**
     * Get QR code for tip payment
     */
    public function getQrCode(Booking $booking): array
    {
        // Generate token if not exists
        if (!$booking->tip_link_token) {
            $booking->tip_link_token = Str::random(40);
        }
        
        // Use frontend URL for React app
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $tipUrl = "{$frontendUrl}/tip/{$booking->tip_link_token}";
        
        // Generate or regenerate QR code
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($tipUrl);
        
        $booking->qr_code_data = 'data:image/svg+xml;base64,' . base64_encode($qrCode);
        $booking->save();
        
        return [
            'success' => true,
            'url' => $tipUrl,
            'qr_code' => $booking->qr_code_data,
            'booking_number' => $booking->booking_number,
        ];
    }
    
    /**
     * Process tip payment
     */
    public function processTip(string $token, float $amount, string $paymentMethodId = null): array
    {
        // Find booking by token
        $booking = Booking::where('tip_link_token', $token)->first();
        
        if (!$booking) {
            return [
                'success' => false,
                'message' => 'Invalid tip link.',
            ];
        }
        
        // Check if already tipped
        if ($booking->hasTipped()) {
            return [
                'success' => false,
                'message' => 'This booking has already been tipped.',
            ];
        }
        
        // Validate amount
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid tip amount.',
            ];
        }
        
        // Process tip through Stripe
        $result = $this->stripeService->chargeTip($booking, $amount, $paymentMethodId);
        
        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Tip added successfully. Thank you!',
                'amount' => $amount,
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to process tip: ' . ($result['error'] ?? 'Unknown error'),
        ];
    }
    
    /**
     * Get booking details for tip page
     */
    public function getBookingForTip(string $token): ?array
    {
        $booking = Booking::where('tip_link_token', $token)
            ->with('vehicleType')
            ->first();
        
        if (!$booking) {
            return null;
        }
        
        $fare = $booking->final_fare ?? $booking->estimated_fare;
        
        return [
            'booking_number' => $booking->booking_number,
            'pickup_date' => $booking->pickup_date->format('M d, Y'),
            'pickup_address' => $booking->pickup_address,
            'dropoff_address' => $booking->dropoff_address,
            'fare_paid' => $fare,
            'already_tipped' => $booking->hasTipped(),
            'tip_amount' => $booking->gratuity_amount,
            'vehicle_type' => $booking->vehicleType->display_name,
            'has_saved_card' => !empty($booking->stripe_payment_method_id),
            'saved_card_last4' => null, // Would need to fetch from Stripe if needed
            'suggested_tips' => $this->getSuggestedTips($fare),
        ];
    }
    
    /**
     * Calculate suggested tip amounts
     */
    public function getSuggestedTips(float $fare): array
    {
        return [
            ['percentage' => 15, 'amount' => round($fare * 0.15, 2)],
            ['percentage' => 20, 'amount' => round($fare * 0.20, 2)],
            ['percentage' => 25, 'amount' => round($fare * 0.25, 2)],
        ];
    }
}