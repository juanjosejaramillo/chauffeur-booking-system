import { useState, useEffect, useRef } from 'react';
import useBookingStore from '../../../store/bookingStore';
import VerificationModal from '../VerificationModal';

const CustomerInfo = () => {
  const {
    customerInfo,
    setCustomerInfo,
    nextStep,
    prevStep,
    emailVerified,
    verificationError,
    loading,
    sendVerificationCode,
    verifyEmailCode,
    resendVerificationCode,
  } = useBookingStore();
  
  const [localError, setLocalError] = useState('');
  const [showVerificationModal, setShowVerificationModal] = useState(false);
  const formRef = useRef(null);

  // Handle autofill - check form values before submit
  const syncFormWithState = () => {
    if (formRef.current) {
      const formData = new FormData(formRef.current);
      const updates = {};
      let hasChanges = false;
      
      ['firstName', 'lastName', 'email', 'phone', 'specialInstructions'].forEach(field => {
        const value = formData.get(field) || '';
        if (value !== customerInfo[field]) {
          updates[field] = value;
          hasChanges = true;
        }
      });
      
      if (hasChanges) {
        setCustomerInfo(updates);
        return updates;
      }
    }
    return null;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLocalError('');
    
    // Sync autofilled values with state
    const updates = syncFormWithState();
    const currentInfo = updates ? { ...customerInfo, ...updates } : customerInfo;
    
    console.log('Form submitted with customerInfo:', currentInfo);
    
    // Validate phone number format
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    if (!currentInfo.phone || !phoneRegex.test(currentInfo.phone)) {
      setLocalError('Please enter a valid phone number');
      return;
    }
    
    if (!currentInfo.lastName) {
      setLocalError('Please enter your last name');
      return;
    }
    
    // If email not verified, send verification code and show modal
    if (!emailVerified) {
      try {
        await sendVerificationCode();
        setShowVerificationModal(true);
      } catch (error) {
        setLocalError('Failed to send verification code. Please try again.');
      }
    } else {
      // Email already verified, proceed to next step
      nextStep();
    }
  };

  const handleVerifyCode = async (code) => {
    try {
      await verifyEmailCode(code);
      // If verification successful, close modal and go to next step
      setShowVerificationModal(false);
      nextStep();
    } catch (error) {
      // Error is handled in the store and displayed in the modal
    }
  };

  const handleResendCode = async () => {
    try {
      await resendVerificationCode();
    } catch (error) {
      // Error is handled in the store and displayed in the modal
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setCustomerInfo({ [name]: value });
    
    // Reset email verification if email changes
    if (name === 'email' && emailVerified) {
      useBookingStore.setState({ emailVerified: false });
    }
  };

  return (
    <>
      <div className="space-y-6">
        <div>
          <h2 className="text-xl font-semibold text-gray-900 mb-2">
            Your Information
          </h2>
          <p className="text-sm text-gray-600">
            Please provide your contact details for the booking
          </p>
        </div>

        <form ref={formRef} onSubmit={handleSubmit} className="space-y-4">
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
            <div className="relative">
              <input
                type="email"
                name="email"
                value={customerInfo.email}
                onChange={handleInputChange}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="you@example.com"
                required
              />
              {emailVerified && (
                <div className="absolute right-2 top-2.5">
                  <svg className="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                </div>
              )}
            </div>
            <p className="text-xs text-gray-500 mt-1">
              {emailVerified 
                ? 'Email verified ✓' 
                : "We'll verify your email before confirming the booking"}
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
              disabled={loading}
              className="flex-1 bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              {loading ? 'Processing...' : 'Continue to Review'}
            </button>
          </div>
        </form>
      </div>

      {/* Verification Modal */}
      <VerificationModal
        isOpen={showVerificationModal}
        email={customerInfo.email}
        onVerify={handleVerifyCode}
        onResend={handleResendCode}
        loading={loading}
        error={verificationError}
      />
    </>
  );
};

export default CustomerInfo;