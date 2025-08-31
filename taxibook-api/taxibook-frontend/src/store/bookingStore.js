import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import api from '../config/api';
import settingsService from '../services/settingsService';

const useBookingStore = create(
  persist(
    (set, get) => ({
  // Wizard state
  currentStep: 1,
  maxStep: 6,
  
  // Booking data
  tripDetails: {
    pickupAddress: '',
    pickupLat: null,
    pickupLng: null,
    dropoffAddress: '',
    dropoffLat: null,
    dropoffLng: null,
    pickupDate: '',
    pickupTime: '',
    isAirportPickup: false,
    isAirportDropoff: false,
  },
  
  selectedVehicle: null,
  availableVehicles: [],
  
  customerInfo: {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    specialInstructions: '',
    flightNumber: '',
    additionalFields: {}, // For dynamic form fields
  },
  
  // Payment options
  gratuityAmount: 0,
  gratuityPercentage: 0,
  savePaymentMethod: false,
  
  // Email verification state
  emailVerified: false,
  verificationSent: false,
  verificationError: null,
  requireEmailVerification: true, // Default to true, will be updated from settings
  
  routeInfo: null,
  booking: null,
  paymentIntent: null,
  
  loading: false,
  error: null,
  
  // Actions
  setCurrentStep: (step) => set({ currentStep: step }),
  
  nextStep: () => {
    set((state) => ({
      currentStep: Math.min(state.currentStep + 1, state.maxStep)
    }));
    // Smooth scroll to top after step change
    setTimeout(() => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 100);
  },
  
  prevStep: () => {
    set((state) => {
      // Clear vehicle prices when going back from vehicle selection step
      if (state.currentStep === 2) {
        return {
          currentStep: Math.max(state.currentStep - 1, 1),
          availableVehicles: [],
          selectedVehicle: null
        };
      }
      return {
        currentStep: Math.max(state.currentStep - 1, 1)
      };
    });
    // Smooth scroll to top after step change
    setTimeout(() => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 100);
  },
  
  setTripDetails: (details) => set((state) => {
    // Check if location details have changed
    const locationChanged = 
      (details.pickupLat !== undefined && details.pickupLat !== state.tripDetails.pickupLat) ||
      (details.pickupLng !== undefined && details.pickupLng !== state.tripDetails.pickupLng) ||
      (details.dropoffLat !== undefined && details.dropoffLat !== state.tripDetails.dropoffLat) ||
      (details.dropoffLng !== undefined && details.dropoffLng !== state.tripDetails.dropoffLng);
    
    // Clear vehicle data if locations changed
    if (locationChanged) {
      return {
        tripDetails: { ...state.tripDetails, ...details },
        availableVehicles: [],
        selectedVehicle: null
      };
    }
    
    return {
      tripDetails: { ...state.tripDetails, ...details }
    };
  }),
  
  setSelectedVehicle: (vehicle) => set({ selectedVehicle: vehicle }),
  
  setCustomerInfo: (info) => set((state) => ({
    customerInfo: { 
      ...state.customerInfo, 
      ...info,
      // Ensure additionalFields is always an object
      additionalFields: info.additionalFields !== undefined 
        ? info.additionalFields 
        : (state.customerInfo.additionalFields || {})
    }
  })),
  
  // Payment setters
  setGratuity: (percentage, amount) => set({ 
    gratuityPercentage: percentage, 
    gratuityAmount: amount 
  }),
  
  setSavePaymentMethod: (save) => set({ savePaymentMethod: save }),
  
  // API calls
  validateRoute: async () => {
    set({ loading: true, error: null });
    try {
      const { tripDetails } = get();
      const payload = {
        pickup_lat: tripDetails.pickupLat,
        pickup_lng: tripDetails.pickupLng,
        dropoff_lat: tripDetails.dropoffLat,
        dropoff_lng: tripDetails.dropoffLng,
        pickup_date: `${tripDetails.pickupDate} ${tripDetails.pickupTime}`,
      };
      
      
      const response = await api.post('/bookings/validate-route', payload);
      
      
      set({ routeInfo: response.data.route });
      return response.data;
    } catch (error) {
      
      // Extract error message from various possible formats
      let errorMessage = 'Failed to validate route';
      
      if (error.response?.data) {
        if (error.response.data.error) {
          errorMessage = error.response.data.error;
        } else if (error.response.data.message) {
          errorMessage = error.response.data.message;
        } else if (error.response.data.errors) {
          // Laravel validation errors format
          const firstError = Object.values(error.response.data.errors)[0];
          errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
        }
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      set({ error: errorMessage });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  calculatePrices: async () => {
    set({ loading: true, error: null });
    try {
      const { tripDetails } = get();
      const response = await api.post('/bookings/calculate-prices', {
        pickup_lat: tripDetails.pickupLat,
        pickup_lng: tripDetails.pickupLng,
        dropoff_lat: tripDetails.dropoffLat,
        dropoff_lng: tripDetails.dropoffLng,
        pickup_date: tripDetails.pickupDate,
        pickup_time: tripDetails.pickupTime,
      });
      
      set({ 
        availableVehicles: response.data.vehicles,
        routeInfo: response.data.route 
      });
      return response.data;
    } catch (error) {
      set({ error: error.response?.data?.error || 'Failed to calculate prices' });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  createBooking: async () => {
    set({ loading: true, error: null });
    try {
      const { tripDetails, selectedVehicle, customerInfo, booking } = get();
      
      // Check if booking already exists (avoid duplicates)
      if (booking && booking.booking_number) {
        console.log('Booking already exists, skipping creation');
        return { booking };
      }
      
      const response = await api.post('/bookings', {
        vehicle_type_id: selectedVehicle.vehicle_type_id,
        customer_first_name: customerInfo.firstName,
        customer_last_name: customerInfo.lastName,
        customer_email: customerInfo.email,
        customer_phone: customerInfo.phone,
        pickup_address: tripDetails.pickupAddress,
        pickup_lat: tripDetails.pickupLat,
        pickup_lng: tripDetails.pickupLng,
        dropoff_address: tripDetails.dropoffAddress,
        dropoff_lat: tripDetails.dropoffLat,
        dropoff_lng: tripDetails.dropoffLng,
        pickup_date: `${tripDetails.pickupDate} ${tripDetails.pickupTime}`,
        special_instructions: customerInfo.specialInstructions,
        flight_number: customerInfo.flightNumber || null,
        is_airport_pickup: tripDetails.isAirportPickup || false,
        is_airport_dropoff: tripDetails.isAirportDropoff || false,
        additional_fields: customerInfo.additionalFields || {},
        // No payment_method_id - will be processed separately
      });
      
      set({ booking: response.data.booking });
      return response.data;
    } catch (error) {
      set({ error: error.response?.data?.error || 'Failed to create booking' });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  processBookingPayment: async (paymentMethodId) => {
    set({ loading: true, error: null });
    try {
      const { booking, gratuityAmount, savePaymentMethod } = get();
      if (!booking || !booking.booking_number) {
        throw new Error('No booking found to process payment');
      }
      
      const response = await api.post(`/bookings/${booking.booking_number}/process-payment`, {
        payment_method_id: paymentMethodId,
        gratuity_amount: gratuityAmount,
        save_payment_method: savePaymentMethod,
      });
      
      set({ booking: response.data.booking });
      return response.data;
    } catch (error) {
      set({ error: error.response?.data?.error || 'Failed to process payment' });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  createPaymentIntent: async () => {
    set({ loading: true, error: null });
    try {
      const { booking } = get();
      const response = await api.post(`/bookings/${booking.booking_number}/payment-intent`);
      
      set({ paymentIntent: response.data });
      return response.data;
    } catch (error) {
      set({ error: error.response?.data?.error || 'Failed to create payment intent' });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  confirmPayment: async (paymentIntentId) => {
    set({ loading: true, error: null });
    try {
      const { booking } = get();
      const response = await api.post(`/bookings/${booking.booking_number}/confirm-payment`, {
        payment_intent_id: paymentIntentId,
      });
      
      set({ booking: response.data.booking });
      return response.data;
    } catch (error) {
      set({ error: error.response?.data?.error || 'Failed to confirm payment' });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  // Email verification methods
  sendVerificationCode: async () => {
    set({ loading: true, verificationError: null });
    try {
      const { customerInfo, tripDetails } = get();
      
      const payload = {
        email: customerInfo.email,
        customer_first_name: customerInfo.firstName,
        customer_last_name: customerInfo.lastName,
        customer_phone: customerInfo.phone,
        pickup_address: tripDetails.pickupAddress,
        dropoff_address: tripDetails.dropoffAddress,
        pickup_date: `${tripDetails.pickupDate} ${tripDetails.pickupTime}`,
      };
      
      
      const response = await api.post('/bookings/send-verification', payload);
      
      // Check if verification was bypassed (when disabled in settings)
      if (response.data.verification_bypassed) {
        set({ emailVerified: true, verificationSent: false });
      } else {
        set({ verificationSent: true });
      }
      
      return response.data;
    } catch (error) {
      const errorMessage = error.response?.data?.error || 'Failed to send verification code';
      set({ verificationError: errorMessage });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  verifyEmailCode: async (code) => {
    set({ loading: true, verificationError: null });
    try {
      const { customerInfo } = get();
      const response = await api.post('/bookings/verify-email', {
        email: customerInfo.email,
        code: code,
      });
      
      if (response.data.verified) {
        set({ emailVerified: true, verificationError: null });
        return response.data;
      }
    } catch (error) {
      const errorMessage = error.response?.data?.error || 'Invalid verification code';
      set({ verificationError: errorMessage });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  resendVerificationCode: async () => {
    set({ loading: true, verificationError: null });
    try {
      const { customerInfo } = get();
      const response = await api.post('/bookings/resend-verification', {
        email: customerInfo.email,
      });
      
      set({ verificationError: null });
      return response.data;
    } catch (error) {
      const errorMessage = error.response?.data?.error || 'Failed to resend verification code';
      set({ verificationError: errorMessage });
      throw error;
    } finally {
      set({ loading: false });
    }
  },
  
  // Fetch and update settings
  fetchSettings: async () => {
    try {
      const settings = await settingsService.getPublicSettings();
      if (settings && settings.booking) {
        set({ requireEmailVerification: settings.booking.require_email_verification });
      }
    } catch (error) {
      console.error('Failed to fetch settings:', error);
      // Keep default value if fetch fails
    }
  },
  
  resetBooking: () => set({
    currentStep: 1,
    tripDetails: {
      pickupAddress: '',
      pickupLat: null,
      pickupLng: null,
      dropoffAddress: '',
      dropoffLat: null,
      dropoffLng: null,
      pickupDate: '',
      pickupTime: '',
    },
    selectedVehicle: null,
    availableVehicles: [],
    customerInfo: {
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      specialInstructions: '',
    },
    gratuityAmount: 0,
    gratuityPercentage: 0,
    savePaymentMethod: false,
    emailVerified: false,
    verificationSent: false,
    verificationError: null,
    routeInfo: null,
    booking: null,
    paymentIntent: null,
    error: null,
  }),
    }),
    {
      name: 'booking-storage', // unique name for storage
      storage: createJSONStorage(() => sessionStorage), // use sessionStorage instead of localStorage
      partialize: (state) => ({ 
        // Only persist essential booking data, not UI states
        currentStep: state.currentStep,
        tripDetails: state.tripDetails,
        selectedVehicle: state.selectedVehicle,
        customerInfo: state.customerInfo,
        gratuityAmount: state.gratuityAmount,
        gratuityPercentage: state.gratuityPercentage,
        savePaymentMethod: state.savePaymentMethod,
        emailVerified: state.emailVerified,
        routeInfo: state.routeInfo,
        booking: state.booking,
      }),
    }
  )
);

export { useBookingStore };

export default useBookingStore;