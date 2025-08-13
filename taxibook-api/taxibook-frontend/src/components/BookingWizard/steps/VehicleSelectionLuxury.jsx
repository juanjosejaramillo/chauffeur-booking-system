import { useState, useEffect } from 'react';
import useBookingStore from '../../../store/bookingStore';

const VehicleSelectionLuxury = () => {
  const {
    availableVehicles,
    selectedVehicle,
    setSelectedVehicle,
    routeInfo,
    calculatePrices,
    nextStep,
    prevStep,
    loading,
    error,
  } = useBookingStore();

  const [localError, setLocalError] = useState('');
  const [showFareBreakdown, setShowFareBreakdown] = useState(null);

  useEffect(() => {
    if (availableVehicles.length === 0 && !loading) {
      calculatePrices();
    }
  }, [availableVehicles, calculatePrices, loading]);

  const handleSelectVehicle = (vehicle) => {
    setSelectedVehicle(vehicle);
  };

  const handleContinue = () => {
    if (!selectedVehicle) {
      setLocalError('Please select a vehicle to continue');
      return;
    }
    nextStep();
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(price);
  };

  const getVehicleIcon = (category) => {
    switch(category) {
      case 'luxury':
        return (
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
      case 'suv':
        return (
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        );
      default:
        return (
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        );
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-luxury-gold mx-auto mb-4"></div>
          <p className="text-luxury-gray/60">Calculating your fare...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto">
      {/* Header */}
      <div className="text-center mb-12">
        <h2 className="font-display text-3xl text-luxury-black mb-4">
          Select Your Vehicle
        </h2>
        {routeInfo && (
          <div className="flex items-center justify-center gap-8 text-sm text-luxury-gray/70">
            <span className="flex items-center gap-2">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {routeInfo.distance.toFixed(1)} miles
            </span>
            <span className="flex items-center gap-2">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {Math.round(routeInfo.duration / 60)} minutes
            </span>
          </div>
        )}
      </div>

      {/* Vehicle Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
        {availableVehicles.map((vehicle) => (
          <div
            key={vehicle.vehicle_type_id}
            onClick={() => handleSelectVehicle(vehicle)}
            className={`relative bg-luxury-white cursor-pointer transition-all duration-300 hover:shadow-luxury-lg group ${
              selectedVehicle?.vehicle_type_id === vehicle.vehicle_type_id
                ? 'ring-2 ring-luxury-gold shadow-luxury-lg'
                : 'shadow-luxury hover:scale-[1.02]'
            }`}
          >
            {/* Selected Badge */}
            {selectedVehicle?.vehicle_type_id === vehicle.vehicle_type_id && (
              <div className="absolute -top-3 -right-3 bg-luxury-gold text-luxury-white rounded-full p-2 shadow-lg z-10">
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                </svg>
              </div>
            )}

            <div className="p-8">
              {/* Vehicle Header */}
              <div className="flex items-start justify-between mb-6">
                <div>
                  <h3 className="font-display text-2xl text-luxury-black mb-2">
                    {vehicle.display_name || vehicle.name}
                  </h3>
                  <p className="text-sm text-luxury-gray/60">
                    {vehicle.description}
                  </p>
                </div>
                <div className="text-luxury-gold">
                  {getVehicleIcon(vehicle.slug || vehicle.category)}
                </div>
              </div>

              {/* Vehicle Details */}
              <div className="grid grid-cols-2 gap-4 mb-6">
                <div className="flex items-center gap-2 text-sm text-luxury-gray/70">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  {vehicle.max_passengers} passengers
                </div>
                <div className="flex items-center gap-2 text-sm text-luxury-gray/70">
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                  {vehicle.max_luggage} bags
                </div>
              </div>

              {/* Features */}
              <div className="flex flex-wrap gap-2 mb-6">
                {vehicle.features && vehicle.features.map((feature, idx) => (
                  <span key={idx} className="px-3 py-1 bg-luxury-light-gray text-xs text-luxury-gray/70 uppercase tracking-wide">
                    {feature}
                  </span>
                ))}
              </div>

              {/* Price Section */}
              <div className="flex items-end justify-between border-t border-luxury-gray/10 pt-6">
                <div>
                  <p className="text-xs text-luxury-gray/50 uppercase tracking-wide mb-1">Total Fare</p>
                  <p className="font-display text-3xl text-luxury-black">
                    {formatPrice(vehicle.estimated_fare || vehicle.total_price)}
                  </p>
                </div>
                <button
                  type="button"
                  onClick={(e) => {
                    e.stopPropagation();
                    setShowFareBreakdown(showFareBreakdown === vehicle.vehicle_type_id ? null : vehicle.vehicle_type_id);
                  }}
                  className="text-xs text-luxury-gold hover:text-luxury-gold-dark transition-colors uppercase tracking-wide"
                >
                  Fare Details
                </button>
              </div>

              {/* Fare Breakdown */}
              {showFareBreakdown === vehicle.vehicle_type_id && vehicle.fare_breakdown && (
                <div className="mt-4 pt-4 border-t border-luxury-gray/10 space-y-2 text-sm">
                  {Object.entries(vehicle.fare_breakdown).map(([key, item]) => (
                    item && item.amount !== undefined && (
                      <div key={key} className="flex justify-between text-luxury-gray/60">
                        <span>{item.label || key.replace(/_/g, ' ')}</span>
                        <span>{formatPrice(item.amount)}</span>
                      </div>
                    )
                  ))}
                  <div className="flex justify-between font-medium text-luxury-black pt-2 border-t border-luxury-gray/10">
                    <span>Total</span>
                    <span>{formatPrice(vehicle.estimated_fare || vehicle.total_price)}</span>
                  </div>
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Error Message */}
      {(localError || error) && (
        <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
          <p className="text-sm text-red-700">
            {localError || error}
          </p>
        </div>
      )}

      {/* Action Buttons */}
      <div className="flex gap-4">
        <button
          type="button"
          onClick={prevStep}
          className="flex-1 btn-luxury-outline uppercase tracking-luxury text-sm"
        >
          Back
        </button>
        <button
          type="button"
          onClick={handleContinue}
          disabled={!selectedVehicle}
          className="flex-1 btn-luxury-gold uppercase tracking-luxury text-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Continue
        </button>
      </div>
    </div>
  );
};

export default VehicleSelectionLuxury;