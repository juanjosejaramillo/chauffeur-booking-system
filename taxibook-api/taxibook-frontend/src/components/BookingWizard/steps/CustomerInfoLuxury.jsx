import { useState, useEffect, useRef } from 'react';
import useBookingStore from '../../../store/bookingStore';
import VerificationModalLuxury from '../VerificationModalLuxury';
import { useSettings } from '../../../hooks/useSettings';
import { ClarityTracking } from '../../../services/clarityTracking';

const CustomerInfoLuxury = () => {
  const {
    customerInfo,
    setCustomerInfo,
    tripDetails,
    nextStep,
    prevStep,
    emailVerified,
    verificationError,
    loading,
    sendVerificationCode,
    verifyEmailCode,
    resendVerificationCode,
  } = useBookingStore();
  
  const { settings } = useSettings();
  const formFields = settings?.form_fields || [];
  
  const [localError, setLocalError] = useState('');
  const [showVerificationModal, setShowVerificationModal] = useState(false);
  const formRef = useRef(null);
  
  // Check if either pickup or dropoff is an airport
  const isAirport = tripDetails.isAirportPickup || tripDetails.isAirportDropoff;

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
      ClarityTracking.trackError('customer_info', 'validation', 'Invalid phone number');
      return;
    }
    
    if (!currentInfo.lastName) {
      setLocalError('Please enter your last name');
      ClarityTracking.trackError('customer_info', 'validation', 'Missing last name');
      return;
    }
    
    // If email not verified, send verification code and show modal
    if (!emailVerified) {
      try {
        ClarityTracking.trackEmailVerification('requested');
        await sendVerificationCode();
        setShowVerificationModal(true);
      } catch (error) {
        setLocalError('Failed to send verification code. Please try again.');
        ClarityTracking.trackEmailVerification('failed');
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
      ClarityTracking.trackEmailVerification('verified');
      
      // Identify user with email hash for better tracking
      if (customerInfo.email) {
        const emailHash = btoa(customerInfo.email.toLowerCase()).slice(0, 10);
        ClarityTracking.identify(emailHash);
      }
      
      setShowVerificationModal(false);
      nextStep();
    } catch (error) {
      // Error is handled in the store and displayed in the modal
      ClarityTracking.trackEmailVerification('failed');
    }
  };

  const handleResendCode = async () => {
    try {
      ClarityTracking.event('email_verification_resend');
      await resendVerificationCode();
    } catch (error) {
      // Error is handled in the store and displayed in the modal
    }
  };

  const handleChangeEmail = () => {
    // Track wrong email clicks
    ClarityTracking.event('email_change_requested');
    
    // Close the verification modal to allow user to edit email
    setShowVerificationModal(false);
    // Clear any verification errors
    resetVerification();
    // Focus on email field
    setTimeout(() => {
      const emailField = document.querySelector('input[name="email"]');
      if (emailField) {
        emailField.focus();
        emailField.select();
      }
    }, 100);
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setCustomerInfo({ [name]: value });
    
    // Track form field interactions
    ClarityTracking.event(`customer_info_${name}_interaction`);
    
    // Reset email verification if email changes
    if (name === 'email' && emailVerified) {
      useBookingStore.setState({ emailVerified: false });
    }
  };
  
  const handleAdditionalFieldChange = (key, value) => {
    setCustomerInfo({
      additionalFields: {
        ...customerInfo.additionalFields,
        [key]: value
      }
    });
  };
  
  // Check if a field should be shown based on conditions
  const shouldShowField = (field) => {
    if (!field.conditions || field.conditions.length === 0) {
      return true;
    }
    
    const context = {
      is_airport: isAirport,
      is_airport_pickup: tripDetails.isAirportPickup,
      is_airport_dropoff: tripDetails.isAirportDropoff,
    };
    
    return field.conditions.every(condition => {
      const contextValue = context[condition.field];
      // Handle boolean values that might come as boolean or string
      let conditionValue = condition.value;
      if (condition.value === 'true' || condition.value === true) {
        conditionValue = true;
      } else if (condition.value === 'false' || condition.value === false) {
        conditionValue = false;
      }
      
      switch (condition.operator) {
        case '==':
          return contextValue == conditionValue;
        case '!=':
          return contextValue != conditionValue;
        default:
          return true;
      }
    });
  };
  
  // Render a dynamic field
  const renderDynamicField = (field) => {
    if (!field) {
      return null;
    }
    
    if (!shouldShowField(field)) {
      return null;
    }
    
    const value = field.key === 'flight_number' 
      ? customerInfo.flightNumber || ''
      : (customerInfo.additionalFields && customerInfo.additionalFields[field.key]) || '';
    
    switch (field.type) {
      case 'text':
      case 'number':
      case 'email':
      case 'tel':
        return (
          <div key={field.key} className="relative">
            <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
              {field.label} {!field.required && <span className="text-luxury-gray/40 normal-case tracking-normal">(Optional)</span>}
            </label>
            <input
              type={field.type}
              name={field.key}
              value={value}
              onChange={(e) => {
                if (field.key === 'flight_number') {
                  setCustomerInfo({ flightNumber: e.target.value });
                } else {
                  handleAdditionalFieldChange(field.key, e.target.value);
                }
              }}
              className="input-luxury text-lg"
              placeholder={field.placeholder}
              required={field.required}
              min={field.validation_rules?.min}
              max={field.validation_rules?.max}
            />
            {field.helper_text && (
              <p className="text-xs text-luxury-gray/50 mt-2">{field.helper_text}</p>
            )}
          </div>
        );
      
      case 'select':
        return (
          <div key={field.key} className="relative">
            <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
              {field.label} {!field.required && <span className="text-luxury-gray/40 normal-case tracking-normal">(Optional)</span>}
            </label>
            <select
              name={field.key}
              value={value}
              onChange={(e) => handleAdditionalFieldChange(field.key, e.target.value)}
              className="input-luxury text-lg"
              required={field.required}
            >
              <option value="">Select...</option>
              {field.options?.map(option => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
            {field.helper_text && (
              <p className="text-xs text-luxury-gray/50 mt-2">{field.helper_text}</p>
            )}
          </div>
        );
      
      case 'checkbox':
        return (
          <div key={field.key} className="relative">
            <label className="flex items-center gap-3 cursor-pointer">
              <input
                type="checkbox"
                name={field.key}
                checked={value === true || value === 'true'}
                onChange={(e) => handleAdditionalFieldChange(field.key, e.target.checked)}
                className="w-5 h-5 text-luxury-gold border-luxury-gray/20 rounded focus:ring-luxury-gold"
              />
              <span className="text-sm text-luxury-black">
                {field.label}
              </span>
            </label>
            {field.helper_text && (
              <p className="text-xs text-luxury-gray/50 mt-2 ml-8">{field.helper_text}</p>
            )}
          </div>
        );
      
      case 'textarea':
        return (
          <div key={field.key} className="relative">
            <label className="block text-xs font-medium text-luxury-gold uppercase tracking-luxury mb-3">
              {field.label} {!field.required && <span className="text-luxury-gray/40 normal-case tracking-normal">(Optional)</span>}
            </label>
            <textarea
              name={field.key}
              value={value}
              onChange={(e) => handleAdditionalFieldChange(field.key, e.target.value)}
              rows={3}
              className="w-full px-0 py-3 bg-transparent border-b-2 border-luxury-gray/20
                       text-luxury-charcoal placeholder-luxury-gray/50
                       transition-all duration-300
                       focus:border-luxury-gold focus:outline-none resize-none"
              placeholder={field.placeholder}
              required={field.required}
            />
            {field.helper_text && (
              <p className="text-xs text-luxury-gray/50 mt-2">{field.helper_text}</p>
            )}
          </div>
        );
      
      default:
        return null;
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
          
          {/* Dynamic Form Fields */}
          {formFields.map(field => renderDynamicField(field))}
          
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
          
          {/* Action Buttons */}
          <div className="flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 pt-4">
            <button
              type="button"
              onClick={prevStep}
              className="w-full sm:flex-1 px-4 py-3 border-2 border-luxury-black text-luxury-black font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-black hover:text-luxury-white hover:shadow-luxury active:scale-[0.98] uppercase text-xs sm:text-sm order-2 sm:order-1"
            >
              Back
            </button>
            <button
              type="submit"
              disabled={loading}
              className="w-full sm:flex-1 px-4 py-3 bg-luxury-gold text-luxury-white font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-gold-dark hover:shadow-luxury active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed uppercase text-xs sm:text-sm order-1 sm:order-2"
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
        onChangeEmail={handleChangeEmail}
        loading={loading}
        error={verificationError}
      />
    </>
  );
};

export default CustomerInfoLuxury;