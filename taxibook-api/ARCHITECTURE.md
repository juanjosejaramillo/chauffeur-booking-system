# System Architecture

## Overview
The LuxRide system follows a modern web application architecture with separated frontend and backend, RESTful APIs, and a service-oriented backend design.

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   React SPA     │────▶│  Laravel API    │────▶│    MySQL DB     │
│   (Frontend)    │     │   (Backend)     │     │                 │
└─────────────────┘     └─────────────────┘     └─────────────────┘
         │                       │                        │
         ▼                       ▼                        │
┌─────────────────┐     ┌─────────────────┐             │
│    Mapbox       │     │     Stripe      │             │
│      API        │     │      API        │             │
└─────────────────┘     └─────────────────┘             │
                                │                        │
                        ┌─────────────────┐             │
                        │  Filament Admin │─────────────┘
                        │     Panel       │
                        └─────────────────┘
```

## Backend Architecture (Laravel)

### Layer Structure

#### 1. **Presentation Layer**
- **API Controllers** (`app/Http/Controllers/Api/`)
  - Handle HTTP requests/responses
  - Input validation via Form Requests
  - Return JSON responses
  - No business logic

- **Filament Resources** (`app/Filament/Resources/`)
  - Admin panel CRUD operations
  - Form schemas and table configurations
  - Custom pages for settings

#### 2. **Service Layer** (`app/Services/`)
Core business logic encapsulated in service classes:

```php
NotificationService     # Email notifications, template rendering
StripeService          # Payment processing, intents, refunds
MapboxService          # Geocoding, route calculation
PricingService         # Fare calculation, pricing tiers
TipService            # Gratuity processing (hardcoded email)
EmailComponentsService # Email template components
```

#### 3. **Data Access Layer**
- **Eloquent Models** (`app/Models/`)
  - Database abstraction
  - Relationships
  - Accessors/Mutators
  - Scopes for common queries

- **Database Migrations** (`database/migrations/`)
  - Schema version control
  - Incremental changes

- **Database Seeders** (`database/seeders/`)
  - `DatabaseSeeder`: Main orchestrator
  - `SettingsSeeder`: 25 system settings
  - `SimplifiedEmailTemplateSeeder`: 13 email templates
  - `BookingFormFieldSeeder`: Dynamic form fields
  - `BookingSeeder`: Sample booking data

#### 4. **Infrastructure Layer**
- **Providers** (`app/Providers/`)
  - `SettingsServiceProvider`: Override configs from DB
  - `AppServiceProvider`: Service bindings
  - `EventServiceProvider`: Event listeners

### Design Patterns

#### Service Pattern
```php
class BookingController {
    public function store(Request $request) {
        // Validation
        $validated = $request->validate([...]);
        
        // Delegate to service
        $booking = $this->bookingService->create($validated);
        
        // Return response
        return new BookingResource($booking);
    }
}
```

#### Repository Pattern (Implicit via Eloquent)
```php
class Booking extends Model {
    // Scopes act as repository methods
    public function scopeUpcoming($query) {
        return $query->where('pickup_date', '>', now());
    }
}
```

#### Observer Pattern
```php
// Email notifications triggered by events
Event::dispatch(new BookingConfirmed($booking));
```

#### Factory Pattern
```php
// Test data generation
BookingFactory::new()->create();
```

## Frontend Architecture (React)

### Component Structure
```
src/
├── components/         # Reusable UI components
│   ├── booking/       # Booking-specific components
│   └── common/        # Shared components
├── pages/             # Route-level components
│   ├── BookingFlow.jsx
│   ├── PaymentPage.jsx
│   └── ThankYou.jsx
├── services/          # API communication
│   └── api.js        # Axios configuration
├── store/            # State management
│   └── bookingStore.js  # Zustand store
└── hooks/            # Custom React hooks
    └── useBooking.js
```

### State Management (Zustand)
```javascript
// Centralized state management
const useBookingStore = create((set) => ({
  booking: null,
  step: 1,
  setBooking: (booking) => set({ booking }),
  nextStep: () => set((state) => ({ step: state.step + 1 }))
}));
```

### Data Flow
1. User interaction → Component
2. Component → Zustand Store (state update)
3. Component → API Service (data fetch/submit)
4. API Response → Store Update
5. Store Update → Component Re-render

## Database Architecture

### Schema Design Principles
- **Normalization**: 3NF for transactional data
- **Denormalization**: JSON fields for flexible data (`additional_data`)
- **Soft Deletes**: Maintain data history
- **Audit Fields**: `created_at`, `updated_at` on all tables

### Key Relationships
```sql
bookings
  ├── belongs_to → users (optional)
  ├── belongs_to → vehicle_types
  ├── has_many → transactions
  └── has_many → email_logs

