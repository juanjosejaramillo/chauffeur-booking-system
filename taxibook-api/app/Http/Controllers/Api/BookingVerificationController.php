<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class BookingVerificationController extends Controller
{
    public function sendVerificationCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'customer_first_name' => 'required|string',
            'pickup_address' => 'required|string',
            'dropoff_address' => 'required|string',
            'pickup_date' => 'required|string',
        ]);

        // Rate limiting: 1 request per minute per email
        $rateLimitKey = 'verification_rate_' . md5($validated['email']);
        if (Cache::has($rateLimitKey)) {
            return response()->json([
                'error' => 'Please wait 1 minute before requesting another code.'
            ], 429);
        }

        // Create or update user (no password needed)
        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['customer_first_name'],
                'last_name' => $request->input('customer_last_name', ''),
                'phone' => $request->input('customer_phone', ''),
                'password' => null,  // No password - email verification only
                'user_type' => 'customer',
            ]
        );

        // If user exists, update their info in case it changed
        if (!$user->wasRecentlyCreated) {
            $user->update([
                'first_name' => $validated['customer_first_name'],
                'last_name' => $request->input('customer_last_name', $user->last_name),
                'phone' => $request->input('customer_phone', $user->phone),
            ]);
        }

        \Log::info('User created/updated for booking', [
            'user_id' => $user->id,
            'email' => $user->email,
            'was_created' => $user->wasRecentlyCreated
        ]);

        // Generate 6-digit code
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in cache with expiration (10 minutes)
        $cacheKey = 'booking_verification_' . md5($validated['email']);
        Cache::put($cacheKey, [
            'code' => $code,
            'email' => $validated['email'],
            'expires_at' => Carbon::now()->addMinutes(10)->toDateTimeString(),
            'attempts' => 0,
            'customer_data' => $validated
        ], 600); // 600 seconds = 10 minutes

        \Log::info('Verification code stored', [
            'email' => $validated['email'],
            'code' => $code,
            'cache_key' => $cacheKey
        ]);

        // Send email
        try {
            Mail::to($validated['email'])->send(new VerificationCodeMail(
                $code,
                $validated['customer_first_name'],
                $validated['pickup_address'],
                $validated['dropoff_address'],
                $validated['pickup_date']
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email', [
                'email' => $validated['email'],
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to send verification email. Please try again.'
            ], 500);
        }

        // Set rate limit
        Cache::put($rateLimitKey, true, 60);

        return response()->json([
            'message' => 'Verification code sent successfully',
            'expires_in' => 600 // 10 minutes in seconds
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $cacheKey = 'booking_verification_' . md5($validated['email']);
        $verificationData = Cache::get($cacheKey);

        \Log::info('Verifying code', [
            'email' => $validated['email'],
            'provided_code' => $validated['code'],
            'cache_key' => $cacheKey,
            'has_data' => !empty($verificationData)
        ]);

        if (!$verificationData) {
            return response()->json([
                'error' => 'No verification code found. Please request a new one.'
            ], 404);
        }

        // Check expiration
        if (Carbon::parse($verificationData['expires_at'])->isPast()) {
            Cache::forget($cacheKey);
            return response()->json([
                'error' => 'Verification code has expired. Please request a new one.'
            ], 410);
        }

        // Check attempts
        if ($verificationData['attempts'] >= 5) {
            Cache::forget($cacheKey);
            return response()->json([
                'error' => 'Too many failed attempts. Please request a new code.'
            ], 429);
        }

        // Verify code
        if ($verificationData['code'] !== $validated['code']) {
            $verificationData['attempts']++;
            Cache::put($cacheKey, $verificationData, 600);
            
            \Log::warning('Invalid verification code', [
                'email' => $validated['email'],
                'expected' => $verificationData['code'],
                'provided' => $validated['code'],
                'attempts' => $verificationData['attempts']
            ]);
            
            return response()->json([
                'error' => 'Invalid verification code',
                'attempts_remaining' => 5 - $verificationData['attempts']
            ], 422);
        }

        // Success - mark as verified
        $verificationData['verified'] = true;
        $verificationData['verified_at'] = Carbon::now()->toDateTimeString();
        Cache::put($cacheKey, $verificationData, 600);

        // Update user's email_verified_at
        $user = User::where('email', $validated['email'])->first();
        if ($user && !$user->email_verified_at) {
            $user->email_verified_at = Carbon::now();
            $user->save();
            \Log::info('User email verified', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        \Log::info('Email verified successfully', ['email' => $validated['email']]);

        return response()->json([
            'message' => 'Email verified successfully',
            'verified' => true
        ]);
    }

    public function resendCode(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        // Check if there's an existing verification
        $cacheKey = 'booking_verification_' . md5($validated['email']);
        $verificationData = Cache::get($cacheKey);

        if (!$verificationData) {
            return response()->json([
                'error' => 'No active verification session. Please start a new booking.'
            ], 404);
        }

        // Rate limiting for resend: 1 per minute
        $resendKey = 'resend_rate_' . md5($validated['email']);
        if (Cache::has($resendKey)) {
            return response()->json([
                'error' => 'Please wait 1 minute before requesting another code.'
            ], 429);
        }

        // Generate new code
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update cache
        $verificationData['code'] = $code;
        $verificationData['expires_at'] = Carbon::now()->addMinutes(10)->toDateTimeString();
        $verificationData['attempts'] = 0;
        Cache::put($cacheKey, $verificationData, 600);

        \Log::info('Resending verification code', [
            'email' => $validated['email'],
            'new_code' => $code
        ]);

        // Send email
        try {
            Mail::to($validated['email'])->send(new VerificationCodeMail(
                $code,
                $verificationData['customer_data']['customer_first_name'] ?? 'Customer',
                $verificationData['customer_data']['pickup_address'] ?? '',
                $verificationData['customer_data']['dropoff_address'] ?? '',
                $verificationData['customer_data']['pickup_date'] ?? ''
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to resend verification email', [
                'email' => $validated['email'],
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Failed to send verification email. Please try again.'
            ], 500);
        }

        // Set rate limit
        Cache::put($resendKey, true, 60);

        return response()->json([
            'message' => 'New verification code sent successfully',
            'expires_in' => 600
        ]);
    }
}