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
- **State Management**: Zustand (with sessionStorage persistence)
- **Routing**: React Router DOM v7
- **Styling**: Tailwind CSS 3.x
- **UI Components**: Headless UI, Heroicons
- **Date/Time Picker**: Separate date and time picker fields
- **Build Tool**: Vite 7.x

### Third-Party Integrations
- **Payment Processing**: Stripe API (v17.5)
  - Support for test/live mode switching
  - Payment intents API
  - Saved cards functionality
  - Gratuity/tips system
- **Maps & Geocoding**: Google Maps API
  - Google Places API for autocomplete with venue names
  - Google Directions API with real-time traffic
  - Google Geocoding API for address lookup
  - Superior business/POI data accuracy
- **Email**: SMTP (Gmail configured)
- **PDF Generation**: DomPDF (receipts)
- **QR Codes**: Simple QRCode (tip links)
- **Analytics & Tracking**: Microsoft Clarity
  - Session recordings and heatmaps
  - Custom event tracking for booking funnel
  - User identification with privacy-compliant hashing
  - Conversion tracking and session prioritization

## Project Structure

```
/taxibook-api/                 # Laravel Backend
├── app/
│   ├── Events/               # Event classes (8 events)
│   ├── Filament/             # Admin panel
│   │   ├── Concerns/        # Shared traits (HasDateRangeFilter)
│   │   ├── Pages/            # Dashboard, ManageSettings
│   │   ├── Resources/        # CRUD interfaces
│   │   └── Widgets/          # Dashboard widgets
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/          # API controllers (6 controllers)
│   ├── Jobs/                 # Queue jobs
│   ├── Listeners/            # Event listeners
│   ├── Mail/                 # Mailable classes
│   ├── Models/               # Eloquent models (10 models)
│   ├── Observers/            # Model observers (BookingObserver)
│   ├── Providers/            # Service providers
│   ├── Services/             # Business logic services (6 services)
│   └── Helpers/              # Helper functions
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # Data seeders (5 seeders)
├── routes/
│   ├── api.php              # API routes
│   └── web.php              # Web routes
└── config/                   # Configuration files

/taxibook-frontend/           # React Frontend (inside taxibook-api)
├── src/
│   ├── components/
│   │   └── BookingWizard/   # 6-step booking wizard
│   │       ├── BookingWizard.jsx
│   │       ├── VerificationModalLuxury.jsx
│   │       ├── WizardProgressLuxury.jsx
│   │       └── steps/       # Step components
│   ├── pages/               # TipPayment, TipSuccess, TipAlready
│   ├── services/            # API services, ClarityTracking
│   ├── store/               # Zustand stores
│   └── hooks/               # Custom React hooks
```

## Key Models & Relationships

### Core Models
1. **Booking** - Central booking entity
   - Has one User (optional)
   - Belongs to VehicleType
   - Has many Transactions
   - Has many BookingExpenses
   - Stores dynamic form data in `additional_data` JSON field
   - Supports hourly bookings (booking_type: 'distance' or 'hourly')

2. **BookingExpense** - Expense tracking per booking
   - Belongs to Booking
   - Fields: description, amount
   - Used for driver pay, tolls, fuel, etc.
   - Aggregated for net profit calculations

3. **User** - Customer accounts
   - Has many Bookings
   - Stores saved Stripe customer ID

4. **VehicleType** - Vehicle categories
   - Has many VehiclePricingTiers
   - Has many Bookings
   - Configurable pricing per distance

5. **EmailTemplate** - Dynamic email templates
   - Supports HTML/WYSIWYG/Blade formats
   - Trigger-based sending
   - Variable replacement system

6. **BookingFormField** - Dynamic form configuration
   - Defines custom booking form fields
   - Multiple field types (text, select, checkbox, etc.)
   - Conditional visibility rules

7. **Setting** - System configuration
   - Key-value store for system settings
   - Managed via Filament admin panel
   - Overrides .env configurations

8. **EmailLog** - Email audit trail
   - Tracks all sent emails
   - Links to bookings and users
   - Stores status and metadata

9. **Transaction** - Payment records
   - Belongs to Booking
   - Tracks Stripe transactions
   - Handles refunds

10. **VehiclePricingTier** - Distance-based pricing tiers
    - Belongs to VehicleType
    - Min/max distance ranges with per-mile rates

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
- Two payment modes:
  - **Immediate Payment**: Charges card at booking via Payment Intent
  - **Save Card Mode**: Saves card via Setup Intent, charges after service
