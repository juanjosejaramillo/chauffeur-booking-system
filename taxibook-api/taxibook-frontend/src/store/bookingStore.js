import { create } from 'zustand';
import api from '../config/api';

const useBookingStore = create((set, get) => ({
  // Wizard state
  currentStep: 1,
  maxStep: 5,
  
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
  
  // Email verification state
  emailVerified: false,
  verificationSent: false,
  verificationError: null,
  
  routeInfo: null,
  booking: null,
  paymentIntent: null,
  
  loading: false,
  error: null,
  
  // Actions
  setCurrentStep: (step) => set({ currentStep: step }),
  
  nextStep: () => set((state) => ({
    currentStep: Math.min(state.currentStep + 1, state.maxStep)
  })),
  
  prevStep: () => set((state) => ({
    currentStep: Math.max(state.currentStep - 1, 1)
  })),
  
  setTripDetails: (details) => set((state) => ({
    tripDetails: { ...state.tripDetails, ...details }
  })),
  
  setSelectedVehicle: (vehicle) => set({ selectedVehicle: vehicle }),
  
  setCustomerInfo: (info) => set((state) => ({
    customerInfo: { ...state.customerInfo, ...info }
  })),
  
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
      
      console.log('Sending validateRoute request:', payload);
      
      const response = await api.post('/bookings/validate-route', payload);
      
      console.log('ValidateRoute response:', response.data);
      
      set({ routeInfo: response.data.route });
      return response.data;
    } catch (error) {
      console.error('ValidateRoute error:', error.response?.data || error);
      
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
      
      console.log('Setting error message:', errorMessage);
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
      const { tripDetails, selectedVehicle, customerInfo } = get();
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
      
      console.log('Sending verification payload:', payload);
      
      const response = await api.post('/bookings/send-verification', payload);
      
      set({ verificationSent: true });
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
    emailVerified: false,
    verificationSent: false,
    verificationError: null,
    routeInfo: null,
    booking: null,
    paymentIntent: null,
    error: null,
  }),
}));

export default useBookingStore;