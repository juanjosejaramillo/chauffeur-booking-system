# Features Documentation

## Core Features

### 1. Dynamic Booking System

#### Multi-Step Booking Flow
The booking process is divided into intuitive steps:

1. **Route Selection**
   - Address autocomplete via Mapbox
   - Real-time route validation
   - Distance and duration calculation
   - Visual map display with route
   - Airport detection (automatic)

2. **Vehicle Selection**
   - Dynamic pricing based on distance
   - Multiple vehicle types with images
   - Capacity indicators (passengers/luggage)
   - Pricing tier system
   - Base price + per mile calculation

3. **Customer Information**
   - Guest booking (no registration required)
   - Dynamic form fields (configurable)
   - Email verification before payment
   - Special instructions field
   - Flight number for airport transfers

4. **Payment Processing**
   - Stripe Elements for secure card input
   - Payment intent creation
   - Save card option for future use
   - Real-time validation
   - 3D Secure support

#### Dynamic Form Fields
Administrators can configure custom fields:
- **Field Types**: text, email, tel, number, select, checkbox, radio, textarea, date, time
- **Validation**: Required, patterns, min/max
- **Conditional Logic**: Show/hide based on other fields
- **Default Values**: Pre-filled options
- **Order Control**: Drag-and-drop ordering

**Current Fields**:
- Flight Number (for airport transfers)
- Number of Bags
- Child Seats Required
- Meet & Greet Service
- Special Occasion

### 2. Email System

#### Template Management
- **WYSIWYG Editor**: Rich text editing with preview
- **HTML Editor**: Direct HTML code editing
- **Variable System**: {{customer_name}}, {{booking_number}}, etc.
- **Luxe Design**: Professional email layout
- **Preview Function**: See email before sending

#### Automated Emails
Trigger-based email sending:

| Email | Trigger | Timing |
|-------|---------|--------|
| Booking Confirmation | booking.confirmed | Immediate |
| 24-Hour Reminder | booking.reminder.24h | 24 hours before |
| 2-Hour Reminder | booking.reminder.2h | 2 hours before |
| Driver Assigned | driver.assigned | When assigned |
| Driver En Route | driver.enroute | When dispatched |
| Trip Completed | booking.completed | After completion |
| Booking Modified | booking.modified | On changes |
| Booking Cancelled | booking.cancelled | On cancellation |

#### Email Features
- **Attachments**: Receipt PDFs, booking details
- **CC/BCC**: Additional recipients
- **Logging**: Complete audit trail
- **Error Handling**: Retry failed emails
- **Test Mode**: Send test emails

### 3. Payment System

#### Stripe Integration
- **Dual Mode**: Test and Live environments
- **Mode Switching**: Via admin panel
- **Payment Methods**: Cards (Visa, MC, Amex, Discover)
- **3D Secure**: European card authentication
- **Webhooks**: Payment confirmation

#### Payment Features
- **Payment Intents**: Secure payment flow
- **Saved Cards**: Store for repeat customers
- **Refunds**: Full and partial refunds
- **Receipt Generation**: PDF receipts
- **Transaction History**: Complete audit trail

#### Gratuity System
- **QR Code Generation**: For in-person tips
- **Tip Links**: Shareable URLs
- **Email Notifications**: Send tip requests
- **Flexible Amounts**: Customer chooses amount
- **Instant Processing**: Real-time payment

### 4. Admin Panel (Filament)

#### Dashboard
- **Statistics**: Bookings, revenue, trends
- **Recent Activity**: Latest bookings
- **Quick Actions**: Common tasks
- **System Status**: Health checks

#### Booking Management
- **List View**: Sortable, filterable table
- **Detail View**: Complete booking information
- **Actions**: Confirm, cancel, modify, refund
- **Status Tracking**: Visual status indicators
- **Notes**: Internal admin notes

#### Email Templates
- **Template Editor**: WYSIWYG interface
- **Variable Reference**: Available placeholders
- **Preview**: See rendered email
- **Test Send**: Send to test address
- **Active/Inactive**: Enable/disable templates

#### Vehicle Types
- **Configuration**: Name, description, image
- **Pricing**: Base price, per mile rate
- **Capacity**: Passengers and luggage
- **Tiers**: Distance-based pricing
- **Ordering**: Display order control

#### Settings Management
- **Business Info**: Company details
- **Payment Settings**: Stripe configuration
- **Email Settings**: SMTP configuration
- **Map Settings**: Mapbox configuration
- **Booking Settings**: Rules and limits

#### Form Builder
- **Field Creation**: Add custom fields
- **Field Types**: Various input types
- **Validation Rules**: Required, patterns
- **Conditional Logic**: Show/hide rules
- **Drag & Drop**: Reorder fields

### 5. Customer Features

#### Guest Booking
- No registration required
- Email verification for security
- Booking lookup by reference
- Receipt download
- Modification requests

#### User Accounts (Optional)
- **Registration**: Email/password
- **Login**: Secure authentication
- **Booking History**: Past trips
- **Saved Cards**: Payment methods
- **Profile**: Personal information

