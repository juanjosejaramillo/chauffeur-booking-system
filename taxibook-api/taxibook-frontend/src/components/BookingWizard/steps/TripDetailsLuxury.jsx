import { useState, useEffect, useRef } from 'react';
import { Loader } from '@googlemaps/js-api-loader';
import useBookingStore from '../../../store/bookingStore';
import { useSettings } from '../../../hooks/useSettings';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import api from '../../../config/api';
import { ClarityTracking } from '../../../services/clarityTracking';

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
    routeInfo,
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
  const google = useRef(null);
  const pickupMarker = useRef(null);
  const dropoffMarker = useRef(null);
  const directionsRenderer = useRef(null);
  const directionsService = useRef(null);
  const pickupTimeout = useRef(null);
  const dropoffTimeout = useRef(null);
  const pickupRef = useRef(null);
  const dropoffRef = useRef(null);
  const pickupDropdownRef = useRef(null);
  const dropoffDropdownRef = useRef(null);

  useEffect(() => {
    if (!map.current && mapContainer.current && settings?.google_maps?.api_key) {
      const loader = new Loader({
        apiKey: settings.google_maps.api_key,
        version: 'weekly',
        libraries: ['places', 'geometry'],
      });

      loader
        .load()
        .then((googleMaps) => {
          google.current = googleMaps;
          
          // Initialize map with similar style to Mapbox light theme
          map.current = new googleMaps.maps.Map(mapContainer.current, {
            center: { lat: 27.9506, lng: -82.4572 }, // Tampa, FL
            zoom: 10,
            disableDefaultUI: true, // Hide default controls
            zoomControl: true,
            zoomControlOptions: {
              position: googleMaps.maps.ControlPosition.TOP_RIGHT
            },
            styles: [
              {
                featureType: 'all',
                elementType: 'geometry',
                stylers: [{ color: '#f5f5f5' }]
              },
              {
                featureType: 'water',
                elementType: 'geometry',
                stylers: [{ color: '#a3ccff' }] // More pronounced blue for water
              },
              {
                featureType: 'water',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#5580aa' }]
              },
              {
                featureType: 'landscape',
                elementType: 'geometry',
                stylers: [{ color: '#e8e3d3' }] // Warmer land color
              },
              {
                featureType: 'road',
                elementType: 'geometry',
                stylers: [{ color: '#ffffff' }]
              },
              {
                featureType: 'road',
                elementType: 'geometry.stroke',
                stylers: [{ color: '#d0d0d0' }]
              }
            ]
          });

          // Initialize directions service and renderer
          directionsService.current = new googleMaps.maps.DirectionsService();
          directionsRenderer.current = new googleMaps.maps.DirectionsRenderer({
            suppressMarkers: true, // We'll use custom markers
            polylineOptions: {
              strokeColor: '#B8860B', // Luxury gold color
              strokeWeight: 4,
              strokeOpacity: 0.8
            }
          });
          directionsRenderer.current.setMap(map.current);
          
          // Recreate existing markers if any
          if (tripDetails.pickupLat && tripDetails.pickupLng) {
            createPickupMarker(tripDetails.pickupLat, tripDetails.pickupLng);
          }
          
          if (tripDetails.dropoffLat && tripDetails.dropoffLng) {
            createDropoffMarker(tripDetails.dropoffLat, tripDetails.dropoffLng);
          }
          
          // Fit bounds if both markers exist
          if (tripDetails.pickupLat && tripDetails.dropoffLat) {
            const bounds = new googleMaps.maps.LatLngBounds();
            bounds.extend({ lat: tripDetails.pickupLat, lng: tripDetails.pickupLng });
            bounds.extend({ lat: tripDetails.dropoffLat, lng: tripDetails.dropoffLng });
            map.current.fitBounds(bounds, { padding: 100 });
          }
          
          // Draw route if it exists
          if (routeInfo) {
            drawRoute();
          }
        })
        .catch((error) => {
          console.error('Error loading Google Maps:', error);
          setLocalError('Failed to load map. Please refresh the page.');
        });
    }

    return () => {
      if (pickupMarker.current) {
        pickupMarker.current.setMap(null);
        pickupMarker.current = null;
      }
      if (dropoffMarker.current) {
        dropoffMarker.current.setMap(null);
        dropoffMarker.current = null;
      }
    };
  }, [settings]);

  // Handle click outside to close dropdowns
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (pickupRef.current && !pickupRef.current.contains(event.target) && 
          pickupDropdownRef.current && !pickupDropdownRef.current.contains(event.target)) {
        setShowPickupSuggestions(false);
      }
      
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

  const createPickupMarker = (lat, lng) => {
    if (!google.current || !map.current) return;
    
    if (pickupMarker.current) {
      pickupMarker.current.setMap(null);
    }
    
    // Create custom marker similar to Mapbox style
    const markerDiv = document.createElement('div');
    markerDiv.className = 'custom-marker';
    markerDiv.innerHTML = '<div style="width: 32px; height: 32px; background-color: #B8860B; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">P</div>';
    
    pickupMarker.current = new google.current.maps.Marker({
      position: { lat, lng },
      map: map.current,
      icon: {
        path: google.current.maps.SymbolPath.CIRCLE,
        scale: 16,
        fillColor: '#B8860B',
        fillOpacity: 1,
        strokeColor: '#FFFFFF',
        strokeWeight: 3,
      },
      label: {
        text: 'P',
        color: '#FFFFFF',
        fontSize: '14px',
        fontWeight: 'bold'
      }
    });
  };

  const createDropoffMarker = (lat, lng) => {
    if (!google.current || !map.current) return;
    
    if (dropoffMarker.current) {
      dropoffMarker.current.setMap(null);
    }
    
    dropoffMarker.current = new google.current.maps.Marker({
      position: { lat, lng },
      map: map.current,
      icon: {
        path: google.current.maps.SymbolPath.CIRCLE,
        scale: 16,
        fillColor: '#1a1a1a',
        fillOpacity: 1,
        strokeColor: '#FFFFFF',
        strokeWeight: 3,
      },
      label: {
        text: 'D',
        color: '#FFFFFF',
        fontSize: '14px',
        fontWeight: 'bold'
      }
    });
  };

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

      // Use our backend API for Google Places search
      const response = await api.post('/bookings/search-addresses', {
        query,
        lat: 27.9506, // Tampa Bay center for better local results
        lng: -82.4572,
      });

      const suggestions = response.data.suggestions || [];
      
      // Format suggestions to match the expected structure
      const formattedSuggestions = suggestions.map(suggestion => ({
        id: suggestion.place_id,
        place_id: suggestion.place_id,
        place_name: suggestion.name,
        full_address: suggestion.full_description || suggestion.address,
        place_formatted: suggestion.address,
        isVenue: suggestion.is_venue,
        isAirport: suggestion.types?.some(type => type.includes('airport')) || 
                  suggestion.name?.toLowerCase().includes('airport'),
        isHotel: suggestion.types?.some(type => type.includes('lodging')) || 
                 suggestion.name?.toLowerCase().includes('hotel'),
        types: suggestion.types,
        latitude: suggestion.latitude,
        longitude: suggestion.longitude,
        rating: suggestion.rating
      }));
      
      // Sort airports to top
      formattedSuggestions.sort((a, b) => {
        if (a.isAirport && !b.isAirport) return -1;
        if (!a.isAirport && b.isAirport) return 1;
        return 0;
      });
      
      const finalSuggestions = formattedSuggestions.slice(0, 8);
      
      if (type === 'pickup') {
        setPickupSuggestions(finalSuggestions);
        setShowPickupSuggestions(true);
      } else {
        setDropoffSuggestions(finalSuggestions);
        setShowDropoffSuggestions(true);
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
    try {
      let latitude, longitude, addressToStore;
      
      // Check if we already have coordinates from the search results
      if (suggestion.latitude && suggestion.longitude) {
        latitude = suggestion.latitude;
        longitude = suggestion.longitude;
        
        // Format address: include venue name if it's a venue
        addressToStore = suggestion.full_address || suggestion.address;
        if (suggestion.isVenue && suggestion.place_name && 
            !addressToStore.toLowerCase().includes(suggestion.place_name.toLowerCase())) {
          addressToStore = `${suggestion.place_name} - ${addressToStore}`;
        }
      } else {
        // Fallback to getting place details if coordinates not available
        const response = await api.post('/bookings/place-details', {
          place_id: suggestion.place_id,
        });

        const place = response.data.place;
        latitude = place.latitude;
        longitude = place.longitude;
        
        // Format address: include venue name if it's a venue
        addressToStore = place.address;
        if (suggestion.isVenue && suggestion.place_name && 
            !place.address.toLowerCase().includes(suggestion.place_name.toLowerCase())) {
          addressToStore = `${suggestion.place_name} - ${place.address}`;
        }
      }
      
      if (type === 'pickup') {
        setTripDetails({
          pickupAddress: addressToStore,
          pickupLat: latitude,
          pickupLng: longitude,
          isAirportPickup: suggestion.isAirport || false,
        });
        setShowPickupSuggestions(false);
        
        // Track successful address selection
        ClarityTracking.trackAddressSearch('pickup', 'completed', {
          isAirport: suggestion.isAirport || false,
          isVenue: !!suggestion.name && !suggestion.isAirport,
          query: suggestion.description
        });
        
        // Set location type tags
        if (suggestion.isAirport) {
          ClarityTracking.setTag('pickup_type', 'airport');
        } else if (suggestion.name) {
          ClarityTracking.setTag('pickup_type', 'venue');
        } else {
          ClarityTracking.setTag('pickup_type', 'address');
        }
        
        createPickupMarker(latitude, longitude);
      } else {
        setTripDetails({
          dropoffAddress: addressToStore,
          dropoffLat: latitude,
          dropoffLng: longitude,
          isAirportDropoff: suggestion.isAirport || false,
        });
        setShowDropoffSuggestions(false);
        
        // Track successful address selection
        ClarityTracking.trackAddressSearch('dropoff', 'completed', {
          isAirport: suggestion.isAirport || false,
          isVenue: !!suggestion.name && !suggestion.isAirport,
          query: suggestion.description
        });
        
        // Set location type tags
        if (suggestion.isAirport) {
          ClarityTracking.setTag('dropoff_type', 'airport');
        } else if (suggestion.name) {
          ClarityTracking.setTag('dropoff_type', 'venue');
        } else {
          ClarityTracking.setTag('dropoff_type', 'address');
        }
        
        createDropoffMarker(latitude, longitude);
      }

      // Check if both locations are now selected
      const hasPickup = type === 'pickup' ? true : !!tripDetails.pickupLat;
      const hasDropoff = type === 'dropoff' ? true : !!tripDetails.dropoffLat;
      
      // Fit map to show both markers if both exist
      if (hasPickup && hasDropoff && google.current && map.current) {
        const bounds = new google.current.maps.LatLngBounds();
        if (type === 'pickup') {
          bounds.extend({ lat: latitude, lng: longitude });
          bounds.extend({ lat: tripDetails.dropoffLat, lng: tripDetails.dropoffLng });
        } else {
          bounds.extend({ lat: tripDetails.pickupLat, lng: tripDetails.pickupLng });
          bounds.extend({ lat: latitude, lng: longitude });
        }
        map.current.fitBounds(bounds, { padding: 100 });
        
        // Automatically validate route when both locations are selected
        if (tripDetails.pickupDate && tripDetails.pickupTime) {
          validateRoute();
        }
      } else if (map.current) {
        map.current.setCenter({ lat: latitude, lng: longitude });
        map.current.setZoom(14);
      }
    } catch (error) {
      console.error('Error getting place details:', error);
    }
  };

  const drawRoute = () => {
    if (!directionsService.current || !directionsRenderer.current || !tripDetails.pickupLat || !tripDetails.dropoffLat) return;
    
    const request = {
      origin: { lat: tripDetails.pickupLat, lng: tripDetails.pickupLng },
      destination: { lat: tripDetails.dropoffLat, lng: tripDetails.dropoffLng },
      travelMode: google.current.maps.TravelMode.DRIVING,
    };

    directionsService.current.route(request, (result, status) => {
      if (status === 'OK') {
        directionsRenderer.current.setDirections(result);
      }
    });
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
      drawRoute(); // Draw the route after successful validation
      
      // Track successful trip details completion
      ClarityTracking.event('trip_details_completed');
      
      // Set trip type tags
      const tripType = tripDetails.isAirportPickup || tripDetails.isAirportDropoff ? 'airport_transfer' : 'point_to_point';
      ClarityTracking.setTag('trip_type', tripType);
      
      // Track if same-day booking
      const today = new Date().toDateString();
      const pickupDay = new Date(tripDetails.pickupDate).toDateString();
      if (today === pickupDay) {
        ClarityTracking.setTag('booking_type', 'same_day');
      } else {
        ClarityTracking.setTag('booking_type', 'advance');
      }
      
      nextStep();
    } catch (error) {
      // Error handled by store
      // Track route validation error
      ClarityTracking.trackError('trip_details', 'route_validation_failed', error?.message || 'Unknown error');
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
    // Always default to tomorrow at 10:00 AM
    const defaultDate = new Date();
    defaultDate.setDate(defaultDate.getDate() + 1); // Set to tomorrow
    defaultDate.setHours(10, 0, 0, 0); // Set to 10:00 AM
    
    // Round minutes according to time increment setting
    const minutes = defaultDate.getMinutes();
    const roundedMinutes = Math.ceil(minutes / bookingSettings.time_increment) * bookingSettings.time_increment;
    if (roundedMinutes >= 60) {
      defaultDate.setHours(defaultDate.getHours() + 1, 0, 0, 0);
    } else {
      defaultDate.setMinutes(roundedMinutes, 0, 0);
    }
    
    // Ensure the default date meets minimum booking hours requirement
    const minDateTime = getMinDateTime();
    if (defaultDate < minDateTime) {
      return minDateTime;
    }
    
    return defaultDate;
  };
  
  const minDateTime = getMinDateTime();
  const defaultDateTime = getDefaultDateTime();
  
  const [selectedDateTime, setSelectedDateTime] = useState(() => {
    if (tripDetails.pickupDate && tripDetails.pickupTime) {
      const savedDateTime = new Date(`${tripDetails.pickupDate}T${tripDetails.pickupTime}`);
      if (savedDateTime >= minDateTime) {
        return savedDateTime;
      }
    }
    return defaultDateTime;
  });
  
  // Initialize store with default date/time on mount if not already set
  useEffect(() => {
    if (!tripDetails.pickupDate || !tripDetails.pickupTime) {
      const date = selectedDateTime.toISOString().split('T')[0];
      const time = selectedDateTime.toTimeString().slice(0, 5);
      setTripDetails({ pickupDate: date, pickupTime: time });
    }
  }, []);

  // Update store when date/time changes
  useEffect(() => {
    if (selectedDateTime) {
      const date = selectedDateTime.toISOString().split('T')[0];
      const time = selectedDateTime.toTimeString().slice(0, 5);
      setTripDetails({ pickupDate: date, pickupTime: time });
    }
  }, [selectedDateTime]);

  // Draw route when routeInfo changes
  useEffect(() => {
    if (map.current && routeInfo) {
      drawRoute();
    }
  }, [routeInfo]);

  return (
    <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* Header with logo on left and text in center */}
        <div className="flex items-center justify-between mb-12 animate-fade-in">
          <img 
            src="/luxride-logo.svg" 
            alt="LuxRide" 
            className="h-16 sm:h-20 lg:h-24 object-contain"
            style={{ backgroundColor: 'transparent' }}
          />
          <div className="text-center flex-1">
            <h1 className="font-display text-4xl md:text-5xl text-luxury-black mb-4">
              LuxRide
            </h1>
            <p className="text-luxury-gray/70 text-lg tracking-wide">
              Experience seamless luxury transportation
            </p>
          </div>
          <div className="w-16 sm:w-20 lg:w-24"></div> {/* Spacer for balance */}
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
                  className="w-full px-4 py-3 bg-white border border-luxury-gray/20 text-luxury-black placeholder-luxury-gray/50 focus:outline-none focus:ring-2 focus:ring-luxury-gold focus:border-transparent transition-all duration-200 text-lg"
                  placeholder="Airport, hotel, or address"
                  required
                />
                
                {isLoadingPickup && (
                  <div className="absolute right-4 top-12">
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
                            : suggestion.isHotel
                            ? 'bg-luxury-cream/50 hover:bg-luxury-gold/10'
                            : 'hover:bg-luxury-cream'} 
                          border-b border-luxury-light-gray last:border-b-0`}
                      >
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="flex items-center gap-2">
                              {suggestion.isAirport && (
                                <svg className="w-4 h-4 text-luxury-gold flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                  <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                </svg>
                              )}
                              {suggestion.isHotel && (
                                <svg className="w-4 h-4 text-luxury-gold flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                              )}
                              {suggestion.isVenue && !suggestion.isAirport && !suggestion.isHotel && (
                                <svg className="w-4 h-4 text-luxury-gray/50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                              )}
                              {!suggestion.isVenue && (
                                <svg className="w-4 h-4 text-luxury-gray/40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                              )}
                              <p className="text-sm font-medium text-luxury-black">
                                {suggestion.place_name}
                              </p>
                            </div>
                            <p className="text-xs text-luxury-gray/60 mt-1 ml-6">
                              {suggestion.place_formatted || suggestion.full_address || ''}
                            </p>
                          </div>
                          {suggestion.rating && (
                            <div className="ml-3 flex items-center gap-1">
                              <svg className="w-3 h-3 text-luxury-gold fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                              </svg>
                              <span className="text-xs text-luxury-gray">{suggestion.rating}</span>
                            </div>
                          )}
                        </div>
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
                  className="w-full px-4 py-3 bg-white border border-luxury-gray/20 text-luxury-black placeholder-luxury-gray/50 focus:outline-none focus:ring-2 focus:ring-luxury-gold focus:border-transparent transition-all duration-200 text-lg"
                  placeholder="Your destination"
                  required
                />
                
                {isLoadingDropoff && (
                  <div className="absolute right-4 top-12">
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
                            : suggestion.isHotel
                            ? 'bg-luxury-cream/50 hover:bg-luxury-gold/10'
                            : 'hover:bg-luxury-cream'} 
                          border-b border-luxury-light-gray last:border-b-0`}
                      >
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="flex items-center gap-2">
                              {suggestion.isAirport && (
                                <svg className="w-4 h-4 text-luxury-gold flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                  <path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
                                </svg>
                              )}
                              {suggestion.isHotel && (
                                <svg className="w-4 h-4 text-luxury-gold flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                              )}
                              {suggestion.isVenue && !suggestion.isAirport && !suggestion.isHotel && (
                                <svg className="w-4 h-4 text-luxury-gray/50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                              )}
                              {!suggestion.isVenue && (
                                <svg className="w-4 h-4 text-luxury-gray/40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                              )}
                              <p className="text-sm font-medium text-luxury-black">
                                {suggestion.place_name}
                              </p>
                            </div>
                            <p className="text-xs text-luxury-gray/60 mt-1 ml-6">
                              {suggestion.place_formatted || suggestion.full_address || ''}
                            </p>
                          </div>
                          {suggestion.rating && (
                            <div className="ml-3 flex items-center gap-1">
                              <svg className="w-3 h-3 text-luxury-gold fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                              </svg>
                              <span className="text-xs text-luxury-gray">{suggestion.rating}</span>
                            </div>
                          )}
                        </div>
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
                className="w-full px-4 py-4 sm:py-5 bg-luxury-gold text-luxury-white font-semibold tracking-wide transition-all duration-300 ease-out hover:bg-luxury-gold-dark hover:shadow-luxury active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed animate-slide-up uppercase text-xs sm:text-sm"
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