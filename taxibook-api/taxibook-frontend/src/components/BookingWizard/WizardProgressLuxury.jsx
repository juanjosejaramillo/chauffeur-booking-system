import { useState, useEffect } from 'react';
import useBookingStore from '../../store/bookingStore';

const steps = [
  { id: 1, name: 'Journey' },
  { id: 2, name: 'Vehicle' },
  { id: 3, name: 'Details' },
  { id: 4, name: 'Review' },
  { id: 5, name: 'Payment' },
  { id: 6, name: 'Confirmed' },
];

const WizardProgressLuxury = () => {
  const currentStep = useBookingStore((state) => state.currentStep);
  const [maxVisibleSteps, setMaxVisibleSteps] = useState(6);

  useEffect(() => {
    const calculateMaxSteps = () => {
      const width = window.innerWidth;
      if (width < 400) {
        setMaxVisibleSteps(3);
      } else if (width < 500) {
        setMaxVisibleSteps(4);
      } else if (width < 640) {
        setMaxVisibleSteps(5);
      } else {
        setMaxVisibleSteps(6);
      }
    };

    calculateMaxSteps();
    window.addEventListener('resize', calculateMaxSteps);
    return () => window.removeEventListener('resize', calculateMaxSteps);
  }, []);

  // Dynamically determine which steps to show
  const getVisibleSteps = () => {
    // On desktop, show all steps
    if (maxVisibleSteps >= 6) {
      return steps;
    }

    // On mobile, show a sliding window of steps
    let visibleSteps = [];
    
    // Always include the current step
    visibleSteps.push(steps[currentStep - 1]);
    
    // Add future steps first (prioritize showing what's coming)
    let stepsToAdd = maxVisibleSteps - 1;
    for (let i = currentStep; i < steps.length && stepsToAdd > 0; i++) {
      visibleSteps.push(steps[i]);
      stepsToAdd--;
    }
    
    // If we have room, add previous uncompleted steps
    if (stepsToAdd > 0 && currentStep > 1) {
      // Only show the immediately previous step if there's room
      visibleSteps.unshift(steps[currentStep - 2]);
    }
    
    return visibleSteps;
  };

  const visibleSteps = getVisibleSteps();

  return (
    <nav aria-label="Booking progress" className="mb-8 px-2">
      <div className="sm:hidden flex items-center justify-center mb-2">
        <span className="text-xs text-luxury-gray/60">
          Step {currentStep} of {steps.length}
        </span>
      </div>
      <ol className="flex items-center justify-center">
        {visibleSteps.map((step, stepIdx) => (
          <li
            key={step.name}
            className="flex items-center flex-shrink-0"
          >
            {stepIdx !== 0 && (
              <div className={`w-6 sm:w-12 md:w-16 h-px mx-1 sm:mx-2 transition-all duration-500 ${
                step.id <= currentStep ? 'bg-luxury-gold' : 'bg-luxury-gray/20'
              }`} />
            )}
            <div className="relative flex flex-col items-center">
              <span
                className={`h-7 w-7 sm:h-10 sm:w-10 rounded-full flex items-center justify-center text-xs sm:text-sm font-light transition-all duration-500 ${
                  step.id < currentStep
                    ? 'bg-luxury-gold text-luxury-white'
                    : step.id === currentStep
                    ? 'bg-luxury-black text-luxury-white ring-2 ring-luxury-gold ring-offset-1 sm:ring-offset-2 ring-offset-luxury-cream'
                    : 'bg-transparent border border-luxury-gray/30 text-luxury-gray/50'
                }`}
              >
                {step.id < currentStep ? (
                  <svg className="h-3 w-3 sm:h-4 sm:w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                ) : (
                  step.id
                )}
              </span>
              <span className={`absolute top-9 sm:top-12 text-[9px] sm:text-xs uppercase tracking-tight sm:tracking-wide whitespace-nowrap transition-all duration-500 ${
                step.id <= currentStep ? 'text-luxury-black font-medium' : 'text-luxury-gray/40'
              } ${step.id === currentStep ? 'font-semibold' : ''}`}>
                {step.name}
              </span>
            </div>
          </li>
        ))}
      </ol>
    </nav>
  );
};

export default WizardProgressLuxury;