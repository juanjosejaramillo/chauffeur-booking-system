/**
 * Hotjar Analytics Service
 * Tracks user behavior, session recordings, and heatmaps
 */

import Hotjar from '@hotjar/browser';

// Hotjar configuration
const HOTJAR_SITE_ID = 6503063;
const HOTJAR_VERSION = 6;

export const HotjarTracking = {
  /**
   * Initialize Hotjar tracking
   * Only runs in production environment
   */
  initialize: () => {
    // Only initialize in production
    if (process.env.NODE_ENV === 'production') {
      try {
        Hotjar.init(HOTJAR_SITE_ID, HOTJAR_VERSION);
        
        // Debug logging in development (this won't run in production)
        console.log('🔥 Hotjar initialized successfully');
      } catch (error) {
        console.error('Failed to initialize Hotjar:', error);
      }
    } else if (process.env.NODE_ENV === 'development') {
      console.log('🔥 Hotjar disabled in development mode');
    }
  },

  /**
   * Identify user for better tracking (optional)
   * @param {string} userId - Unique user identifier
   * @param {object} attributes - User attributes (email, name, etc.)
   */
  identify: (userId, attributes = {}) => {
    if (process.env.NODE_ENV === 'production' && window.hj) {
      try {
        window.hj('identify', userId, attributes);
      } catch (error) {
        console.error('Failed to identify user in Hotjar:', error);
      }
    }
  },

  /**
   * Track custom events
   * @param {string} eventName - Name of the event to track
   */
  event: (eventName) => {
    if (process.env.NODE_ENV === 'production' && window.hj) {
      try {
        window.hj('event', eventName);
      } catch (error) {
        console.error('Failed to track Hotjar event:', error);
      }
    }
  },

  /**
   * Track virtual page views (useful for SPAs)
   * @param {string} path - The virtual page path
   */
  vpv: (path) => {
    if (process.env.NODE_ENV === 'production' && window.hj) {
      try {
        window.hj('vpv', path);
      } catch (error) {
        console.error('Failed to track virtual page view:', error);
      }
    }
  },

  /**
   * Track state changes in the application
   * @param {string} stateName - Name of the state
   * @param {any} stateData - Data associated with the state
   */
  stateChange: (stateName, stateData) => {
    if (process.env.NODE_ENV === 'production' && window.hj) {
      try {
        window.hj('stateChange', stateName, stateData);
      } catch (error) {
        console.error('Failed to track state change:', error);
      }
    }
  },

  /**
   * Track booking funnel progression
   * @param {number} step - Current step in booking process
   * @param {string} stepName - Name of the step
   */
  trackBookingStep: (step, stepName) => {
    if (process.env.NODE_ENV === 'production') {
      HotjarTracking.event(`booking_step_${step}_${stepName}`);
      HotjarTracking.stateChange('booking_step', {
        step,
        stepName,
        timestamp: new Date().toISOString()
      });
    }
  },

  /**
   * Track booking conversion
   * @param {object} bookingData - Booking details
   */
  trackBookingConversion: (bookingData) => {
    if (process.env.NODE_ENV === 'production') {
      HotjarTracking.event('booking_completed');
      HotjarTracking.stateChange('booking_completed', {
        bookingId: bookingData.id,
        amount: bookingData.amount,
        vehicle: bookingData.vehicleType,
        timestamp: new Date().toISOString()
      });
    }
  }
};