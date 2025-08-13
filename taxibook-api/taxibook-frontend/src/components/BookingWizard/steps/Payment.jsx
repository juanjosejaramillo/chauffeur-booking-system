import { useState, useEffect } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import {
  Elements,
  CardElement,
  useStripe,
  useElements,
} from '@stripe/react-stripe-js';
import { CheckCircleIcon } from '@heroicons/react/24/solid';
import useBookingStore from '../../../store/bookingStore';

const stripePromise = loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY || '');

const PaymentForm = () => {
  const stripe = useStripe();
  const elements = useElements();
  const {
    booking,
    paymentIntent,
    createPaymentIntent,
    confirmPayment,
    loading,
    error,
  } = useBookingStore();
  
  const [processing, setProcessing] = useState(false);
  const [succeeded, setSucceeded] = useState(false);
  const [paymentError, setPaymentError] = useState(null);

  useEffect(() => {
    // Create payment intent when component mounts
    if (booking && !paymentIntent) {
      createPaymentIntent();
    }
  }, [booking]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!stripe || !elements) {
      return;
    }
    
    setProcessing(true);
    setPaymentError(null);
    
    try {
      // Confirm the payment with Stripe
      const result = await stripe.confirmCardPayment(paymentIntent.client_secret, {
        payment_method: {
          card: elements.getElement(CardElement),
          billing_details: {
            name: `${booking.customer_first_name} ${booking.customer_last_name}`,
            email: booking.customer_email,
            phone: booking.customer_phone,
          },
        },
      });
      
      if (result.error) {
        setPaymentError(result.error.message);
      } else {
        // Payment succeeded, update booking status
        await confirmPayment(result.paymentIntent.id);
        setSucceeded(true);
      }
    } catch (error) {
      setPaymentError('An unexpected error occurred.');
    } finally {
      setProcessing(false);
    }
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(price);
  };

  const cardElementOptions = {
    style: {
      base: {
        fontSize: '16px',
        color: '#424770',
        '::placeholder': {
          color: '#aab7c4',
        },
      },
      invalid: {
        color: '#9e2146',
      },
    },
  };

  if (succeeded) {
    return (
      <div className="text-center py-12">
        <CheckCircleIcon className="h-16 w-16 text-green-500 mx-auto mb-4" />
        <h2 className="text-2xl font-bold text-gray-900 mb-2">
          Payment Successful!
        </h2>
        <p className="text-gray-600 mb-6">
          Your booking has been confirmed
        </p>
        
        <div className="bg-gray-50 rounded-lg p-6 text-left max-w-md mx-auto">
          <h3 className="font-semibold text-gray-900 mb-3">Booking Details</h3>
          <div className="space-y-2">
            <div>
              <p className="text-sm text-gray-600">Booking Number</p>
              <p className="font-mono font-semibold">{booking.booking_number}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">Amount Authorized</p>
              <p className="font-semibold">{formatPrice(booking.estimated_fare)}</p>
            </div>
            <div>
              <p className="text-sm text-gray-600">Status</p>
              <p className="text-green-600 font-semibold">Confirmed</p>
            </div>
          </div>
        </div>
        
        <div className="mt-8 space-y-3">
          <p className="text-sm text-gray-600">
            A confirmation email has been sent to {booking.customer_email}
          </p>
          <p className="text-sm text-gray-600">
            💳 Your card has been authorized but not charged yet.
            The final amount will be charged after your trip is completed.
          </p>
        </div>
        
        <button
          onClick={() => window.location.href = '/'}
          className="mt-6 bg-indigo-600 text-white py-3 px-6 rounded-md hover:bg-indigo-700 transition-colors"
        >
          Book Another Ride
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold text-gray-900 mb-2">
          Payment Information
        </h2>
        <p className="text-sm text-gray-600">
          Enter your card details to authorize the payment
        </p>
      </div>

      {booking && (
        <div className="bg-gray-50 rounded-lg p-4">
          <div className="flex items-center justify-between mb-2">
            <p className="text-sm text-gray-600">Booking Number</p>
            <p className="font-mono font-semibold">{booking.booking_number}</p>
          </div>
          <div className="flex items-center justify-between">
            <p className="text-sm text-gray-600">Amount to Authorize</p>
            <p className="text-xl font-bold text-indigo-600">
              {formatPrice(booking.estimated_fare)}
            </p>
          </div>
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Card Details
          </label>
          <div className="border border-gray-300 rounded-md p-3">
            <CardElement options={cardElementOptions} />
          </div>
        </div>
        
        <div className="bg-blue-50 border border-blue-200 rounded-md p-4">
          <h3 className="text-sm font-semibold text-blue-900 mb-2">
            Authorization Only
          </h3>
          <p className="text-xs text-blue-700">
            Your card will be authorized for the estimated fare amount but not charged immediately.
            The actual charge will be processed after your trip is completed, and may be adjusted
            based on the actual route taken.
          </p>
        </div>
        
        {(error || paymentError) && (
          <div className="p-3 bg-red-50 border border-red-200 rounded-md">
            <p className="text-sm text-red-600">{error || paymentError}</p>
          </div>
        )}
        
        <button
          type="submit"
          disabled={!stripe || processing || loading || !paymentIntent}
          className="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
        >
          {processing || loading ? (
            <>
              <span className="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              Processing...
            </>
          ) : (
            `Authorize ${booking ? formatPrice(booking.estimated_fare) : 'Payment'}`
          )}
        </button>
        
        <div className="flex items-center justify-center space-x-4 text-xs text-gray-500">
          <span>🔒 Secure payment powered by Stripe</span>
        </div>
      </form>
    </div>
  );
};

const Payment = () => {
  return (
    <Elements stripe={stripePromise}>
      <PaymentForm />
    </Elements>
  );
};

export default Payment;