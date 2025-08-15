import { useState, useEffect } from 'react';
import settingsService from '../services/settingsService';

export function useSettings() {
  const [settings, setSettings] = useState({
    support_phone: '1-800-TAXIBOOK',
    business_email: 'info@luxride.com',
    business_name: 'LuxRide'
  });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    settingsService.getPublicSettings()
      .then(data => {
        setSettings(data);
        setLoading(false);
      })
      .catch(err => {
        setError(err);
        setLoading(false);
      });
  }, []);

  return { settings, loading, error };
}

export default useSettings;