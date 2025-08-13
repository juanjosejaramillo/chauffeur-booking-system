import { useState, useEffect, useRef } from 'react';
import { MapPinIcon, CalendarIcon, ClockIcon } from '@heroicons/react/24/outline';
import mapboxgl from 'mapbox-gl';
import useBookingStore from '../../../store/bookingStore';
import 'mapbox-gl/dist/mapbox-gl.css';

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN || '';

const TripDetails = () => {
  const {
    tripDetails,
    setTripDetails,
    validateRoute,
    nextStep,
    loading,
    error,
  } = useBookingStore();
  
  const [localError, setLocalError] = useState('');
  const [pickupSuggestions, setPickupSuggestions] = useState([]);
  const [dropoffSuggestions, setDropoffSuggestions] = useState([]);
  const [showPickupSuggestions, setShowPickupSuggestions] = useState(false);
  const [showDropoffSuggestions, setShowDropoffSuggestions] = useState(false);
  const [isLoadingPickup, setIsLoadingPickup] = useState(false);
  const [isLoadingDropoff, setIsLoadingDropoff] = useState(false);
  
  const mapContainer = useRef(null);
  const map = useRef(null);
  const pickupMarker = useRef(null);
  const dropoffMarker = useRef(null);
  const pickupTimeout = useRef(null);
  const dropoffTimeout = useRef(null);

  useEffect(() => {
    if (!map.current && mapContainer.current && mapboxgl.accessToken) {
      try {
        map.current = new mapboxgl.Map({
          container: mapContainer.current,
          style: 'mapbox://styles/mapbox/streets-v12',
          center: [-82.4572, 27.9506], // Default to Tampa, FL
          zoom: 10,
        });

        // Add navigation controls
        map.current.addControl(new mapboxgl.NavigationControl(), 'top-right');
      } catch (error) {
        console.error('Error initializing map:', error);
      }
    }

    // Cleanup
    return () => {
      if (map.current) {
        map.current.remove();
        map.current = null;
      }
    };
  }, []);

  const searchAddresses = async (query, type) => {
    if (query.length < 3) {
      if (type === 'pickup') {
        setPickupSuggestions([]);
        setShowPickupSuggestions(false);
      } else {
        setDropoffSuggestions([]);
        setShowDropoffSuggestions(false);
      }
      return;
    }

    try {
      if (type === 'pickup') {
        setIsLoadingPickup(true);
      } else {
        setIsLoadingDropoff(true);
      }

      const response = await fetch(
        `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(
          query
        )}.json?access_token=${mapboxgl.accessToken}&limit=5&country=US&types=poi,address,place`
      );
      const data = await response.json();
      
      if (data.features && data.features.length > 0) {
        const suggestions = data.features.map(feature => ({
          id: feature.id,
          place_name: feature.place_name,
          center: feature.center,
          place_type: feature.properties?.category || feature.place_type?.[0] || null,
        }));
        
        if (type === 'pickup') {
          setPickupSuggestions(suggestions);
          setShowPickupSuggestions(true);
        } else {
          setDropoffSuggestions(suggestions);
          setShowDropoffSuggestions(true);
        }
      } else {
        if (type === 'pickup') {
          setPickupSuggestions([]);
        } else {
          setDropoffSuggestions([]);
        }
      }
    } catch (error) {
      console.error('Search error:', error);
    } finally {
      if (type === 'pickup') {
        setIsLoadingPickup(false);
      } else {
        setIsLoadingDropoff(false);
      }
    }
  };

  const handleAddressChange = (value, type) => {
    if (type === 'pickup') {
      setTripDetails({ pickupAddress: value });
      
      // Clear previous timeout
      if (pickupTimeout.current) {
        clearTimeout(pickupTimeout.current);
      }
      
      // Set new timeout for debounced search
      pickupTimeout.current = setTimeout(() => {
        searchAddresses(value, 'pickup');
      }, 300);
    } else {
      setTripDetails({ dropoffAddress: value });
      
      // Clear previous timeout
      if (dropoffTimeout.current) {
        clearTimeout(dropoffTimeout.current);
      }
      
      // Set new timeout for debounced search
      dropoffTimeout.current = setTimeout(() => {
        searchAddresses(value, 'dropoff');
      }, 300);
    }
  };

  const selectSuggestion = (suggestion, type) => {
    const [lng, lat] = suggestion.center;
    
    if (type === 'pickup') {
      setTripDetails({
        pickupAddress: suggestion.place_name,
        pickupLat: lat,
        pickupLng: lng,
      });
      setShowPickupSuggestions(false);
      
      if (pickupMarker.current) {
        pickupMarker.current.setLngLat([lng, lat]);
      } else {
        pickupMarker.current = new mapboxgl.Marker({ color: 'green' })
          .setLngLat([lng, lat])
          .addTo(map.current);
      }
    } else {
      setTripDetails({
        dropoffAddress: suggestion.place_name,
        dropoffLat: lat,
        dropoffLng: lng,
      });
      setShowDropoffSuggestions(false);
      
      if (dropoffMarker.current) {
        dropoffMarker.current.setLngLat([lng, lat]);
      } else {
        dropoffMarker.current = new mapboxgl.Marker({ color: 'red' })
          .setLngLat([lng, lat])
          .addTo(map.current);
      }
    }
    
    // Update map view
    if (tripDetails.pickupLat && (type === 'dropoff' || tripDetails.dropoffLat)) {
      const bounds = new mapboxgl.LngLatBounds();
      if (type === 'pickup') {
        bounds.extend([lng, lat]);
        if (tripDetails.dropoffLat) {
          bounds.extend([tripDetails.dropoffLng, tripDetails.dropoffLat]);
        }
      } else {
        bounds.extend([tripDetails.pickupLng, tripDetails.pickupLat]);
        bounds.extend([lng, lat]);
      }
      map.current.fitBounds(bounds, { padding: 50 });
    } else {
      map.current.flyTo({ center: [lng, lat], zoom: 14 });
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLocalError('');
    
    console.log('Form submitted with tripDetails:', tripDetails);
    
    // Validate all fields
    if (!tripDetails.pickupAddress || !tripDetails.dropoffAddress) {
      setLocalError('Please enter both pickup and dropoff addresses');
      return;
    }
    
    if (!tripDetails.pickupDate || !tripDetails.pickupTime) {
      setLocalError('Please select pickup date and time');
      return;
    }
    
    if (!tripDetails.pickupLat || !tripDetails.dropoffLat) {
      setLocalError('Please select valid addresses from the suggestions');
      return;
    }
    
    console.log('Validation passed, calling validateRoute...');
    
    try {
      await validateRoute();
      console.log('Route validated successfully');
      nextStep();
    } catch (error) {
      console.error('Route validation failed:', error);
      // Error is handled in the store
    }
  };

  // Get minimum date (today) and time (2 hours from now)
  const getMinDateTime = () => {
    const now = new Date();
    now.setHours(now.getHours() + 2);
    
    const date = now.toISOString().split('T')[0];
    const time = now.toTimeString().slice(0, 5);
    
    return { date, time };
  };
  
  const { date: minDate, time: minTime } = getMinDateTime();

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="space-y-4">
          <h2 className="text-xl font-semibold text-gray-900">
            Where would you like to go?
          </h2>
          
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="relative">
              <label className="block text-sm font-medium text-gray-700 mb-1">
                <MapPinIcon className="inline h-4 w-4 mr-1 text-green-600" />
                Pickup Address
              </label>
              <input
                type="text"
                value={tripDetails.pickupAddress}
                onChange={(e) => handleAddressChange(e.target.value, 'pickup')}
                onFocus={() => tripDetails.pickupAddress.length >= 3 && setShowPickupSuggestions(true)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="e.g., TPA, Tampa Airport, hotel name..."
                required
              />
              
              {/* Loading indicator */}
              {isLoadingPickup && (
                <div className="absolute right-3 top-9 mt-0.5">
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
                </div>
              )}
              
              {/* Suggestions dropdown */}
              {showPickupSuggestions && pickupSuggestions.length > 0 && (
                <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                  {pickupSuggestions.map((suggestion) => {
                    // Determine icon based on place type
                    let icon = '📍';
                    if (suggestion.place_type?.includes('airport')) icon = '✈️';
                    else if (suggestion.place_type?.includes('hotel')) icon = '🏨';
                    else if (suggestion.place_type?.includes('restaurant')) icon = '🍴';
                    else if (suggestion.place_type?.includes('hospital')) icon = '🏥';
                    else if (suggestion.place_type?.includes('station')) icon = '🚉';
                    else if (suggestion.place_type?.includes('poi')) icon = '🏢';
                    
                    return (
                      <button
                        key={suggestion.id}
                        type="button"
                        onClick={() => selectSuggestion(suggestion, 'pickup')}
                        className="w-full text-left px-3 py-2 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none border-b border-gray-100 last:border-b-0"
                      >
                        <div className="flex items-start">
                          <span className="mr-2 text-lg">{icon}</span>
                          <div className="flex-1">
                            <p className="text-sm text-gray-900 font-medium">
                              {suggestion.place_name.split(',')[0]}
                            </p>
                            <p className="text-xs text-gray-500">
                              {suggestion.place_name.split(',').slice(1).join(',')}
                            </p>
                          </div>
                        </div>
                      </button>
                    );
                  })}
                </div>
              )}
            </div>
            
            <div className="relative">
              <label className="block text-sm font-medium text-gray-700 mb-1">
                <MapPinIcon className="inline h-4 w-4 mr-1 text-red-600" />
                Dropoff Address
              </label>
              <input
                type="text"
                value={tripDetails.dropoffAddress}
                onChange={(e) => handleAddressChange(e.target.value, 'dropoff')}
                onFocus={() => tripDetails.dropoffAddress.length >= 3 && setShowDropoffSuggestions(true)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="e.g., airport code, hotel, address..."
                required
              />
              
              {/* Loading indicator */}
              {isLoadingDropoff && (
                <div className="absolute right-3 top-9 mt-0.5">
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600"></div>
                </div>
              )}
              
              {/* Suggestions dropdown */}
              {showDropoffSuggestions && dropoffSuggestions.length > 0 && (
                <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                  {dropoffSuggestions.map((suggestion) => {
                    // Determine icon based on place type
                    let icon = '📍';
                    if (suggestion.place_type?.includes('airport')) icon = '✈️';
                    else if (suggestion.place_type?.includes('hotel')) icon = '🏨';
                    else if (suggestion.place_type?.includes('restaurant')) icon = '🍴';
                    else if (suggestion.place_type?.includes('hospital')) icon = '🏥';
                    else if (suggestion.place_type?.includes('station')) icon = '🚉';
                    else if (suggestion.place_type?.includes('poi')) icon = '🏢';
                    
                    return (
                      <button
                        key={suggestion.id}
                        type="button"
                        onClick={() => selectSuggestion(suggestion, 'dropoff')}
                        className="w-full text-left px-3 py-2 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none border-b border-gray-100 last:border-b-0"
                      >
                        <div className="flex items-start">
                          <span className="mr-2 text-lg">{icon}</span>
                          <div className="flex-1">
                            <p className="text-sm text-gray-900 font-medium">
                              {suggestion.place_name.split(',')[0]}
                            </p>
                            <p className="text-xs text-gray-500">
                              {suggestion.place_name.split(',').slice(1).join(',')}
                            </p>
                          </div>
                        </div>
                      </button>
                    );
                  })}
                </div>
              )}
            </div>
            
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  <CalendarIcon className="inline h-4 w-4 mr-1" />
                  Pickup Date
                </label>
                <input
                  type="date"
                  value={tripDetails.pickupDate}
                  onChange={(e) => setTripDetails({ pickupDate: e.target.value })}
                  min={minDate}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  <ClockIcon className="inline h-4 w-4 mr-1" />
                  Pickup Time
                </label>
                <input
                  type="time"
                  value={tripDetails.pickupTime}
                  onChange={(e) => setTripDetails({ pickupTime: e.target.value })}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  required
                />
              </div>
            </div>
            
            {(error || localError) && (
              <div className="p-3 bg-red-50 border border-red-200 rounded-md">
                <p className="text-sm text-red-600">{error || localError}</p>
              </div>
            )}
            
            <div className="text-sm text-gray-600 bg-blue-50 p-3 rounded-md">
              <p>⏰ Bookings must be made at least 2 hours in advance</p>
              <p>📍 Start typing an address (3+ characters) to see suggestions</p>
            </div>
            
            {/* Click outside to close suggestions */}
            {(showPickupSuggestions || showDropoffSuggestions) && (
              <div 
                className="fixed inset-0 z-0" 
                onClick={() => {
                  setShowPickupSuggestions(false);
                  setShowDropoffSuggestions(false);
                }}
              />
            )}
            
            <button
              type="submit"
              disabled={loading}
              className="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              {loading ? 'Validating Route...' : 'Continue to Vehicle Selection'}
            </button>
          </form>
        </div>
        
        <div className="h-96 lg:h-full rounded-lg overflow-hidden bg-gray-100 relative">
          <div ref={mapContainer} className="w-full h-full" />
          {!mapboxgl.accessToken && (
            <div className="absolute inset-0 flex items-center justify-center bg-gray-100">
              <div className="text-center p-4">
                <p className="text-gray-600 mb-2">Map preview unavailable</p>
                <p className="text-sm text-gray-500">Mapbox token not configured</p>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default TripDetails;