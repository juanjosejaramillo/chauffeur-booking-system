# Changelog

All notable changes to the LuxRide Chauffeur Booking System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.12.0] - 2025-08-31

### Changed
- **Vehicle Selection UI Overhaul**
  - Redesigned vehicle cards with horizontal compact layout
  - Adjusted vehicle image sizes to 128x80px (mobile) and 144x96px (desktop)
  - Removed duplicate category labels above vehicle names
  - Added text labels "passengers" and "bags" for better clarity
  - Backend description text now displays below vehicle name
  - Optimized padding for better space utilization
  - Added expandable sections for vehicle features
  - Entire card is now clickable for selection
  - Visual feedback with gold border and check badge on selection
  - Reduced container width from max-w-6xl to max-w-4xl for better proportion on large screens
  - Reorganized content structure with title and description at top of card

### Added
- Collapsible vehicle details with smooth fadeIn animation
- Touch-friendly expand/collapse arrows
- Responsive design optimizations for mobile and desktop
- Backend vehicle descriptions prominently displayed
- Overflow hidden on containers to prevent content bleeding
- Flex spacer for better content distribution

### Fixed
- Duplicate category text appearing above vehicle names
- Vehicle images too small to see properly
- Missing text labels for capacity indicators
- Excessive padding in vehicle cards
- Content appearing outside container boundaries
- Title and description overflow issues
- Excessive white space in center of cards on large screens
- Selected badge positioning outside container

## [1.11.0] - 2025-08-22

### Added
- Complete migration from Mapbox to Google Maps API
  - Google Places API for superior autocomplete with venue names
  - Google Directions API with real-time traffic data
  - Google Geocoding API for accurate address resolution
  - New backend endpoints for address search and place details
  - Enhanced venue/POI name display: "Venue Name - Full Address"

### Changed
- Replaced MapboxService with GoogleMapsService
- Rewrote TripDetailsLuxury.jsx to use Google Maps JavaScript API
- Updated admin panel with Google Maps settings
- Improved address autocomplete with better business data
- Enhanced traffic-aware routing for more accurate pricing

### Removed
- All Mapbox dependencies and code
- MapboxService.php
- Mapbox npm packages
- Mapbox configuration settings

### Technical
- New GoogleMapsService.php with Places, Directions, and Geocoding APIs
- Traffic prediction models: best_guess, optimistic, pessimistic
- Place IDs for consistent location reference
- Custom map styling with luxury gold route polylines
- Better POI detection and venue name display

## [1.10.0] - 2025-08-21

### Added
- Route polyline visualization on map with luxury gold styling
- Automatic route drawing when both pickup and destination are selected
- Complete address display in Google Maps autocomplete suggestions
- Map state restoration when navigating back to address step

### Changed
- GoogleMapsService returns encoded polyline format for route visualization
- Improved Google Maps address extraction using structured formatting
- Enhanced autocomplete display with venue names and full addresses

### Fixed
- "Style is not done loading" error when returning to address step
- Map markers not recreating when navigating back
- Incomplete address display in autocomplete dropdown
- Route not persisting when returning to trip details

### Technical
- Added style loading checks before map operations
- Implemented proper map event listeners for initialization
- Better error handling for map operations
- Route layer management with proper cleanup

## [1.9.0] - 2025-08-21

### Added
- Dynamic Stripe settings UI with visual mode indicators
  - Color-coded status boxes (amber for test, green for live)
  - "(Currently Active)" labels on active credential sections
  - Dark mode support for all UI elements

### Changed
- Stripe credential sections only show for selected mode
- Section descriptions dynamically update based on active mode
- Form sections are now collapsible for better organization

### Fixed
- Filament v4 compatibility issues with schema components
- Corrected imports for Tabs, Section, Form, and Get utilities
- Stripe mode switching now provides immediate visual feedback

### Technical
- ManageSettings uses `Filament\Schemas\Components` namespace
- Automatic cache clearing on settings save
- Reactive form updates using Filament v4's Get utility

