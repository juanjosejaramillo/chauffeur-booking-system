import { CheckIcon } from '@heroicons/react/24/solid';
import useBookingStore from '../../store/bookingStore';

const steps = [
  { id: 1, name: 'Trip Details' },
  { id: 2, name: 'Vehicle Selection' },
  { id: 3, name: 'Customer Info' },
  { id: 4, name: 'Review' },
  { id: 5, name: 'Payment' },
];

const WizardProgress = () => {
  const currentStep = useBookingStore((state) => state.currentStep);

  return (
    <nav aria-label="Progress">
      <ol className="flex items-center justify-between">
        {steps.map((step, stepIdx) => (
          <li
            key={step.name}
            className={`${
              stepIdx !== steps.length - 1 ? 'flex-1' : ''
            } relative`}
          >
            {stepIdx !== steps.length - 1 && (
              <div
                className="absolute top-4 w-full"
                aria-hidden="true"
              >
                <div className="h-0.5 w-full bg-gray-200">
                  <div
                    className={`h-0.5 ${
                      step.id < currentStep
                        ? 'bg-indigo-600'
                        : 'bg-gray-200'
                    } transition-all duration-300`}
                    style={{
                      width: step.id < currentStep ? '100%' : '0%',
                    }}
                  />
                </div>
              </div>
            )}
            <div className="relative flex items-center justify-center">
              <span
                className={`h-8 w-8 rounded-full flex items-center justify-center text-sm font-medium ${
                  step.id < currentStep
                    ? 'bg-indigo-600 text-white'
                    : step.id === currentStep
                    ? 'bg-indigo-600 text-white border-2 border-indigo-600'
                    : 'bg-white border-2 border-gray-300 text-gray-500'
                }`}
              >
                {step.id < currentStep ? (
                  <CheckIcon className="h-5 w-5" />
                ) : (
                  step.id
                )}
              </span>
              <span className="absolute top-10 text-xs text-center w-20 -ml-10">
                {step.name}
              </span>
            </div>
          </li>
        ))}
      </ol>
    </nav>
  );
};

export default WizardProgress;