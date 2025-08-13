import { useState, useRef, useEffect } from 'react';
import { XMarkIcon } from '@heroicons/react/24/outline';

const VerificationModal = ({ 
  isOpen, 
  email, 
  onVerify, 
  onResend, 
  loading = false,
  error = null 
}) => {
  const [code, setCode] = useState(['', '', '', '', '', '']);
  const [resendDisabled, setResendDisabled] = useState(true);
  const [resendCountdown, setResendCountdown] = useState(60);
  const inputRefs = useRef([]);

  useEffect(() => {
    if (isOpen) {
      // Focus first input when modal opens
      setTimeout(() => {
        inputRefs.current[0]?.focus();
      }, 100);

      // Start countdown for resend button
      setResendDisabled(true);
      setResendCountdown(60);
      const timer = setInterval(() => {
        setResendCountdown((prev) => {
          if (prev <= 1) {
            setResendDisabled(false);
            clearInterval(timer);
            return 0;
          }
          return prev - 1;
        });
      }, 1000);

      return () => clearInterval(timer);
    }
  }, [isOpen]);

  const handleChange = (index, value) => {
    // Only allow digits
    if (value && !/^\d$/.test(value)) return;

    const newCode = [...code];
    newCode[index] = value;
    setCode(newCode);

    // Auto-advance to next input
    if (value && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }

    // Auto-submit when all digits entered
    if (value && index === 5 && newCode.every(digit => digit)) {
      handleSubmit(newCode.join(''));
    }
  };

  const handleKeyDown = (index, e) => {
    // Handle backspace
    if (e.key === 'Backspace' && !code[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }

    // Handle paste
    if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      handlePaste(e);
    }
  };

  const handlePaste = async (e) => {
    e.preventDefault();
    const text = await navigator.clipboard.readText();
    const digits = text.replace(/\D/g, '').slice(0, 6);
    
    if (digits.length > 0) {
      const newCode = digits.split('').concat(Array(6 - digits.length).fill(''));
      setCode(newCode);
      
      // Focus last filled input or last input if all filled
      const lastFilledIndex = Math.min(digits.length - 1, 5);
      inputRefs.current[lastFilledIndex]?.focus();
      
      // Auto-submit if complete
      if (digits.length === 6) {
        handleSubmit(digits);
      }
    }
  };

  const handleSubmit = (codeString = null) => {
    const verificationCode = codeString || code.join('');
    if (verificationCode.length === 6) {
      onVerify(verificationCode);
    }
  };

  const handleResend = async () => {
    setCode(['', '', '', '', '', '']);
    setResendDisabled(true);
    setResendCountdown(60);
    await onResend();
    
    // Restart countdown
    const timer = setInterval(() => {
      setResendCountdown((prev) => {
        if (prev <= 1) {
          setResendDisabled(false);
          clearInterval(timer);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg max-w-md w-full p-6 relative">
        {/* Header */}
        <div className="mb-6">
          <h2 className="text-2xl font-bold text-gray-900 mb-2">
            Verify Your Email
          </h2>
          <p className="text-gray-600">
            We sent a verification code to <strong>{email}</strong>
          </p>
        </div>

        {/* Code Input */}
        <div className="mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-3">
            Enter 6-digit code
          </label>
          <div className="flex gap-2 justify-center">
            {code.map((digit, index) => (
              <input
                key={index}
                ref={el => inputRefs.current[index] = el}
                type="text"
                inputMode="numeric"
                maxLength="1"
                value={digit}
                onChange={(e) => handleChange(index, e.target.value)}
                onKeyDown={(e) => handleKeyDown(index, e)}
                onPaste={handlePaste}
                disabled={loading}
                className="w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-indigo-500 focus:outline-none disabled:bg-gray-100"
                autoComplete="off"
              />
            ))}
          </div>
        </div>

        {/* Error Message */}
        {error && (
          <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <p className="text-sm text-red-600">{error}</p>
          </div>
        )}

        {/* Info Message */}
        <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
          <p className="text-sm text-blue-800">
            Didn't receive the code? Check your spam folder or wait {resendCountdown > 0 ? `${resendCountdown}s to` : ''} 
            {' '}
            <button
              onClick={handleResend}
              disabled={resendDisabled || loading}
              className={`font-semibold ${
                resendDisabled || loading
                  ? 'text-gray-400 cursor-not-allowed'
                  : 'text-blue-600 hover:text-blue-800 cursor-pointer'
              }`}
            >
              resend code
            </button>
          </p>
        </div>

        {/* Submit Button */}
        <button
          onClick={() => handleSubmit()}
          disabled={loading || code.some(d => !d)}
          className="w-full bg-indigo-600 text-white py-3 px-4 rounded-md font-semibold hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
        >
          {loading ? 'Verifying...' : 'Verify Email'}
        </button>

        {/* Note */}
        <p className="mt-4 text-xs text-gray-500 text-center">
          This code expires in 10 minutes
        </p>
      </div>
    </div>
  );
};

export default VerificationModal;