vehicle_types
  ├── has_many → bookings
  └── has_many → vehicle_pricing_tiers

email_templates
  └── has_many → email_logs

booking_form_fields (standalone)
settings (key-value store)
```

### Performance Optimizations
- **Indexes**: On foreign keys, frequently queried fields
- **Eager Loading**: Prevent N+1 queries
- **Query Optimization**: Use scopes and query builder
- **Caching**: File-based for settings and configurations

## API Architecture

### RESTful Design
```
GET    /api/bookings          # List bookings
POST   /api/bookings          # Create booking
GET    /api/bookings/{id}     # Get booking
PUT    /api/bookings/{id}     # Update booking
DELETE /api/bookings/{id}     # Delete booking
```

### Authentication Strategy
- **Public Endpoints**: Booking creation, pricing calculation
- **Protected Endpoints**: User bookings, admin operations
- **Token-Based**: Laravel Sanctum for API tokens
- **Session-Based**: Filament admin panel

### Request/Response Flow
```
Request → Middleware → Controller → Service → Model → Database
                                        ↓
Response ← Transformer ← Resource ← Controller
```

## Integration Architecture

### Stripe Integration
```
Booking Creation
    ↓
Create Payment Intent (Server)
    ↓
Confirm Payment (Client)
    ↓
Webhook Confirmation (Server)
    ↓
Update Booking Status
```

### Mapbox Integration
```
Address Input
    ↓
Geocoding API
    ↓
Route Calculation
    ↓
Distance/Duration
    ↓
Price Calculation
```

### Email System
```
Event Triggered
    ↓
Load Template
    ↓
Replace Variables
    ↓
Send via SMTP
    ↓
Log Email
```

## Security Architecture

### Application Security
- **Input Validation**: Form requests, sanitization
- **SQL Injection**: Prevented by Eloquent ORM
- **XSS Protection**: Auto-escaping in Blade/React
- **CSRF Protection**: Laravel middleware
- **Rate Limiting**: API throttling

### Payment Security
- **PCI Compliance**: No card storage
- **Stripe Elements**: Secure card input
- **HTTPS Only**: Enforced in production
- **Webhook Verification**: Stripe signature validation

### Authentication & Authorization
- **Password Hashing**: Bcrypt with salt rounds
- **Session Security**: Database sessions, HTTPS only
- **API Tokens**: Sanctum with expiration
- **Admin Access**: Filament policies

## Deployment Architecture

### Infrastructure
```
Hostinger Shared Hosting
    ├── Apache/LiteSpeed
    ├── PHP 8.2 FPM
    ├── MySQL 8.0
    └── File Storage
```

### Deployment Process
1. Git push to repository
2. SSH to server
3. Pull latest changes
4. Run migrations
5. Clear caches
6. Build frontend assets
7. Restart queue workers

### Environment Management
- **Production**: `.env.production`
- **Staging**: `.env.staging`
- **Local**: `.env`
- **Settings Override**: Via Filament admin

## Scalability Considerations

### Current Limitations (Shared Hosting)
- Single server deployment
- File-based sessions/cache
- No Redis/Memcached
- Limited queue workers

### Future Scalability Path
1. **Phase 1**: Move to VPS
   - Redis for caching/sessions
   - Dedicated queue workers
   - Better performance monitoring

2. **Phase 2**: Horizontal Scaling
   - Load balancer
   - Multiple app servers
   - Dedicated database server
   - CDN for assets

3. **Phase 3**: Microservices
   - Separate email service
   - Payment service
   - Notification service
   - API gateway

## Monitoring & Logging

### Current Implementation
- **Error Logging**: Laravel log files
- **Email Logs**: Database tracking
- **Payment Logs**: Stripe dashboard
- **Performance**: Basic Laravel debugging

### Recommended Additions
- Application Performance Monitoring (APM)
- Error tracking (Sentry/Bugsnag)
- Uptime monitoring
- Database query analysis
- User behavior analytics

## Development Practices

### Code Organization
- **Single Responsibility**: One class, one purpose
- **DRY Principle**: Reusable services and components
- **SOLID Principles**: Followed in service layer
- **PSR Standards**: PSR-12 coding style

### Testing Strategy
- **Unit Tests**: Service methods, model methods
- **Feature Tests**: API endpoints, user flows
- **Browser Tests**: Critical user paths (future)
- **Test Database**: Separate from development

### Version Control
- **Git Flow**: Feature branches, develop, main
- **Commit Standards**: Conventional commits
- **Code Review**: Before merging to main
- **CI/CD**: Planned implementation