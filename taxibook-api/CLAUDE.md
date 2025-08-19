# LuxRide Chauffeur Booking System

## Project Overview
LuxRide is a premium chauffeur booking system for luxury transportation services. It provides a complete solution for managing bookings, payments, drivers, and customer communications with a focus on high-end service delivery.

## Tech Stack

### Backend
- **Framework**: Laravel 11.x
- **PHP Version**: 8.2+
- **Admin Panel**: Filament 3.x
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum (API tokens)
- **Queue**: Database driver
- **Cache**: File driver (for shared hosting compatibility)
- **Session**: Database driver

### Frontend
- **Framework**: React 19.x with Vite
- **State Management**: Zustand
- **Routing**: React Router DOM v7
- **Styling**: Tailwind CSS 3.x
- **UI Components**: Headless UI, Heroicons
- **Date Picker**: React Datepicker
- **Build Tool**: Vite 7.x

### Third-Party Integrations
- **Payment Processing**: Stripe API (v17.5)
  - Support for test/live mode switching
  - Payment intents API
  - Saved cards functionality
  - Gratuity/tips system
- **Maps & Geocoding**: Mapbox GL JS (v3.14)
  - Route calculation
  - Distance/duration estimation
  - Address autocomplete
- **Email**: SMTP (Gmail configured)
- **PDF Generation**: DomPDF (receipts)
- **QR Codes**: Simple QRCode (tip links)

## Project Structure

```
/taxibook-api/                 # Laravel Backend
├── app/
│   ├── Filament/             # Admin panel resources
│   │   ├── Resources/        # CRUD interfaces
│   │   └── Pages/            # Custom pages (Settings)
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/          # API controllers
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   └── Helpers/              # Helper functions
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # Data seeders
├── routes/
│   ├── api.php              # API routes
│   └── web.php              # Web routes
└── config/                   # Configuration files

/taxibook-frontend/           # React Frontend
├── src/
│   ├── components/          # Reusable components
│   ├── pages/              # Page components
│   ├── services/           # API services
│   ├── store/              # Zustand stores
│   └── hooks/              # Custom React hooks
```

## Key Models & Relationships

### Core Models
1. **Booking** - Central booking entity
   - Has one User (optional)
   - Belongs to VehicleType
   - Has many Transactions
   - Stores dynamic form data in `additional_data` JSON field

2. **User** - Customer accounts
   - Has many Bookings
   - Stores saved Stripe customer ID

3. **VehicleType** - Vehicle categories
   - Has many VehiclePricingTiers
   - Has many Bookings
   - Configurable pricing per distance

4. **EmailTemplate** - Dynamic email templates
   - Supports HTML/WYSIWYG/Blade formats
   - Trigger-based sending
   - Variable replacement system

5. **BookingFormField** - Dynamic form configuration
   - Defines custom booking form fields
   - Multiple field types (text, select, checkbox, etc.)
   - Conditional visibility rules

6. **Setting** - System configuration
   - Key-value store for system settings
   - Managed via Filament admin panel
   - Overrides .env configurations

7. **EmailLog** - Email audit trail
   - Tracks all sent emails
   - Links to bookings and users
   - Stores status and metadata

8. **Transaction** - Payment records
   - Belongs to Booking
   - Tracks Stripe transactions
   - Handles refunds

## Key Features

### 1. Dynamic Booking System
- Multi-step booking form with mobile-optimized UI
- Real-time route calculation
- Dynamic pricing based on distance/vehicle type
- Airport transfer detection
- Custom form fields (flight numbers, child seats, etc.)
- Email verification before payment with change email option
- Smart date/time selection with minimum booking hours
- Browser back button navigation support
- Session persistence using Zustand with sessionStorage

### 2. Payment Processing
- Stripe integration with test/live mode switching
- Separate card input fields (number, expiry, CVV, postal) for better mobile UX
- Payment intents for secure processing
- Saved cards functionality
- Gratuity/tips system with QR codes and responsive tip buttons
- Refund management
- Webhook support for payment confirmations

### 3. Email System
- Template-based email system
- WYSIWYG editor for admin
- Variable replacement ({{customer_name}}, etc.)
- Trigger-based sending (booking confirmed, 24hr reminder, etc.)
- HTML email support with luxe design
- Attachment support (receipts, booking details)

