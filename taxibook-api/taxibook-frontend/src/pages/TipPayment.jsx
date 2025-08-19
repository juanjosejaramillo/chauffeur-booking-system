import { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { loadStripe } from '@stripe/stripe-js';
import {
  Elements,
  CardElement,
  useStripe,
  useElements,
} from '@stripe/react-stripe-js';
import api from '../config/api';
import useSettings from '../hooks/useSettings';

const TipPaymentForm = ({ booking, token }) => {
  const stripe = useStripe();
  const elements = useElements();
  const navigate = useNavigate();
  
  const [selectedTip, setSelectedTip] = useState(0);
  const [customAmount, setCustomAmount] = useState('');
  const [saveCard, setSaveCard] = useState(false);
  const [processing, setProcessing] = useState(false);
  const [error, setError] = useState('');

  const tipOptions = booking.suggested_tips || [
    { percentage: 15, amount: (booking.fare_paid * 0.15).toFixed(2) },
    { percentage: 20, amount: (booking.fare_paid * 0.20).toFixed(2) },
    { percentage: 25, amount: (booking.fare_paid * 0.25).toFixed(2) },
  ];

  const handleTipSelect = (amount) => {
    setSelectedTip(amount);
    setCustomAmount('');
  };

  const handleCustomAmount = (value) => {
    const amount = parseFloat(value) || 0;
    setCustomAmount(value);
    setSelectedTip(amount);
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (selectedTip <= 0) {
      setError('Please select a tip amount');
      return;
    }

    setProcessing(true);
    setError('');

    try {
      let paymentMethodId = null;

      // If no saved card, create payment method
      if (!booking.has_saved_card) {
        if (!stripe || !elements) {
          throw new Error('Stripe not loaded');
        }

        const card = elements.getElement(CardElement);
        const { error: stripeError, paymentMethod } = await stripe.createPaymentMethod({
          type: 'card',
          card: card,
        });

        if (stripeError) {
          throw new Error(stripeError.message);
        }

        paymentMethodId = paymentMethod.id;
      }

      // Process tip
      const response = await api.post(`/tip/${token}/process`, {
        amount: selectedTip,
        payment_method_id: paymentMethodId,
        save_card: saveCard,
      });

      if (response.data.success) {
        // Immediately redirect to success page
        window.location.href = `/tip/${token}/success`;
      } else {
        throw new Error(response.data.message || 'Payment failed');
      }
    } catch (err) {
      setError(err.message || 'Payment failed. Please try again.');
      setProcessing(false);
    }
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
  };

  return (
    <div className="max-w-2xl mx-auto relative">
      {/* Processing Overlay */}
      {processing && (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
          <div className="bg-white rounded-lg p-8 max-w-sm w-full mx-4">
            <div className="flex flex-col items-center">
              <div className="animate-spin h-12 w-12 border-4 border-luxury-gold border-t-transparent rounded-full mb-4"></div>
              <p className="text-lg font-medium text-luxury-black mb-2">Processing Payment</p>
              <p className="text-sm text-luxury-gray/60 text-center">Please wait while we process your tip...</p>
            </div>
          </div>
        </div>
      )}
      
      {/* Trip Details Card */}
      <div className="bg-luxury-white shadow-luxury mb-8">
        <div className="border-b border-luxury-gray/10 p-6">
          <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-4">
            Trip Details
          </h3>
          <div className="space-y-3">
            <div className="flex justify-between items-center">
              <span className="text-sm text-luxury-gray/60">Booking Number</span>
              <span className="text-sm font-medium text-luxury-black">#{booking.booking_number}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-sm text-luxury-gray/60">Date</span>
              <span className="text-sm font-medium text-luxury-black">{booking.pickup_date}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-sm text-luxury-gray/60">Vehicle</span>
              <span className="text-sm font-medium text-luxury-black">{booking.vehicle_type}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-sm text-luxury-gray/60">Fare Paid</span>
              <span className="text-sm font-medium text-luxury-black">{formatPrice(booking.fare_paid)}</span>
            </div>
          </div>
        </div>

        {/* Tip Selection */}
        <div className="p-6">
          <h4 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
            Select Gratuity Amount
          </h4>

          {/* Preset Options */}
          <div className="grid grid-cols-3 gap-3 mb-6">
            {tipOptions.map((option) => (
              <button
                key={option.percentage}
                type="button"
                onClick={() => handleTipSelect(parseFloat(option.amount))}
                className={`py-4 px-4 rounded border-2 transition-all ${
                  selectedTip === parseFloat(option.amount) && !customAmount
                    ? 'border-luxury-gold bg-luxury-light-gray'
                    : 'border-luxury-gray/20 hover:border-luxury-gray/40'
                }`}
              >
                <div className="text-lg font-medium text-luxury-black">{option.percentage}%</div>
                <div className="text-sm text-luxury-gray/60">{formatPrice(option.amount)}</div>
              </button>
            ))}
          </div>

          {/* Custom Amount */}
          <div className="mb-6">
            <label className="block text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-3">
              Or Enter Custom Amount
            </label>
            <div className="relative">
              <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-luxury-gray/60">$</span>
              <input
                type="number"
                value={customAmount}
                onChange={(e) => handleCustomAmount(e.target.value)}
                placeholder="0.00"
                min="0"
                step="0.01"
                className={`w-full pl-8 pr-3 py-3 border-2 rounded transition-colors ${
                  customAmount ? 'border-luxury-gold bg-luxury-light-gray' : 'border-luxury-gray/20 focus:border-luxury-gold'
                } focus:outline-none`}
              />
            </div>
          </div>

          {/* Selected Amount Display */}
          <div className="bg-luxury-light-gray p-4 rounded mb-6">
            <div className="flex justify-between items-center">
              <span className="text-sm font-medium text-luxury-gray/70">Selected Tip:</span>
              <span className="text-xl font-display text-luxury-black">{formatPrice(selectedTip)}</span>
            </div>
          </div>

          {/* Payment Form */}
          <form onSubmit={handleSubmit}>
            {!booking.has_saved_card ? (
              <>
                {/* Card Input */}
                <div className="mb-6">
                  <label className="block text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-3">
                    Card Information
                  </label>
                  <div className="p-4 border-2 border-luxury-gray/20 rounded focus-within:border-luxury-gold transition-colors">
                    <CardElement options={cardElementOptions} />
                  </div>
                </div>

                {/* Save Card Option */}
                <div className="flex items-start mb-6">
                  <input
                    type="checkbox"
                    id="save-card-tip"
                    checked={saveCard}
                    onChange={(e) => setSaveCard(e.target.checked)}
                    className="mt-1 h-4 w-4 text-luxury-gold focus:ring-luxury-gold border-luxury-gray/30 rounded"
                  />
                  <label htmlFor="save-card-tip" className="ml-3 text-sm">
                    <span className="font-medium text-luxury-black">Save payment method for future use</span>
                    <p className="text-xs text-luxury-gray/60 mt-1">
                      Save your card for faster future bookings
                    </p>
                  </label>
                </div>
              </>
            ) : (
              <div className="bg-luxury-gold/10 border border-luxury-gold/30 rounded p-4 mb-6">
                <p className="text-sm text-luxury-black">
                  Using saved card ending in {booking.saved_card_last4 || '****'}
                </p>
              </div>
            )}

            {/* Error Message */}
            {error && (
              <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p className="text-sm text-red-700">{error}</p>
              </div>
            )}

            {/* Submit Button */}
            <button
              type="submit"
              disabled={!selectedTip || processing}
              className="w-full btn-luxury-gold uppercase tracking-luxury text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {processing ? (
                <span className="flex items-center justify-center gap-2">
                  <svg className="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                  </svg>
                  Processing...
                </span>
              ) : (
                `Add Tip - ${formatPrice(selectedTip)}`
              )}
            </button>
          </form>

          {/* Skip Option */}
          <div className="text-center mt-4">
            <button
              onClick={() => navigate('/')}
              className="text-sm text-luxury-gray/60 hover:text-luxury-gray transition-colors"
            >
              Skip - No tip at this time
            </button>
          </div>
        </div>
      </div>

      {/* Note */}
      <div className="bg-luxury-gold/10 border border-luxury-gold/30 p-4 rounded">
        <p className="text-xs text-luxury-black">
          <strong>Note:</strong> This gratuity is optional and goes directly to your driver as appreciation for their service.
        </p>
      </div>
    </div>
  );
};

