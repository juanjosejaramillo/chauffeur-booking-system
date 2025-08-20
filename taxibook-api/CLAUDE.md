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
- Dynamic Stripe key management - frontend fetches correct key from backend
- Stripe integration with test/live mode switching controlled from admin panel
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
- Stripe keys dynamically loaded from backend settings (no longer hardcoded in frontend)
- Database settings override via admin panel

### Settings Priority
1. Filament admin panel settings (highest priority)
2. Backend API response (for frontend configuration)
3. Environment variables (.env)
4. Config files (lowest priority)

### Stripe Key Management Flow
1. Admin sets Stripe mode (test/live) in Filament settings
2. Backend `SettingsController` fetches appropriate key based on mode
3. Frontend requests settings via `/api/settings/public`
4. React components use dynamic key for Stripe initialization

## Legal Document Management
- **Settings API Endpoint**: `/api/settings/public` returns:
  ```json
  {
    "legal": {
      "terms_url": "https://luxridesuv.com/terms",
      "cancellation_policy_url": "https://luxridesuv.com/cancellation-policy"
    }
  }
  ```
- **Admin Configuration**: Settings → Legal Settings tab
- **Frontend Usage**: Automatically fetched via useSettings hook
- **Supported URL Types**: Website pages, Google Docs, PDF files, any valid URL

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

### Dynamic Stripe Key Management
- **Backend API Update**: `SettingsController::getPublicSettings()` now returns the correct Stripe public key based on admin settings
- **Frontend Integration**: React components fetch Stripe key dynamically from API instead of using hardcoded env variables
- **Mode Control**: Stripe test/live mode is now centrally controlled from admin panel
- **Safety Measures**: Production env file defaults to test key as fallback

## Previous Updates (2025-08-19)

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

## Recent Updates (2025-08-20 - Session 2)

### Email System Simplification
- **Reduced Email Triggers**: Simplified to only 8 essential triggers:
  - `booking.created` - When new booking is created (pending status) - Admin only notification
  - `booking.confirmed` - When booking is confirmed - Customer notification
  - `trip.started` - When trip starts (status changes to in_progress)
  - `booking.modified` - When booking details are changed
  - `booking.cancelled` - When booking is cancelled
  - `booking.completed` - When booking/trip is completed
  - `payment.captured` - When payment is captured
  - `payment.refunded` - When payment is refunded
- **Removed Triggers**: Driver events, admin summaries, custom manual triggers
- **Hardcoded Emails**: Gratuity (OptionalTipEmail) and verification (VerificationCodeMail) remain hardcoded
- **Admin Email**: Changed from admin@taxibook.com to admin@luxridesuv.com
- **New Events**: Added BookingCreated and TripStarted events
- **Scheduled Emails**: Support for X hours before/after trip timing
- **Booking Pending Template**: Updated to send only to admin with full customer information

### Settings Configuration
- **Business Settings**:
  - Business Name: LuxRide
  - Business Tagline: Premium Transportation Service
  - Business Address: Florida, USA
  - Business Phone: +1-813-333-8680
  - Admin Email: admin@luxridesuv.com
- **Booking Settings**:
  - Minimum Advance Booking: 12 hours
  - Maximum Advance Booking: 90 days
  - Allow Same Day Bookings: Yes
  - Time Selection Increment: 5 minutes
- **Email Settings**:
  - From Name: LuxRide
  - Reply-To Address: contact@luxridesuv.com

### Database Seeders
- **DatabaseSeeder**: Main orchestrator calling all seeders
- **SettingsSeeder**: Creates 25 system settings
- **SimplifiedEmailTemplateSeeder**: Creates only required email templates
- **BookingFormFieldSeeder**: Dynamic form fields
- **BookingSeeder**: Creates sample booking (1 record)
- **Removed**: ComprehensiveEmailTemplateSeeder, duplicate settings seeders

### Migration Fixes
- Removed route_polyline migration that referenced non-existent field
- Fixed cleanup_duplicate_email_templates migration to use SimplifiedEmailTemplateSeeder

## Recent Updates (2025-08-20 - Session 5)

### Fixed Duplicate Cancellation Emails
- **Issue**: Cancellation emails were being sent twice when cancelling from admin panel
- **Cause**: Both BookingObserver and EditBooking.php were firing BookingCancelled events
- **Solution**: 
  - Removed manual event triggers from EditBooking.php
  - Let BookingObserver handle all cancellation events automatically
  - Now stores cancellation_reason in database before status update
- **Result**: Only one cancellation email is sent per cancellation

## Recent Updates (2025-08-20 - Session 4)

### Configurable Legal URLs
- **Dynamic Legal Document Links**:
  - Terms and Conditions URL configurable from admin panel
  - Cancellation Policy URL configurable from admin panel
  - Privacy Policy URL (optional)
  - Refund Policy URL (optional)
  - All managed through new "Legal Settings" tab in admin panel