### 4. Admin Panel (Filament)
- Complete booking management
- Email template editor
- Vehicle type configuration
- Dynamic form field builder
- Settings management
- Email logs viewer
- Analytics dashboard (planned)

### 5. Customer Features
- Guest booking (no registration required)
- Email verification
- Real-time pricing
- Booking status tracking
- Receipt downloads
- Tip payment via QR code/link

## Configuration Management

### Environment Variables
- Production settings in `.env.production`
- Sensitive keys managed via Filament settings
- Stripe keys switchable between test/live modes
- Database settings override via admin panel

### Settings Priority
1. Filament admin panel settings (highest)
2. Environment variables (.env)
3. Config files (lowest)

## API Authentication
- Sanctum for API authentication
- Token-based authentication for mobile apps (future)
- Guest booking support (no auth required)
- Admin routes protected by Filament auth

## Development Workflow

### Local Setup
```bash
# Backend
cd taxibook-api
composer install
php artisan migrate
php artisan db:seed
php artisan serve

# Frontend
cd taxibook-frontend
npm install
npm run dev
```

### Testing
- PHPUnit for backend tests
- Feature and unit tests
- Factory classes for test data
- Separate test database

## Security Features
- Input validation on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection
- CSRF protection
- Rate limiting on API endpoints
- Secure payment processing via Stripe
- Email verification for bookings
- Admin panel protection

## Performance Optimizations
- Database indexing on frequently queried fields
- Eager loading to prevent N+1 queries
- File-based caching for shared hosting
- Optimized asset delivery via Vite
- Lazy loading of frontend components

## Deployment
- **Production URL**: https://book.luxridesuv.com
- **Hosting**: Hostinger shared hosting
- **PHP Version**: 8.2
- **SSL**: Enabled
- **Database**: MySQL 8.0

## Current Status
- ✅ Core booking system complete
- ✅ Payment processing functional
- ✅ Email system implemented
- ✅ Admin panel operational
- ✅ Dynamic form fields
- ✅ Gratuity system
- ✅ Mobile responsive design optimized
- ✅ Browser navigation handling
- ✅ Session persistence for bookings
- 🚧 Driver mobile app (planned)
- 🚧 SMS notifications (planned)
- 🚧 Analytics dashboard (in progress)

## Quick Reference

### Important Services
- `NotificationService` - Email sending
- `StripeService` - Payment processing
- `MapboxService` - Maps and geocoding
- `PricingService` - Fare calculation
- `TipService` - Gratuity handling

### Key Admin Routes
- `/admin` - Admin dashboard
- `/admin/bookings` - Booking management
- `/admin/email-templates` - Email templates
- `/admin/settings` - System settings
- `/admin/booking-form-fields` - Form configuration

### API Endpoints (Public)
- `POST /api/bookings` - Create booking
- `POST /api/bookings/calculate-prices` - Get pricing
- `POST /api/bookings/validate-route` - Validate route
- `GET /api/settings/public` - Public settings
- `POST /api/tip/{token}/process` - Process tip

## Recent Updates (2025-08-19)

### Mobile Responsiveness Improvements
- **Step Indicator**: Dynamic display showing only visible steps on mobile with "Step X of 6" counter
- **Tip Selection**: 2-column grid on mobile (4 on desktop) with proper text truncation
- **Credit Card Input**: Separated into individual fields for better mobile input handling
- **Pay Button**: Full-width stacked buttons on mobile with reduced text/icons
- **Auto-scroll**: Smooth scroll to top when navigating between steps
- **Container Padding**: Reduced padding/margins throughout for mobile screens

### Browser Navigation
- **Back Button Support**: Proper handling of browser back button to navigate steps
- **Session Persistence**: Using Zustand with sessionStorage to maintain form data
- **Mobile Detection**: Disabled beforeunload warnings on mobile devices
- **History Management**: Smart history state management to prevent accidental site exit

### UX Improvements
- **Default Date/Time**: Automatically sets pickup time respecting minimum booking hours
- **Email Change Option**: Added "Wrong email?" link in verification modal
- **Form Validation**: Smart validation with auto-focus on error fields

## Contact & Support
- **Company**: LuxRide SUV
- **Admin Email**: admin@luxridesuv.com
- **Support Email**: contact@luxridesuv.com
- **Documentation Updates**: Update this file when adding major features