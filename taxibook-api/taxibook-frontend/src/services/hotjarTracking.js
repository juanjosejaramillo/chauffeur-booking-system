/**
 * Hotjar Analytics Service
 * Uses traditional Hotjar script loaded in index.html
 */

export const HotjarTracking = {
  /**
   * Check if Hotjar is available
   * @returns {boolean} Whether Hotjar is loaded and ready
   */
  isAvailable: () => {
    return typeof window !== 'undefined' && typeof window.hj === 'function';
  },

  /**
   * Identify user for better tracking (optional)
   * @param {string} userId - Unique user identifier
   * @param {object} attributes - User attributes (email, name, etc.)
   */
  identify: (userId, attributes = {}) => {
    if (HotjarTracking.isAvailable()) {
      try {
        window.hj('identify', userId, attributes);
        console.log('Hotjar: User identified');
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
    if (HotjarTracking.isAvailable()) {
      try {
        window.hj('event', eventName);
        console.log(`Hotjar: Event tracked - ${eventName}`);
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
    if (HotjarTracking.isAvailable()) {
      try {
        window.hj('vpv', path);
        console.log(`Hotjar: Virtual page view - ${path}`);
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
    if (HotjarTracking.isAvailable()) {
      try {
        window.hj('stateChange', stateName, stateData);
        console.log(`Hotjar: State change - ${stateName}`);
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
    HotjarTracking.event(`booking_step_${step}_${stepName}`);
    HotjarTracking.stateChange('booking_step', {
      step,
      stepName,
      timestamp: new Date().toISOString()
    });
  },

  /**
   * Track booking conversion
   * @param {object} bookingData - Booking details
   */
  trackBookingConversion: (bookingData) => {
    HotjarTracking.event('booking_completed');
    HotjarTracking.stateChange('booking_completed', {
      bookingId: bookingData.id,
      amount: bookingData.amount,
      vehicle: bookingData.vehicleType,
      timestamp: new Date().toISOString()
    });
  }
};