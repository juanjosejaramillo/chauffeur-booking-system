import { useState } from 'react';
import { CalendarIcon, ClockIcon, MapPinIcon, UserIcon, CreditCardIcon } from '@heroicons/react/24/outline';
import useBookingStore from '../../../store/bookingStore';

const ReviewBooking = () => {
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
  
  const [agreed, setAgreed] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!agreed) {
      alert('Please agree to the terms and conditions');
      return;
    }
    
    try {
      await createBooking();
      nextStep();
    } catch (error) {
      // Error handled in store
    }
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(price);
  };

  const formatDateTime = () => {
    const date = new Date(`${tripDetails.pickupDate} ${tripDetails.pickupTime}`);
    return date.toLocaleString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold text-gray-900 mb-2">
          Review Your Booking
        </h2>
        <p className="text-sm text-gray-600">
          Please review your booking details before proceeding to payment
        </p>
      </div>

      <div className="bg-gray-50 rounded-lg p-6 space-y-6">
        {/* Trip Details */}
        <div>
          <h3 className="font-semibold text-gray-900 mb-3">Trip Details</h3>
          <div className="space-y-3">
            <div className="flex items-start">
              <MapPinIcon className="h-5 w-5 text-green-600 mt-0.5 mr-3" />
              <div>
                <p className="text-sm font-medium text-gray-700">Pickup</p>
                <p className="text-sm text-gray-600">{tripDetails.pickupAddress}</p>
              </div>
            </div>
            
            <div className="flex items-start">
              <MapPinIcon className="h-5 w-5 text-red-600 mt-0.5 mr-3" />
              <div>
                <p className="text-sm font-medium text-gray-700">Dropoff</p>
                <p className="text-sm text-gray-600">{tripDetails.dropoffAddress}</p>
              </div>
            </div>
            
            <div className="flex items-start">
              <CalendarIcon className="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
              <div>
                <p className="text-sm font-medium text-gray-700">Date & Time</p>
                <p className="text-sm text-gray-600">{formatDateTime()}</p>
              </div>
            </div>
            
            {routeInfo && (
              <div className="flex items-start">
                <ClockIcon className="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
                <div>
                  <p className="text-sm font-medium text-gray-700">Estimated Duration</p>
                  <p className="text-sm text-gray-600">
                    {routeInfo.duration_minutes} minutes ({routeInfo.distance} miles)
                  </p>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Vehicle Details */}
        <div className="border-t pt-6">
          <h3 className="font-semibold text-gray-900 mb-3">Vehicle</h3>
          <div className="flex items-start justify-between">
            <div>
              <p className="text-sm font-medium text-gray-700">
                {selectedVehicle.display_name}
              </p>
              <p className="text-xs text-gray-600">
                {selectedVehicle.max_passengers} passengers, {selectedVehicle.max_luggage} bags
              </p>
            </div>
            <p className="text-lg font-semibold text-gray-900">
              {formatPrice(selectedVehicle.estimated_fare)}
            </p>
          </div>
        </div>

        {/* Customer Details */}
        <div className="border-t pt-6">
          <h3 className="font-semibold text-gray-900 mb-3">Passenger Details</h3>
          <div className="space-y-2">
            <div className="flex items-center">
              <UserIcon className="h-5 w-5 text-gray-400 mr-3" />
              <p className="text-sm text-gray-600">
                {customerInfo.firstName} {customerInfo.lastName}
              </p>
            </div>
            <div className="flex items-center">
              <span className="h-5 w-5 text-gray-400 mr-3 text-center">📧</span>
              <p className="text-sm text-gray-600">{customerInfo.email}</p>
            </div>
            <div className="flex items-center">
              <span className="h-5 w-5 text-gray-400 mr-3 text-center">📱</span>
              <p className="text-sm text-gray-600">{customerInfo.phone}</p>
            </div>
            {customerInfo.specialInstructions && (
              <div className="mt-3">
                <p className="text-sm font-medium text-gray-700">Special Instructions:</p>
                <p className="text-sm text-gray-600 mt-1">
                  {customerInfo.specialInstructions}
                </p>
              </div>
            )}
          </div>
        </div>

        {/* Total */}
        <div className="border-t pt-6">
          <div className="flex items-center justify-between">
            <p className="text-lg font-semibold text-gray-900">Total Amount</p>
            <p className="text-2xl font-bold text-indigo-600">
              {formatPrice(selectedVehicle.estimated_fare)}
            </p>
          </div>
          <p className="text-xs text-gray-500 mt-1 text-right">
            * Final fare may vary based on actual route taken
          </p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="flex items-start">
          <input
            type="checkbox"
            id="terms"
            checked={agreed}
            onChange={(e) => setAgreed(e.target.checked)}
            className="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
          />
          <label htmlFor="terms" className="ml-2 text-sm text-gray-600">
            I agree to the{' '}
            <a href="#" className="text-indigo-600 hover:text-indigo-700">
              terms and conditions
            </a>{' '}
            and{' '}
            <a href="#" className="text-indigo-600 hover:text-indigo-700">
              cancellation policy
            </a>
          </label>
        </div>

        {error && (
          <div className="p-3 bg-red-50 border border-red-200 rounded-md">
            <p className="text-sm text-red-600">{error}</p>
          </div>
        )}

        <div className="flex space-x-4">
          <button
            type="button"
            onClick={prevStep}
            disabled={loading}
            className="flex-1 bg-gray-200 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-300 disabled:bg-gray-100 transition-colors"
          >
            Back
          </button>
          <button
            type="submit"
            disabled={loading || !agreed}
            className="flex-1 bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
          >
            {loading ? (
              <>
                <span className="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                Creating Booking...
              </>
            ) : (
              <>
                <CreditCardIcon className="h-5 w-5 mr-2" />
                Proceed to Payment
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

export default ReviewBooking;