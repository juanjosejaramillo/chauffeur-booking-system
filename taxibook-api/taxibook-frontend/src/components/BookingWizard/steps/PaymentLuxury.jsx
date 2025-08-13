import { useState, useEffect } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import {
  Elements,
  CardElement,
  useStripe,
  useElements,
} from '@stripe/react-stripe-js';
import useBookingStore from '../../../store/bookingStore';

const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY);

const PaymentForm = () => {
  const stripe = useStripe();
  const elements = useElements();
  const {
    booking,
    selectedVehicle,
    createPaymentIntent,
    confirmPayment,
    prevStep,
    nextStep,
    loading,
    error,
  } = useBookingStore();

  const [localError, setLocalError] = useState('');
  const [processing, setProcessing] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!stripe || !elements) {
      return;
    }

    setProcessing(true);
    setLocalError('');

    try {
      // Step 1: Create payment intent on backend
      const paymentIntentData = await createPaymentIntent();
      
      // Step 2: Confirm payment with Stripe using card details
      const card = elements.getElement(CardElement);
      const { error: stripeError, paymentIntent } = await stripe.confirmCardPayment(
        paymentIntentData.client_secret,
        {
          payment_method: {
            card: card,
          },
        }
      );

      if (stripeError) {
        setLocalError(stripeError.message);
        setProcessing(false);
        return;
      }

      // Step 3: Confirm payment on backend
      if (paymentIntent.status === 'succeeded' || paymentIntent.status === 'requires_capture') {
        await confirmPayment(paymentIntent.id);
        // Move to next step (confirmation)
        nextStep();
      }
      
    } catch (error) {
      setLocalError(error.message || 'Payment failed. Please try again.');
    } finally {
      setProcessing(false);
    }
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price);
  };

  const cardElementOptions = {
    style: {
      base: {
        fontFamily: 'Inter, system-ui, sans-serif',
        fontSize: '16px',
        fontWeight: '300',
        color: '#1A1A1A',
        letterSpacing: '0.025em',
        '::placeholder': {
          color: '#9CA3AF',
        },
        iconColor: '#C9A961',
      },
      invalid: {
        color: '#DC2626',
        iconColor: '#DC2626',
      },
    },
    hidePostalCode: false,
  };

  return (
    <div className="max-w-4xl mx-auto">
      {/* Header */}
      <div className="text-center mb-12">
        <h2 className="font-display text-3xl text-luxury-black mb-4">
          Complete Your Booking
        </h2>
        <p className="text-luxury-gray/60 text-sm tracking-wide">
          Enter your card details to authorize the payment
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column - Booking Summary */}
        <div className="lg:col-span-2">
          {/* Booking Details */}
          <div className="bg-luxury-white shadow-luxury p-8 mb-8">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury">
                Booking Reference
              </h3>
              <span className="font-mono text-luxury-black text-lg tracking-wider">
                {booking?.booking_number}
              </span>
            </div>

            <div className="border-t border-luxury-gray/10 pt-6">
              <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-4">
                Authorization Amount
              </h3>
              <div className="flex items-baseline justify-between">
                <span className="text-luxury-gray/60 text-sm">Amount to be held</span>
                <span className="font-display text-3xl text-luxury-black">
                  {formatPrice(selectedVehicle.estimated_fare || selectedVehicle.total_price)}
                </span>
              </div>
            </div>
          </div>

          {/* Card Form */}
          <div className="bg-luxury-white shadow-luxury p-8">
            <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
              Card Details
            </h3>
            
            <form onSubmit={handleSubmit}>
              <div className="p-4 border-2 border-luxury-gray/20 focus-within:border-luxury-gold transition-colors duration-300">
                <CardElement options={cardElementOptions} />
              </div>

              {/* Authorization Notice */}
              <div className="mt-6 p-6 bg-luxury-light-gray border-l-4 border-luxury-gold">
                <h4 className="text-xs font-semibold text-luxury-black uppercase tracking-luxury mb-3">
                  Authorization Only
                </h4>
                <p className="text-xs text-luxury-gray/70 leading-relaxed">
                  Your card will be authorized for the estimated fare amount but not charged immediately. 
                  The actual charge will be processed after your trip is completed, and may be adjusted 
                  based on the actual route taken.
                </p>
              </div>

              {/* Error Message */}
              {(localError || error) && (
                <div className="mt-6 bg-red-50 border-l-4 border-red-500 p-4 animate-fade-in">
                  <p className="text-sm text-red-700">{localError || error}</p>
                </div>
              )}

              {/* Action Buttons */}
              <div className="flex gap-4 mt-8">
                <button
                  type="button"
                  onClick={prevStep}
                  disabled={processing || loading}
                  className="flex-1 btn-luxury-outline uppercase tracking-luxury text-sm"
                >
                  Back
                </button>
                <button
                  type="submit"
                  disabled={!stripe || processing || loading}
                  className="flex-1 btn-luxury-gold uppercase tracking-luxury text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                  {processing || loading ? (
                    <span className="flex items-center gap-2">
                      <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      Processing...
                    </span>
                  ) : (
                    <>
                      <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                      Authorize {formatPrice(selectedVehicle.estimated_fare || selectedVehicle.total_price)}
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>

        {/* Right Column - Security & Summary */}
        <div className="space-y-8">
          {/* Security Notice */}
          <div className="bg-luxury-white shadow-luxury p-8">
            <div className="flex items-center gap-3 mb-4">
              <svg className="w-6 h-6 text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
              </svg>
              <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury">
                Secure Payment
              </h3>
            </div>
            <p className="text-xs text-luxury-gray/70 leading-relaxed mb-4">
              Your payment information is encrypted and securely processed through Stripe. 
              We never store your card details.
            </p>
            <div className="flex items-center gap-2 pt-4 border-t border-luxury-gray/10">
              <svg className="w-4 h-4 text-luxury-gray/50" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
              </svg>
              <span className="text-xs text-luxury-gray/60">
                Powered by Stripe
              </span>
            </div>
          </div>

          {/* Cancellation Policy */}
          <div className="bg-luxury-light-gray p-6">
            <h3 className="text-xs font-semibold text-luxury-black uppercase tracking-luxury mb-3">
              Cancellation Policy
            </h3>
            <ul className="space-y-2 text-xs text-luxury-gray/70">
              <li className="flex items-start gap-2">
                <span className="text-luxury-gold mt-0.5">•</span>
                <span>Free cancellation up to 24 hours before pickup</span>
              </li>
              <li className="flex items-start gap-2">
                <span className="text-luxury-gold mt-0.5">•</span>
                <span>50% charge for cancellations within 24 hours</span>
              </li>
              <li className="flex items-start gap-2">
                <span className="text-luxury-gold mt-0.5">•</span>
                <span>Full charge for no-shows</span>
              </li>
            </ul>
          </div>

          {/* Contact Support */}
          <div className="text-center">
            <p className="text-xs text-luxury-gray/60 mb-2">
              Need assistance?
            </p>
            <a href="tel:+1-800-TAXIBOOK" className="text-luxury-gold hover:text-luxury-gold-dark text-sm font-medium">
              +1-800-TAXIBOOK
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};

const PaymentLuxury = () => {
  return (
    <Elements stripe={stripePromise}>
      <PaymentForm />
    </Elements>
  );
};

export default PaymentLuxury;