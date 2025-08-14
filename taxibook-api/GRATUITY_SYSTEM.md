# Gratuity System Documentation

## Overview
This document describes the complete gratuity (tipping) system implementation for the chauffeur booking platform.

## System Architecture

### Payment Flow
1. **At Booking**: Customer pays fare immediately with optional tip
2. **After Trip**: Admin can send tip link or show QR code for additional gratuity
3. **No Auto-emails**: System only sends emails when manually triggered

## Database Schema

### New Fields in `bookings` table:
- `gratuity_amount` - Tip amount (decimal)
- `gratuity_added_at` - When tip was added (timestamp)
- `tip_link_token` - Unique token for tip links (string)
- `tip_link_sent_at` - When tip link was sent (timestamp)
- `save_payment_method` - Whether to save card (boolean)
- `stripe_customer_id` - Stripe customer ID (string)
- `qr_code_data` - Base64 encoded QR code (text)

## Backend Components

### 1. Services

#### StripeService (`app/Services/StripeService.php`)
- `chargeBooking()` - Charges fare + optional tip immediately
- `chargeTip()` - Processes post-trip gratuity
- Handles saved payment methods

#### TipService (`app/Services/TipService.php`)
- `sendTipLink()` - Sends email with tip link
- `getQrCode()` - Generates QR code for in-person tipping
- `processTip()` - Handles tip payment processing
- `getBookingForTip()` - Retrieves booking data for tip page

### 2. Controllers

#### BookingController
- Modified `store()` method to handle immediate payment with optional tip
- Processes `gratuity_amount` and `save_payment_method` fields

#### TipController (`app/Http/Controllers/Api/TipController.php`)
**API Endpoints:**
- `POST /api/bookings/{booking}/send-tip-link` - Send tip email (admin)
- `GET /api/bookings/{booking}/tip-qr` - Get QR code (admin)
- `GET /api/tip/{token}` - Get booking for tip page (public)
- `POST /api/tip/{token}/process` - Process tip payment (public)
- `POST /api/tip/{token}/payment-intent` - Create payment intent for tip

#### TipPaymentController (`app/Http/Controllers/TipPaymentController.php`)
**Web Routes:**
- `GET /tip/{token}` - Show tip payment page
- `GET /tip/{token}/success` - Show success page

### 3. Models

#### Booking Model Updates
- Added gratuity fields to `$fillable`
- Added casts for new fields
- Helper methods:
  - `hasTipped()` - Check if booking has tip
  - `canAddTip()` - Check if tip can be added
  - `getTotalAmountAttribute()` - Get fare + tip
  - `generateTipToken()` - Generate unique tip token

## Admin Panel Features

### Booking Table Actions (`app/Filament/Resources/Bookings/Tables/BookingsTable.php`)

#### 1. Capture with Tip
- For verbal tip confirmations
- Admin selects percentage or custom amount
- Charges saved card if available

#### 2. Send Tip Link
- Sends email to customer
- Only available for completed trips without tips
- Email contains payment link and suggested amounts

#### 3. Show Tip QR
- Displays QR code in modal
- Customer can scan to add tip
- Includes copyable link

## Frontend Components

### 1. Booking Form (`resources/js/components/`)
Available in both Vue and React versions:

**Features:**
- Gratuity selection (0%, 15%, 20%, 25%, custom)
- Real-time total calculation
- Save card checkbox with clear benefits
- Payment notice explaining immediate charge

### 2. Tip Payment Page (`resources/views/tip/payment.blade.php`)
**Features:**
- Trip details display
- Suggested tip buttons
- Custom tip input
- Stripe card element for new payments
- Uses saved card if available

### 3. Email Template (`resources/views/emails/optional-tip.blade.php`)
- Professional design
- Clear "optional" messaging
- Suggested tip amounts
- Direct link to payment page

## API Usage Examples

### 1. Create Booking with Tip
```javascript
POST /api/bookings
{
  "vehicle_type_id": 1,
  "customer_first_name": "John",
  "customer_last_name": "Doe",
  "customer_email": "john@example.com",
  "payment_method_id": "pm_xxx",
  "gratuity_amount": 20.00,  // Optional tip
  "save_payment_method": true,
  // ... other booking fields
}
```

### 2. Send Tip Link (Admin)
```javascript
POST /api/bookings/123/send-tip-link
Authorization: Bearer {admin_token}
```

### 3. Process Tip (Customer)
```javascript
POST /api/tip/{token}/process
{
  "amount": 25.00,
  "payment_method_id": "pm_xxx"  // Optional if saved card exists
}
```

## Testing the System

### 1. Test Booking with Tip
```bash
# Use Stripe test card: 4242 4242 4242 4242
# Any future expiry date and any CVC
```

### 2. Test Admin Actions
1. Log into admin panel
2. Find a completed booking
3. Click action menu
4. Test "Send Tip Link" or "Show Tip QR"

### 3. Test Tip Payment Page
1. Access `/tip/{token}` with valid token
2. Select tip amount
3. Enter test card if no saved card
4. Submit payment

## Configuration

### Required Environment Variables
```env
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

### Email Configuration
Ensure mail settings are configured in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourcompany.com
```

## Security Considerations

1. **Payment Security**
   - All payments handled by Stripe
   - PCI compliance maintained
   - No card details stored locally

2. **Token Security**
   - 40-character random tokens for tip links
   - Tokens are unique per booking
   - Links can only be used once (tip already added check)

3. **Access Control**
   - Admin actions require authentication
   - Public tip pages use secure tokens
   - No booking details exposed without valid token

## Troubleshooting

### Common Issues

1. **QR Code Not Displaying**
   - Ensure `simplesoftwareio/simple-qrcode` package is installed
   - Run `composer require simplesoftwareio/simple-qrcode`

2. **Email Not Sending**
   - Check mail configuration in `.env`
   - Verify SMTP credentials
   - Check Laravel logs: `storage/logs/laravel.log`

3. **Stripe Payment Failing**
   - Verify Stripe keys in `.env`
   - Check Stripe dashboard for error details
   - Ensure webhook endpoint is configured if using webhooks

## Future Enhancements

1. **Automatic Tip Suggestions**
   - Based on trip distance/duration
   - Driver rating-based suggestions

2. **Tip Reporting**
   - Driver tip summaries
   - Tax reporting features
   - Tip analytics dashboard

3. **Multiple Payment Methods**
   - Support for digital wallets
   - Cash tip recording
   - Split payment options

## Support

For issues or questions:
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify Stripe dashboard for payment issues
4. Review this documentation for configuration details