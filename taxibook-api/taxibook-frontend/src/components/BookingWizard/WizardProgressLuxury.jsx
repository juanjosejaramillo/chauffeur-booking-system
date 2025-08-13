import useBookingStore from '../../store/bookingStore';

const steps = [
  { id: 1, name: 'Journey' },
  { id: 2, name: 'Vehicle' },
  { id: 3, name: 'Details' },
  { id: 4, name: 'Review' },
  { id: 5, name: 'Payment' },
];

const WizardProgressLuxury = () => {
  const currentStep = useBookingStore((state) => state.currentStep);

  return (
    <nav aria-label="Booking progress" className="mb-8">
      <ol className="flex items-center justify-center">
        {steps.map((step, stepIdx) => (
          <li
            key={step.name}
            className="flex items-center"
          >
            {stepIdx !== 0 && (
              <div className={`w-16 h-px mx-2 transition-all duration-500 ${
                step.id <= currentStep ? 'bg-luxury-gold' : 'bg-luxury-gray/20'
              }`} />
            )}
            <div className="relative flex flex-col items-center">
              <span
                className={`h-10 w-10 rounded-full flex items-center justify-center text-sm font-light transition-all duration-500 ${
                  step.id < currentStep
                    ? 'bg-luxury-gold text-luxury-white'
                    : step.id === currentStep
                    ? 'bg-luxury-black text-luxury-white ring-2 ring-luxury-gold ring-offset-2 ring-offset-luxury-cream'
                    : 'bg-transparent border border-luxury-gray/30 text-luxury-gray/50'
                }`}
              >
                {step.id < currentStep ? (
                  <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                  </svg>
                ) : (
                  step.id
                )}
              </span>
              <span className={`absolute top-12 text-xs uppercase tracking-wide whitespace-nowrap transition-all duration-500 ${
                step.id <= currentStep ? 'text-luxury-black font-medium' : 'text-luxury-gray/40'
              }`}>
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