- Separate card input fields (number, expiry, CVV, postal) for better mobile UX
- Payment intents and setup intents for secure processing
- Saved cards functionality
- Gratuity/tips system with QR codes and responsive tip buttons
- Refund management (full and partial)
- Webhook support for payment confirmations
- Admin safeguards for saved card payments

### 3. Email System
- Template-based email system
- WYSIWYG editor for admin
- Variable replacement ({{customer_name}}, etc.)
- Trigger-based sending (booking confirmed, 24hr reminder, etc.)
- HTML email support with luxe design
- Attachment support (receipts, booking details)

### 4. Admin Panel (Filament)
- Analytics dashboard with date range filtering (today, this week, this month, custom range)
- Dashboard widgets:
  - **DashboardStatsOverview**: Bookings, revenue, net profit, expenses, completion rate
  - **RevenueTrendChart**: Fares vs tips vs expenses over time
  - **BookingTrendChart**: Booking volume trends
  - **NextBookingsWidget**: Confirmed bookings in 7-day window with time-until badges
  - **UpcomingBookingsWidget**: Upcoming bookings list
- Complete booking management with expense tracking (ExpensesRelationManager)
- Email template editor with PDF attachment controls
- Vehicle type configuration
- Dynamic form field builder
- Settings management (Business, Stripe, Google Maps, Booking, Email, Legal)
- Email logs viewer

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
- ✅ Payment processing functional (immediate + save-card modes)
- ✅ Email system implemented
- ✅ Admin panel operational
- ✅ Dynamic form fields
- ✅ Gratuity system
- ✅ Mobile responsive design optimized
- ✅ Browser navigation handling
- ✅ Session persistence for bookings
- ✅ Analytics dashboard with date filtering
- ✅ Revenue trend charts and booking trend charts
- ✅ Net profit tracking with expense management
- ✅ Hourly booking support
- ✅ Google Maps integration (traffic-aware routing)
- ✅ Microsoft Clarity analytics
- ✅ Configurable legal document URLs
- ✅ Email verification toggle
- ✅ Separate date/time pickers
- 🚧 Driver mobile app (planned)
- 🚧 SMS notifications (planned)

## Quick Reference

### Important Services
- `NotificationService` - Email sending
- `StripeService` - Payment processing (intents, setup intents, refunds)
- `GoogleMapsService` - Maps, geocoding, and traffic-aware routing
- `PricingService` - Fare calculation (distance and hourly)
- `TipService` - Gratuity handling
- `EmailComponentsService` - Email template components
- `ClarityTracking` - Microsoft Clarity analytics (Frontend)

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
- `POST /api/bookings/search-addresses` - Google Places autocomplete
- `POST /api/bookings/place-details` - Google Place details
- `POST /api/bookings/send-verification` - Send email verification
- `POST /api/bookings/verify-email` - Verify email code
- `GET /api/bookings/payment-mode` - Get payment mode setting
- `POST /api/bookings/{bookingNumber}/setup-intent` - Create setup intent (save-card)
- `POST /api/bookings/{bookingNumber}/complete-setup` - Complete setup intent
- `POST /api/bookings/{bookingNumber}/payment-intent` - Create payment intent
- `POST /api/bookings/{bookingNumber}/confirm-payment` - Confirm payment
- `GET /api/settings/public` - Public settings
- `POST /api/tip/{token}/process` - Process tip

## Recent Updates (2026-02-17)

### Admin Dashboard Revamp & Expense Tracking
- **Analytics Dashboard**: Complete revamp with date range filtering (today, this week, this month, custom)
- **Dashboard Widgets**:
  - `DashboardStatsOverview`: 7 stat cards (bookings, revenue, net profit, pending revenue, expenses, cancelled, completion rate)
  - `RevenueTrendChart`: Multi-line chart showing fares (green), tips (amber), expenses (red dashed)
  - `BookingTrendChart`: Booking volume trends with automatic time grouping
  - `NextBookingsWidget`: Confirmed bookings in 7-day window with time-until badges and color coding
  - `UpcomingBookingsWidget`: Upcoming bookings list
- **Date Range Filter**: `HasDateRangeFilter` concern shared across widgets with presets
- **Expense Tracking System**:
  - New `BookingExpense` model (description, amount per booking)
  - `ExpensesRelationManager` in Filament for CRUD on booking expenses
  - Migration: `2026_02_17_000001_create_booking_expenses_table`
  - Net profit = Total Revenue (fares + tips) - Total Expenses
  - Expenses cover: driver pay, tolls, fuel, etc.

