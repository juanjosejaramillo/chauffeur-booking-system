import { useState, useEffect, useRef } from 'react';
import useBookingStore from '../../../store/bookingStore';
import VerificationModalLuxury from '../VerificationModalLuxury';

const CustomerInfoLuxury = () => {
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
      <div className="max-w-3xl mx-auto">
        {/* Header */}
        <div className="text-center mb-12">
          <h2 className="font-display text-3xl text-luxury-black mb-4">
            Your Information
          </h2>
          <p className="text-luxury-gray/60 text-sm tracking-wide">
            Please provide your contact details for the booking
          </p>
        </div>

        <form ref={formRef} onSubmit={handleSubmit} className="space-y-8">
          {/* Name Fields */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="relative">
              <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
                First Name
              </label>
              <input
                type="text"
                name="firstName"
                value={customerInfo.firstName}
                onChange={handleInputChange}
                className="input-luxury text-lg"
                required
              />
            </div>
            
            <div className="relative">
              <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
                Last Name
              </label>
              <input
                type="text"
                name="lastName"
                value={customerInfo.lastName}
                onChange={handleInputChange}
                className="input-luxury text-lg"
                required
              />
            </div>
          </div>
          
          {/* Email Field */}
          <div className="relative">
            <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
              Email Address
            </label>
            <div className="relative">
              <input
                type="email"
                name="email"
                value={customerInfo.email}
                onChange={handleInputChange}
                className="input-luxury text-lg pr-10"
                placeholder="your@email.com"
                required
              />
              {emailVerified && (
                <div className="absolute right-2 top-3">
                  <svg className="w-5 h-5 text-luxury-gold" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                  </svg>
                </div>
              )}
            </div>
            <p className="text-xs text-luxury-gray/50 mt-2">
              {emailVerified 
                ? 'Email verified' 
                : "We'll verify your email before confirming the booking"}
            </p>
          </div>
          
          {/* Phone Field */}
          <div className="relative">
            <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
              Phone Number
            </label>
            <input
              type="tel"
              name="phone"
              value={customerInfo.phone}
              onChange={handleInputChange}
              className="input-luxury text-lg"
              placeholder="+1 (555) 123-4567"
              required
            />
            <p className="text-xs text-luxury-gray/50 mt-2">
              Your chauffeur may contact you on this number
            </p>
          </div>
          
          {/* Special Instructions */}
          <div className="relative">
            <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
              Special Requests <span className="text-luxury-gray/40 normal-case tracking-normal">(Optional)</span>
            </label>
            <textarea
              name="specialInstructions"
              value={customerInfo.specialInstructions}
              onChange={handleInputChange}
              rows={4}
              className="w-full px-0 py-3 bg-transparent border-b-2 border-luxury-gray/20
                       text-luxury-charcoal placeholder-luxury-gray/50
                       transition-all duration-300
                       focus:border-luxury-gold focus:outline-none resize-none"
              placeholder="Any special requests or preferences for your journey..."
              maxLength={500}
            />
            <p className="text-xs text-luxury-gray/40 mt-2 text-right">
              {customerInfo.specialInstructions.length}/500
            </p>
          </div>
          
          {/* Error Message */}
          {localError && (
            <div className="bg-red-50 border-l-4 border-red-500 p-4 animate-fade-in">
              <p className="text-sm text-red-700">{localError}</p>
            </div>
          )}
          
          {/* Privacy Notice */}
          <div className="bg-luxury-light-gray p-6 border-l-4 border-luxury-gold">
            <h3 className="text-xs font-semibold text-luxury-black uppercase tracking-luxury mb-3">
              Privacy & Data Protection
            </h3>
            <p className="text-xs text-luxury-gray/70 leading-relaxed">
              Your personal information is handled with the utmost care and discretion. 
              We maintain strict confidentiality and only use your data for booking purposes. 
              Your information is never shared with third parties without explicit consent.
            </p>
          </div>
          
          {/* Action Buttons */}
          <div className="flex gap-4 pt-4">
            <button
              type="button"
              onClick={prevStep}
              className="flex-1 btn-luxury-outline uppercase tracking-luxury text-sm"
            >
              Back
            </button>
            <button
              type="submit"
              disabled={loading}
              className="flex-1 btn-luxury-gold uppercase tracking-luxury text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Processing...' : 'Continue to Review'}
            </button>
          </div>
        </form>
      </div>

      {/* Verification Modal */}
      <VerificationModalLuxury
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

export default CustomerInfoLuxury;