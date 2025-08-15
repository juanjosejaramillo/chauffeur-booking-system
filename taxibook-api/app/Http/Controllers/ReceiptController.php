<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    /**
     * Display receipt in browser
     */
    public function show($bookingNumber)
    {
        $booking = $this->getAuthorizedBooking($bookingNumber);
        
        if (!$booking) {
            abort(404, 'Booking not found');
        }
        
        // Check if payment has been made
        if (!in_array($booking->payment_status, ['authorized', 'captured', 'partial'])) {
            abort(403, 'Receipt not available - payment not processed');
        }
        
        return view('pdf.receipt', compact('booking'));
    }
    
    /**
     * Download receipt as PDF
     */
    public function download($bookingNumber)
    {
        $booking = $this->getAuthorizedBooking($bookingNumber);
        
        if (!$booking) {
            abort(404, 'Booking not found');
        }
        
        // Check if payment has been made
        if (!in_array($booking->payment_status, ['authorized', 'captured', 'partial'])) {
            abort(403, 'Receipt not available - payment not processed');
        }
        
        $pdf = Pdf::loadView('pdf.receipt', compact('booking'));
        
        // Set paper size and orientation
        $pdf->setPaper('letter', 'portrait');
        
        // Download with filename
        return $pdf->download('receipt-' . $booking->booking_number . '.pdf');
    }
    
    /**
     * Stream receipt PDF (display in browser)
     */
    public function stream($bookingNumber)
    {
        $booking = $this->getAuthorizedBooking($bookingNumber);
        
        if (!$booking) {
            abort(404, 'Booking not found');
        }
        
        // Check if payment has been made
        if (!in_array($booking->payment_status, ['authorized', 'captured', 'partial'])) {
            abort(403, 'Receipt not available - payment not processed');
        }
        
        $pdf = Pdf::loadView('pdf.receipt', compact('booking'));
        
        // Set paper size and orientation
        $pdf->setPaper('letter', 'portrait');
        
        // Stream to browser
        return $pdf->stream('receipt-' . $booking->booking_number . '.pdf');
    }
    
    /**
     * Get booking with authorization check
     */
    private function getAuthorizedBooking($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)
            ->with('vehicleType')
            ->first();
        
        if (!$booking) {
            return null;
        }
        
        // Allow access if:
        // 1. User is authenticated and owns the booking
        // 2. User is an admin
        // 3. The email matches (for guest bookings)
        // 4. Coming from a valid email link (check for token in future)
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if admin
            if ($user->isAdmin()) {
                return $booking;
            }
            
            // Check if user owns the booking
            if ($booking->user_id && $booking->user_id === $user->id) {
                return $booking;
            }
        }
        
        // For now, allow access to anyone with the booking number
        // In production, you might want to add email verification or token-based access
        return $booking;
    }
}