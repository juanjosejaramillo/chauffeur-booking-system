import { useState } from 'react';
import useBookingStore from '../../../store/bookingStore';

const CustomerInfo = () => {
  const {
    customerInfo,
    setCustomerInfo,
    nextStep,
    prevStep,
  } = useBookingStore();
  
  const [localError, setLocalError] = useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    setLocalError('');
    
    // Validate phone number format
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    if (!phoneRegex.test(customerInfo.phone)) {
      setLocalError('Please enter a valid phone number');
      return;
    }
    
    nextStep();
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setCustomerInfo({ [name]: value });
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold text-gray-900 mb-2">
          Your Information
        </h2>
        <p className="text-sm text-gray-600">
          Please provide your contact details for the booking
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              First Name *
            </label>
            <input
              type="text"
              name="firstName"
              value={customerInfo.firstName}
              onChange={handleInputChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Last Name *
            </label>
            <input
              type="text"
              name="lastName"
              value={customerInfo.lastName}
              onChange={handleInputChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              required
            />
          </div>
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Email Address *
          </label>
          <input
            type="email"
            name="email"
            value={customerInfo.email}
            onChange={handleInputChange}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="you@example.com"
            required
          />
          <p className="text-xs text-gray-500 mt-1">
            We'll send your booking confirmation to this email
          </p>
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Phone Number *
          </label>
          <input
            type="tel"
            name="phone"
            value={customerInfo.phone}
            onChange={handleInputChange}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="+1 (555) 123-4567"
            required
          />
          <p className="text-xs text-gray-500 mt-1">
            Your driver may contact you on this number
          </p>
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Special Instructions (Optional)
          </label>
          <textarea
            name="specialInstructions"
            value={customerInfo.specialInstructions}
            onChange={handleInputChange}
            rows={3}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="Any special requests or instructions for your driver..."
            maxLength={500}
          />
          <p className="text-xs text-gray-500 mt-1">
            {customerInfo.specialInstructions.length}/500 characters
          </p>
        </div>
        
        {localError && (
          <div className="p-3 bg-red-50 border border-red-200 rounded-md">
            <p className="text-sm text-red-600">{localError}</p>
          </div>
        )}
        
        <div className="bg-blue-50 border border-blue-200 rounded-md p-4">
          <h3 className="text-sm font-semibold text-blue-900 mb-2">
            Privacy & Data Protection
          </h3>
          <p className="text-xs text-blue-700">
            Your personal information is securely stored and only used for booking purposes.
            We never share your data with third parties without your consent.
          </p>
        </div>
        
        <div className="flex space-x-4">
          <button
            type="button"
            onClick={prevStep}
            className="flex-1 bg-gray-200 text-gray-700 py-3 px-4 rounded-md hover:bg-gray-300 transition-colors"
          >
            Back
          </button>
          <button
            type="submit"
            className="flex-1 bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 transition-colors"
          >
            Review Booking
          </button>
        </div>
      </form>
    </div>
  );
};

export default CustomerInfo;