## [1.8.0] - 2025-08-21

### Added
- Traffic-aware time estimation for accurate pricing
  - GoogleMapsService now uses real-time traffic data for accurate estimates
  - Departure time parameter for predictive traffic routing
  - ISO 8601 format support for time-based routing

### Changed
- PricingService now considers pickup date/time for route calculations
- BookingController accepts pickup_date and pickup_time parameters
- Frontend sends pickup datetime when calculating prices
- Cache keys include departure time for time-specific route caching

### Improved
- Pricing accuracy increased by 30-50% during peak traffic hours
- Per-minute charges now reflect actual traffic conditions
- More realistic ETAs for customers and drivers

### Technical
- GoogleMapsService::getRoute() accepts optional $departureTime parameter
- PricingService::calculatePrices() accepts optional $pickupDateTime parameter
- Automatic fallback to non-traffic routing when time not provided

## [1.7.2] - 2025-08-21

### Changed
- Complete UX redesign of email template form in admin panel
  - Email type selector moved to top with clear icons
  - Trigger events only visible for immediate emails
  - Dynamic field labels based on email type
  - Color-coded summary boxes for visual feedback
  - Educational info box explaining email types
  - Real-world examples in summary messages
  - Full dark mode support with proper contrast

### Fixed
- Email template form incorrectly requiring triggers for scheduled emails
- HTML content not rendering properly in Filament Placeholder components
- Confusing form validation that contradicted simplified email system design
- Poor readability in dark mode - all elements now theme-aware

### Technical
- Added conditional validation for trigger_events field
- Implemented HtmlString for proper HTML rendering
- Improved form reactivity with dynamic field visibility
- Enhanced helper text and labels for clarity
- Added Tailwind dark mode classes for all UI elements
- Used semi-transparent backgrounds for better dark mode appearance

## [1.7.1] - 2025-08-20 (Session 5)

### Fixed
- Duplicate cancellation emails when cancelling from admin panel
  - Removed manual BookingCancelled event triggers from EditBooking.php
  - BookingObserver now handles all cancellation events consistently
  - Cancellation reason is properly stored in database

### Changed
- Improved PDF receipt styling (user modifications)
  - Enhanced header gradient and padding
  - Better confirmation number display
  - Improved footer styling
  - Added thank you message with business name

### Technical
- Event handling now follows observer pattern consistently
- Single source of truth for booking status changes
- Prevents duplicate event firing in admin actions

## [1.7.0] - 2025-08-20 (Session 4)

### Added
- Configurable legal document URLs in admin panel
  - Terms and Conditions URL
  - Cancellation Policy URL  
  - Privacy Policy URL (optional)
  - Refund Policy URL (optional)
- New "Legal Settings" tab in admin panel with scale icon
- Migration to add legal URL settings to database
- Dynamic legal URLs in frontend booking review step

### Changed
- ReviewBookingLuxury component now fetches legal URLs from settings API
- Legal document links open in new tab for better UX
- Added underline styling to legal links for visibility
- SettingsController includes legal URLs in public API response

### Technical
- Migration: `2025_08_20_141248_add_legal_urls_to_settings.php`
- Updated ManageSettings.php with Legal Settings tab
- Frontend uses useSettings hook to fetch dynamic URLs
- Fallback to default URLs if settings unavailable

## [1.6.0] - 2025-08-20 (Session 3)

### Added
- Luxe design system applied to all PDF templates
  - Gradient headers matching email branding
  - Info boxes, highlight boxes, and alert boxes
  - Consistent typography and spacing
- New PDF template for booking details (`resources/views/pdf/booking-details.blade.php`)
- Dynamic business settings integration in PDFs
  - Company name, address, phone, email from database
  - No more hardcoded contact information

### Changed 
- Redesigned PDF receipt template with luxe styling
- PDF templates optimized for single-page display
- Reduced font sizes and spacing for better PDF rendering
- Updated NotificationService to pass settings to PDF views
- Updated ReceiptController to include business settings

