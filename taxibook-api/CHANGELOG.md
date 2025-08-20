# Changelog

All notable changes to the LuxRide Chauffeur Booking System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
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