#### Booking Features
- **Real-time Pricing**: Instant quotes
- **Route Visualization**: Map display
- **Airport Detection**: Automatic recognition
- **Special Requests**: Custom instructions
- **Email Confirmations**: Instant receipts

### 6. Airport Features

#### Airport Detection
Automatically detects airport addresses:
- Tampa International Airport (TPA)
- Miami International Airport (MIA)
- Orlando International Airport (MCO)
- Fort Lauderdale Airport (FLL)
- Custom airport additions

#### Airport-Specific Fields
When airport detected:
- Flight number field appears
- Terminal selection (future)
- Meet & greet option
- Baggage claim instructions

### 7. Pricing System

#### Dynamic Pricing
- **Base Fare**: Starting price
- **Distance Rate**: Per mile charge
- **Time Rate**: Per minute (optional)
- **Minimum Fare**: Floor price
- **Airport Fees**: Additional charges

#### Pricing Tiers
Distance-based pricing adjustments:
```
0-10 miles: Base rate
10-25 miles: Reduced per-mile rate
25-50 miles: Further reduced rate
50+ miles: Long distance rate
```

#### Fare Calculation
```
Total = Base Fare 
      + (Distance × Per Mile Rate)
      + (Duration × Per Minute Rate)
      + Airport Fees
      + Service Fees
      + Tax
```

### 8. Notification System

#### Email Notifications
- **Customer Emails**: Booking journey emails
- **Admin Alerts**: New bookings, cancellations
- **Driver Notifications**: Assignment alerts (future)

#### SMS Notifications (Planned)
- Booking confirmations
- Reminders
- Driver arrival
- Trip completion

### 9. Security Features

#### Data Protection
- **Password Hashing**: Bcrypt encryption
- **SQL Injection**: Prevented via ORM
- **XSS Protection**: Input sanitization
- **CSRF Protection**: Token validation

#### Payment Security
- **PCI Compliance**: No card storage
- **Stripe Elements**: Secure tokenization
- **HTTPS Only**: Encrypted transmission
- **Webhook Verification**: Signature validation

#### Access Control
- **Admin Authentication**: Filament policies
- **API Protection**: Sanctum tokens
- **Rate Limiting**: Request throttling
- **Session Security**: Database storage

### 10. Reporting & Analytics

#### Booking Reports
- Daily/weekly/monthly summaries
- Revenue tracking
- Popular routes
- Vehicle utilization
- Customer demographics

#### Financial Reports
- Payment summaries
- Refund tracking
- Gratuity reports
- Tax calculations
- Stripe fee tracking

### 11. Mobile Responsiveness

#### Customer Interface
- Responsive design
- Touch-friendly controls
- Mobile-optimized forms
- Swipe gestures
- Progressive enhancement

#### Admin Panel
- Responsive tables
- Mobile navigation
- Touch interactions
- Compact views
- Essential features prioritized

### 12. Integration Capabilities

#### Current Integrations
- **Stripe**: Payment processing
- **Mapbox**: Maps and geocoding
- **Gmail SMTP**: Email delivery
- **DomPDF**: Document generation
- **Simple QRCode**: QR generation

#### API Access
- RESTful endpoints
- JSON responses
- Token authentication
- Rate limiting
- Webhook support

## Feature Configuration

### Enable/Disable Features
Via admin settings panel:
- Payment processing
- Email notifications
- Gratuity system
- User registration
- Dynamic fields

### Feature Flags (Future)
```php
if (feature('new-feature')) {
    // New feature code
}
```

## Feature Roadmap

### Completed ✅
- Core booking system
- Payment processing
- Email templates
- Admin panel
- Dynamic form fields
- Gratuity system
- Airport detection

### In Progress 🚧
- Analytics dashboard
- Advanced reporting
- Customer portal

### Planned 📋
- SMS notifications
- Driver mobile app
- Recurring bookings
- Loyalty program
- Multi-language support
- Advanced pricing rules
- Integration API
- Webhook management

## Feature Dependencies

### Required Services
- **Stripe Account**: Payment processing
- **Mapbox Account**: Maps and geocoding
- **SMTP Service**: Email delivery
- **SSL Certificate**: Secure connections

### Optional Services
- **SMS Gateway**: Text notifications
- **CDN**: Asset delivery
- **Analytics**: Google Analytics
- **Error Tracking**: Sentry/Bugsnag

## Feature Testing

### Test Scenarios
1. **Booking Flow**: Complete booking process
2. **Payment**: Test card processing
3. **Emails**: Verify template rendering
4. **Admin**: CRUD operations
5. **API**: Endpoint responses

### Test Data
- Use Stripe test cards
- Create test bookings
- Generate sample data
- Test email addresses

## Feature Documentation

### User Guides
- Customer booking guide
- Admin panel tutorial
- Email template guide
- Payment configuration
- Form builder tutorial

### Technical Docs
- API documentation
- Database schema
- Architecture overview
- Deployment guide
- Security practices