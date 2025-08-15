import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';

let settingsCache = null;
let settingsPromise = null;

export const settingsService = {
  async getPublicSettings() {
    // Return cached settings if available
    if (settingsCache) {
      return settingsCache;
    }

    // If a request is already in progress, return the same promise
    if (settingsPromise) {
      return settingsPromise;
    }

    // Make the API request
    settingsPromise = axios.get(`${API_BASE_URL}/api/settings/public`)
      .then(response => {
        settingsCache = response.data;
        settingsPromise = null;
        return settingsCache;
      })
      .catch(error => {
        settingsPromise = null;
        // Return default values if the API call fails
        return {
          support_phone: '1-800-TAXIBOOK',
          business_email: 'info@luxride.com',
          business_name: 'LuxRide'
        };
      });

    return settingsPromise;
  },

  clearCache() {
    settingsCache = null;
    settingsPromise = null;
  }
};

export default settingsService;