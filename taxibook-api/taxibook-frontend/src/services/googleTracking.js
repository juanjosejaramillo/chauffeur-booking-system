/**
 * Google Ads Tracking Service
 * Handles all conversion tracking for Google Ads campaigns
 * Only tracks actual company revenue (excludes driver tips)
 */

export const GoogleTracking = {
  /**
   * Track when user sees vehicle prices
   * @param {number} lowestPrice - Lowest available fare
   */
  trackViewItem: (lowestPrice) => {
    if (typeof window !== 'undefined' && window.dataLayer) {
      window.dataLayer.push({
        event: 'view_item',
        ecommerce: {
          currency: 'USD',
          value: lowestPrice,
          items: [{
            item_name: 'Chauffeur Service Quote',
            price: lowestPrice,
            quantity: 1
          }]
        }
      });
      
      // Debug logging in development
      if (process.env.NODE_ENV === 'development') {
        console.log('🎯 Google Ads: view_item tracked', { lowestPrice });
      }
    }
  },

  /**
   * Track when user confirms vehicle selection and proceeds
   * @param {string} vehicleName - Name of selected vehicle
   * @param {number} fare - Base fare (no tips)
   * @param {string} vehicleDescription - Description of vehicle type
   */
  trackAddToCart: (vehicleName, fare, vehicleDescription) => {
    if (typeof window !== 'undefined' && window.dataLayer) {
      window.dataLayer.push({
        event: 'add_to_cart',
        ecommerce: {
          currency: 'USD',
          value: fare,
          items: [{
            item_name: vehicleName,
            item_category: vehicleDescription || 'Chauffeur Service',
            price: fare,
            quantity: 1
          }]
        }
      });
      
      // Debug logging in development
      if (process.env.NODE_ENV === 'development') {
        console.log('🎯 Google Ads: add_to_cart tracked', { vehicleName, fare, vehicleDescription });
      }
    }
  },

  /**
   * Track when user reaches payment page
   * @param {number} baseFare - Base fare without tips
   * @param {string} vehicleName - Name of selected vehicle
   * @param {string} vehicleDescription - Description of vehicle type
   */
  trackBeginCheckout: (baseFare, vehicleName, vehicleDescription) => {
    if (typeof window !== 'undefined' && window.dataLayer) {
      window.dataLayer.push({
        event: 'begin_checkout',
        ecommerce: {
          currency: 'USD',
          value: baseFare,
          items: [{
            item_name: vehicleName || 'Chauffeur Service',
            item_category: vehicleDescription || 'Chauffeur Service',
            price: baseFare,
            quantity: 1
          }]
        }
      });
      
      // Debug logging in development
      if (process.env.NODE_ENV === 'development') {
        console.log('🎯 Google Ads: begin_checkout tracked', { baseFare, vehicleName, vehicleDescription });
      }
    }
  },

  /**
   * Track completed booking (main conversion)
   * @param {string} bookingId - Unique booking ID
   * @param {number} baseFare - Base fare (company revenue, no tips)
   * @param {string} vehicleName - Name of selected vehicle
   * @param {string} vehicleDescription - Description of vehicle type
   */
  trackPurchase: (bookingId, baseFare, vehicleName, vehicleDescription) => {
    if (typeof window !== 'undefined' && window.dataLayer) {
      window.dataLayer.push({
        event: 'purchase',
        ecommerce: {
          transaction_id: String(bookingId),
          currency: 'USD',
          value: baseFare, // Actual company revenue only
          items: [{
            item_name: vehicleName || 'Chauffeur Service',
            item_category: vehicleDescription || 'Chauffeur Service',
            price: baseFare,
            quantity: 1
          }]
        }
      });
      
      // Debug logging in development
      if (process.env.NODE_ENV === 'development') {
        console.log('🎯 Google Ads: purchase tracked', { bookingId, baseFare, vehicleName, vehicleDescription });
      }
    }
  }
};