import { useState, useEffect, useRef } from 'react';

const VerificationModalLuxury = ({ isOpen, email, onVerify, onResend, onChangeEmail, loading, error }) => {
  const [code, setCode] = useState(['', '', '', '', '', '']);
  const [resendTimer, setResendTimer] = useState(0);
  const inputRefs = useRef([]);

  useEffect(() => {
    if (isOpen) {
      // Focus first input when modal opens
      inputRefs.current[0]?.focus();
      // Start resend timer (60 seconds)
      setResendTimer(60);
    }
  }, [isOpen]);

  useEffect(() => {
    if (resendTimer > 0) {
      const timer = setTimeout(() => setResendTimer(resendTimer - 1), 1000);
      return () => clearTimeout(timer);
    }
  }, [resendTimer]);

  const handleChange = (index, value) => {
    if (value.length > 1) return; // Only allow single digit
    
    const newCode = [...code];
    newCode[index] = value;
    setCode(newCode);

    // Auto-focus next input
    if (value && index < 5) {
      inputRefs.current[index + 1]?.focus();
    }

    // Auto-submit when all digits entered
    if (index === 5 && value) {
      const fullCode = newCode.join('');
      if (fullCode.length === 6) {
        onVerify(fullCode);
      }
    }
  };

  const handleKeyDown = (index, e) => {
    if (e.key === 'Backspace' && !code[index] && index > 0) {
      inputRefs.current[index - 1]?.focus();
    }
  };

  const handlePaste = (e) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    const newCode = [...code];
    
    for (let i = 0; i < pastedData.length; i++) {
      newCode[i] = pastedData[i];
    }
    
    setCode(newCode);
    
    if (pastedData.length === 6) {
      onVerify(pastedData);
    } else if (pastedData.length > 0) {
      inputRefs.current[Math.min(pastedData.length, 5)]?.focus();
    }
  };

  const handleResend = () => {
    setCode(['', '', '', '', '', '']);
    setResendTimer(60);
    onResend();
    inputRefs.current[0]?.focus();
  };

  const handleSubmit = () => {
    const fullCode = code.join('');
    if (fullCode.length === 6) {
      onVerify(fullCode);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-luxury-black/50 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in">
      <div className="bg-luxury-white w-full max-w-md mx-4 shadow-luxury-lg animate-slide-up">
        <div className="p-8">
          {/* Header */}
          <div className="text-center mb-8">
            <h2 className="font-display text-3xl text-luxury-black mb-4">
              Verify Your Email
            </h2>
            <p className="text-luxury-gray/70 text-sm">
              We sent a verification code to
            </p>
            <p className="text-luxury-black font-medium mt-1">
              {email}
            </p>
            <button
              onClick={onChangeEmail}
              className="text-xs text-luxury-gold hover:text-luxury-gold-dark transition-colors mt-2 underline"
            >
              Wrong email? Click here to change it
            </button>
          </div>

          {/* Code Input */}
          <div className="mb-8">
            <p className="text-xs text-luxury-gold uppercase tracking-luxury text-center mb-6">
              Enter 6-digit code
            </p>
            <div className="flex justify-center gap-2">
              {code.map((digit, index) => (
                <input
                  key={index}
                  ref={(el) => (inputRefs.current[index] = el)}
                  type="text"
                  inputMode="numeric"
                  pattern="[0-9]"
                  maxLength="1"
                  value={digit}
                  onChange={(e) => handleChange(index, e.target.value)}
                  onKeyDown={(e) => handleKeyDown(index, e)}
                  onPaste={index === 0 ? handlePaste : undefined}
                  className={`w-12 h-14 text-center text-xl font-light border-2 transition-all duration-200
                    ${digit ? 'border-luxury-gold bg-luxury-light-gray' : 'border-luxury-gray/20 bg-transparent'}
                    focus:border-luxury-gold focus:outline-none focus:bg-luxury-light-gray`}
                  disabled={loading}
                />
              ))}
            </div>
          </div>

          {/* Error Message */}
          {error && (
            <div className="mb-6 p-4 bg-red-50 border-l-4 border-red-500">
              <p className="text-sm text-red-700">{error}</p>
            </div>
          )}

          {/* Resend Section */}
          <div className="text-center mb-8">
            {resendTimer > 0 ? (
              <p className="text-sm text-luxury-gray/60">
                Didn't receive the code? Check your spam folder or wait{' '}
                <span className="text-luxury-black font-medium">{resendTimer}s</span> to{' '}
                <span className="text-luxury-gray/40">resend code</span>
              </p>
            ) : (
              <button
                onClick={handleResend}
                disabled={loading}
                className="text-sm text-luxury-gold hover:text-luxury-gold-dark transition-colors disabled:opacity-50"
              >
                Resend verification code
              </button>
            )}
          </div>

          {/* Submit Button */}
          <button
            onClick={handleSubmit}
            disabled={loading || code.join('').length !== 6}
            className="w-full px-4 py-3 sm:py-4 bg-luxury-gold text-luxury-white font-medium tracking-wide transition-all duration-300 ease-out hover:bg-luxury-gold-dark hover:shadow-luxury active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed uppercase text-xs sm:text-sm"
          >
            {loading ? 'Verifying...' : 'Verify Email'}
          </button>

          {/* Footer */}
          <p className="text-center text-xs text-luxury-gray/50 mt-6">
            This code expires in 10 minutes
          </p>
        </div>
      </div>
    </div>
  );
};

export default VerificationModalLuxury;