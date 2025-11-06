<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $webhook_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $webhook_secret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
                
            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;
                
            default:
                // Unhandled event type
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $booking = Booking::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($booking) {
            // Update booking status if payment was captured
            if ($paymentIntent->status === 'succeeded' && $paymentIntent->amount_received > 0) {
                $booking->update([
                    'payment_status' => 'captured',
                    'status' => 'confirmed',
                ]);
                
                // Update transaction record
                Transaction::where('stripe_transaction_id', $paymentIntent->id)
                    ->where('type', 'capture')
                    ->update([
                        'status' => 'succeeded',
                        'stripe_response' => $paymentIntent,
                    ]);
            }
        }
    }

    private function handlePaymentIntentFailed($paymentIntent)
    {
        $booking = Booking::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        
        if ($booking) {
            $booking->update([
                'payment_status' => 'failed',
            ]);
            
            // Log the failure
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'authorization',
                'amount' => $paymentIntent->amount / 100,
                'status' => 'failed',
                'stripe_transaction_id' => $paymentIntent->id,
                'stripe_response' => $paymentIntent,
                'notes' => $paymentIntent->last_payment_error?->message,
            ]);
        }
    }

    private function handleChargeRefunded($charge)
    {
        $booking = Booking::where('stripe_payment_intent_id', $charge->payment_intent)->first();
        
        if ($booking) {
            $booking->update([
                'payment_status' => 'refunded',
            ]);
            
            // Log the refund
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => $charge->amount_refunded === $charge->amount ? 'refund' : 'partial_refund',
                'amount' => $charge->amount_refunded / 100,
                'status' => 'succeeded',
                'stripe_transaction_id' => $charge->id,
                'stripe_response' => $charge,
                'processed_by' => 'Stripe Webhook',
            ]);
        }
    }
}