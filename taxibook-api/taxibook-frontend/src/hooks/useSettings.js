import { useState, useEffect } from 'react';
import settingsService from '../services/settingsService';

export function useSettings() {
  // Initialize with empty values - will be populated from API
  const [settings, setSettings] = useState({
    support_phone: '',
    business_email: '',
    business_name: ''
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Fetch settings from backend API
    settingsService.getPublicSettings()
      .then(data => {
        // Data will either be from API or fallback values from service
        setSettings(data);
        setLoading(false);
      })
      .catch(err => {
        // This should rarely happen as service handles errors internally
        setError(err);
        setLoading(false);
      });
  }, []);

  return { settings, loading, error };
}

export default useSettings;