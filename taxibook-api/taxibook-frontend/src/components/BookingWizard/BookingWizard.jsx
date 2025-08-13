import { useEffect } from 'react';
import useBookingStore from '../../store/bookingStore';
import TripDetailsLuxury from './steps/TripDetailsLuxury';
import VehicleSelectionLuxury from './steps/VehicleSelectionLuxury';
import CustomerInfoLuxury from './steps/CustomerInfoLuxury';
import ReviewBookingLuxury from './steps/ReviewBookingLuxury';
import PaymentLuxury from './steps/PaymentLuxury';
import WizardProgressLuxury from './WizardProgressLuxury';

const BookingWizard = () => {
  const { currentStep, resetBooking } = useBookingStore();

  useEffect(() => {
    return () => {
      // Reset booking when component unmounts
      resetBooking();
    };
  }, [resetBooking]);

  const renderStep = () => {
    switch (currentStep) {
      case 1:
        return <TripDetailsLuxury />;
      case 2:
        return <VehicleSelectionLuxury />;
      case 3:
        return <CustomerInfoLuxury />;
      case 4:
        return <ReviewBookingLuxury />;
      case 5:
        return <PaymentLuxury />;
      default:
        return <TripDetailsLuxury />;
    }
  };

  return (
    <div className="min-h-screen">
      {currentStep === 1 ? (
        // TripDetailsLuxury has its own full-screen layout
        renderStep()
      ) : (
        // Other steps use the contained layout
        <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray py-12">
          <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="text-center mb-8">
              <h1 className="font-display text-4xl text-luxury-black mb-2">
                Complete Your Booking
              </h1>
              <div className="mt-8">
                <WizardProgressLuxury />
              </div>
            </div>
            <div className="bg-luxury-white shadow-luxury p-8">
              {renderStep()}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default BookingWizard;