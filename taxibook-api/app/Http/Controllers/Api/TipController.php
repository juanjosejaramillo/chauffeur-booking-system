<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\TipService;
use Illuminate\Http\Request;

class TipController extends Controller
{
    private TipService $tipService;
    
    public function __construct(TipService $tipService)
    {
        $this->tipService = $tipService;
    }
    
    /**
     * Send tip link to customer
     */
    public function sendTipLink(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        
        $result = $this->tipService->sendTipLink($booking);
        
        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'url' => $result['url'],
            ]);
        }
        
        return response()->json([
            'error' => $result['message'],
        ], 422);
    }
    
    /**
     * Get QR code for tip payment
     */
    public function getQrCode($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        
        $result = $this->tipService->getQrCode($booking);
        
        return response()->json($result);
    }
    
    /**
     * Get booking details for tip page (public endpoint)
     */
    public function getBookingForTip($token)
    {
        $booking = $this->tipService->getBookingForTip($token);
        
        if (!$booking) {
            return response()->json([
                'error' => 'Invalid or expired tip link.',
            ], 404);
        }
        
        // Add suggested tip amounts
        $booking['suggested_tips'] = $this->tipService->getSuggestedTips($booking['fare_paid']);
        
        return response()->json($booking);
    }
    
    /**
     * Process tip payment (public endpoint)
     */
    public function processTip(Request $request, $token)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.50',
            'payment_method_id' => 'nullable|string', // Required if no saved card
        ]);
        
        $result = $this->tipService->processTip(
            $token,
            $validated['amount'],
            $validated['payment_method_id'] ?? null
        );
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'amount' => $result['amount'],
            ]);
        }
        
        return response()->json([
            'error' => $result['message'],
        ], 422);
    }
    
    /**
     * Create Stripe Payment Intent for tip (for frontend card collection)
     */
    public function createTipPaymentIntent(Request $request, $token)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.50',
        ]);
        
        // Find booking by token
        $booking = Booking::where('tip_link_token', $token)->first();
        
        if (!$booking) {
            return response()->json([
                'error' => 'Invalid tip link.',
            ], 404);
        }
        
        if ($booking->hasTipped()) {
            return response()->json([
                'error' => 'This booking has already been tipped.',
            ], 422);
        }
        
        // Create payment intent for tip
        // Use Stripe key from settings (already configured by SettingsServiceProvider)
        $stripeKey = config('services.stripe.secret') ?: config('stripe.secret_key');
        if (!$stripeKey) {
            return response()->json([
                'error' => 'Payment system not configured. Please contact support.',
            ], 500);
        }
        $stripe = new \Stripe\StripeClient($stripeKey);
        
        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => round($validated['amount'] * 100),
                'currency' => 'usd',
                'metadata' => [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'type' => 'tip',
                ],
                'description' => "Tip for booking {$booking->booking_number}",
            ]);
            
            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create payment intent.',
            ], 500);
        }
    }
}