# LuxRide API - Chauffeur Booking System Backend

Laravel 11.x API backend for the LuxRide premium chauffeur booking platform.

## Tech Stack

- **Framework**: Laravel 11.x
- **PHP**: 8.2+
- **Admin Panel**: Filament 3.x
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum
- **Payments**: Stripe API
- **Maps**: Google Maps API (Places, Directions, Geocoding)
- **PDF**: DomPDF
- **QR Codes**: Simple QRCode

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Admin Panel

Access at `http://localhost:8000/admin`

Default credentials:
- Email: admin@luxridesuv.com
- Password: password

## Documentation

- [CLAUDE.md](./CLAUDE.md) - Full project context
- [API_REFERENCE.md](./API_REFERENCE.md) - API endpoints
- [ARCHITECTURE.md](./ARCHITECTURE.md) - System architecture
- [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md) - Database design
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Deployment guide
- [FEATURES.md](./FEATURES.md) - Feature documentation
- [CHANGELOG.md](./CHANGELOG.md) - Version history
- [TROUBLESHOOTING.md](./TROUBLESHOOTING.md) - Common issues

## License

Proprietary - LuxRide SUV. All rights reserved.
