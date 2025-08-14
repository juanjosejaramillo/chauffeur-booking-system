<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\TipService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipPaymentController extends Controller
{
    private TipService $tipService;
    
    public function __construct(TipService $tipService)
    {
        $this->tipService = $tipService;
    }
    
    /**
     * Show the tip payment page
     */
    public function show(string $token): View
    {
        $bookingData = $this->tipService->getBookingForTip($token);
        
        if (!$bookingData) {
            abort(404, 'Invalid or expired tip link.');
        }
        
        if ($bookingData['already_tipped']) {
            return view('tip.already-tipped', [
                'booking' => $bookingData,
            ]);
        }
        
        return view('tip.payment', [
            'booking' => $bookingData,
            'token' => $token,
            'stripePublicKey' => config('services.stripe.key'),
        ]);
    }
    
    /**
     * Show success page after tip payment
     */
    public function success(string $token): View
    {
        $booking = Booking::where('tip_link_token', $token)->first();
        
        if (!$booking) {
            abort(404, 'Invalid tip link.');
        }
        
        return view('tip.success', [
            'booking' => $booking,
            'tipAmount' => $booking->gratuity_amount,
        ]);
    }
}