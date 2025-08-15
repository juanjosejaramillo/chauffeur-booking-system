import { useState, useEffect, useRef } from 'react';
import mapboxgl from 'mapbox-gl';
import useBookingStore from '../../../store/bookingStore';
import { useSettings } from '../../../hooks/useSettings';
import 'mapbox-gl/dist/mapbox-gl.css';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_TOKEN || '';

// Common Florida airports for better matching
const FLORIDA_AIRPORTS = [
  { code: 'TPA', name: 'Tampa International Airport', coords: [-82.5332, 27.9756] },
  { code: 'MCO', name: 'Orlando International Airport', coords: [-81.3089, 28.4294] },
  { code: 'MIA', name: 'Miami International Airport', coords: [-80.2906, 25.7932] },
  { code: 'FLL', name: 'Fort Lauderdale-Hollywood International Airport', coords: [-80.1527, 26.0726] },
  { code: 'PBI', name: 'Palm Beach International Airport', coords: [-80.0956, 26.6832] },
  { code: 'JAX', name: 'Jacksonville International Airport', coords: [-81.6879, 30.4942] },
  { code: 'RSW', name: 'Southwest Florida International Airport', coords: [-81.7552, 26.5362] },
  { code: 'SRQ', name: 'Sarasota-Bradenton International Airport', coords: [-82.5543, 27.3954] },
  { code: 'PIE', name: 'St. Pete-Clearwater International Airport', coords: [-82.6872, 27.9102] },
  { code: 'TLH', name: 'Tallahassee International Airport', coords: [-84.3503, 30.3965] }
];

