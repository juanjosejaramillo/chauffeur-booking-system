import { Link } from 'react-router-dom';
import { TruckIcon, ShieldCheckIcon, ClockIcon, CreditCardIcon } from '@heroicons/react/24/outline';

const HomePage = () => {
  return (
    <div className="bg-white">
      {/* Hero Section */}
      <div className="relative bg-indigo-800">
        <div className="absolute inset-0">
          <img
            className="w-full h-full object-cover opacity-30"
            src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?ixlib=rb-4.0.3"
            alt="Taxi"
          />
        </div>
        <div className="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
          <h1 className="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
            TaxiBook
          </h1>
          <p className="mt-6 text-xl text-indigo-100 max-w-3xl">
            Premium taxi service at your fingertips. Book your ride in advance with transparent pricing and reliable service.
          </p>
          <div className="mt-10">
            <Link
              to="/book"
              className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 transition-colors"
            >
              Book Your Ride Now
            </Link>
          </div>
        </div>
      </div>

      {/* Features Section */}
      <div className="py-16 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h2 className="text-3xl font-extrabold text-gray-900">
              Why Choose TaxiBook?
            </h2>
            <p className="mt-4 text-lg text-gray-600">
              Experience the difference with our premium taxi booking service
            </p>
          </div>

          <div className="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div className="text-center">
              <div className="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mx-auto">
                <TruckIcon className="h-6 w-6" />
              </div>
              <h3 className="mt-4 text-lg font-medium text-gray-900">Multiple Vehicle Options</h3>
              <p className="mt-2 text-sm text-gray-600">
                Choose from economy to luxury vehicles based on your needs
              </p>
            </div>

            <div className="text-center">
              <div className="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mx-auto">
                <ShieldCheckIcon className="h-6 w-6" />
              </div>
              <h3 className="mt-4 text-lg font-medium text-gray-900">Safe & Secure</h3>
              <p className="mt-2 text-sm text-gray-600">
                All drivers are vetted and your payment information is secure
              </p>
            </div>

            <div className="text-center">
              <div className="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mx-auto">
                <ClockIcon className="h-6 w-6" />
              </div>
              <h3 className="mt-4 text-lg font-medium text-gray-900">Advance Booking</h3>
              <p className="mt-2 text-sm text-gray-600">
                Book your rides up to 30 days in advance with guaranteed availability
              </p>
            </div>

            <div className="text-center">
              <div className="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 text-white mx-auto">
                <CreditCardIcon className="h-6 w-6" />
              </div>
              <h3 className="mt-4 text-lg font-medium text-gray-900">Transparent Pricing</h3>
              <p className="mt-2 text-sm text-gray-600">
                Know your fare upfront with no hidden charges
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* How It Works Section */}
      <div className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h2 className="text-3xl font-extrabold text-gray-900">
              How It Works
            </h2>
            <p className="mt-4 text-lg text-gray-600">
              Book your ride in 5 simple steps
            </p>
          </div>

          <div className="mt-12">
            <ol className="space-y-4 md:flex md:space-y-0 md:space-x-4">
              {[
                { step: 1, title: 'Enter Trip Details', description: 'Provide pickup and dropoff locations with date and time' },
                { step: 2, title: 'Select Vehicle', description: 'Choose from available vehicles with upfront pricing' },
                { step: 3, title: 'Enter Your Info', description: 'Provide contact details for booking confirmation' },
                { step: 4, title: 'Review Booking', description: 'Check all details before proceeding to payment' },
                { step: 5, title: 'Secure Payment', description: 'Authorize payment with your card (charged after trip)' },
              ].map((item) => (
                <li key={item.step} className="flex-1">
                  <div className="flex flex-col border-l-4 border-indigo-300 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4">
                    <span className="text-sm font-medium text-indigo-600">
                      Step {item.step}
                    </span>
                    <span className="text-xl font-semibold">{item.title}</span>
                    <span className="mt-2 text-sm text-gray-600">
                      {item.description}
                    </span>
                  </div>
                </li>
              ))}
            </ol>
          </div>

          <div className="mt-12 text-center">
            <Link
              to="/book"
              className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
            >
              Start Booking Now
            </Link>
          </div>
        </div>
      </div>

      {/* Footer */}
      <footer className="bg-gray-800">
        <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
          <p className="text-center text-sm text-gray-400">
            © 2024 TaxiBook. All rights reserved.
          </p>
        </div>
      </footer>
    </div>
  );
};

export default HomePage;