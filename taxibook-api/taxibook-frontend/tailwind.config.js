/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'luxury': {
          'gold': '#C9A961',
          'gold-light': '#D4C48E',
          'gold-dark': '#A08A4F',
          'black': '#060606',
          'charcoal': '#0D0D0D',
          'gray': '#161616',
          'slate': '#1E1E1E',
          'graphite': '#262626',
          'ash': '#333333',
          'muted': '#6B6B6B',
          'silver': '#9A9A9A',
          'light-gray': '#F0EDE8',
          'cream': '#E8E3DB',
          'white': '#F5F2ED',
        }
      },
      fontFamily: {
        'display': ['Cormorant Garamond', 'serif'],
        'sans': ['Inter', 'system-ui', 'sans-serif'],
        'ui': ['Outfit', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        'hero': '4.5rem',
        'display': '3rem',
      },
      letterSpacing: {
        'luxury': '0.15em',
        'wide': '0.05em',
      },
      animation: {
        'fade-in': 'fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1)',
        'fadeIn': 'fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1)',
        'slide-up': 'slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
        'smooth-bounce': 'smoothBounce 2s infinite',
        'gold-pulse': 'goldPulse 2s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        smoothBounce: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        goldPulse: {
          '0%, 100%': { boxShadow: '0 0 0 0 rgba(201, 169, 97, 0.2)' },
          '50%': { boxShadow: '0 0 20px 4px rgba(201, 169, 97, 0.15)' },
        },
      },
      boxShadow: {
        'luxury': '0 4px 24px rgba(0,0,0,0.4), 0 0 0 1px rgba(201,169,97,0.06)',
        'luxury-lg': '0 12px 48px rgba(0,0,0,0.5), 0 0 0 1px rgba(201,169,97,0.08)',
        'luxury-glow': '0 0 30px rgba(201,169,97,0.12), 0 4px 20px rgba(0,0,0,0.3)',
      },
    },
  },
  plugins: [],
}