const TipPaymentPage = () => {
  const { token } = useParams();
  const navigate = useNavigate();
  const { settings, loading: settingsLoading } = useSettings();
  const [booking, setBooking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchBookingData();
  }, [token]);

  const fetchBookingData = async () => {
    try {
      const response = await api.get(`/tip/${token}`);
      
      if (response.data.already_tipped) {
        navigate(`/tip/${token}/already-tipped`);
        return;
      }
      
      setBooking(response.data);
    } catch (err) {
      if (err.response?.status === 404) {
        setError('Invalid or expired tip link');
      } else {
        setError('Failed to load booking information');
      }
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray flex items-center justify-center">
        <div className="animate-spin h-12 w-12 border-4 border-luxury-gold border-t-transparent rounded-full"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray flex items-center justify-center">
        <div className="bg-luxury-white shadow-luxury p-8 max-w-md text-center">
          <svg className="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h2 className="font-display text-2xl text-luxury-black mb-2">Error</h2>
          <p className="text-luxury-gray/60 mb-6">{error}</p>
          <button
            onClick={() => navigate('/')}
            className="btn-luxury-gold uppercase tracking-luxury text-sm"
          >
            Return Home
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray py-12">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-8">
          <h1 className="font-display text-4xl text-luxury-black mb-2">
            Add Gratuity
          </h1>
          <p className="text-luxury-gray/60 text-sm tracking-wide">
            Thank your driver for exceptional service
          </p>
        </div>

        {/* Payment Form */}
        {booking && !settingsLoading && (
          <Elements stripe={loadStripe(settings?.stripe?.public_key || import.meta.env.VITE_STRIPE_PUBLIC_KEY)}>
            <TipPaymentForm booking={booking} token={token} />
          </Elements>
        )}
      </div>
    </div>
  );
};

export default TipPaymentPage;