- **Frontend Integration**:
  - ReviewBookingLuxury component fetches URLs from settings API
  - Links open in new tab with `target="_blank"`
  - Fallback to default URLs if settings not available
  - Added underline styling for better visibility

- **Backend Implementation**:
  - Created migration `add_legal_urls_to_settings`
  - Added legal settings to SettingsSeeder
  - Updated SettingsController to include legal URLs in API response
  - Added Legal Settings tab to ManageSettings.php in Filament

- **Admin Panel Features**:
  - New "Legal Settings" tab with scale icon
  - Four URL fields for legal documents
  - Support for any URL type (website, Google Docs, PDFs, etc.)
  - Real-time updates affect frontend immediately

## Recent Updates (2025-08-20 - Session 3)

### PDF Template Redesign
- **Luxe Email Design Applied to PDFs**:
  - Receipt PDF now matches luxe email template styling
  - Created dedicated PDF template for booking details
  - Gradient header with company branding (#1a1a1a to #2d2d2d)
  - Info boxes, highlight boxes, and alert boxes for structured content
  - Consistent typography using system fonts
  - Optimized for single-page display with reduced font sizes

- **Dynamic Business Settings in PDFs**:
  - PDFs now pull company information from Settings model
  - Business name, address, phone, email dynamically loaded
  - No more hardcoded contact information in templates
  - ReceiptController passes settings to views
  - NotificationService includes settings when generating PDFs

- **PDF Template Structure**:
  - `/resources/views/pdf/receipt.blade.php` - Payment receipt PDF
  - `/resources/views/pdf/booking-details.blade.php` - Booking confirmation PDF
  - Both use consistent luxe design language
  - Responsive to content while maintaining single-page layout

- **Cache Management**:
  - Must run `php artisan optimize:clear` after PDF template changes
  - Views are compiled and cached in Laravel
  - PDF changes require view cache clearing to take effect

## Recent Updates (2025-08-20 - Session 2)

### Frontend UI Improvements
- **Removed from Payment Step**:
  - Removed "Remove anytime from your account" text from save card option
  - Removed "We never store your card details" text from security notice
  - Simplified payment security messaging

### Email Template Admin Panel Enhancements
- **PDF Attachments Control**:
  - Added "PDF Attachments" section to email template editor
  - Two toggles per template:
    - "Attach PDF Receipt" - Include payment receipt when available
    - "Attach PDF Booking Details" - Include full booking information PDF
  - Allows enabling/disabling PDFs per email template from admin panel
  - Located at bottom of email template form for easy access

### Code Cleanup
- **Removed Duplicate Form Schema**:
  - Deleted unused SimplifiedEmailTemplateForm.php
  - Consolidated all functionality into SimpleEmailTemplateForm.php
  - Prevents confusion and maintains single source of truth

### PDF Generation System
- **PDF Attachments in Emails**:
  - Controlled via admin panel toggles per email template
  - Two types: Receipt PDF and Booking Details PDF
  - Generated on-demand when emails are sent
  - Stored temporarily in `storage/app/temp/`
  - Automatic cleanup after sending

- **PDF Generation Flow**:
  1. Email template checks `attach_receipt` or `attach_booking_details` flags
  2. NotificationService generates PDFs if enabled
  3. PDFs attached to email using Laravel's attachment system
  4. Temporary files cleaned up after sending

### Production Deployment Notes
- **Git Branch Issue**: Production may be on 'master' branch while GitHub uses 'main'
  - Solution: `git checkout -b main origin/main` then delete master branch
- **Tip URL Structure**: Production tip URLs follow pattern: `https://book.luxridesuv.com/tip/{token}`
  - URL is dynamic based on `FRONTEND_URL` in .env
  - Must run `php artisan config:clear` after changing .env in production
- **Admin Email Setting**: Controlled via Settings in admin panel or database
- **React Router on Apache**: Requires .htaccess file in public folder for client-side routing
  - File automatically included in builds from `/public/.htaccess`
  - Enables proper handling of routes like `/tip/:token`

## Testing Tools
- **MailHog**: Local email testing at http://localhost:8025
  - Captures all outgoing emails
  - Displays PDF attachments
  - Allows downloading attachments
  - Shows email HTML/text content

## Cache Commands
After making changes to templates or configurations:
```bash
# Clear all caches
php artisan optimize:clear

# Or individually:
php artisan config:clear  # Configuration cache
php artisan cache:clear   # Application cache
php artisan view:clear    # Compiled views
php artisan route:clear   # Route cache
```

## Contact & Support
- **Company**: LuxRide SUV
- **Admin Email**: admin@luxridesuv.com
- **Support Email**: contact@luxridesuv.com
- **Support Phone**: +1-813-333-8680
- **Documentation Updates**: Update this file when adding major features