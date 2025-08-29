/**
 * Microsoft Clarity Analytics Service
 * Provides comprehensive tracking for user behavior, session recordings, and heatmaps
 */

export const ClarityTracking = {
  /**
   * Check if Clarity is available
   * @returns {boolean} Whether Clarity is loaded and ready
   */
  isAvailable: () => {
    return typeof window !== 'undefined' && typeof window.clarity === 'function';
  },

  /**
   * Track custom events
   * @param {string} eventName - Name of the event to track
   */
  event: (eventName) => {
    if (ClarityTracking.isAvailable()) {
      try {
        window.clarity('event', eventName);
        console.log(`Clarity: Event tracked - ${eventName}`);
      } catch (error) {
        console.error('Failed to track Clarity event:', error);
      }
    }
  },

  /**
   * Set custom tags for filtering and analysis
   * @param {string} key - Tag key
   * @param {string|string[]} value - Tag value(s)
   */
  setTag: (key, value) => {
    if (ClarityTracking.isAvailable()) {
      try {
        window.clarity('set', key, value);
        console.log(`Clarity: Tag set - ${key}: ${value}`);
      } catch (error) {
        console.error('Failed to set Clarity tag:', error);
      }
    }
  },

  /**
   * Identify user for better tracking
   * @param {string} userId - Unique user identifier
   * @param {string} sessionId - Optional session ID
   * @param {string} pageId - Optional page ID
   * @param {string} friendlyName - Optional friendly name
   */
  identify: (userId, sessionId = null, pageId = null, friendlyName = null) => {
    if (ClarityTracking.isAvailable()) {
      try {
        window.clarity('identify', userId, sessionId, pageId, friendlyName);
        console.log('Clarity: User identified');
      } catch (error) {
        console.error('Failed to identify user in Clarity:', error);
      }
    }
  },

  /**
   * Upgrade session priority for important conversions
   * @param {string} reason - Reason for upgrade
   */
  upgrade: (reason) => {
    if (ClarityTracking.isAvailable()) {
      try {
        window.clarity('upgrade', reason);
        console.log(`Clarity: Session upgraded - ${reason}`);
      } catch (error) {
        console.error('Failed to upgrade Clarity session:', error);
      }
    }
  },

  /**
   * Track booking funnel progression
   * @param {number} step - Current step number
   * @param {string} stepName - Name of the step
   * @param {object} metadata - Additional metadata
   */
  trackBookingStep: (step, stepName, metadata = {}) => {
    // Track as custom event
    ClarityTracking.event(`booking_step_${step}_${stepName}`);
    
    // Set tags for the current step
    ClarityTracking.setTag('booking_step', stepName);
    ClarityTracking.setTag('booking_step_number', step.toString());
    
    // Add any additional metadata as tags
    Object.entries(metadata).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        ClarityTracking.setTag(key, String(value));
      }
    });
  },

  /**
   * Track successful booking conversion
   * @param {object} bookingData - Booking details
   */
  trackConversion: (bookingData) => {
    // Track conversion event
    ClarityTracking.event('booking_completed');
    
    // Set conversion-related tags
    if (bookingData.id) {
      ClarityTracking.setTag('booking_id', bookingData.id);
    }
    if (bookingData.amount) {
      ClarityTracking.setTag('booking_amount', bookingData.amount.toString());
      // Categorize booking value
      const amount = parseFloat(bookingData.amount);
      let valueCategory = 'low';
      if (amount > 200) valueCategory = 'high';
      else if (amount > 100) valueCategory = 'medium';
      ClarityTracking.setTag('booking_value', valueCategory);
    }
    if (bookingData.vehicleType) {
      ClarityTracking.setTag('vehicle_type', bookingData.vehicleType);
    }
    
    // Upgrade session for conversion tracking
    ClarityTracking.upgrade('booking_conversion');
  },

  /**
   * Track form validation errors
   * @param {string} formName - Name of the form
   * @param {string} errorType - Type of error
   * @param {string} errorMessage - Error message
   */
  trackError: (formName, errorType, errorMessage) => {
    ClarityTracking.event(`error_${formName}_${errorType}`);
    ClarityTracking.setTag('error_form', formName);
    ClarityTracking.setTag('error_type', errorType);
    if (errorMessage) {
      ClarityTracking.setTag('error_message', errorMessage.substring(0, 100)); // Limit length
    }
  },

  /**
   * Track address search interactions
   * @param {string} type - 'pickup' or 'dropoff'
   * @param {string} status - 'started', 'completed', 'failed'
   * @param {object} details - Additional details
   */
  trackAddressSearch: (type, status, details = {}) => {
    ClarityTracking.event(`address_search_${type}_${status}`);
    
    if (details.isAirport) {
      ClarityTracking.setTag(`${type}_is_airport`, 'true');
    }
    if (details.isVenue) {
      ClarityTracking.setTag(`${type}_is_venue`, 'true');
    }
    if (details.query) {
      ClarityTracking.setTag(`${type}_search_query`, details.query.substring(0, 50));
    }
  },

  /**
   * Track vehicle selection
   * @param {object} vehicle - Vehicle details
   */
  trackVehicleSelection: (vehicle) => {
    ClarityTracking.event('vehicle_selected');
    
    if (vehicle.name) {
      ClarityTracking.setTag('selected_vehicle', vehicle.name);
    }
    if (vehicle.price) {
      ClarityTracking.setTag('vehicle_price', vehicle.price.toString());
    }
    if (vehicle.category) {
      ClarityTracking.setTag('vehicle_category', vehicle.category);
    }
  },

  /**
   * Track email verification process
   * @param {string} status - 'requested', 'verified', 'failed', 'changed'
   */
  trackEmailVerification: (status) => {
    ClarityTracking.event(`email_verification_${status}`);
    ClarityTracking.setTag('email_verification_status', status);
  },

  /**
   * Track payment interactions
   * @param {string} action - Payment action
   * @param {object} details - Payment details
   */
  trackPayment: (action, details = {}) => {
    ClarityTracking.event(`payment_${action}`);
    
    if (details.method) {
      ClarityTracking.setTag('payment_method', details.method);
    }
    if (details.saveCard !== undefined) {
      ClarityTracking.setTag('save_card', details.saveCard ? 'yes' : 'no');
    }
    if (details.hasGratuity !== undefined) {
      ClarityTracking.setTag('has_gratuity', details.hasGratuity ? 'yes' : 'no');
    }
    if (details.gratuityPercent) {
      ClarityTracking.setTag('gratuity_percent', details.gratuityPercent.toString());
    }
    
    // Upgrade session when payment is attempted
    if (action === 'attempted' || action === 'submitted') {
      ClarityTracking.upgrade('payment_attempted');
    }
  },

  /**
   * Track tip payment flow
   * @param {string} action - Tip action
   * @param {object} details - Tip details
   */
  trackTipPayment: (action, details = {}) => {
    ClarityTracking.event(`tip_${action}`);
    
    if (details.amount) {
      ClarityTracking.setTag('tip_amount', details.amount.toString());
    }
    if (details.percentage) {
      ClarityTracking.setTag('tip_percentage', details.percentage);
    }
    if (details.method) {
      ClarityTracking.setTag('tip_payment_method', details.method);
    }
    if (details.source) {
      ClarityTracking.setTag('tip_source', details.source); // 'qr_code', 'email_link', etc.
    }
  },

  /**
   * Track navigation patterns
   * @param {string} action - Navigation action
   * @param {object} details - Navigation details
   */
  trackNavigation: (action, details = {}) => {
    ClarityTracking.event(`navigation_${action}`);
    
    if (details.from) {
      ClarityTracking.setTag('nav_from', details.from);
    }
    if (details.to) {
      ClarityTracking.setTag('nav_to', details.to);
    }
    if (details.method) {
      ClarityTracking.setTag('nav_method', details.method); // 'button', 'back_button', 'browser_back'
    }
  },

  /**
   * Track mobile vs desktop usage
   */
  trackDeviceType: () => {
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    ClarityTracking.setTag('device_type', isMobile ? 'mobile' : 'desktop');
    
    // Track screen size category
    const width = window.innerWidth;
    let screenSize = 'large';
    if (width < 768) screenSize = 'small';
    else if (width < 1024) screenSize = 'medium';
    ClarityTracking.setTag('screen_size', screenSize);
  },

  /**
   * Track legal document interactions
   * @param {string} documentType - Type of document
   * @param {string} action - Action taken
   */
  trackLegalDocument: (documentType, action) => {
    ClarityTracking.event(`legal_${documentType}_${action}`);
    ClarityTracking.setTag('legal_document', documentType);
    ClarityTracking.setTag('legal_action', action);
  },

  /**
   * Initialize tracking with basic session info
   */
  initialize: () => {
    if (ClarityTracking.isAvailable()) {
      // Track device type on initialization
      ClarityTracking.trackDeviceType();
      
      // Track timezone
      const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
      ClarityTracking.setTag('timezone', timezone);
      
      // Track referrer if available
      if (document.referrer) {
        const referrerDomain = new URL(document.referrer).hostname;
        ClarityTracking.setTag('referrer', referrerDomain);
      }
      
      console.log('Clarity tracking initialized');
    }
  }
};