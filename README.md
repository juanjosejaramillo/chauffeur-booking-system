# TaxiBook MVP System

A complete taxi booking platform with Laravel API backend, React frontend, and Filament admin panel.

## System Architecture

- **Backend**: Laravel 12 API with RESTful endpoints
- **Frontend**: React SPA with Vite
- **Admin Panel**: Filament 3.x
- **Database**: MySQL 8.0
- **Payments**: Stripe Payment Intents with manual capture
- **Maps**: Mapbox for geocoding and routing
- **Authentication**: Laravel Sanctum for SPA authentication

## Features

### Customer Features
- 5-step booking wizard
- Real-time route validation
- Dynamic vehicle pricing
- Multiple vehicle types with tiered pricing
- Secure payment authorization (captured after trip)
- Booking confirmation emails

### Admin Features
- Dashboard with real-time statistics
- Booking management
- Payment capture/refund
- Vehicle type and pricing configuration
- Email template management

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0
- Stripe account
- Mapbox account

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
- Mapbox API key (MAPBOX_API_KEY)
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
- VITE_MAPBOX_TOKEN (your Mapbox public token)

5. Start the development server:
```bash
npm run dev
```

The React app will be available at `http://localhost:5173`

## Usage

### Admin Access

Access the admin panel at `http://localhost:8000/admin`

Default credentials:
- Email: admin@taxibook.com
- Password: password

### Booking Flow

1. **Trip Details**: Enter pickup/dropoff addresses and select date/time
2. **Vehicle Selection**: Choose from available vehicles with upfront pricing
3. **Customer Info**: Provide contact details
4. **Review**: Verify all booking details
5. **Payment**: Authorize payment with credit card (not charged immediately)

### Payment Processing

- **Authorization**: Card is authorized when booking is created (within 7 days of trip)
- **Capture**: Admin captures payment after trip completion
- **Refunds**: Can be processed through admin panel

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
- `POST /api/bookings/validate-route` - Validate trip route
- `POST /api/bookings/calculate-prices` - Get vehicle prices
- `POST /api/bookings` - Create booking
- `GET /api/bookings/{bookingNumber}` - Get booking details
- `POST /api/bookings/{bookingNumber}/payment-intent` - Create payment intent
- `POST /api/bookings/{bookingNumber}/confirm-payment` - Confirm payment

### Authenticated Endpoints
- `GET /api/user` - Get current user
- `POST /api/logout` - Logout
- `GET /api/user/bookings` - Get user's bookings

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
- Production Mapbox keys
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