# LuxRide Booking Frontend

## Overview
React-based booking interface for LuxRide's premium chauffeur service. This application provides a seamless, mobile-optimized booking experience with real-time pricing, secure payments, and comprehensive analytics tracking.

## Tech Stack

### Core Technologies
- **React** 19.x - UI framework
- **Vite** 7.x - Build tool and dev server
- **Tailwind CSS** 3.x - Utility-first CSS framework
- **Zustand** - State management with session persistence
- **React Router DOM** v7 - Client-side routing

### Key Integrations
- **Stripe Elements** - Secure payment processing
- **Google Maps JavaScript API** - Maps, autocomplete, and routing
- **Microsoft Clarity** - Behavioral analytics and session recordings
- **Google Tag Manager** - Marketing and conversion tracking

### UI Libraries
- **Headless UI** - Unstyled, accessible UI components
- **Heroicons** - SVG icon library
- **React Datepicker** - Date and time selection
- **React Toastify** - Toast notifications

## Features

### Booking Flow
1. **Trip Details** - Address autocomplete with Google Places, date/time selection
2. **Vehicle Selection** - Dynamic pricing based on distance and vehicle type
3. **Customer Information** - Email verification system
4. **Review Booking** - Summary and terms acceptance
5. **Payment** - Stripe integration with tip selection
6. **Confirmation** - Booking reference and details

### Key Capabilities
- **Real-time pricing** with traffic-aware calculations
- **Airport transfer detection** with special fields
- **Email verification** for booking security
- **Save payment methods** for returning customers
- **Gratuity system** with QR code support
- **Mobile-optimized** responsive design
- **Session persistence** across page refreshes
- **Browser back button** navigation support

## Analytics Integration

### Microsoft Clarity
Provides comprehensive behavioral analytics including:
- **Session Recordings** - Watch real user sessions
- **Heatmaps** - Visualize click and scroll patterns
- **Custom Events** - Track specific user actions
- **Conversion Funnels** - Analyze booking completion rates

#### Key Tracked Events
- Booking funnel progression
- Address search interactions
- Vehicle selection patterns
- Payment attempts and completions
- Error occurrences and recovery
- Device type and navigation methods

## Project Structure

```
src/
├── components/          # Reusable UI components
│   └── BookingWizard/  # Main booking flow components
│       └── steps/      # Individual booking steps
├── config/             # Configuration files
├── hooks/              # Custom React hooks
├── pages/              # Route page components
├── services/           # API and third-party services
│   ├── api.js         # Backend API integration
│   ├── clarityTracking.js  # Microsoft Clarity
│   └── googleTracking.js   # Google Analytics
├── store/              # Zustand state management
└── styles/             # Global styles and Tailwind
```

## Development Setup

### Prerequisites
- Node.js 18+ and npm
- Access to backend API (Laravel)
- Google Maps API key
- Stripe publishable key

### Environment Variables
Create a `.env` file in the root directory:

```env
# API Configuration
VITE_API_URL=http://localhost:8000/api

# Google Maps
VITE_GOOGLE_MAPS_API_KEY=your_google_maps_key

# Stripe (fetched dynamically from backend)
# Keys are managed through admin panel

# Frontend URL
VITE_FRONTEND_URL=http://localhost:5173
```

### Installation
```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run linting
npm run lint
```

## Testing Analytics

### Microsoft Clarity Testing
1. **Check Script Loading**
   ```javascript
   // In browser console
   typeof window.clarity // Should return "function"
   ```

2. **Test Events**
   ```javascript
   // Manual event test
   window.clarity('event', 'test_event')
   ```

3. **Verify in Dashboard**
   - Visit [clarity.microsoft.com](https://clarity.microsoft.com)
   - Select project (ID: t26s11c8vq)
   - Check for active sessions and events

### Debug Mode
Open browser DevTools console to see tracking logs:
- "Clarity: Event tracked - [event_name]"
- "Clarity: Tag set - [key]: [value]"
- "Clarity: User identified"

## API Integration

### Endpoints Used
- `POST /api/bookings` - Create booking
- `POST /api/bookings/calculate-prices` - Get vehicle pricing
- `POST /api/bookings/validate-route` - Validate route
- `POST /api/bookings/verify-email` - Send verification code
- `POST /api/bookings/confirm-email` - Verify code
- `GET /api/settings/public` - Get public settings
- `GET /api/vehicle-types` - Get available vehicles
- `POST /api/tip/{token}/process` - Process tip payment

### State Management
Zustand store modules:
- `bookingStore` - Manages booking flow state
- Session persistence via `sessionStorage`
- Automatic state hydration on page refresh

## Deployment

### Build Process
```bash
# Production build
npm run build

# Output directory: dist/
# Contains optimized assets ready for deployment
```

### Production Considerations
- Ensure all environment variables are set
- Google Maps API key must have proper domain restrictions
- Stripe keys are fetched from backend (no frontend config needed)
- Enable CORS on backend for frontend domain
- Configure proper CSP headers for third-party scripts

### Apache Configuration
For Apache servers, ensure `.htaccess` file includes:
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

## Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Optimizations
- Code splitting with React.lazy()
- Image optimization with Vite
- CSS purging with Tailwind
- Bundle size optimization
- Lazy loading of Google Maps
- Debounced API calls

## Security
- XSS protection via React
- CSRF tokens for API requests
- Secure payment processing via Stripe
- Email verification for bookings
- Input validation and sanitization
- Content Security Policy headers

## Troubleshooting

### Common Issues
1. **Google Maps not loading**
   - Check API key and domain restrictions
   - Verify billing is enabled on Google Cloud

2. **Stripe not initializing**
   - Confirm backend is returning correct keys
   - Check Stripe mode (test/live) in admin panel

3. **Analytics not tracking**
   - Disable ad blockers
   - Check browser console for errors
   - Verify Clarity script is loading

## Contributing
1. Create feature branch from `main`
2. Follow existing code patterns
3. Test thoroughly including analytics
4. Update documentation as needed
5. Submit pull request with description

## License
Proprietary - LuxRide SUV © 2025

## Support
- Admin Email: admin@luxridesuv.com
- Support Email: contact@luxridesuv.com
- Documentation: See CLAUDE.md in parent directory