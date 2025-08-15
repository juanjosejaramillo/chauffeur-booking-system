import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../config/api';
import useSettings from '../hooks/useSettings';

const TipAlready = () => {
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
      console.error('Failed to load booking:', err);
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
          {/* Info Icon */}
          <div className="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6">
            <svg className="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          {/* Message */}
          <h1 className="font-display text-3xl text-luxury-black mb-4">
            Already Tipped
          </h1>
          <p className="text-luxury-gray/60 mb-6">
            A gratuity has already been added for this booking
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
                  <span className="text-sm text-luxury-gray/60">Trip Date</span>
                  <span className="text-sm font-medium text-luxury-black">{booking.pickup_date}</span>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-sm text-luxury-gray/60">Tip Added</span>
                  <span className="text-lg font-bold text-green-600">
                    {formatPrice(booking.tip_amount || 0)}
                  </span>
                </div>
              </div>
            </div>
          )}

          {/* Thank You Message */}
          <div className="mb-8">
            <p className="text-sm text-luxury-gray/70">
              Thank you for your generosity! Your tip has been received and will be passed on to your driver.
            </p>
          </div>

          {/* Action Button */}
          <button
            onClick={() => navigate('/')}
            className="w-full btn-luxury-gold uppercase tracking-luxury text-sm"
          >
            Return Home
          </button>
        </div>

        {/* Support Info */}
        <div className="text-center mt-6">
          <p className="text-xs text-luxury-gray/60">
            Questions about your booking?
          </p>
          <a href={`tel:${settings.support_phone}`} className="text-luxury-gold hover:text-luxury-gold-dark text-sm font-medium">
            {settings.support_phone}
          </a>
        </div>
      </div>
    </div>
  );
};

export default TipAlready;