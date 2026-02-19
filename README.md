# LuxRide Chauffeur Booking System

A premium chauffeur booking platform with Laravel API backend, React frontend, and Filament admin panel.

## System Architecture

- **Backend**: Laravel 11.x API with RESTful endpoints
- **Frontend**: React 19.x SPA with Vite 7.x
- **Admin Panel**: Filament 3.x
- **Database**: MySQL 8.0
- **Payments**: Stripe Payment Intents (immediate charge or save-card for post-service)
- **Maps**: Google Maps API (Places, Directions, Geocoding with traffic-aware routing)
- **Authentication**: Laravel Sanctum for API tokens
- **Analytics**: Microsoft Clarity for session recordings and heatmaps

## Features

### Customer Features
- 6-step booking wizard (Route, Vehicle, Info, Review, Payment, Confirmation)
- Real-time route validation with traffic-aware pricing
- Dynamic vehicle pricing with tiered distance rates
- Multiple vehicle types with images and capacity info
- Hourly booking support
- Two payment modes: immediate charge or save card for post-service billing
- Email verification before payment
- Booking confirmation emails with PDF attachments
- Gratuity/tip system via QR code or link

### Admin Features
- Analytics dashboard with revenue trends, booking charts, and date filtering
- Net profit tracking with expense management
- Next Up widget showing upcoming confirmed bookings
- Booking management with expense tracking per booking
- Payment capture/refund
- Vehicle type and pricing configuration
- Email template management with PDF attachment controls
- Configurable legal document URLs

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0
- Stripe account
- Google Cloud account (Maps, Places, Directions APIs)

### Backend Setup

1. Navigate to the API directory:
```bash
cd taxibook-api
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Configure your `.env` file with:
- Database credentials
- Stripe keys (STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET)
- Google Maps API key (GOOGLE_MAPS_API_KEY)
- Mail settings

5. Generate application key:
```bash
php artisan key:generate
```

6. Run migrations:
```bash
php artisan migrate
```

7. Seed the database:
```bash
php artisan db:seed
```

8. Start the development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### Frontend Setup

1. Navigate to the frontend directory:
```bash
cd taxibook-frontend
```

2. Install dependencies:
```bash
npm install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Configure your `.env` file with:
- VITE_API_BASE_URL=http://localhost:8000
- VITE_STRIPE_PUBLIC_KEY (your Stripe publishable key)
- VITE_GOOGLE_MAPS_API_KEY (your Google Maps API key)

5. Start the development server:
```bash
npm run dev
```

The React app will be available at `http://localhost:5173`

## Usage

### Admin Access

Access the admin panel at `http://localhost:8000/admin`

Default credentials:
- Email: admin@luxridesuv.com
- Password: password

### Booking Flow

1. **Trip Details**: Enter pickup/dropoff addresses with Google Places autocomplete and select date/time
2. **Vehicle Selection**: Choose from available vehicles with traffic-aware upfront pricing
3. **Customer Info**: Provide contact details with email verification
4. **Review**: Verify all booking details
5. **Payment**: Pay immediately or save card for post-service billing
6. **Confirmation**: Booking summary with receipt

### Payment Processing

- **Immediate Payment**: Card is charged when booking is confirmed
- **Save Card Mode**: Card is saved via Stripe Setup Intent; charged after service
- **Capture**: Admin captures payment after trip completion
- **Refunds**: Full or partial refunds through admin panel
- **Gratuity**: Tips can be added at booking or after via QR code/link

## Pricing Configuration

Each vehicle type has:
- Base fare (covers initial miles)
- Progressive tiered pricing for distance
- Per-minute rate for time
- Service fee multiplier
- Optional tax calculation
- Minimum fare enforcement

## Queue Processing

Run the queue worker for background jobs:
```bash
php artisan queue:work
```

For production, set up a cron job:
```bash
* * * * * php /path-to-project/artisan schedule:run
```

## API Endpoints

### Public Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `GET /api/settings/public` - Public settings (Stripe key, Google Maps key, etc.)
- `POST /api/bookings/validate-route` - Validate trip route
- `POST /api/bookings/calculate-prices` - Get vehicle prices
- `POST /api/bookings/search-addresses` - Google Places autocomplete
- `POST /api/bookings/place-details` - Google Place details
- `POST /api/bookings/send-verification` - Send email verification code
- `POST /api/bookings/verify-email` - Verify email code
- `POST /api/bookings/resend-verification` - Resend verification code
- `GET /api/bookings/payment-mode` - Get payment mode setting
- `POST /api/bookings` - Create booking
- `GET /api/bookings/{bookingNumber}` - Get booking details
- `POST /api/bookings/{bookingNumber}/process-payment` - Process payment
- `POST /api/bookings/{bookingNumber}/payment-intent` - Create payment intent
- `POST /api/bookings/{bookingNumber}/confirm-payment` - Confirm payment
- `POST /api/bookings/{bookingNumber}/setup-intent` - Create setup intent (save-card mode)
- `POST /api/bookings/{bookingNumber}/complete-setup` - Complete setup intent
- `POST /api/stripe/webhook` - Stripe webhook
- `GET /api/tip/{token}` - Get booking for tip page
- `POST /api/tip/{token}/process` - Process tip payment

### Authenticated Endpoints
- `GET /api/user` - Get current user
- `POST /api/logout` - Logout
- `GET /api/user/bookings` - Get user's bookings
- `POST /api/bookings/{booking}/send-tip-link` - Send tip email
- `GET /api/bookings/{booking}/tip-qr` - Get QR code for tips

## Testing

### Test Cards (Stripe)
- Success: 4242 4242 4242 4242
- Decline: 4000 0000 0000 0002
- Requires authentication: 4000 0025 0000 3155

### Test Addresses
Use any valid US addresses for testing the booking flow.

## Deployment Considerations

### Shared Hosting
- Uses database queue driver
- File cache driver
- Optimized for shared hosting constraints

### Environment Variables
Ensure all required environment variables are set in production:
- APP_ENV=production
- APP_DEBUG=false
- Secure database credentials
- Production Stripe keys
- Production Google Maps API key
- Proper mail configuration

### Security
- Always use HTTPS in production
- Keep Stripe webhook endpoint secure
- Regularly update dependencies
- Use strong passwords for admin accounts

## Support

For issues or questions, please refer to the documentation or contact support.

## License

Proprietary - All rights reserved