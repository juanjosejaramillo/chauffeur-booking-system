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
        $amountInCents = round(($booking->estimated_fare + $booking->extras_total) * 100);

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

        // Calculate the refund amount
        $chargedAmount = $booking->final_fare ?? $booking->estimated_fare;
        $refundAmount = $amount ?? $chargedAmount;

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

        // Calculate new total refunded amount
        $newTotalRefunded = $booking->total_refunded + $refundAmount;
        
        // Determine the correct payment status
        // Since SQLite doesn't support 'partial' in the enum, we'll keep using 'captured' for partial refunds
        // and only use 'refunded' for full refunds
        $paymentStatus = 'refunded'; // Default to fully refunded
        if ($newTotalRefunded < $chargedAmount) {
            $paymentStatus = 'captured'; // Keep as captured for partial refunds
        }

        // Update booking with new refund information
        $booking->update([
            'payment_status' => $paymentStatus,
            'total_refunded' => $newTotalRefunded,
        ]);

        // Create transaction record
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'type' => $refundAmount < $chargedAmount ? 'partial_refund' : 'refund',
            'amount' => $refundAmount,
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

    /**
     * Create a Setup Intent to save a card WITHOUT charging.
     * Use this when payment_mode is 'post_service' - card is validated
     * and saved at booking, but charged only after ride completion.
     */
    public function createSetupIntent(Booking $booking): array
    {
        try {
            // Create or get Stripe customer
            $customerId = null;

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
                        'booking_number' => $booking->booking_number,
                    ],
                ]);
                $customerId = $customer->id;

                // Update user with customer ID if exists
                if ($booking->user) {
                    $booking->user->update(['stripe_customer_id' => $customerId]);
                }
            }

            // Create Setup Intent for off-session usage
            $setupIntent = $this->stripe->setupIntents->create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'usage' => 'off_session', // Important: allows charging later without customer present
                'metadata' => [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                ],
            ]);

            // Store the customer ID on the booking for later use
            $booking->update([
                'stripe_customer_id' => $customerId,
            ]);

            return [
                'success' => true,
                'client_secret' => $setupIntent->client_secret,
                'setup_intent_id' => $setupIntent->id,
                'customer_id' => $customerId,
            ];

        } catch (\Exception $e) {
            \Log::error('Setup Intent creation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Complete Setup Intent and save the payment method.
     * Called after frontend confirms the SetupIntent with Stripe.
     */
    public function completeSetupIntent(Booking $booking, string $setupIntentId): array
    {
        try {
            // Retrieve the SetupIntent to get the payment method
            $setupIntent = $this->stripe->setupIntents->retrieve($setupIntentId);

            if ($setupIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'error' => 'Setup Intent not completed. Status: ' . $setupIntent->status,
                ];
            }

            $paymentMethodId = $setupIntent->payment_method;

            // Set as default payment method for customer
            $this->stripe->customers->update(
                $setupIntent->customer,
                [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethodId,
                    ],
                ]
            );

            // Get card details for display
            $paymentMethod = $this->stripe->paymentMethods->retrieve($paymentMethodId);

            // Update booking with saved payment method
            $booking->update([
                'stripe_payment_method_id' => $paymentMethodId,
                'stripe_customer_id' => $setupIntent->customer,
                'save_payment_method' => true,
            ]);

            return [
                'success' => true,
                'payment_method_id' => $paymentMethodId,
                'customer_id' => $setupIntent->customer,
                'card' => [
                    'brand' => $paymentMethod->card->brand,
                    'last4' => $paymentMethod->card->last4,
                    'exp_month' => $paymentMethod->card->exp_month,
                    'exp_year' => $paymentMethod->card->exp_year,
                ],
            ];

        } catch (\Exception $e) {
            \Log::error('Complete Setup Intent failed', [
                'booking_id' => $booking->id,
                'setup_intent_id' => $setupIntentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Charge a saved payment method later (off-session).
     * Called when admin marks booking as "completed" in post_service mode.
     * Can also be used for no-show fees or additional charges.
     */
    public function chargeWithSavedCard(Booking $booking, float $amount, string $reason = null): array
    {
        try {
            // Validate booking has saved payment method
            if (!$booking->stripe_customer_id || !$booking->stripe_payment_method_id) {
                return [
                    'success' => false,
                    'error' => 'No saved payment method found for this booking',
                ];
            }

            $amountInCents = round($amount * 100);

            // Create and confirm payment intent off-session
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'customer' => $booking->stripe_customer_id,
                'payment_method' => $booking->stripe_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'payment_method_types' => ['card'],
                'metadata' => [
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'charge_type' => 'saved_card',
                    'reason' => $reason ?? 'Ride completed',
                ],
                'description' => $reason
                    ? "Booking {$booking->booking_number}: {$reason}"
                    : "Booking {$booking->booking_number} - Ride completed",
            ]);

            // Update booking payment details
            $booking->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
                'final_fare' => $amount,
                'payment_status' => 'captured',
            ]);

            // Create transaction record
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'succeeded',
                'stripe_transaction_id' => $paymentIntent->id,
                'stripe_response' => $paymentIntent->toArray(),
                'notes' => $reason ?? 'Charged saved card after ride completion',
                'processed_by' => auth()->user()->full_name ?? 'System',
            ]);

            \Log::info('Charged saved card successfully', [
                'booking_id' => $booking->id,
                'amount' => $amount,
                'payment_intent_id' => $paymentIntent->id,
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
            ];

        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined
            \Log::error('Saved card charge declined', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'decline_code' => $e->getDeclineCode(),
            ]);

            // Log failed transaction
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'failed',
                'notes' => 'Card declined: ' . $e->getMessage(),
                'processed_by' => auth()->user()->full_name ?? 'System',
            ]);

            return [
                'success' => false,
                'error' => 'Card was declined: ' . $e->getMessage(),
                'decline_code' => $e->getDeclineCode(),
            ];

        } catch (\Exception $e) {
            \Log::error('Saved card charge failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get saved payment method details for a booking.
     */
    public function getSavedPaymentMethod(Booking $booking): ?array
    {
        if (!$booking->stripe_payment_method_id) {
            return null;
        }

        try {
            $paymentMethod = $this->stripe->paymentMethods->retrieve(
                $booking->stripe_payment_method_id
            );

            return [
                'id' => $paymentMethod->id,
                'brand' => $paymentMethod->card->brand,
                'last4' => $paymentMethod->card->last4,
                'exp_month' => $paymentMethod->card->exp_month,
                'exp_year' => $paymentMethod->card->exp_year,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to retrieve saved payment method', [
                'booking_id' => $booking->id,
                'payment_method_id' => $booking->stripe_payment_method_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}