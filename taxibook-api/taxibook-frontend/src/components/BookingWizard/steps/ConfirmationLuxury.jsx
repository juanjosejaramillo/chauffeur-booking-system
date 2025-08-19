import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import useBookingStore from '../../../store/bookingStore';
import useSettings from '../../../hooks/useSettings';

const ConfirmationLuxury = () => {
  const navigate = useNavigate();
  const { settings } = useSettings();
  const { booking, selectedVehicle, tripDetails, customerInfo, gratuityAmount, resetBooking } = useBookingStore();

  const handleNewBooking = () => {
    resetBooking();
    navigate('/');
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price);
  };

  const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const formatTime = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  };

  return (
    <div className="max-w-3xl mx-auto">
      {/* Success Icon */}
      <div className="text-center mb-8">
        <div className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
          <svg className="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 className="font-display text-3xl text-luxury-black mb-2">
          Booking Confirmed!
        </h2>
        <p className="text-luxury-gray/60 text-sm">
          Your reservation has been successfully processed
        </p>
      </div>

      {/* Booking Reference */}
      <div className="bg-luxury-gold/10 border-2 border-luxury-gold/30 p-6 mb-8 text-center">
        <p className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-2">
          Booking Reference
        </p>
        <p className="font-mono text-2xl text-luxury-black tracking-wider">
          {booking?.booking_number}
        </p>
        <p className="text-xs text-luxury-gray/60 mt-2">
          Please keep this reference for your records
        </p>
      </div>

      {/* Booking Details */}
      <div className="bg-luxury-white shadow-luxury p-8 mb-8">
        <h3 className="text-xs font-semibold text-luxury-gold uppercase tracking-luxury mb-6">
          Trip Details
        </h3>
        
        <div className="space-y-4">
          {/* Pickup */}
          <div className="flex items-start gap-4">
            <div className="flex-shrink-0 w-8 h-8 bg-luxury-gold/20 rounded-full flex items-center justify-center">
              <svg className="w-4 h-4 text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
              </svg>
            </div>
            <div className="flex-1">
              <p className="text-xs text-luxury-gray/60 uppercase tracking-wide mb-1">Pickup</p>
              <p className="text-sm text-luxury-black font-medium">{tripDetails.pickupAddress}</p>
              <p className="text-sm text-luxury-gray/60 mt-1">
                {formatDate(tripDetails.pickupDate)} at {formatTime(`${tripDetails.pickupDate} ${tripDetails.pickupTime}`)}
              </p>
            </div>
          </div>

          {/* Dropoff */}
          <div className="flex items-start gap-4">
            <div className="flex-shrink-0 w-8 h-8 bg-luxury-gold/20 rounded-full flex items-center justify-center">
              <svg className="w-4 h-4 text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
              </svg>
            </div>
            <div className="flex-1">
              <p className="text-xs text-luxury-gray/60 uppercase tracking-wide mb-1">Dropoff</p>
              <p className="text-sm text-luxury-black font-medium">{tripDetails.dropoffAddress}</p>
            </div>
          </div>

          {/* Vehicle */}
          <div className="flex items-start gap-4">
            <div className="flex-shrink-0 w-8 h-8 bg-luxury-gold/20 rounded-full flex items-center justify-center">
              <svg className="w-4 h-4 text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7h4.05C18.574 7 19 7.426 19 7.95V11h-5V7z" />
              </svg>
            </div>
            <div className="flex-1">
              <p className="text-xs text-luxury-gray/60 uppercase tracking-wide mb-1">Vehicle</p>
              <p className="text-sm text-luxury-black font-medium">{selectedVehicle?.name}</p>
              <p className="text-sm text-luxury-gray/60">{selectedVehicle?.description}</p>
            </div>
          </div>

          {/* Passenger */}
          <div className="flex items-start gap-4">
            <div className="flex-shrink-0 w-8 h-8 bg-luxury-gold/20 rounded-full flex items-center justify-center">
              <svg className="w-4 h-4 text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
              </svg>
            </div>
            <div className="flex-1">
              <p className="text-xs text-luxury-gray/60 uppercase tracking-wide mb-1">Passenger</p>
              <p className="text-sm text-luxury-black font-medium">
                {customerInfo.firstName} {customerInfo.lastName}
              </p>
              <p className="text-sm text-luxury-gray/60">{customerInfo.email}</p>
              <p className="text-sm text-luxury-gray/60">{customerInfo.phone}</p>
            </div>
          </div>

          {/* Payment Summary */}
          <div className="flex items-start gap-4 pt-4 border-t border-luxury-gray/10">
            <div className="flex-shrink-0 w-8 h-8 bg-luxury-gold/20 rounded-full flex items-center justify-center">
              <svg className="w-4 h-4 text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                <path fillRule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clipRule="evenodd" />
              </svg>
            </div>
            <div className="flex-1">
              <p className="text-xs text-luxury-gray/60 uppercase tracking-wide mb-1">Payment Summary</p>
              <div className="space-y-1">
                <div className="flex justify-between items-center">
                  <span className="text-sm text-luxury-gray/60">Trip Fare:</span>
                  <span className="text-sm font-medium text-luxury-black">
                    {formatPrice(selectedVehicle?.estimated_fare || selectedVehicle?.total_price)}
                  </span>
                </div>
                {gratuityAmount > 0 && (
                  <div className="flex justify-between items-center">
                    <span className="text-sm text-luxury-gray/60">Gratuity:</span>
                    <span className="text-sm font-medium text-luxury-black">
                      {formatPrice(gratuityAmount)}
                    </span>
                  </div>
                )}
                <div className="flex justify-between items-center pt-2 border-t border-luxury-gray/10">
                  <span className="text-sm font-semibold text-luxury-black">Total Paid:</span>
                  <span className="text-xl font-display text-luxury-black">
                    {formatPrice((selectedVehicle?.estimated_fare || selectedVehicle?.total_price) + gratuityAmount)}
                  </span>
                </div>
              </div>
              <p className="text-xs text-luxury-gray/60 mt-2">
                Payment has been processed successfully
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* What's Next */}
      <div className="bg-luxury-light-gray p-6 mb-8">
        <h3 className="text-xs font-semibold text-luxury-black uppercase tracking-luxury mb-4">
          What's Next?
        </h3>
        <ul className="space-y-3 text-sm text-luxury-gray/70">
          <li className="flex items-start gap-2">
            <span className="text-luxury-gold mt-0.5">•</span>
            <span>A confirmation email has been sent to {customerInfo.email}</span>
          </li>
          <li className="flex items-start gap-2">
            <span className="text-luxury-gold mt-0.5">•</span>
            <span>Your driver will arrive at the pickup location at the scheduled time</span>
          </li>
          <li className="flex items-start gap-2">
            <span className="text-luxury-gold mt-0.5">•</span>
            <span>The final charge will be processed after your trip is completed</span>
          </li>
        </ul>
      </div>

      {/* Actions */}
      <div className="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">
        <button
          onClick={() => window.print()}
          className="w-full sm:flex-1 px-4 py-3 border-2 border-luxury-black text-luxury-black font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-black hover:text-luxury-white hover:shadow-luxury active:scale-[0.98] uppercase text-xs sm:text-sm order-2 sm:order-1"
        >
          Print Confirmation
        </button>
        <button
          onClick={handleNewBooking}
          className="w-full sm:flex-1 px-4 py-3 bg-luxury-gold text-luxury-white font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-gold-dark hover:shadow-luxury active:scale-[0.98] uppercase text-xs sm:text-sm order-1 sm:order-2"
        >
          Make Another Booking
        </button>
      </div>

      {/* Support */}
      <div className="text-center mt-8 pt-8 border-t border-luxury-gray/10">
        <p className="text-xs text-luxury-gray/60 mb-2">
          Need assistance with your booking?
        </p>
        <a href={`tel:${settings.support_phone}`} className="text-luxury-gold hover:text-luxury-gold-dark text-sm font-medium">
          {settings.support_phone}
        </a>
        <p className="text-[10px] text-luxury-gray/40 mt-4">
          Press back twice quickly to start a new booking
        </p>
      </div>
    </div>
  );
};

export default ConfirmationLuxury;