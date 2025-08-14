import { useEffect, useState } from 'react';
import { UserGroupIcon, BriefcaseIcon, CheckCircleIcon } from '@heroicons/react/24/outline';
import useBookingStore from '../../../store/bookingStore';

const VehicleSelection = () => {
  const {
    availableVehicles,
    selectedVehicle,
    setSelectedVehicle,
    calculatePrices,
    nextStep,
    prevStep,
    loading,
    error,
    routeInfo,
  } = useBookingStore();
  
  const [localError, setLocalError] = useState('');

  useEffect(() => {
    // Load vehicles when component mounts
    if (availableVehicles.length === 0) {
      calculatePrices();
    }
  }, []);

  const handleSubmit = (e) => {
    e.preventDefault();
    setLocalError('');
    
    if (!selectedVehicle) {
      setLocalError('Please select a vehicle');
      return;
    }
    
    nextStep();
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(price);
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold text-gray-900 mb-2">
          Select Your Vehicle
        </h2>
        
        {routeInfo && (
          <div className="text-sm text-gray-600 bg-gray-50 p-3 rounded-md mb-4">
            <p>📍 Distance: {routeInfo.distance} miles</p>
            <p>⏱️ Estimated time: {routeInfo.duration_minutes} minutes</p>
          </div>
        )}
      </div>

      {loading ? (
        <div className="text-center py-8">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p className="mt-2 text-gray-600">Calculating prices...</p>
        </div>
      ) : (
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {availableVehicles.map((vehicle) => (
              <div
                key={vehicle.vehicle_type_id}
                onClick={() => setSelectedVehicle(vehicle)}
                className={`relative cursor-pointer rounded-lg border-2 p-4 transition-all ${
                  selectedVehicle?.vehicle_type_id === vehicle.vehicle_type_id
                    ? 'border-indigo-600 bg-indigo-50'
                    : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                {selectedVehicle?.vehicle_type_id === vehicle.vehicle_type_id && (
                  <CheckCircleIcon className="absolute top-4 right-4 h-6 w-6 text-indigo-600" />
                )}
                
                <div className="flex items-start space-x-4">
                  <div className="flex-shrink-0">
                    {vehicle.image_url ? (
                      <img
                        src={vehicle.image_url}
                        alt={vehicle.display_name}
                        className="w-32 h-20 object-cover rounded-lg"
                        onError={(e) => {
                          e.target.style.display = 'none';
                        }}
                      />
                    ) : (
                      <div className="w-32 h-20 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M8 7h12l-.757 10.602A2 2 0 0117.244 19H10.756a2 2 0 01-1.999-1.398L8 7zm0 0l-1-3h10l-1 3M8 7H5a1 1 0 00-1 1v1m4-2v0m8 0v0m0 0h3a1 1 0 011 1v1" />
                        </svg>
                      </div>
                    )}
                  </div>
                  
                  <div className="flex-1">
                    <h3 className="font-semibold text-gray-900">
                      {vehicle.display_name}
                    </h3>
                    
                    {vehicle.description && (
                      <p className="text-sm text-gray-600 mt-1">
                        {vehicle.description}
                      </p>
                    )}
                    
                    <div className="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                      <span className="flex items-center">
                        <UserGroupIcon className="h-4 w-4 mr-1" />
                        {vehicle.max_passengers} passengers
                      </span>
                      <span className="flex items-center">
                        <BriefcaseIcon className="h-4 w-4 mr-1" />
                        {vehicle.max_luggage} bags
                      </span>
                    </div>
                    
                    {vehicle.features && vehicle.features.length > 0 && (
                      <div className="flex flex-wrap gap-2 mt-2">
                        {vehicle.features.map((feature, idx) => (
                          <span
                            key={idx}
                            className="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded"
                          >
                            {feature}
                          </span>
                        ))}
                      </div>
                    )}
                    
                    <div className="mt-3">
                      <span className="text-2xl font-bold text-gray-900">
                        {formatPrice(vehicle.estimated_fare)} USD
                      </span>
                    </div>
                    
                    {/* Fare breakdown hidden for now */}
                    {false && vehicle.fare_breakdown && (
                      <button
                        type="button"
                        onClick={(e) => {
                          e.stopPropagation();
                          // TODO: Show fare breakdown modal
                        }}
                        className="text-xs text-indigo-600 hover:text-indigo-700 mt-1"
                      >
                        View fare breakdown →
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
          
          {(error || localError) && (
            <div className="p-3 bg-red-50 border border-red-200 rounded-md">
              <p className="text-sm text-red-600">{error || localError}</p>
            </div>
          )}
          
          <div className="flex space-x-4">
            <button
              type="button"
              onClick={prevStep}
              className="flex-1 bg-gray-200 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-300 transition-colors"
            >
              Back
            </button>
            <button
              type="submit"
              disabled={loading || !selectedVehicle}
              className="flex-1 bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              Continue to Customer Info
            </button>
          </div>
        </form>
      )}
    </div>
  );
};

export default VehicleSelection;