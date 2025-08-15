import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../config/api';
import useSettings from '../hooks/useSettings';

const TipSuccess = () => {
  const { token } = useParams();
  const navigate = useNavigate();
  const { settings } = useSettings();
  const [booking, setBooking] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchBookingData();
  }, [token]);

  const fetchBookingData = async () => {
    try {
      const response = await api.get(`/tip/${token}`);
      setBooking(response.data);
    } catch (err) {
    } finally {
      setLoading(false);
    }
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(price);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray flex items-center justify-center">
        <div className="animate-spin h-12 w-12 border-4 border-luxury-gold border-t-transparent rounded-full"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray flex items-center justify-center py-12 px-4">
      <div className="max-w-md w-full">
        <div className="bg-luxury-white shadow-luxury p-8 text-center">
          {/* Success Icon */}
          <div className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <svg className="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>

          {/* Success Message */}
          <h1 className="font-display text-3xl text-luxury-black mb-4">
            Thank You!
          </h1>
          <p className="text-luxury-gray/60 mb-6">
            Your gratuity has been successfully processed
          </p>

          {/* Details */}
          {booking && (
            <div className="bg-luxury-light-gray rounded p-6 mb-8">
              <div className="space-y-3">
                <div className="flex justify-between items-center">
                  <span className="text-sm text-luxury-gray/60">Booking</span>
                  <span className="text-sm font-medium text-luxury-black">#{booking.booking_number}</span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-sm text-luxury-gray/60">Tip Amount</span>
                  <span className="text-lg font-bold text-luxury-black">
                    {formatPrice(booking.tip_amount || 0)}
                  </span>
                </div>
              </div>
            </div>
          )}

          {/* Message */}
          <div className="mb-8">
            <p className="text-sm text-luxury-gray/70">
              Your driver appreciates your generosity! This gratuity goes directly to them as recognition for their exceptional service.
            </p>
          </div>

          {/* Action Buttons */}
          <div className="space-y-3">
            <button
              onClick={() => navigate('/')}
              className="w-full btn-luxury-gold uppercase tracking-luxury text-sm"
            >
              Return Home
            </button>
            <button
              onClick={() => window.print()}
              className="w-full btn-luxury-outline uppercase tracking-luxury text-sm"
            >
              Print Receipt
            </button>
          </div>
        </div>

        {/* Support Info */}
        <div className="text-center mt-6">
          <p className="text-xs text-luxury-gray/60">
            Need assistance? Contact us at
          </p>
          <a href={`tel:${settings.support_phone}`} className="text-luxury-gold hover:text-luxury-gold-dark text-sm font-medium">
            {settings.support_phone}
          </a>
        </div>
      </div>
    </div>
  );
};

export default TipSuccess;