import { useEffect } from 'react';
import useBookingStore from '../../store/bookingStore';
import TripDetails from './steps/TripDetails';
import VehicleSelection from './steps/VehicleSelection';
import CustomerInfo from './steps/CustomerInfo';
import ReviewBooking from './steps/ReviewBooking';
import Payment from './steps/Payment';
import WizardProgress from './WizardProgress';

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
        return <TripDetails />;
      case 2:
        return <VehicleSelection />;
      case 3:
        return <CustomerInfo />;
      case 4:
        return <ReviewBooking />;
      case 5:
        return <Payment />;
      default:
        return <TripDetails />;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-white shadow-xl rounded-lg">
          <div className="px-4 py-6 sm:px-6">
            <h1 className="text-3xl font-bold text-gray-900 text-center mb-8">
              Book Your Ride
            </h1>
            <WizardProgress />
          </div>
          <div className="px-4 py-6 sm:px-6">
            {renderStep()}
          </div>
        </div>
      </div>
    </div>
  );
};

export default BookingWizard;