### Payment Mode System (2025-12-03)
- **Two Payment Modes**: Configurable from admin settings
  - **Immediate Payment**: Card charged at booking via Stripe Payment Intent
  - **Save Card (Post-Service)**: Card saved via Stripe Setup Intent, charged after service
- **Setup Intent Flow**: `createSetupIntent()` and `completeSetupIntent()` endpoints
- **Dynamic UI**: Button text and confirmation page adapt based on payment mode
- **Admin Safeguards**: Protection for saved card payment processing
- **Legal Links Moved**: Terms/cancellation policy links moved from Review to Payment step
- **Separate Date/Time Pickers**: Individual fields for date and time selection

### Hourly Booking Support (2025-11)
- **Booking Types**: Support for both distance-based and hourly bookings
- **Hourly Pricing**: Per-hour rates configurable per vehicle type
- **Duration Selection**: Minimum hours configurable in settings
- **Migration**: Added hourly booking fields and settings

### Vehicle Card Layout Improvements
- **Mobile Fixes**: Fixed cropping and visibility issues on mobile devices
- **Large Screen Optimization**: Better layout on large screens
- **Removed 'Rental' Terminology**: All UI uses 'service' instead

## Recent Updates (2025-08-29)

### Analytics Integration - Microsoft Clarity
- **Removed**: Hotjar tracking code and all dependencies
  - Deleted `src/services/hotjarTracking.js`
  - Removed Hotjar script from `index.html`
  - Uninstalled `@hotjar/browser` npm package
- **Added**: Microsoft Clarity for behavioral analytics
  - Project ID: `t26s11c8vq`
  - Script integration in `index.html`
  - Created `ClarityTracking` service at `src/services/clarityTracking.js`
- **Tracking Implementation**:
  - Complete booking funnel tracking from trip details to confirmation
  - Custom events for all major user interactions
  - Error tracking for validation and payment failures
  - User identification using hashed email addresses (privacy-compliant)
  - Session prioritization for high-value conversions
  - Device type detection (mobile vs desktop)
- **Components Updated**:
  - BookingWizard: Step navigation and abandonment tracking
  - TripDetailsLuxury: Address searches, airport detection, date/time selection
  - VehicleSelectionLuxury: Vehicle views, selections, and pricing
  - CustomerInfoLuxury: Form interactions and email verification flow
  - ReviewBookingLuxury: Legal document clicks and terms agreement
  - PaymentLuxury: Payment attempts, tip selection, and card saving
  - ConfirmationLuxury: Booking conversion and user identification
  - TipPayment: Tip selection and payment processing
- **Benefits**:
  - Session recordings for UX analysis
  - Heatmaps for click pattern visualization
  - Conversion funnel analytics
  - Real-time behavioral insights

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

## Recent Updates (2025-08-21 - Latest)

### Traffic-Aware Time Estimation
- **Issue**: System was using static time estimates regardless of pickup date/time, leading to inaccurate pricing
- **Solution**: Implemented traffic-aware routing using Google Maps' traffic data
- **Changes**:
  - GoogleMapsService now accepts optional departure time parameter
  - Switches to `driving-traffic` profile when departure time is provided
  - Includes `depart_at` parameter for traffic predictions
  - PricingService passes pickup datetime to GoogleMapsService
  - BookingController validates and passes pickup date/time
  - Frontend sends pickup date/time when calculating prices
- **Impact**: 
  - More accurate time estimates during peak hours
  - Per-minute charges now reflect actual traffic conditions
  - Better pricing accuracy (30-50% more accurate during rush hours)
  - No UI or database changes required
- **Technical Details**:
  - Uses ISO 8601 format for departure time
  - Cache keys include departure time hash
  - Falls back to non-traffic routing when no time provided

## Recent Updates (2025-08-21)

### Email Template Form UX Improvements
- **Issue**: Form required trigger selection even for time-based emails, causing confusion
- **Solution**: Complete redesign of email template form for clarity:
  - **Conditional Validation**: Triggers only required for immediate emails
  - **Smart Field Visibility**: Trigger selection hidden for scheduled emails
  - **Clear Type Selection**: Email type selector with icons (⚡ for event-triggered, ⏰ for time-based)
  - **Dynamic Labels**: Form fields change labels based on email type selected
  - **Visual Feedback**: Color-coded summary boxes (red for errors, green for immediate, blue for scheduled)
  - **Educational Content**: Info box explaining the two email types
  - **Real Examples**: Shows exactly when emails will be sent with examples
  - **Dark Mode Support**: All UI elements now use Tailwind's dark mode classes for proper contrast
