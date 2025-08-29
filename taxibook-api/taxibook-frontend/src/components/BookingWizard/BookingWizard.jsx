import { useEffect, useState, useRef } from 'react';
import useBookingStore from '../../store/bookingStore';
import TripDetailsLuxury from './steps/TripDetailsLuxury';
import VehicleSelectionLuxury from './steps/VehicleSelectionLuxury';
import CustomerInfoLuxury from './steps/CustomerInfoLuxury';
import ReviewBookingLuxury from './steps/ReviewBookingLuxury';
import PaymentLuxury from './steps/PaymentLuxury';
import ConfirmationLuxury from './steps/ConfirmationLuxury';
import WizardProgressLuxury from './WizardProgressLuxury';
import { ClarityTracking } from '../../services/clarityTracking';

const BookingWizard = () => {
  const { currentStep, resetBooking, setCurrentStep } = useBookingStore();
  const [backPressCount, setBackPressCount] = useState(0);
  const backPressTimeoutRef = useRef(null);
  const previousStepRef = useRef(currentStep);

  // Handle browser back button navigation
  useEffect(() => {
    // Initialize history state on mount
    if (!window.history.state || window.history.state.step === undefined) {
      window.history.replaceState({ step: currentStep }, '', window.location.href);
    }

    const handlePopState = (event) => {
      // If on confirmation step (step 6), handle special back behavior
      if (currentStep === 6) {
        // Increment back press count
        const newCount = backPressCount + 1;
        setBackPressCount(newCount);
        
        // Clear existing timeout
        if (backPressTimeoutRef.current) {
          clearTimeout(backPressTimeoutRef.current);
        }
        
        // If pressed twice within 1 second, reset booking and go to step 1
        if (newCount >= 2) {
          // Track booking abandonment from confirmation
          ClarityTracking.event('booking_abandoned_from_confirmation');
          ClarityTracking.trackNavigation('reset_from_confirmation', {
            from: 'confirmation',
            to: 'trip_details',
            method: 'double_back'
          });
          
          sessionStorage.removeItem('booking-storage'); // Clear persisted data
          resetBooking(); // Reset all booking data
          setCurrentStep(1);
          setBackPressCount(0);
          window.history.pushState({ step: 1 }, '', window.location.href);
        } else {
          // Stay on confirmation step and set timeout to reset count
          window.history.pushState({ step: 6 }, '', window.location.href);
          backPressTimeoutRef.current = setTimeout(() => {
            setBackPressCount(0);
          }, 1000); // Reset count after 1 second
        }
        return;
      }
      
      // Normal navigation for other steps
      if (event.state && typeof event.state.step === 'number') {
        // Navigate to the step stored in history
        const targetStep = event.state.step;
        if (targetStep >= 1 && targetStep <= 5) {
          // Track browser back button usage
          ClarityTracking.trackNavigation('browser_back', {
            from: `step_${currentStep}`,
            to: `step_${targetStep}`,
            method: 'browser_back'
          });
          setCurrentStep(targetStep);
        }
      } else if (currentStep > 1) {
        // No state, go back one step
        const newStep = currentStep - 1;
        setCurrentStep(newStep);
        window.history.pushState({ step: newStep }, '', window.location.href);
      } else {
        // We're on step 1, prevent going back further
        window.history.pushState({ step: 1 }, '', window.location.href);
      }
    };

    window.addEventListener('popstate', handlePopState);

    return () => {
      window.removeEventListener('popstate', handlePopState);
      if (backPressTimeoutRef.current) {
        clearTimeout(backPressTimeoutRef.current);
      }
    };
  }, [currentStep, setCurrentStep, resetBooking, backPressCount]);

  // Update history when moving forward through steps and track with Clarity
  useEffect(() => {
    // Initialize Clarity tracking on first mount
    if (!previousStepRef.current) {
      ClarityTracking.initialize();
    }

    // Track step changes with Clarity
    const stepNames = {
      1: 'trip_details',
      2: 'vehicle_selection',
      3: 'customer_info',
      4: 'review_booking',
      5: 'payment',
      6: 'confirmation'
    };
    
    const stepName = stepNames[currentStep] || 'unknown';
    
    // Track booking step progression
    ClarityTracking.trackBookingStep(currentStep, stepName, {
      previous_step: previousStepRef.current,
      navigation_direction: currentStep > previousStepRef.current ? 'forward' : 'backward'
    });
    
    // Track navigation patterns
    if (previousStepRef.current && previousStepRef.current !== currentStep) {
      ClarityTracking.trackNavigation('step_change', {
        from: stepNames[previousStepRef.current] || 'unknown',
        to: stepName,
        method: currentStep > previousStepRef.current ? 'next_button' : 'back_navigation'
      });
    }
    
    // Track if user started booking process
    if (currentStep === 1 && !previousStepRef.current) {
      ClarityTracking.event('booking_started');
    }
    
    // Update previous step reference
    previousStepRef.current = currentStep;
    
    // Only push state when moving forward (not on back navigation)
    const lastStep = window.history.state?.step;
    if (lastStep && currentStep > lastStep) {
      window.history.pushState({ step: currentStep }, '', window.location.href);
    } else if (!lastStep) {
      window.history.replaceState({ step: currentStep }, '', window.location.href);
    }
  }, [currentStep]);

  // Only show warning when actually leaving the site, not on back navigation
  useEffect(() => {
    const handleBeforeUnload = (e) => {
      // Only warn if we have booking data and haven't completed
      if (currentStep > 1 && currentStep < 6) {
        // Check if this is a real page unload (not just history navigation)
        const isActuallyLeaving = !e.persisted && performance.navigation.type !== 2;
        if (isActuallyLeaving) {
          e.preventDefault();
          e.returnValue = 'You have unsaved booking information. Are you sure you want to leave?';
          return e.returnValue;
        }
      }
    };

    // Only add beforeunload on desktop, as mobile browsers handle this poorly
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (!isMobile) {
      window.addEventListener('beforeunload', handleBeforeUnload);
      return () => {
        window.removeEventListener('beforeunload', handleBeforeUnload);
      };
    }
  }, [currentStep]);

  // Only reset booking data on unmount if booking is completed
  useEffect(() => {
    return () => {
      // Only reset if booking is completed to allow users to continue their booking
      if (currentStep === 6) {
        // Clear the session storage after successful completion
        sessionStorage.removeItem('booking-storage');
        resetBooking();
      }
    };
  }, []);

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
      case 6:
        return <ConfirmationLuxury />;
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
        <div className="min-h-screen bg-gradient-to-b from-luxury-cream to-luxury-light-gray py-6 sm:py-12">
          <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            {/* Header with logo and title on same level */}
            <div className="flex items-center justify-between mb-6 sm:mb-8">
              <img 
                src="/luxride-logo.svg" 
                alt="LuxRide" 
                className="h-14 sm:h-16 lg:h-20 object-contain"
                style={{ backgroundColor: 'transparent' }}
              />
              <h1 className="font-display text-2xl sm:text-3xl lg:text-4xl text-luxury-black">
                Complete Your Booking
              </h1>
              <div className="w-14 sm:w-16 lg:w-20"></div> {/* Spacer for centering */}
            </div>
            <div className="mb-6 sm:mb-8">
              <WizardProgressLuxury />
            </div>
            <div className="bg-luxury-white shadow-luxury p-4 sm:p-6 lg:p-8">
              {renderStep()}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default BookingWizard;