import { useState, useEffect, useMemo, useRef } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import {
  Elements,
  CardNumberElement,
  CardExpiryElement,
  CardCvcElement,
  useStripe,
  useElements,
} from '@stripe/react-stripe-js';
import useBookingStore from '../../../store/bookingStore';
import useSettings from '../../../hooks/useSettings';
import { GoogleTracking } from '../../../services/googleTracking';
import { ClarityTracking } from '../../../services/clarityTracking';

const PaymentForm = () => {
  const stripe = useStripe();
  const elements = useElements();
  const { settings } = useSettings();
  const {
    booking,
    selectedVehicle,
    processBookingPayment,
    createSetupIntent,
    completeSetupIntent,
    prevStep,
    nextStep,
    loading,
    error,
    gratuityAmount,
    gratuityPercentage,
    selectedExtras,
    savePaymentMethod,
    setGratuity,
    setSavePaymentMethod,
  } = useBookingStore();

  const extrasTotal = Object.values(selectedExtras || {}).reduce((sum, e) => sum + e.price * e.quantity, 0);

  // Get payment mode from settings (default to 'immediate' for backward compatibility)
  const paymentMode = settings?.stripe?.payment_mode || 'immediate';
  const isPostServiceMode = paymentMode === 'post_service';
  // Legal URLs from settings
  const termsUrl = settings?.legal?.terms_url || '';
  const cancellationPolicyUrl = settings?.legal?.cancellation_policy_url || '';
  const privacyPolicyUrl = settings?.legal?.privacy_policy_url || '';
  const refundPolicyUrl = settings?.legal?.refund_policy_url || '';

  // Check which legal links are available
  const hasTerms = termsUrl && termsUrl !== '#';
  const hasCancellationPolicy = cancellationPolicyUrl && cancellationPolicyUrl !== '#';
  const hasPrivacyPolicy = privacyPolicyUrl && privacyPolicyUrl !== '#';
  const hasRefundPolicy = refundPolicyUrl && refundPolicyUrl !== '#';
  const hasAnyLegalLinks = hasTerms || hasCancellationPolicy || hasPrivacyPolicy || hasRefundPolicy;

  const [localError, setLocalError] = useState('');
  const [processing, setProcessing] = useState(false);
  const [customTip, setCustomTip] = useState('');
  const [postalCode, setPostalCode] = useState('');
  const hasTrackedCheckout = useRef(false);

  const baseFare = selectedVehicle?.estimated_fare || selectedVehicle?.total_price || 0;
  const totalAmount = baseFare + extrasTotal + gratuityAmount;

  useEffect(() => {
    // Track begin_checkout when payment page loads (only once)
    if (baseFare > 0 && !hasTrackedCheckout.current && selectedVehicle) {
      const vehicleName = selectedVehicle.display_name || selectedVehicle.name || 'Chauffeur Service';
      const vehicleDescription = selectedVehicle.description || 'Chauffeur Service';
      GoogleTracking.trackBeginCheckout(baseFare, vehicleName, vehicleDescription);
      
      // Track payment page view with Clarity (begin_checkout)
      ClarityTracking.trackPayment('page_viewed', {
        amount: totalAmount,
        vehicle_name: vehicleName
      });
      
      hasTrackedCheckout.current = true;
    }
  }, []); // Only track once on mount

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
    
    // Track tip selection with Clarity
    ClarityTracking.trackPayment('tip_selected', {
      gratuityPercent: percentage,
      gratuityAmount: amount,
      tipType: percentage === 0 ? 'no_tip' : 'percentage'
    });
  };

  const handleCustomTip = (value) => {
    setCustomTip(value);
    const amount = parseFloat(value) || 0;
    if (amount >= 0) {
      setGratuity('custom', amount);
      
      // Track custom tip selection with Clarity
      if (amount > 0) {
        ClarityTracking.trackPayment('tip_selected', {
          gratuityAmount: amount,
          tipType: 'custom'
        });
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!stripe || !elements) {
      return;
    }

    setProcessing(true);
    setLocalError('');

    // Track payment submission and upgrade session
    ClarityTracking.trackPayment('submitted', {
      amount: totalAmount,
      hasGratuity: gratuityAmount > 0,
      gratuityAmount: gratuityAmount,
      saveCard: savePaymentMethod,
      paymentMode: paymentMode
    });
    ClarityTracking.upgrade('payment_attempted');

    try {
      if (isPostServiceMode) {
        // POST_SERVICE MODE: Save card without charging
        // Step 1: Create Setup Intent from backend
        const setupIntentData = await createSetupIntent();

        // Step 2: Confirm the Setup Intent with Stripe (validates and saves card)
        const cardNumber = elements.getElement(CardNumberElement);
        const { error: stripeError, setupIntent } = await stripe.confirmCardSetup(
          setupIntentData.client_secret,
          {
            payment_method: {
              card: cardNumber,
              billing_details: {
                address: {
                  postal_code: postalCode,
                },
              },
            },
          }
        );

        if (stripeError) {
          setLocalError(stripeError.message);
          ClarityTracking.trackPayment('failed', { error: stripeError.message, mode: 'setup_intent' });
          setProcessing(false);
          return;
        }

        // Step 3: Complete the setup and save card to booking
        await completeSetupIntent(setupIntent.id);

        // Track successful card save
        ClarityTracking.trackPayment('card_saved', {
          amount: totalAmount,
          setupIntentId: setupIntent.id
        });

      } else {
        // IMMEDIATE MODE: Charge now (existing behavior)
        const cardNumber = elements.getElement(CardNumberElement);
        const { error: stripeError, paymentMethod } = await stripe.createPaymentMethod({
          type: 'card',
          card: cardNumber,
          billing_details: {
            address: {
              postal_code: postalCode,
            },
          },
        });

        if (stripeError) {
          setLocalError(stripeError.message);
          ClarityTracking.trackPayment('failed', { error: stripeError.message });
          setProcessing(false);
          return;
        }

        // Process payment for existing booking
        await processBookingPayment(paymentMethod.id);

        // Track successful payment
        ClarityTracking.trackPayment('succeeded', {
          amount: totalAmount,
          paymentMethodId: paymentMethod.id
        });
      }

      // Move to confirmation step
      nextStep();

    } catch (error) {
      setLocalError(error.message || (isPostServiceMode ? 'Failed to save card. Please try again.' : 'Payment failed. Please try again.'));
      ClarityTracking.trackPayment('failed', { error: error.message });
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
        fontSize: '14px',
        fontWeight: '300',
        color: '#1A1A1A',
        letterSpacing: '0.025em',
        '::placeholder': {
          color: '#9CA3AF',
        },
        iconColor: '#C9A961',
        lineHeight: '40px',
        padding: '12px',
      },
      invalid: {
        color: '#DC2626',
        iconColor: '#DC2626',
      },
    },
  };

  return (
    <div className="max-w-4xl mx-auto">
      {/* Header */}
      <div className="text-center mb-8 sm:mb-12">
        <h2 className="font-display text-2xl sm:text-3xl text-luxury-black mb-4">
          {isPostServiceMode ? 'Secure Your Booking' : 'Complete Your Payment'}
        </h2>
        {booking?.booking_number && (
          <p className="text-luxury-gold text-sm font-semibold mb-2">
            Booking #{booking.booking_number}
          </p>
        )}
        <p className="text-luxury-gray/60 text-sm tracking-wide">
          {isPostServiceMode
            ? 'Enter your card details to reserve your ride'
            : 'Enter your payment details to confirm your booking'}
        </p>
      </div>

      {/* Post-Service Mode Info Banner */}
      {isPostServiceMode && (
        <div className="mb-6 sm:mb-8 p-5 sm:p-6 bg-gradient-to-br from-amber-50/80 via-yellow-50/50 to-orange-50/30 border border-luxury-gold/20 shadow-luxury rounded-sm">
          <div className="flex items-start gap-4">
            <div className="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-br from-luxury-gold/20 to-luxury-gold/5 flex items-center justify-center flex-shrink-0 border border-luxury-gold/20">
              <svg className="w-6 h-6 sm:w-7 sm:h-7 text-luxury-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
              </svg>
            </div>
            <div className="flex-1">
              <h3 className="font-bold text-luxury-black tracking-wide text-sm sm:text-base mb-2">Your card will not be charged now</h3>
              <p className="text-sm text-luxury-gray/80 leading-relaxed mb-3">
                We securely save your payment method and only charge after your ride is completed.
                Estimated fare: <span className="font-semibold text-luxury-gold">{formatPrice(baseFare)}</span>
              </p>
              <p className="text-xs text-luxury-gray/60 leading-relaxed">
                By proceeding, you authorize us to charge your card after service is provided.
                {hasAnyLegalLinks && (
                  <>
                    {' '}View our{' '}
                    {hasTerms && (
                      <a
                        href={termsUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                      >
                        terms
                      </a>
                    )}
                    {hasTerms && hasCancellationPolicy && ', '}
                    {hasCancellationPolicy && (
                      <a
                        href={cancellationPolicyUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                      >
                        cancellation policy
                      </a>
                    )}
                    {(hasTerms || hasCancellationPolicy) && hasPrivacyPolicy && ', '}
                    {hasPrivacyPolicy && (
                      <a
                        href={privacyPolicyUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                      >
                        privacy policy
                      </a>
                    )}
                    {(hasTerms || hasCancellationPolicy || hasPrivacyPolicy) && hasRefundPolicy && ', '}
                    {hasRefundPolicy && (
                      <a
                        href={refundPolicyUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                      >
                        refund policy
                      </a>
                    )}
                    .
                  </>
                )}
              </p>
            </div>
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        {/* Left Column - Payment Details */}
        <div className="lg:col-span-2">
          {/* Fare Summary with Gratuity */}
          <div className="bg-luxury-white shadow-luxury p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
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

              {/* Extras Line Items */}
              {Object.values(selectedExtras || {}).map((extra) => (
                <div key={extra.id} className="flex justify-between items-center">
                  <span className="text-luxury-gray/60 text-sm">
                    {extra.name} {extra.quantity > 1 ? `x${extra.quantity}` : ''}
                  </span>
                  <span className="text-sm font-medium text-luxury-black">
                    {formatPrice(extra.price * extra.quantity)}
                  </span>
                </div>
              ))}

              {/* Gratuity Section */}
              <div className="pt-4 border-t border-luxury-gray/10">
                <label className="block text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-4">
                  Add Gratuity (Optional)
                </label>
                <p className="text-xs text-luxury-gray/60 mb-4">
                  You can also add a tip after your trip
                </p>
                
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                  {gratuityOptions.map(option => (
                    <button
                      key={option.percentage}
                      type="button"
                      onClick={() => selectGratuity(option.percentage)}
                      className={`py-2 sm:py-3 px-1 sm:px-2 rounded border-2 transition-all text-center ${
                        gratuityPercentage === option.percentage && gratuityPercentage !== 'custom'
                          ? 'border-luxury-gold bg-luxury-light-gray'
                          : 'border-luxury-gray/20 hover:border-luxury-gray/40'
                      }`}
                    >
                      <div className="text-xs sm:text-sm font-medium">{option.label}</div>
                      <div className="text-[10px] sm:text-xs text-luxury-gray/60 truncate">
                        {formatPrice(calculateTip(option.percentage))}
                      </div>
                    </button>
                  ))}
                </div>

                <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                  <label className="text-xs sm:text-sm text-luxury-gray/60 whitespace-nowrap">Custom amount:</label>
                  <div className="relative flex-1">
                    <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-luxury-gray/60">$</span>
                    <input
                      type="number"
                      value={customTip}
                      onChange={(e) => handleCustomTip(e.target.value)}
                      placeholder="0.00"
                      min="0"
                      step="0.01"
                      className={`w-full pl-8 pr-3 py-2 border-2 rounded transition-colors text-sm ${
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
                <span className="text-luxury-black font-semibold text-sm sm:text-base">Total Amount</span>
                <span className="font-display text-xl sm:text-2xl text-luxury-black">
                  {formatPrice(totalAmount)}
                </span>
              </div>
            </div>
          </div>

          {/* Card Form */}
          <div className="bg-luxury-white shadow-luxury p-4 sm:p-6 lg:p-8">
            <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
              Card Details
            </h3>
            
            <form onSubmit={handleSubmit}>
              <div className="space-y-4">
                {/* Card Number */}
                <div>
                  <label className="block text-xs text-luxury-gray/60 mb-2">Card Number</label>
                  <div className="p-3 sm:p-4 border-2 border-luxury-gray/20 focus-within:border-luxury-gold transition-colors duration-300 rounded">
                    <CardNumberElement options={cardElementOptions} />
                  </div>
                </div>

                {/* Expiry and CVC in a row on desktop, stacked on mobile */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs text-luxury-gray/60 mb-2">Expiry Date</label>
                    <div className="p-3 sm:p-4 border-2 border-luxury-gray/20 focus-within:border-luxury-gold transition-colors duration-300 rounded">
                      <CardExpiryElement options={cardElementOptions} />
                    </div>
                  </div>
                  <div>
                    <label className="block text-xs text-luxury-gray/60 mb-2">Security Code</label>
                    <div className="p-3 sm:p-4 border-2 border-luxury-gray/20 focus-within:border-luxury-gold transition-colors duration-300 rounded">
                      <CardCvcElement options={cardElementOptions} />
                    </div>
                  </div>
                </div>

                {/* Postal Code */}
                <div>
                  <label className="block text-xs text-luxury-gray/60 mb-2">Postal Code</label>
                  <input
                    type="text"
                    value={postalCode}
                    onChange={(e) => setPostalCode(e.target.value)}
                    placeholder="12345"
                    required
                    className="w-full p-3 sm:p-4 border-2 border-luxury-gray/20 focus:border-luxury-gold transition-colors duration-300 rounded text-sm text-luxury-black placeholder-luxury-gray/50 focus:outline-none"
                  />
                </div>
              </div>

              {/* Save Card Option - Only show for immediate payment mode (post-service automatically saves card) */}
              {!isPostServiceMode && (
                <div className="mt-6 flex items-start">
                  <input
                    type="checkbox"
                    id="save-card"
                    checked={savePaymentMethod}
                    onChange={(e) => {
                      setSavePaymentMethod(e.target.checked);
                      ClarityTracking.trackPayment('save_card_toggled', {
                        saveCard: e.target.checked
                      });
                    }}
                    className="mt-1 h-4 w-4 text-luxury-gold focus:ring-luxury-gold border-luxury-gray/30 rounded"
                  />
                  <label htmlFor="save-card" className="ml-3 text-sm">
                    <span className="font-medium text-luxury-black">Save payment method for future use</span>
                    <p className="text-xs text-luxury-gray/60 mt-1">
                      Save your card for faster future bookings and easy post-trip tipping
                    </p>
                    <p className="text-xs text-luxury-gray/50 mt-1">
                      🔒 Secured by Stripe
                    </p>
                  </label>
                </div>
              )}

              {/* Payment Notice - Only show for immediate payment mode */}
              {!isPostServiceMode && (
                <div className="mt-6 p-6 bg-luxury-light-gray border-l-4 border-luxury-gold">
                  <h4 className="text-xs font-semibold text-luxury-black uppercase tracking-luxury mb-3">
                    Immediate Payment
                  </h4>
                  <p className="text-xs text-luxury-gray/70 leading-relaxed">
                    Your card will be charged {formatPrice(totalAmount)} immediately upon booking confirmation.
                    {gratuityAmount > 0 && ` This includes a ${formatPrice(gratuityAmount)} gratuity.`}
                    {hasAnyLegalLinks ? (
                      <>
                        {' '}By proceeding, you agree to our{' '}
                        {hasTerms && (
                          <a
                            href={termsUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                          >
                            terms
                          </a>
                        )}
                        {hasTerms && hasCancellationPolicy && ', '}
                        {hasCancellationPolicy && (
                          <a
                            href={cancellationPolicyUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                          >
                            cancellation policy
                          </a>
                        )}
                        {(hasTerms || hasCancellationPolicy) && hasPrivacyPolicy && ', '}
                        {hasPrivacyPolicy && (
                          <a
                            href={privacyPolicyUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                          >
                            privacy policy
                          </a>
                        )}
                        {(hasTerms || hasCancellationPolicy || hasPrivacyPolicy) && hasRefundPolicy && ', '}
                        {hasRefundPolicy && (
                          <a
                            href={refundPolicyUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-luxury-gold hover:text-luxury-gold/80 underline underline-offset-2 font-medium transition-colors"
                          >
                            refund policy
                          </a>
                        )}
                        .
                      </>
                    ) : (
                      ' The charge is final and will be processed now.'
                    )}
                  </p>
                </div>
              )}

              {/* Error Message */}
              {(localError || error) && (
                <div className="mt-6 bg-red-50 border-l-4 border-red-500 p-4 animate-fade-in">
                  <p className="text-sm text-red-700">{localError || error}</p>
                </div>
              )}

              {/* Action Buttons */}
              <div className="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 mt-6 sm:mt-8">
                <button
                  type="button"
                  onClick={prevStep}
                  disabled={processing || loading}
                  className="w-full sm:flex-1 px-4 py-3 border-2 border-luxury-black text-luxury-black font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-black hover:text-luxury-white hover:shadow-luxury active:scale-[0.98] uppercase text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed order-2 sm:order-1"
                >
                  Back
                </button>
                <button
                  type="submit"
                  disabled={!stripe || processing || loading}
                  className="w-full sm:flex-1 px-4 py-3 bg-luxury-gold text-luxury-white font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-gold-dark hover:shadow-luxury active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed uppercase text-xs sm:text-sm order-1 sm:order-2"
                >
                  {processing || loading ? (
                    <span className="flex items-center justify-center gap-1">
                      <svg className="animate-spin h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                      </svg>
                      <span className="hidden sm:inline">{isPostServiceMode ? 'Confirming...' : 'Processing...'}</span>
                      <span className="sm:hidden">{isPostServiceMode ? 'Confirming...' : 'Processing'}</span>
                    </span>
                  ) : isPostServiceMode ? (
                    <span className="flex items-center justify-center gap-1">
                      <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                      </svg>
                      <span>Confirm Booking</span>
                    </span>
                  ) : (
                    <span className="flex items-center justify-center gap-1">
                      <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                      <span>Pay {formatPrice(totalAmount)}</span>
                    </span>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>

        {/* Right Column - Security & Summary */}
        <div className="space-y-6 sm:space-y-8 mt-6 lg:mt-0">
          {/* Booking Summary */}
          <div className="bg-luxury-white shadow-luxury p-4 sm:p-6 lg:p-8">
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
              {Object.values(selectedExtras || {}).map((extra) => (
                <div key={extra.id}>
                  <p className="text-luxury-gray/60 text-xs">{extra.name} {extra.quantity > 1 ? `x${extra.quantity}` : ''}</p>
                  <p className="text-luxury-black font-medium">{formatPrice(extra.price * extra.quantity)}</p>
                </div>
              ))}
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
          <div className="bg-luxury-white shadow-luxury p-4 sm:p-6 lg:p-8">
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
            <a href={`tel:${settings.support_phone}`} className="text-luxury-gold hover:text-luxury-gold-dark text-sm font-medium">
              {settings.support_phone}
            </a>
          </div>
        </div>
      </div>
    </div>
  );
};

const PaymentLuxury = () => {
  const { settings, loading: settingsLoading } = useSettings();
  
  // Get Stripe public key from settings or fallback to env variable
  const stripePublicKey = useMemo(() => {
    if (settings?.stripe?.public_key) {
      return settings.stripe.public_key;
    }
    // Fallback to environment variable if settings not loaded or key not available
    return import.meta.env.VITE_STRIPE_PUBLIC_KEY;
  }, [settings]);
  
  const stripePromise = useMemo(() => {
    if (stripePublicKey) {
      return loadStripe(stripePublicKey);
    }
    return null;
  }, [stripePublicKey]);
  
  // Show loading while settings are being fetched
  if (settingsLoading || !stripePromise) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="text-center">
          <svg className="animate-spin h-8 w-8 mx-auto text-luxury-gold" viewBox="0 0 24 24" fill="none">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
          <p className="mt-4 text-luxury-gray/60">Loading payment system...</p>
        </div>
      </div>
    );
  }
  
  return (
    <Elements stripe={stripePromise}>
      <PaymentForm />
    </Elements>
  );
};

export default PaymentLuxury;