- **Technical**: 
  - Uses HtmlString for proper HTML rendering in Filament Placeholder components
  - Implemented Tailwind dark mode classes (dark:bg-*, dark:text-*) for theme-aware colors
  - Semi-transparent backgrounds for better dark mode appearance
- **Result**: Zero confusion about email configuration with full dark/light mode support

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

## Recent Updates (2025-08-21 - Session 3)

### Route Polyline Visualization
- **Map Route Display**: Added gold-colored polyline showing the route from pickup to destination
- **Automatic Route Drawing**: Route automatically displays when both locations are selected
- **Backend Change**: GoogleMapsService now returns encoded polyline for route visualization
- **Luxury Styling**: Route displayed in gold color (#B8860B) with 4px width and rounded caps

### Google Maps Address Display Improvements
- **Complete Address Extraction**: Now properly extracts place names and addresses from Google Places API
- **Better Autocomplete Display**: Shows venue name with complete address below
- **Address Format**: Displays as "Venue Name" with "City, State ZIP, Country" underneath
- **Direct API Field Usage**: Uses Google's structured formatting for cleaner display

### Map Loading Bug Fix
- **Fixed Style Loading Error**: Resolved "Style is not done loading" error when navigating back
- **Proper Initialization**: Map waits for style to load before adding sources/layers
- **Marker Recreation**: Automatically recreates markers when returning to the step
- **Route Persistence**: Route redraws when navigating back to address step

## Recent Updates (2025-08-21 - Session 2)

### Stripe Settings UI Improvements
- **Dynamic Mode Labels**: Stripe credential sections now show "(Currently Active)" suffix when that mode is selected
- **Visual Status Indicators**: 
  - Color-coded status boxes at top of Stripe settings
  - TEST MODE: Amber badge with warning about test payments
  - LIVE MODE: Green badge with warning about real payments
  - Dark mode support with proper color contrasts
- **Improved UX**:
  - Only shows credentials for currently selected mode
  - Dynamic section descriptions based on active mode
  - Sections are collapsible for better organization
  - Reactive form updates when switching modes
- **Filament v4 Compatibility**: 
  - Fixed imports for Filament v4 schema components
  - Uses `Filament\Schemas\Components` for Tabs, Section, Form
  - Uses `Filament\Schemas\Components\Utilities\Get` for reactive logic
- **Automatic Key Switching**:
  - SettingsServiceProvider (lines 66-90) automatically switches between test/live keys
  - StripeService uses correct keys based on mode
  - Frontend API endpoint returns appropriate public key
  - Cache clearing ensures immediate effect after saving

## Recent Updates (2025-08-22 - Latest)

### Complete Migration to Google Maps
- **Motivation**: Google Maps provides superior accuracy for business locations and traffic data
- **Backend Changes**:
  - Created `GoogleMapsService.php` as the primary mapping service
  - Implements Google Places API for rich autocomplete with venue names
  - Google Directions API with real-time and predictive traffic
  - Traffic models: best_guess (default), optimistic, pessimistic
  - New API endpoints: `/api/bookings/search-addresses` and `/api/bookings/place-details`
- **Frontend Changes**:
  - Complete rewrite of `TripDetailsLuxury.jsx` using Google Maps JavaScript API
  - Rich autocomplete showing venue names prominently
  - Format: "Venue Name - Full Address" for POIs
  - Custom gold/black markers matching luxury theme
  - Route polyline in luxury gold color
- **Settings Updates**:
  - Google Maps settings in admin panel
  - Admin panel "Maps Settings" tab for API key and traffic model
  - Database migration to update settings
- **Benefits**:
  - More accurate business/venue names (hotels, airports, restaurants)
  - Better traffic predictions for accurate pricing
  - Richer place data (business hours, ratings available if needed)
  - Place IDs for consistent location reference
  - Superior address parsing and geocoding

### Enhanced Venue/POI Name Display
- **Issue**: When selecting venues like airports or hotels, only the street address was shown, not the venue name
- **Solution**: Enhanced address display to include venue names with full addresses
- **Format**: "Orlando International Airport - 1 Jeff Fuqua Boulevard, Orlando, FL 32827"
- **Detection**: Automatically identifies venues, POIs, and businesses
- **Benefits**: Drivers immediately recognize destinations, better UX for customers

## Recent Updates (2025-08-31)

### Email Verification Toggle Feature
- **Admin Control**: New setting in Filament admin panel to enable/disable email verification
- **Location**: Settings → Booking Settings → Require Email Verification
- **Backend Implementation**:
  - Added `require_email_verification` setting to database
  - `BookingVerificationController` checks setting before sending codes
  - When disabled, verification endpoints return success immediately
- **Frontend Implementation**:
  - Settings fetched on BookingWizard mount
  - CustomerInfoLuxury component checks setting before showing verification modal
  - When disabled, customers proceed directly to payment
- **Migration**: `2025_08_30_220047_add_email_verification_toggle_setting.php`

### Early Booking Notification
- **Changed Behavior**: Admin notification now sent when customer completes info form (not at payment)
- **Booking Creation Moved**: 
  - Previously: Created when moving from Review to Payment step
  - Now: Created when moving from Customer Info to Review step
- **Benefits**:
  - Admin gets notified earlier in the booking process
  - Better tracking of abandoned bookings
  - Improved conversion rate monitoring
- **Implementation**:
  - `CustomerInfoLuxury` now calls `createBooking()` after email verification
  - `ReviewBookingLuxury` no longer creates booking (already exists)
  - Booking store prevents duplicate bookings with existence check
- **Email Template**: "New Booking Pending (Admin)" triggered by `booking.created` event

### Vehicle Selection UI Improvements (Session 2)
- **Layout Overflow Fixes**: Resolved issues with content appearing outside container boundaries
  - Added `overflow-hidden rounded-lg` to prevent content overflow
  - Moved vehicle title and description inside the container (top section)
  - Adjusted selected badge position from negative to positive positioning (top-3 right-3)
  - Ensured all content stays within white card boundaries
- **Optimized Container Width**: Reduced maximum width for better visual proportion on large screens
  - Changed from `max-w-6xl` to `max-w-4xl` (896px max width)
  - Prevents cards from being too wide on desktop displays
  - Better visual balance and readability
- **Improved Content Structure**:
  - Reorganized layout: Title → Description → Main content row
  - Vehicle name and description now appear at top of card
  - Below that: horizontal layout with image, capacity info, and price
  - Added flex spacer to push price section to the right
- **Reduced White Space**: Eliminated excessive gaps in center of cards
  - Removed `flex-1` from capacity info section
  - Capacity info positioned closer to vehicle image
  - Reduced gap between capacity items (gap-3 instead of gap-4)
  - Added `whitespace-nowrap` to prevent text wrapping

### Vehicle Selection UI Improvements (Session 1)
- **Compact Vehicle Cards**: Redesigned vehicle selection step for better mobile and desktop experience
  - Horizontal layout with vehicle image, info, and price in single row
  - Optimized padding (p-4 sm:p-5) to balance content and whitespace
  - Large vehicle images for better visibility (128x80px mobile, 144x96px desktop)
- **Cleaner Information Display**:
  - Removed duplicate category text that was showing above vehicle names
  - Vehicle name displayed as main title without redundant labels
  - Backend description text shown below vehicle name (e.g., "Affordable rides for everyday travel")
- **Enhanced Capacity Display**:
  - Added text labels "passengers" and "bags" next to capacity icons
  - Format: "4 passengers" and "2 bags" for clearer understanding
  - Icons with flex-shrink-0 to prevent distortion
- **Expandable Details**: Added collapsible sections for additional vehicle information
  - Dropdown arrow button to show/hide additional features
  - Clicking arrow also selects the vehicle automatically
  - Smooth animation (fadeIn) when expanding/collapsing details
  - Features displayed as tags when expanded
- **Click-to-Select**: Entire vehicle card is now clickable for selection
  - Visual feedback with gold ring border when selected
  - Check badge appears on selected vehicle
  - Hover effects for better interactivity (scale 1.01 on hover)
- **Responsive Design**: Optimized for both mobile and desktop views
  - Smaller fonts and spacing on mobile devices
  - Touch-friendly expand buttons with proper hit areas
  - Consistent spacing across breakpoints
- **Component Updates**: `VehicleSelectionLuxury.jsx` refinements
  - Removed unused `getVehicleIcon` function
  - Added `expandedVehicle` state for managing collapsible sections
  - `handleToggleExpand` function for arrow click handling
  - Displays vehicle.description from backend API

## Contact & Support
- **Company**: LuxRide SUV
- **Admin Email**: admin@luxridesuv.com
- **Support Email**: contact@luxridesuv.com
- **Support Phone**: +1-813-333-8680
- **Documentation Updates**: Update this file when adding major features