const TripDetailsLuxury = () => {
  const {
    tripDetails,
    setTripDetails,
    validateRoute,
    nextStep,
    loading,
    error,
  } = useBookingStore();
  
  const { settings } = useSettings();
  const bookingSettings = settings?.booking || {
    minimum_hours: 2,
    maximum_days: 90,
    allow_same_day: true,
    time_increment: 5
  };
  
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
  const pickupRef = useRef(null);
  const dropoffRef = useRef(null);
  const pickupDropdownRef = useRef(null);
  const dropoffDropdownRef = useRef(null);

  useEffect(() => {
    if (!map.current && mapContainer.current && mapboxgl.accessToken) {
      try {
        map.current = new mapboxgl.Map({
          container: mapContainer.current,
          style: 'mapbox://styles/mapbox/light-v11', // Lighter, cleaner map style
          center: [-82.4572, 27.9506], // Default to Tampa, FL
          zoom: 10,
          attributionControl: false,
        });

        // Add navigation controls with custom positioning
        map.current.addControl(new mapboxgl.NavigationControl(), 'bottom-right');
      } catch (error) {
      }
    }

    return () => {
      if (map.current) {
        map.current.remove();
        map.current = null;
      }
    };
  }, []);

  // Handle click outside to close dropdowns
  useEffect(() => {
    const handleClickOutside = (event) => {
      // Check pickup dropdown
      if (pickupRef.current && !pickupRef.current.contains(event.target) && 
          pickupDropdownRef.current && !pickupDropdownRef.current.contains(event.target)) {
        setShowPickupSuggestions(false);
      }
      
      // Check dropoff dropdown
      if (dropoffRef.current && !dropoffRef.current.contains(event.target) && 
          dropoffDropdownRef.current && !dropoffDropdownRef.current.contains(event.target)) {
        setShowDropoffSuggestions(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  const searchAddresses = async (query, type) => {
    if (query.length < 2) {
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

      const sessionToken = `session-${Date.now()}`;
      const searchBoxUrl = `https://api.mapbox.com/search/searchbox/v1/suggest`;
      
      const params = new URLSearchParams({
        q: query,
        access_token: mapboxgl.accessToken,
        session_token: sessionToken,
        language: 'en',
        limit: '10',
        country: 'US',
        proximity: '-81.5158,27.6648',
        types: 'poi,address,place'
      });

      const response = await fetch(`${searchBoxUrl}?${params}`);
      const data = await response.json();
      
      if (data.suggestions && data.suggestions.length > 0) {
        const suggestions = data.suggestions.map(suggestion => {
          const properties = suggestion.properties || {};
          const context = properties.context || {};
          
          let placeName = suggestion.name || suggestion.place_name || '';
          if (context.place) {
            placeName += `, ${context.place.name}`;
          }
          if (context.region) {
            placeName += `, ${context.region.name}`;
          }
          
          const nameAndCategory = (suggestion.name + ' ' + (properties.category || '')).toLowerCase();
          const isAirport = nameAndCategory.includes('airport') || 
                           nameAndCategory.includes('international') ||
                           properties.poi_category?.includes('airport');
          
          return {
            id: suggestion.mapbox_id || suggestion.id || `place-${Date.now()}-${Math.random()}`,
            place_name: placeName,
            center: properties.coordinates ? [properties.coordinates.longitude, properties.coordinates.latitude] : null,
            place_type: properties.category || properties.poi_category || suggestion.place_type?.[0] || null,
            isAirport: isAirport,
            full_address: properties.full_address || properties.place_formatted || placeName,
            mapbox_id: suggestion.mapbox_id
          };
        });
        
        suggestions.sort((a, b) => {
          if (a.isAirport && !b.isAirport) return -1;
          if (!a.isAirport && b.isAirport) return 1;
          return 0;
        });
        
        const finalSuggestions = suggestions.slice(0, 8);
        
        if (type === 'pickup') {
          setPickupSuggestions(finalSuggestions);
          setShowPickupSuggestions(true);
        } else {
          setDropoffSuggestions(finalSuggestions);
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
      clearTimeout(pickupTimeout.current);
      pickupTimeout.current = setTimeout(() => {
        searchAddresses(value, 'pickup');
      }, 300);
    } else {
      setTripDetails({ dropoffAddress: value });
      clearTimeout(dropoffTimeout.current);
      dropoffTimeout.current = setTimeout(() => {
        searchAddresses(value, 'dropoff');
      }, 300);
    }
  };

  const selectSuggestion = async (suggestion, type) => {
    let lng, lat;
    
    if (!suggestion.center && suggestion.mapbox_id) {
      try {
        const retrieveUrl = `https://api.mapbox.com/search/searchbox/v1/retrieve/${suggestion.mapbox_id}`;
        const params = new URLSearchParams({
          access_token: mapboxgl.accessToken,
          session_token: `session-${Date.now()}`
        });
        
        const response = await fetch(`${retrieveUrl}?${params}`);
        const data = await response.json();
        
        if (data.features && data.features.length > 0) {
          const feature = data.features[0];
          if (feature.geometry && feature.geometry.coordinates) {
            [lng, lat] = feature.geometry.coordinates;
          }
        }
      } catch (error) {
      }
    } else if (suggestion.center) {
      [lng, lat] = suggestion.center;
    }
    
    if (!lng || !lat) {
      return;
    }
    
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
        const el = document.createElement('div');
        el.className = 'w-8 h-8 bg-luxury-gold rounded-full shadow-luxury flex items-center justify-center text-luxury-white';
        el.innerHTML = 'P';
        pickupMarker.current = new mapboxgl.Marker(el)
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
        const el = document.createElement('div');
        el.className = 'w-8 h-8 bg-luxury-black rounded-full shadow-luxury flex items-center justify-center text-luxury-white';
        el.innerHTML = 'D';
        dropoffMarker.current = new mapboxgl.Marker(el)
          .setLngLat([lng, lat])
          .addTo(map.current);
      }
    }

    // Fit map to show both markers if both exist
    if (tripDetails.pickupLat && tripDetails.dropoffLat) {
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
      map.current.fitBounds(bounds, { padding: 100 });
    } else {
      map.current.flyTo({ center: [lng, lat], zoom: 14 });
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLocalError('');
    
    if (!tripDetails.pickupAddress || !tripDetails.dropoffAddress) {
      setLocalError('Please select both pickup and destination locations');
      return;
    }
    
    if (!tripDetails.pickupDate || !tripDetails.pickupTime) {
      setLocalError('Please select your preferred travel date and time');
      return;
    }
    
    if (!tripDetails.pickupLat || !tripDetails.dropoffLat) {
      setLocalError('Please select valid locations from the suggestions');
      return;
    }
    
    try {
      await validateRoute();
      nextStep();
    } catch (error) {
    }
  };

  const getMinDateTime = () => {
    const now = new Date();
    now.setHours(now.getHours() + bookingSettings.minimum_hours);
    return now;
  };
  
  const getMaxDateTime = () => {
    const max = new Date();
    max.setDate(max.getDate() + bookingSettings.maximum_days);
    return max;
  };
  
  const getDefaultDateTime = () => {
    const defaultDate = new Date();
    const currentHour = defaultDate.getHours();
    
    // Add minimum booking hours
    defaultDate.setHours(defaultDate.getHours() + bookingSettings.minimum_hours);
    
    // If it's still today and same day booking is not allowed, move to tomorrow
    const today = new Date();
    if (!bookingSettings.allow_same_day && 
        defaultDate.toDateString() === today.toDateString()) {
      defaultDate.setDate(defaultDate.getDate() + 1);
      defaultDate.setHours(7, 0, 0, 0); // Set to 7 AM next day
    } else {
      // Round to next available time increment
      const minutes = defaultDate.getMinutes();
      const roundedMinutes = Math.ceil(minutes / bookingSettings.time_increment) * bookingSettings.time_increment;
      if (roundedMinutes >= 60) {
        defaultDate.setHours(defaultDate.getHours() + 1, 0, 0, 0);
      } else {
        defaultDate.setMinutes(roundedMinutes, 0, 0);
      }
    }
    
    return defaultDate;
  };
  
  const minDateTime = getMinDateTime();
  const [selectedDateTime, setSelectedDateTime] = useState(
    tripDetails.pickupDate && tripDetails.pickupTime 
      ? new Date(`${tripDetails.pickupDate}T${tripDetails.pickupTime}`)
      : getDefaultDateTime()
  );
  
  // Update store when date/time changes
  useEffect(() => {
    if (selectedDateTime) {
      const date = selectedDateTime.toISOString().split('T')[0];
      const time = selectedDateTime.toTimeString().slice(0, 5);
      setTripDetails({ pickupDate: date, pickupTime: time });
    }
  }, [selectedDateTime]);

  return (
    <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* Header */}
        <div className="text-center mb-12 animate-fade-in">
          <h1 className="font-display text-4xl md:text-5xl text-luxury-black mb-4">
            Your Journey Begins Here
          </h1>
          <p className="text-luxury-gray/70 text-lg tracking-wide">
            Experience seamless luxury transportation
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-5 gap-8">
          {/* Form Section */}
          <div className="lg:col-span-2 space-y-6">
            <form onSubmit={handleSubmit} className="bg-luxury-white p-8 shadow-luxury space-y-8">
              {/* Pickup Location */}
              <div className="relative animate-slide-up" ref={pickupRef}>
                <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
                  Pickup Location
                </label>
                <input
                  type="text"
                  value={tripDetails.pickupAddress}
                  onChange={(e) => handleAddressChange(e.target.value, 'pickup')}
                  onFocus={() => tripDetails.pickupAddress.length >= 2 && setShowPickupSuggestions(true)}
                  className="input-luxury text-lg"
                  placeholder="Airport, hotel, or address"
                  required
                />
                
                {isLoadingPickup && (
                  <div className="absolute right-0 top-12">
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-luxury-gold"></div>
                  </div>
                )}
                
                {showPickupSuggestions && pickupSuggestions.length > 0 && (
                  <div 
                    ref={pickupDropdownRef}
                    className="absolute z-[100] w-full mt-2 bg-white border border-luxury-gray/20 shadow-xl max-h-60 overflow-auto isolate"
                    style={{ backgroundColor: '#FFFFFF' }}
                  >
                    {pickupSuggestions.map((suggestion) => (
                      <button
                        key={suggestion.id}
                        type="button"
                        onClick={() => selectSuggestion(suggestion, 'pickup')}
                        onMouseDown={(e) => e.preventDefault()} 
                        className={`w-full text-left px-6 py-4 transition-all duration-200 
                          ${suggestion.isAirport 
                            ? 'bg-luxury-light-gray hover:bg-luxury-gold/10' 
                            : 'hover:bg-luxury-cream'} 
                          border-b border-luxury-light-gray last:border-b-0`}
                      >
                        <p className="text-sm font-medium text-luxury-black">
                          {suggestion.place_name.split(',')[0]}
                        </p>
                        <p className="text-xs text-luxury-gray/60 mt-1">
                          {suggestion.full_address || suggestion.place_name.split(',').slice(1).join(',')}
                        </p>
                      </button>
                    ))}
                  </div>
                )}
              </div>

              {/* Dropoff Location */}
              <div className="relative animate-slide-up" style={{ animationDelay: '0.1s' }} ref={dropoffRef}>
                <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
                  Destination
                </label>
                <input
                  type="text"
                  value={tripDetails.dropoffAddress}
                  onChange={(e) => handleAddressChange(e.target.value, 'dropoff')}
                  onFocus={() => tripDetails.dropoffAddress.length >= 2 && setShowDropoffSuggestions(true)}
                  className="input-luxury text-lg"
                  placeholder="Your destination"
                  required
                />
                
                {isLoadingDropoff && (
                  <div className="absolute right-0 top-12">
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-luxury-gold"></div>
                  </div>
                )}
                
                {showDropoffSuggestions && dropoffSuggestions.length > 0 && (
                  <div 
                    ref={dropoffDropdownRef}
                    className="absolute z-[100] w-full mt-2 bg-white border border-luxury-gray/20 shadow-xl max-h-60 overflow-auto isolate"
                    style={{ backgroundColor: '#FFFFFF' }}
                  >
                    {dropoffSuggestions.map((suggestion) => (
                      <button
                        key={suggestion.id}
                        type="button"
                        onClick={() => selectSuggestion(suggestion, 'dropoff')}
                        onMouseDown={(e) => e.preventDefault()} 
                        className={`w-full text-left px-6 py-4 transition-all duration-200 
                          ${suggestion.isAirport 
                            ? 'bg-luxury-light-gray hover:bg-luxury-gold/10' 
                            : 'hover:bg-luxury-cream'} 
                          border-b border-luxury-light-gray last:border-b-0`}
                      >
                        <p className="text-sm font-medium text-luxury-black">
                          {suggestion.place_name.split(',')[0]}
                        </p>
                        <p className="text-xs text-luxury-gray/60 mt-1">
                          {suggestion.full_address || suggestion.place_name.split(',').slice(1).join(',')}
                        </p>
                      </button>
                    ))}
                  </div>
                )}
              </div>

              {/* Date & Time Row */}
              <div className="animate-slide-up" style={{ animationDelay: '0.2s' }}>
                <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
                  Pickup Date & Time
                </label>
                <DatePicker
                  selected={selectedDateTime}
                  onChange={(date) => setSelectedDateTime(date)}
                  showTimeSelect
                  timeFormat="h:mm aa"
                  timeIntervals={bookingSettings.time_increment}
                  timeCaption="Time"
                  dateFormat="MMMM d, yyyy h:mm aa"
                  minDate={minDateTime}
                  maxDate={getMaxDateTime()}
                  minTime={
                    selectedDateTime && selectedDateTime.toDateString() === minDateTime.toDateString()
                      ? minDateTime
                      : new Date(new Date().setHours(0, 0, 0, 0))
                  }
                  maxTime={new Date(new Date().setHours(23, 59, 59, 999))}
                  placeholderText="Select pickup date and time"
                  className="w-full px-4 py-3 bg-white border border-luxury-gray/20 text-luxury-black placeholder-luxury-gray/50 focus:outline-none focus:ring-2 focus:ring-luxury-gold focus:border-transparent transition-all duration-200 text-sm"
                  calendarClassName="luxury-calendar"
                  wrapperClassName="w-full"
                  required
                  filterDate={(date) => {
                    // If same day booking is not allowed, exclude today
                    if (!bookingSettings.allow_same_day) {
                      const today = new Date();
                      today.setHours(0, 0, 0, 0);
                      return date > today;
                    }
                    return true;
                  }}
                  filterTime={(time) => {
                    const currentDate = new Date();
                    const selectedDate = new Date(selectedDateTime || currentDate);
                    
                    // If it's today, filter times that are at least minimum_hours from now
                    if (selectedDate.toDateString() === currentDate.toDateString()) {
                      const minTime = new Date();
                      minTime.setHours(minTime.getHours() + bookingSettings.minimum_hours);
                      return time >= minTime;
                    }
                    return true;
                  }}
                />
              </div>

              {/* Error Message */}
              {(localError || error) && (
                <div className="bg-red-50 border-l-4 border-red-500 p-4 animate-fade-in">
                  <p className="text-sm text-red-700">
                    {localError || error}
                  </p>
                </div>
              )}

              {/* Submit Button */}
              <button
                type="submit"
                disabled={loading}
                className="w-full btn-luxury-gold text-center uppercase tracking-luxury py-5 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed animate-slide-up"
                style={{ animationDelay: '0.3s' }}
              >
                {loading ? 'Processing...' : 'Select Vehicle'}
              </button>
            </form>

            {/* Trust Indicators */}
            <div className="bg-luxury-white p-6 shadow-luxury animate-fade-in">
              <div className="flex items-center justify-between text-xs text-luxury-gray/60">
                <span className="flex items-center gap-2">
                  <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  Secure Booking
                </span>
                <span className="flex items-center gap-2">
                  <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                  </svg>
                  24/7 Support
                </span>
                <span className="flex items-center gap-2">
                  <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                  Licensed Chauffeurs
                </span>
              </div>
            </div>
          </div>

          {/* Map Section */}
          <div className="lg:col-span-3">
            <div className="bg-luxury-white p-2 shadow-luxury h-[600px] animate-fade-in">
              <div ref={mapContainer} className="w-full h-full" />
            </div>
          </div>
        </div>

        {/* Click outside to close suggestions */}
        {(showPickupSuggestions || showDropoffSuggestions) && (
          <div 
            className="fixed inset-0 z-40" 
            onClick={() => {
              setShowPickupSuggestions(false);
              setShowDropoffSuggestions(false);
            }}
          />
        )}
      </div>
    </div>
  );
};

export default TripDetailsLuxury;