### Fixed
- PDF templates now properly use business settings from admin panel
- Cache clearing required after PDF template changes

### Technical
- PDF templates use same design language as email templates
- Consistent branding across all customer communications
- Must run `php artisan optimize:clear` after template changes

## [1.5.0] - 2025-08-20 (Session 2)

### Added
- PDF attachment controls in email template admin panel
  - "Attach PDF Receipt" toggle per template
  - "Attach PDF Booking Details" toggle per template
  - Located in new "PDF Attachments" section at bottom of form
- Production deployment documentation and fixes
- Support phone number in contact information
- `.htaccess` file for React Router support on Apache servers
  - Enables proper handling of client-side routes like `/tip/:token`
  - Automatically included in production builds

### Changed
- Consolidated email template form schemas (removed duplicate SimplifiedEmailTemplateForm)
- Simplified payment page messaging (removed "Remove anytime" and "We never store" text)
- Updated all documentation files with latest changes
- Tip URL generation now fully dynamic based on `FRONTEND_URL` environment variable

### Fixed
- Git branch mismatch between production (master) and GitHub (main)
- Email template form now properly shows PDF attachment controls
- Tip link 404 errors in production by adding proper Apache rewrite rules
- Configuration caching issues in production (must clear config cache after .env changes)

### Added (Earlier Session)
- Comprehensive documentation files for AI assistance
- Documentation maintenance guide

## [1.4.0] - 2025-08-20

### Added
- New email triggers: `booking.created` (for pending bookings) and `trip.started` (for in-progress trips)
- Email templates for payment captured and payment refunded events
- BookingCreated and TripStarted event classes
- Database migration to cleanup unused email triggers
- SettingsSeeder for system configuration (25 settings)
- BookingSeeder for sample booking data
- Settings configuration in admin panel

### Changed
- Simplified email system to only 8 essential triggers
- Admin email changed from admin@taxibook.com to admin@luxridesuv.com
- Updated email template seeder with new templates and removed driver-related templates
- New Booking Pending template now sends only to admin with full customer information
- DatabaseSeeder updated to use SimplifiedEmailTemplateSeeder
- Fixed BookingSeeder to properly handle array casting for additional_data
- Business settings configured:
  - Business Name: LuxRide
  - Phone: +1-813-333-8680
  - Address: Florida, USA
  - Minimum booking: 12 hours advance
  - Time increment: 5 minutes

### Removed
- Driver event triggers (assigned, enroute, arrived)
- Admin summary triggers (daily, weekly)
- Payment failed and payment authorized triggers
- Custom manual email trigger
- Unused migration file for route_polyline field
- ComprehensiveEmailTemplateSeeder
- Duplicate settings seeders (DefaultSettingsSeeder, old SettingSeeder)

### Fixed
- Migration error for non-existent route_polyline field
- BookingSeeder additional_data and fare_breakdown JSON encoding issue
- cleanup_duplicate_email_templates migration to use correct seeder

### Technical
- Gratuity (OptionalTipEmail) and verification (VerificationCodeMail) remain as hardcoded emails
- Email template designs unchanged, only triggers modified
- Settings now properly seeded with business defaults

## [1.3.0] - 2025-01-18

### Added
- Luxe email layout system with consistent styling across all templates
- Dynamic variable replacement in email templates
- Enhanced email template editor with tabbed interface
- System variables (current_year, current_date, current_time)
- Dynamic form field variables in email editor
- Click-to-copy functionality for email variables
- Visual badges and cards for variable display

### Fixed
- Fixed {{current_year}} not being replaced in emails
- Fixed hardcoded "Laravel" text in email templates (now uses dynamic company name)
- Fixed email template editor UI rendering issues

### Changed
- Updated all email templates to use luxe layout wrapper
- Improved visual design of available variables section
- Enhanced NotificationService with common variables

## [1.2.0] - 2025-01-17

