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
    processBookingPayment,
    prevStep,
    nextStep,
    loading,
    error,
    gratuityAmount,
    gratuityPercentage,
    savePaymentMethod,
    setGratuity,
    setSavePaymentMethod,
  } = useBookingStore();

  const [localError, setLocalError] = useState('');
  const [processing, setProcessing] = useState(false);
  const [customTip, setCustomTip] = useState('');

  const baseFare = selectedVehicle?.estimated_fare || selectedVehicle?.total_price || 0;
  const totalAmount = baseFare + gratuityAmount;

  const gratuityOptions = [
    { percentage: 0, label: 'No tip' },
    { percentage: 15, label: '15%' },
    { percentage: 20, label: '20%' },
    { percentage: 25, label: '25%' },
  ];

  const calculateTip = (percentage) => {
    return baseFare * (percentage / 100);
  };

  const selectGratuity = (percentage) => {
    setCustomTip('');
    const amount = calculateTip(percentage);
    setGratuity(percentage, amount);
  };

  const handleCustomTip = (value) => {
    setCustomTip(value);
    const amount = parseFloat(value) || 0;
    if (amount >= 0) {
      setGratuity('custom', amount);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!stripe || !elements) {
      return;
    }

    setProcessing(true);
    setLocalError('');

    try {
      // Create payment method with Stripe
      const card = elements.getElement(CardElement);
      const { error: stripeError, paymentMethod } = await stripe.createPaymentMethod({
        type: 'card',
        card: card,
      });

      if (stripeError) {
        setLocalError(stripeError.message);
        setProcessing(false);
        return;
      }

      // Process payment for existing booking
      await processBookingPayment(paymentMethod.id);
      
      // Move to confirmation step
      nextStep();
      
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
          Complete Your Payment
        </h2>
        {booking?.booking_number && (
          <p className="text-luxury-gold text-sm font-semibold mb-2">
            Booking #{booking.booking_number}
          </p>
        )}
        <p className="text-luxury-gray/60 text-sm tracking-wide">
          Enter your payment details to confirm your booking
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column - Payment Details */}
        <div className="lg:col-span-2">
          {/* Fare Summary with Gratuity */}
          <div className="bg-luxury-white shadow-luxury p-8 mb-8">
            <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
              Fare Summary
            </h3>
            
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-luxury-gray/60 text-sm">Base Fare</span>
                <span className="font-display text-xl text-luxury-black">
                  {formatPrice(baseFare)}
                </span>
              </div>

              {/* Gratuity Section */}
              <div className="pt-4 border-t border-luxury-gray/10">
                <label className="block text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-4">
                  Add Gratuity (Optional)
                </label>
                <p className="text-xs text-luxury-gray/60 mb-4">
                  You can also add a tip after your trip
                </p>
                
                <div className="grid grid-cols-4 gap-2 mb-4">
                  {gratuityOptions.map(option => (
                    <button
                      key={option.percentage}
                      type="button"
                      onClick={() => selectGratuity(option.percentage)}
                      className={`py-3 px-2 rounded border-2 transition-all text-center ${
                        gratuityPercentage === option.percentage && gratuityPercentage !== 'custom'
                          ? 'border-luxury-gold bg-luxury-light-gray'
                          : 'border-luxury-gray/20 hover:border-luxury-gray/40'
                      }`}
                    >
                      <div className="text-sm font-medium">{option.label}</div>
                      <div className="text-xs text-luxury-gray/60">
                        {formatPrice(calculateTip(option.percentage))}
                      </div>
                    </button>
                  ))}
                </div>

                <div className="flex items-center gap-2">
                  <label className="text-sm text-luxury-gray/60">Custom amount:</label>
                  <div className="relative flex-1">
                    <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-luxury-gray/60">$</span>
                    <input
                      type="number"
                      value={customTip}
                      onChange={(e) => handleCustomTip(e.target.value)}
                      placeholder="0.00"
                      min="0"
                      step="0.01"
                      className={`w-full pl-8 pr-3 py-2 border-2 rounded transition-colors ${
                        gratuityPercentage === 'custom'
                          ? 'border-luxury-gold bg-luxury-light-gray'
                          : 'border-luxury-gray/20 focus:border-luxury-gold'
                      } focus:outline-none`}
                    />
                  </div>
                </div>

                {gratuityAmount > 0 && (
                  <div className="flex justify-between items-center mt-4 text-green-600">
                    <span className="text-sm">Gratuity:</span>
                    <span className="font-medium">+{formatPrice(gratuityAmount)}</span>
                  </div>
                )}
              </div>

              {/* Total */}
              <div className="flex justify-between items-center pt-4 border-t border-luxury-gray/10">
                <span className="text-luxury-black font-semibold">Total Amount</span>
                <span className="font-display text-2xl text-luxury-black">
                  {formatPrice(totalAmount)}
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

              {/* Save Card Option */}
              <div className="mt-6 flex items-start">
                <input
                  type="checkbox"
                  id="save-card"
                  checked={savePaymentMethod}
                  onChange={(e) => setSavePaymentMethod(e.target.checked)}
                  className="mt-1 h-4 w-4 text-luxury-gold focus:ring-luxury-gold border-luxury-gray/30 rounded"
                />
                <label htmlFor="save-card" className="ml-3 text-sm">
                  <span className="font-medium text-luxury-black">Save payment method for future use</span>
                  <p className="text-xs text-luxury-gray/60 mt-1">
                    Save your card for faster future bookings and easy post-trip tipping
                  </p>
                  <p className="text-xs text-luxury-gray/50 mt-1">
                    🔒 Secured by Stripe • Remove anytime from your account
                  </p>
                </label>
              </div>

              {/* Payment Notice */}
              <div className="mt-6 p-6 bg-luxury-light-gray border-l-4 border-luxury-gold">
                <h4 className="text-xs font-semibold text-luxury-black uppercase tracking-luxury mb-3">
                  Immediate Payment
                </h4>
                <p className="text-xs text-luxury-gray/70 leading-relaxed">
                  Your card will be charged {formatPrice(totalAmount)} immediately upon booking confirmation.
                  {gratuityAmount > 0 && ` This includes a ${formatPrice(gratuityAmount)} gratuity.`}
                  {' '}The charge is final and will be processed now.
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
                      Pay {formatPrice(totalAmount)}
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>

        {/* Right Column - Security & Summary */}
        <div className="space-y-8">
          {/* Booking Summary */}
          <div className="bg-luxury-white shadow-luxury p-8">
            <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-4">
              Booking Summary
            </h3>
            <div className="space-y-3 text-sm">
              <div>
                <p className="text-luxury-gray/60 text-xs">Vehicle</p>
                <p className="text-luxury-black font-medium">{selectedVehicle?.display_name}</p>
              </div>
              <div>
                <p className="text-luxury-gray/60 text-xs">Trip Fare</p>
                <p className="text-luxury-black font-medium">{formatPrice(baseFare)}</p>
              </div>
              {gratuityAmount > 0 && (
                <div>
                  <p className="text-luxury-gray/60 text-xs">Gratuity</p>
                  <p className="text-luxury-black font-medium">{formatPrice(gratuityAmount)}</p>
                </div>
              )}
              <div className="pt-3 border-t border-luxury-gray/10">
                <p className="text-luxury-gray/60 text-xs">Total to Pay</p>
                <p className="text-luxury-black font-bold text-lg">{formatPrice(totalAmount)}</p>
              </div>
            </div>
          </div>

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

          {/* Tip Info */}
          <div className="bg-luxury-light-gray p-6">
            <h3 className="text-xs font-semibold text-luxury-black uppercase tracking-luxury mb-3">
              About Gratuity
            </h3>
            <ul className="space-y-2 text-xs text-luxury-gray/70">
              <li className="flex items-start gap-2">
                <span className="text-luxury-gold mt-0.5">•</span>
                <span>Tips are optional and go directly to your driver</span>
              </li>
              <li className="flex items-start gap-2">
                <span className="text-luxury-gold mt-0.5">•</span>
                <span>You can add a tip now or after your trip</span>
              </li>
              <li className="flex items-start gap-2">
                <span className="text-luxury-gold mt-0.5">•</span>
                <span>100% of the gratuity goes to your driver</span>
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