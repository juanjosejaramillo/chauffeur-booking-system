<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use App\Events\PaymentCaptured;
use App\Events\PaymentRefunded;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        // Get Stripe key from settings (already overridden by SettingsServiceProvider)
        $stripeKey = config('services.stripe.secret') ?: config('stripe.secret_key') ?: env('STRIPE_SECRET_KEY');
        
        if (!$stripeKey) {
            throw new \Exception('Stripe secret key not configured. Please configure it in Settings.');
        }
        
        $this->stripe = new StripeClient($stripeKey);
    }

    public function createOrUpdateCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            // Update existing customer
            $customer = $this->stripe->customers->update(
                $user->stripe_customer_id,
                [
                    'email' => $user->email,
                    'name' => $user->full_name,
                    'phone' => $user->phone,
                ]
            );
        } else {
            // Create new customer
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->full_name,
                'phone' => $user->phone,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
        }

        return $customer->id;
    }

    public function createPaymentIntent(Booking $booking)
    {
        $amountInCents = round($booking->estimated_fare * 100);

        $paymentIntentData = [
            'amount' => $amountInCents,
            'currency' => 'usd',
            'payment_method_types' => ['card'], // Explicitly set to card only
            'capture_method' => 'manual',
            'metadata' => [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
            ],
            'description' => "Taxi booking {$booking->booking_number}",
        ];

        // Add customer if user is logged in and has Stripe customer ID
        if ($booking->user && $booking->user->stripe_customer_id) {
            $paymentIntentData['customer'] = $booking->user->stripe_customer_id;
        }

        $paymentIntent = $this->stripe->paymentIntents->create($paymentIntentData);

        // Update booking with payment intent ID
        $booking->update([
            'stripe_payment_intent_id' => $paymentIntent->id,
        ]);

        // Create transaction record
        Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'authorization',
            'amount' => $booking->estimated_fare,
            'status' => 'pending',
            'stripe_transaction_id' => $paymentIntent->id,
            'stripe_response' => $paymentIntent->toArray(),
        ]);

        return $paymentIntent;
    }
    
    public function chargeBooking(Booking $booking, string $paymentMethodId, float $amount, bool $saveCard = false): array
    {
        try {
            $amountInCents = round($amount * 100);
            
            // Create or get customer if saving card
            $customerId = null;
            if ($saveCard) {
                if ($booking->user && $booking->user->stripe_customer_id) {
                    $customerId = $booking->user->stripe_customer_id;
                } else {
                    // Create new customer
                    $customer = $this->stripe->customers->create([
                        'email' => $booking->customer_email,
                        'name' => $booking->customer_full_name,
                        'phone' => $booking->customer_phone,
                        'metadata' => [
                            'booking_id' => $booking->id,
                        ],
                    ]);
                    $customerId = $customer->id;
                    
                    // Update user with customer ID if exists
                    if ($booking->user) {
                        $booking->user->update(['stripe_customer_id' => $customerId]);
                    }
                }
            }
            
            // Create payment intent data
            $paymentIntentData = [
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method' => $paymentMethodId,
                'payment_method_types' => ['card'], // Explicitly set to card only
                'confirm' => true,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'base_fare' => $booking->estimated_fare,
                    'tip_amount' => $booking->gratuity_amount,
                ],
                'description' => "Booking {$booking->booking_number}",
            ];
            
            // Add customer and save card if requested
            if ($saveCard && $customerId) {
                $paymentIntentData['customer'] = $customerId;
                $paymentIntentData['setup_future_usage'] = 'off_session';
            }
            
            // Create and confirm payment
            $paymentIntent = $this->stripe->paymentIntents->create($paymentIntentData);
            
            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'payment_method_id' => $paymentMethodId,
                'customer_id' => $customerId,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    public function chargeTip(Booking $booking, float $tipAmount, string $paymentMethodId = null): array
    {
        try {
            $amountInCents = round($tipAmount * 100);
            
            $paymentIntentData = [
                'amount' => $amountInCents,
                'currency' => 'usd',
                'payment_method_types' => ['card'], // Explicitly set to card only
                'metadata' => [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'type' => 'tip',
                ],
                'description' => "Tip for booking {$booking->booking_number}",
            ];
            
            // If booking has saved payment method, use it
            if (!$paymentMethodId && $booking->stripe_payment_method_id && $booking->stripe_customer_id) {
                $paymentIntentData['customer'] = $booking->stripe_customer_id;
                $paymentIntentData['payment_method'] = $booking->stripe_payment_method_id;
                $paymentIntentData['off_session'] = true;
                $paymentIntentData['confirm'] = true;
            } else if ($paymentMethodId) {
                $paymentIntentData['payment_method'] = $paymentMethodId;
                $paymentIntentData['confirm'] = true;
            } else {
                throw new \Exception('No payment method available for tip');
            }
            
            $paymentIntent = $this->stripe->paymentIntents->create($paymentIntentData);
            
            // Update booking with tip
            $booking->update([
                'gratuity_amount' => $tipAmount,
                'gratuity_added_at' => now(),
            ]);
            
            // Create transaction record
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'tip',
                'amount' => $tipAmount,
                'status' => 'succeeded',
                'stripe_transaction_id' => $paymentIntent->id,
                'stripe_response' => $paymentIntent->toArray(),
            ]);
            
            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function confirmPaymentIntent(Booking $booking, string $paymentIntentId)
    {
        $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

        // Update transaction status
        $transaction = Transaction::where('booking_id', $booking->id)
            ->where('stripe_transaction_id', $paymentIntentId)
            ->where('type', 'authorization')
            ->first();

        if ($transaction) {
            $transaction->update([
                'status' => $paymentIntent->status === 'requires_capture' ? 'succeeded' : 'failed',
                'stripe_response' => $paymentIntent->toArray(),
            ]);
        }

        return $paymentIntent;
    }

    public function capturePayment(Booking $booking, float $amount = null)
    {
        if (!$booking->stripe_payment_intent_id) {
            throw new \Exception('No payment intent found for this booking');
        }

        $amountInCents = $amount ? round($amount * 100) : null;

        $paymentIntent = $this->stripe->paymentIntents->capture(
            $booking->stripe_payment_intent_id,
            $amountInCents ? ['amount_to_capture' => $amountInCents] : []
        );

        // Update booking
        $booking->update([
            'final_fare' => $amount ?? $booking->estimated_fare,
            'payment_status' => 'captured',
        ]);

        // Create transaction record
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'type' => 'capture',
            'amount' => $amount ?? $booking->estimated_fare,
            'status' => 'succeeded',
            'stripe_transaction_id' => $paymentIntent->id,
            'stripe_response' => $paymentIntent->toArray(),
            'processed_by' => auth()->user()->full_name ?? 'System',
        ]);

        // Trigger payment captured event
        event(new PaymentCaptured($booking->fresh(), $transaction));

        return $paymentIntent;
    }

    public function cancelPaymentIntent(Booking $booking)
    {
        if (!$booking->stripe_payment_intent_id) {
            return;
        }

        try {
            $paymentIntent = $this->stripe->paymentIntents->cancel(
                $booking->stripe_payment_intent_id
            );

            // Update booking
            $booking->update([
                'payment_status' => 'cancelled',
            ]);

            // Create transaction record
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'void',
                'amount' => 0,
                'status' => 'succeeded',
                'stripe_transaction_id' => $paymentIntent->id,
                'stripe_response' => $paymentIntent->toArray(),
                'processed_by' => auth()->user()->full_name ?? 'System',
            ]);

            return $paymentIntent;
        } catch (\Exception $e) {
            // Payment intent may already be captured or cancelled
            throw $e;
        }
    }

    public function refundPayment(Booking $booking, float $amount = null, string $reason = null)
    {
        if (!$booking->stripe_payment_intent_id) {
            throw new \Exception('No payment intent found for this booking');
        }

        $refundData = [
            'payment_intent' => $booking->stripe_payment_intent_id,
        ];

        if ($amount) {
            $refundData['amount'] = round($amount * 100);
        }

        if ($reason) {
            $refundData['metadata'] = ['reason' => $reason];
        }

        $refund = $this->stripe->refunds->create($refundData);

        // Update booking
        $booking->update([
            'payment_status' => 'refunded',
        ]);

        // Create transaction record
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'type' => $amount && $amount < $booking->final_fare ? 'partial_refund' : 'refund',
            'amount' => $amount ?? $booking->final_fare,
            'status' => 'succeeded',
            'stripe_transaction_id' => $refund->id,
            'stripe_response' => $refund->toArray(),
            'notes' => $reason,
            'processed_by' => auth()->user()->full_name ?? 'System',
        ]);

        // Trigger payment refunded event
        event(new PaymentRefunded($booking->fresh(), $transaction, $reason));

        return $refund;
    }

    public function savePaymentMethod(User $user, string $paymentMethodId)
    {
        // Ensure customer exists
        $customerId = $this->createOrUpdateCustomer($user);

        // Attach payment method to customer
        $this->stripe->paymentMethods->attach(
            $paymentMethodId,
            ['customer' => $customerId]
        );

        // Set as default payment method
        $this->stripe->customers->update(
            $customerId,
            [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]
        );

        return $paymentMethodId;
    }
}