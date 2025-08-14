<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\BookingVerificationController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\TipController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Booking routes (partially public)
Route::prefix('bookings')->group(function () {
    Route::post('/validate-route', [BookingController::class, 'validateRoute']);
    Route::post('/calculate-prices', [BookingController::class, 'calculatePrices']);
    Route::post('/send-verification', [BookingVerificationController::class, 'sendVerificationCode']);
    Route::post('/verify-email', [BookingVerificationController::class, 'verifyCode']);
    Route::post('/resend-verification', [BookingVerificationController::class, 'resendCode']);
    Route::post('/', [BookingController::class, 'store']);
    Route::get('/{bookingNumber}', [BookingController::class, 'show']);
    Route::post('/{bookingNumber}/process-payment', [BookingController::class, 'processPayment']);
    Route::post('/{bookingNumber}/payment-intent', [BookingController::class, 'createPaymentIntent']);
    Route::post('/{bookingNumber}/confirm-payment', [BookingController::class, 'confirmPayment']);
});

// Stripe webhook endpoint (no CSRF protection)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Tip endpoints (public - accessed via link/QR)
Route::prefix('tip')->group(function () {
    Route::get('/{token}', [TipController::class, 'getBookingForTip']);
    Route::post('/{token}/process', [TipController::class, 'processTip']);
    Route::post('/{token}/payment-intent', [TipController::class, 'createTipPaymentIntent']);
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/bookings', [BookingController::class, 'userBookings']);
    
    // Admin tip management (requires auth)
    Route::prefix('bookings')->group(function () {
        Route::post('/{booking}/send-tip-link', [TipController::class, 'sendTipLink']);
        Route::get('/{booking}/tip-qr', [TipController::class, 'getQrCode']);
    });
});