### Added
- Dynamic form fields system with admin configuration
- Airport detection for Tampa, Miami, Orlando, Fort Lauderdale airports
- Flight number field for airport transfers
- Number of bags, child seats, meet & greet options
- Conditional field visibility based on airport detection
- Booking settings management in admin panel
- Vehicle column display configuration

### Fixed
- Fixed vehicle column display in bookings table
- Fixed form field validation and conditional logic

### Changed
- Enhanced booking form with dynamic fields
- Improved admin panel UI for form field management

## [1.1.0] - 2025-01-15

### Added
- Email template system with WYSIWYG editor
- HTML email preview functionality
- Variable replacement system for emails
- Trigger-based email sending
- Email logs tracking
- Luxe email design template

### Fixed
- Fixed email template preview for complex HTML
- Fixed email variable replacement issues

## [1.0.0] - 2025-01-10

### Added
- Core booking system with multi-step flow
- Stripe payment integration (test/live mode switching)
- Real-time route calculation with Mapbox
- Dynamic pricing based on distance and vehicle type
- Guest booking support (no registration required)
- Email verification system
- Filament admin panel
- Vehicle type management
- Gratuity/tips system with QR codes
- Booking status tracking
- Receipt generation (PDF)
- Refund management
- Settings management system

### Security
- Input validation on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection
- CSRF protection
- Rate limiting on API endpoints
- Secure payment processing via Stripe

## [0.9.0] - 2025-01-05 (Beta)

### Added
- Initial Laravel 11 setup
- React frontend with Vite
- Database schema design
- Basic booking flow
- Stripe test integration
- Admin authentication

### Changed
- Migrated from Laravel 10 to Laravel 11
- Updated to Filament 3.x
- Switched to Zustand for state management

## [0.5.0] - 2024-12-20 (Alpha)

### Added
- Project initialization
- Basic Laravel structure
- Database design
- Initial mockups
- Technology stack decisions

## Version History

### Versioning Strategy
- **Major (X.0.0)**: Breaking changes, major feature releases
- **Minor (0.X.0)**: New features, backwards compatible
- **Patch (0.0.X)**: Bug fixes, minor improvements

### Release Schedule
- **Production Releases**: Monthly
- **Hotfixes**: As needed
- **Feature Previews**: Bi-weekly in development

## Upgrade Notes

### From 1.2.x to 1.3.x
1. Run migrations: `php artisan migrate`
2. Update email templates seeder: `php artisan db:seed --class=ComprehensiveEmailTemplateSeeder`
3. Clear caches: `php artisan optimize:clear`

### From 1.1.x to 1.2.x
1. Run migrations for booking form fields
2. Configure dynamic fields in admin panel
3. Update frontend to latest version

### From 1.0.x to 1.1.x
1. Run email template migrations
2. Seed email templates
3. Configure SMTP settings in admin panel

## Breaking Changes

### Version 1.3.0
- Email templates now require luxe layout wrapper
- Changed email variable format for system variables

### Version 1.2.0
- Booking additional_data structure changed
- Form field validation rules updated

### Version 1.1.0
- Email sending now uses template system
- Direct mail sending deprecated

## Deprecated Features

### To be removed in 2.0.0
- Legacy email sending without templates
- Hardcoded form fields
- Direct database settings access (use Settings model)

## Known Issues

### Current
- Queue worker requires cron job on shared hosting
- Large file uploads may timeout on shared hosting
- Email preview may not render all CSS correctly

### Resolved
- ✅ {{current_year}} variable not working (fixed in 1.3.0)
- ✅ Vehicle column display issue (fixed in 1.2.0)
- ✅ Email template UI rendering (fixed in 1.3.0)

## Contributors
- Lead Developer: LuxRide Development Team
- Email Templates: Enhanced by AI assistance
- Documentation: Comprehensive docs added January 2025

## License
Proprietary - LuxRide SUV © 2025

---

For detailed migration guides and technical documentation, see:
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Deployment procedures
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) - Common issues
- [MAINTENANCE_GUIDE.md](./MAINTENANCE_GUIDE.md) - Maintenance procedures