import { useState, useEffect, useRef } from 'react';
import useBookingStore from '../../../store/bookingStore';
import { GoogleTracking } from '../../../services/googleTracking';
import { ClarityTracking } from '../../../services/clarityTracking';

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
  const [expandedVehicle, setExpandedVehicle] = useState(null);
  const hasTrackedView = useRef(false);

  useEffect(() => {
    // Always recalculate prices when component mounts
    // This ensures fresh prices when users go back and change locations
    calculatePrices();
  }, []); // Empty dependency array - only run on mount

  useEffect(() => {
    // Track view_item when prices are first displayed (only once)
    if (availableVehicles && availableVehicles.length > 0 && !loading && !hasTrackedView.current) {
      const lowestPrice = Math.min(...availableVehicles.map(v => v.estimated_fare || v.total_price || 0));
      GoogleTracking.trackViewItem(lowestPrice);
      
      // Track vehicle prices displayed with Clarity
      ClarityTracking.event('vehicle_prices_displayed');
      ClarityTracking.setTag('vehicle_count', availableVehicles.length.toString());
      ClarityTracking.setTag('lowest_price', lowestPrice.toString());
      
      hasTrackedView.current = true;
    }
  }, [availableVehicles, loading]);

  // Track price calculation errors
  useEffect(() => {
    if (error && !loading) {
      ClarityTracking.trackError('vehicle_selection', 'price_calculation', error);
    }
  }, [error, loading]);

  const handleSelectVehicle = (vehicle) => {
    setSelectedVehicle(vehicle);
    
    // Track vehicle selection with Clarity
    ClarityTracking.trackVehicleSelection({
      name: vehicle.display_name || vehicle.name,
      price: vehicle.estimated_fare || vehicle.total_price,
      category: vehicle.slug || vehicle.category,
      vehicle_type_id: vehicle.vehicle_type_id
    });
  };

  const handleToggleExpand = (e, vehicleId) => {
    e.stopPropagation();
    setExpandedVehicle(expandedVehicle === vehicleId ? null : vehicleId);
    
    // Also select the vehicle when expanding
    const vehicle = availableVehicles.find(v => v.vehicle_type_id === vehicleId);
    if (vehicle) {
      handleSelectVehicle(vehicle);
    }
  };

  const handleContinue = () => {
    if (!selectedVehicle) {
      setLocalError('Please select a vehicle to continue');
      
      // Track validation error with Clarity
      ClarityTracking.trackError('vehicle_selection', 'validation', 'No vehicle selected');
      return;
    }
    
    // Track add_to_cart when user proceeds with their final selection
    const fare = selectedVehicle.estimated_fare || selectedVehicle.total_price || 0;
    const vehicleName = selectedVehicle.display_name || selectedVehicle.name;
    const vehicleDescription = selectedVehicle.description || 'Chauffeur Service';
    GoogleTracking.trackAddToCart(vehicleName, fare, vehicleDescription);
    
    // Track successful vehicle selection and proceed with Clarity
    ClarityTracking.event('vehicle_selection_completed');
    ClarityTracking.setTag('final_vehicle', vehicleName);
    ClarityTracking.setTag('final_price', fare.toString());
    
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
      <div className="text-center mb-8 sm:mb-12">
        <h2 className="font-display text-2xl sm:text-3xl text-luxury-black mb-3 sm:mb-4">
          Select Your Vehicle
        </h2>
        {routeInfo && (
          <div className="flex items-center justify-center gap-4 sm:gap-8 text-xs sm:text-sm text-luxury-gray/70">
            <span className="flex items-center gap-1 sm:gap-2">
              <svg className="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {routeInfo.distance.toFixed(1)} miles
            </span>
            <span className="flex items-center gap-1 sm:gap-2">
              <svg className="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {Math.round(routeInfo.duration / 60)} minutes
            </span>
          </div>
        )}
      </div>

      {/* Vehicle List - More compact */}
      <div className="space-y-3 sm:space-y-4 mb-8 sm:mb-12">
        {availableVehicles.map((vehicle) => {
          const isExpanded = expandedVehicle === vehicle.vehicle_type_id;
          const isSelected = selectedVehicle?.vehicle_type_id === vehicle.vehicle_type_id;
          
          return (
            <div
              key={vehicle.vehicle_type_id}
              onClick={() => handleSelectVehicle(vehicle)}
              className={`relative bg-luxury-white cursor-pointer transition-all duration-300 hover:shadow-luxury-lg group ${
                isSelected
                  ? 'ring-2 ring-luxury-gold shadow-luxury-lg'
                  : 'shadow-luxury hover:scale-[1.01]'
              }`}
            >
              {/* Selected Badge */}
              {isSelected && (
                <div className="absolute -top-2 -right-2 sm:-top-3 sm:-right-3 bg-luxury-gold text-luxury-white rounded-full p-1.5 sm:p-2 shadow-lg z-10">
                  <svg className="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                </div>
              )}

              {/* Compact Vehicle Info */}
              <div className="p-4 sm:p-5">
                <div className="flex items-center justify-between">
                  {/* Left: Vehicle Image and Basic Info */}
                  <div className="flex items-center gap-3 sm:gap-4 flex-1">
                    {/* Vehicle Image - Much larger */}
                    {vehicle.image_url && (
                      <div className="w-36 h-24 sm:w-48 sm:h-32 flex-shrink-0">
                        <img
                          src={vehicle.image_url}
                          alt={vehicle.display_name}
                          className="w-full h-full object-contain"
                          onError={(e) => {
                            e.target.style.display = 'none';
                          }}
                        />
                      </div>
                    )}
                    
                    {/* Vehicle Details */}
                    <div className="flex-1 min-w-0">
                      {/* Vehicle Name and Description */}
                      <div className="mb-2">
                        <h3 className="font-display text-base sm:text-xl text-luxury-black">
                          {vehicle.display_name || vehicle.name}
                        </h3>
                        {/* Description from backend */}
                        {vehicle.description && (
                          <p className="text-xs sm:text-sm text-luxury-gray/60 mt-1">
                            {vehicle.description}
                          </p>
                        )}
                      </div>
                      
                      {/* Capacity Icons with text labels */}
                      <div className="flex items-center gap-4 sm:gap-6">
                        <span className="flex items-center gap-1.5 text-xs sm:text-sm text-luxury-gray/60">
                          <svg className="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                          </svg>
                          <span>{vehicle.max_passengers} passengers</span>
                        </span>
                        <span className="flex items-center gap-1.5 text-xs sm:text-sm text-luxury-gray/60">
                          <svg className="w-3.5 h-3.5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                          </svg>
                          <span>{vehicle.max_luggage} bags</span>
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Right: Price and Expand Arrow */}
                  <div className="flex items-center gap-2 sm:gap-3">
                    {/* Price */}
                    <div className="text-right">
                      <p className="text-lg sm:text-xl font-light text-luxury-black">
                        {formatPrice(vehicle.estimated_fare || vehicle.total_price)}
                      </p>
                      <p className="text-[10px] sm:text-xs text-luxury-gray/50">USD</p>
                    </div>
                    
                    {/* Expand Arrow */}
                    <button
                      type="button"
                      onClick={(e) => handleToggleExpand(e, vehicle.vehicle_type_id)}
                      className="p-1.5 sm:p-2 hover:bg-luxury-light-gray rounded-full transition-colors"
                      aria-label={isExpanded ? "Hide details" : "Show details"}
                    >
                      <svg 
                        className={`w-4 h-4 sm:w-5 sm:h-5 text-luxury-gray transition-transform duration-300 ${
                          isExpanded ? 'rotate-180' : ''
                        }`} 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                      >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                  </div>
                </div>

                {/* Expandable Details */}
                {isExpanded && (
                  <div className="mt-4 pt-4 border-t border-luxury-gray/10 animate-fadeIn">
                    {/* Features */}
                    {vehicle.features && vehicle.features.length > 0 && (
                      <div className="flex flex-wrap gap-1.5 sm:gap-2">
                        {vehicle.features.map((feature, idx) => (
                          <span 
                            key={idx} 
                            className="px-2 sm:px-3 py-0.5 sm:py-1 bg-luxury-light-gray text-[10px] sm:text-xs text-luxury-gray/70 uppercase tracking-wide"
                          >
                            {feature}
                          </span>
                        ))}
                      </div>
                    )}
                  </div>
                )}
              </div>
            </div>
          );
        })}
      </div>

      {/* Error Message */}
      {(localError || error) && (
        <div className="bg-red-50 border-l-4 border-red-500 p-3 sm:p-4 mb-4 sm:mb-6">
          <p className="text-xs sm:text-sm text-red-700">
            {localError || error}
          </p>
        </div>
      )}

      {/* Action Buttons */}
      <div className="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">
        <button
          type="button"
          onClick={prevStep}
          className="w-full sm:flex-1 px-4 py-3 border-2 border-luxury-black text-luxury-black font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-black hover:text-luxury-white hover:shadow-luxury active:scale-[0.98] uppercase text-xs sm:text-sm order-2 sm:order-1"
        >
          Back
        </button>
        <button
          type="button"
          onClick={handleContinue}
          disabled={!selectedVehicle}
          className="w-full sm:flex-1 px-4 py-3 bg-luxury-gold text-luxury-white font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-gold-dark hover:shadow-luxury active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed uppercase text-xs sm:text-sm order-1 sm:order-2"
        >
          Continue
        </button>
      </div>
    </div>
  );
};

export default VehicleSelectionLuxury;