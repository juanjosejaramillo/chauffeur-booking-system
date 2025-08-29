import { useState } from 'react';
import useBookingStore from '../../../store/bookingStore';
import useSettings from '../../../hooks/useSettings';
import { ClarityTracking } from '../../../services/clarityTracking';

const ReviewBookingLuxury = () => {
  const {
    tripDetails,
    selectedVehicle,
    customerInfo,
    routeInfo,
    createBooking,
    nextStep,
    prevStep,
    loading,
    error,
  } = useBookingStore();

  const { settings } = useSettings();
  const [agreed, setAgreed] = useState(false);
  const [localError, setLocalError] = useState('');
  
  // Get legal URLs from settings with fallbacks
  const termsUrl = settings?.legal?.terms_url || 'https://luxridesuv.com/terms';
  const cancellationPolicyUrl = settings?.legal?.cancellation_policy_url || 'https://luxridesuv.com/cancellation-policy';

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!agreed) {
      setLocalError('Please agree to the terms and conditions');
      ClarityTracking.trackError('review_booking', 'validation', 'Terms not agreed');
      return;
    }

    try {
      // Track booking creation attempt
      ClarityTracking.event('booking_creation_attempted');
      
      // Create booking without payment
      await createBooking();
      
      // Track successful booking creation
      ClarityTracking.event('booking_creation_success');
      
      // Move to payment step
      nextStep();
    } catch (error) {
      // Track booking creation failure
      ClarityTracking.trackError('review_booking', 'booking_creation', error.message || 'Unknown error');
    }
  };

  const formatDateTime = (date, time) => {
    const dt = new Date(`${date}T${time}`);
    return dt.toLocaleString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price);
  };

  return (
    <div className="max-w-4xl mx-auto">
      {/* Header */}
      <div className="text-center mb-12">
        <h2 className="font-display text-3xl text-luxury-black mb-4">
          Review Your Booking
        </h2>
        <p className="text-luxury-gray/60 text-sm tracking-wide">
          Please review your booking details before proceeding to payment
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column - Trip & Passenger Details */}
        <div className="lg:col-span-2 space-y-8">
          {/* Trip Details */}
          <div className="bg-luxury-white shadow-luxury p-8">
            <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
              Journey Details
            </h3>
            
            <div className="space-y-6">
              <div className="flex items-start gap-4">
                <div className="w-8 h-8 rounded-full bg-luxury-gold/10 flex items-center justify-center flex-shrink-0">
                  <svg className="w-4 h-4 text-luxury-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div className="flex-1">
                  <p className="text-xs text-luxury-gray/50 uppercase tracking-wide mb-1">Pickup</p>
                  <p className="text-luxury-black font-medium">{tripDetails.pickupAddress}</p>
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="w-8 h-8 rounded-full bg-luxury-black/10 flex items-center justify-center flex-shrink-0">
                  <svg className="w-4 h-4 text-luxury-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div className="flex-1">
                  <p className="text-xs text-luxury-gray/50 uppercase tracking-wide mb-1">Destination</p>
                  <p className="text-luxury-black font-medium">{tripDetails.dropoffAddress}</p>
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="w-8 h-8 rounded-full bg-luxury-light-gray flex items-center justify-center flex-shrink-0">
                  <svg className="w-4 h-4 text-luxury-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div className="flex-1">
                  <p className="text-xs text-luxury-gray/50 uppercase tracking-wide mb-1">Date & Time</p>
                  <p className="text-luxury-black font-medium">
                    {formatDateTime(tripDetails.pickupDate, tripDetails.pickupTime)}
                  </p>
                </div>
              </div>

              <div className="flex items-start gap-4">
                <div className="w-8 h-8 rounded-full bg-luxury-light-gray flex items-center justify-center flex-shrink-0">
                  <svg className="w-4 h-4 text-luxury-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div className="flex-1">
                  <p className="text-xs text-luxury-gray/50 uppercase tracking-wide mb-1">Estimated Duration</p>
                  <p className="text-luxury-black font-medium">
                    {Math.round(routeInfo.duration / 60)} minutes ({routeInfo.distance.toFixed(1)} miles)
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Passenger Details */}
          <div className="bg-luxury-white shadow-luxury p-8">
            <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
              Passenger Details
            </h3>
            
            <div className="space-y-4">
              <div className="flex items-center gap-3">
                <svg className="w-4 h-4 text-luxury-gray/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span className="text-luxury-black">{customerInfo.firstName} {customerInfo.lastName}</span>
              </div>
              
              <div className="flex items-center gap-3">
                <svg className="w-4 h-4 text-luxury-gray/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span className="text-luxury-black">{customerInfo.email}</span>
              </div>
              
              <div className="flex items-center gap-3">
                <svg className="w-4 h-4 text-luxury-gray/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span className="text-luxury-black">{customerInfo.phone}</span>
              </div>

              {customerInfo.specialInstructions && (
                <div className="mt-4 pt-4 border-t border-luxury-gray/10">
                  <p className="text-xs text-luxury-gray/50 uppercase tracking-wide mb-2">Special Requests</p>
                  <p className="text-luxury-black text-sm">{customerInfo.specialInstructions}</p>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Right Column - Vehicle & Pricing */}
        <div className="space-y-8">
          {/* Vehicle Details */}
          <div className="bg-luxury-white shadow-luxury p-8">
            <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
              Vehicle
            </h3>
            
            <div className="space-y-4">
              <div>
                <p className="font-display text-xl text-luxury-black mb-2">
                  {selectedVehicle.display_name || selectedVehicle.name}
                </p>
                <p className="text-sm text-luxury-gray/60">
                  {selectedVehicle.max_passengers} passengers, {selectedVehicle.max_luggage} bags
                </p>
              </div>
              
              {selectedVehicle.features && selectedVehicle.features.length > 0 && (
                <div className="flex flex-wrap gap-2 pt-2">
                  {selectedVehicle.features.map((feature, idx) => (
                    <span key={idx} className="px-2 py-1 bg-luxury-light-gray text-xs text-luxury-gray/70">
                      {feature}
                    </span>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Total Amount */}
          <div className="bg-luxury-black text-luxury-white p-8">
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <span className="text-xs uppercase tracking-luxury">Total Amount</span>
                <span className="font-display text-3xl">
                  {formatPrice(selectedVehicle.estimated_fare || selectedVehicle.total_price)}
                </span>
              </div>
            </div>
          </div>

          {/* Terms Agreement */}
          <div className="bg-luxury-light-gray p-6">
            <label className="flex items-start gap-3 cursor-pointer">
              <input
                type="checkbox"
                checked={agreed}
                onChange={(e) => {
                  setAgreed(e.target.checked);
                  ClarityTracking.event(`terms_agreement_${e.target.checked ? 'checked' : 'unchecked'}`);
                }}
                className="mt-1 w-4 h-4 accent-luxury-gold"
              />
              <span className="text-xs text-luxury-gray/70 leading-relaxed">
                I agree to the{' '}
                <a 
                  href={termsUrl} 
                  target="_blank" 
                  rel="noopener noreferrer"
                  onClick={() => ClarityTracking.trackLegalDocument('terms', 'clicked')}
                  className="text-luxury-gold hover:text-luxury-gold-dark underline"
                >
                  terms and conditions
                </a>{' '}
                and{' '}
                <a 
                  href={cancellationPolicyUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  onClick={() => ClarityTracking.trackLegalDocument('cancellation_policy', 'clicked')}
                  className="text-luxury-gold hover:text-luxury-gold-dark underline"
                >
                  cancellation policy
                </a>
              </span>
            </label>
          </div>
        </div>
      </div>

      {/* Error Message */}
      {(localError || error) && (
        <div className="mt-8 bg-red-50 border-l-4 border-red-500 p-4 animate-fade-in">
          <p className="text-sm text-red-700">{localError || error}</p>
        </div>
      )}

      {/* Action Buttons */}
      <div className="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 mt-8 sm:mt-12">
        <button
          type="button"
          onClick={prevStep}
          className="w-full sm:flex-1 px-4 py-3 border-2 border-luxury-black text-luxury-black font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-black hover:text-luxury-white hover:shadow-luxury active:scale-[0.98] uppercase text-xs sm:text-sm order-2 sm:order-1"
        >
          Back
        </button>
        <button
          type="button"
          onClick={handleSubmit}
          disabled={loading || !agreed}
          className="w-full sm:flex-1 px-4 py-3 bg-luxury-gold text-luxury-white font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-gold-dark hover:shadow-luxury active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 uppercase text-xs sm:text-sm order-1 sm:order-2"
        >
          {loading ? (
            'Processing...'
          ) : (
            <>
              <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span className="truncate">Proceed to Payment</span>
            </>
          )}
        </button>
      </div>
    </div>
  );
};

export default ReviewBookingLuxury;