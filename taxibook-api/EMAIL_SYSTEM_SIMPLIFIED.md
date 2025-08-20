# Simplified Email Template System

## Overview

The email template system has been simplified to resolve logical inconsistencies between triggers and timing. The new system clearly separates immediate event-driven emails from scheduled time-based emails.

## Key Changes

### 1. **Clear Separation of Email Types**

#### Immediate Emails (Event-Triggered)
- **When**: Sent immediately when specific events occur
- **Configuration**: Use `trigger_events` array + `send_timing_type: 'immediate'`
- **Examples**: Booking confirmation, cancellation, driver assigned
- **Logic**: Event happens → Email sent immediately

#### Scheduled Emails (Time-Based)  
- **When**: Sent at specific times relative to booking dates
- **Configuration**: Use `send_timing_type` + `send_timing_value` + `send_timing_unit`, leave `trigger_events` EMPTY
- **Examples**: 24-hour reminder, 2-hour reminder, follow-up emails
- **Logic**: Time condition met → Email sent

### 2. **Removed Logical Conflicts**

**Before (Problematic)**:
```php
// CONFUSING: Has both trigger AND timing
'trigger_events' => ['booking.reminder.2h'],  // Non-existent event
'send_timing_type' => 'before_pickup',        // Conflicting logic
'send_timing_value' => 2,
'send_timing_unit' => 'hours',
```

**After (Clear)**:
```php
// CLEAR: Time-based only, no triggers
'trigger_events' => [],                       // Empty for time-based
'send_timing_type' => 'before_pickup',        // Clear timing logic
'send_timing_value' => 2,
'send_timing_unit' => 'hours',
```

## Email Template Configuration

### Immediate Email Template Example
```php
[
    'name' => 'Booking Confirmation',
    'trigger_events' => ['booking.confirmed'],      // Event that fires the email
    'send_timing_type' => 'immediate',              // Send immediately
    'send_timing_value' => 0,                       // Not used for immediate
    'send_timing_unit' => 'minutes',                // Not used for immediate
]
```

### Scheduled Email Template Example
```php
[
    'name' => '24 Hour Reminder',
    'trigger_events' => [],                         // NO triggers for time-based
    'send_timing_type' => 'before_pickup',          // When relative to pickup
    'send_timing_value' => 24,                      // How many units
    'send_timing_unit' => 'hours',                  // Unit type
]
```

## Available Triggers (For Immediate Emails Only)

These triggers fire emails immediately when events occur:

### Booking Events
- `booking.created` - When a new booking is created (pending status)
- `booking.confirmed` - When booking is confirmed with payment
- `booking.modified` - When booking details are changed
- `booking.cancelled` - When booking is cancelled  
- `booking.completed` - When booking/trip is completed

### Trip Events
- `trip.started` - When trip starts (status changes to in_progress)

### Payment Events
- `payment.captured` - When payment is captured
- `payment.refunded` - When payment is refunded

## Timing Types (For Scheduled Emails Only)

### Available Timing Types
- `immediate` - Send right away (use with triggers)
- `before_pickup` - Send X time before pickup date/time
- `after_pickup` - Send X time after pickup date/time  
- `after_booking` - Send X time after booking was created
- `after_completion` - Send X time after trip was completed

### Timing Units
- `minutes` - For short intervals
- `hours` - Most common for reminders
- `days` - For follow-up emails

## Email Processing Flow

### 1. Immediate Emails (Event-Driven)
```
Event occurs (e.g., booking.confirmed)
    ↓
SendTriggeredEmails listener activated
    ↓
Find templates with matching trigger_events
    ↓
Filter: only send_timing_type = 'immediate'
    ↓
Send email immediately
```

### 2. Scheduled Emails (Time-Based)
```
Cron job runs ProcessScheduledEmails command
    ↓
Find templates with send_timing_type ≠ 'immediate'
    ↓
Calculate when email should be sent based on timing
    ↓
Find bookings that match the timing window
    ↓
Send emails for matching bookings
```

## Current Email Templates

### Immediate Templates
1. **Booking Created** - `booking.created`
2. **Booking Confirmation** - `booking.confirmed`
3. **Trip Started** - `trip.started`
4. **Trip Completed** - `booking.completed`
5. **Booking Modified** - `booking.modified`
6. **Booking Cancelled** - `booking.cancelled`
7. **Payment Captured** - `payment.captured`
8. **Payment Refunded** - `payment.refunded`
9. **New Booking Alert** (Admin) - `booking.confirmed`
10. **Cancellation Alert** (Admin) - `booking.cancelled`

### Scheduled Templates  
1. **24 Hour Reminder** - 24 hours before pickup
2. **2 Hour Reminder** - 2 hours before pickup
3. **Trip Follow-up** - 24 hours after completion

## Benefits of Simplified System

### 1. **Clear Logic**
- No more confusion between triggers vs timing
- Each email has one clear activation method
- Easy to understand when emails will be sent

### 2. **Reduced Conflicts**
- No more "trip starts" trigger with "2 hours before pickup" timing
- No more non-existent triggers like `booking.reminder.24h`
- Timing and triggers are mutually exclusive

### 3. **Better Maintainability**
- Easier to add new emails
- Clear separation of concerns
- Reduced debugging complexity

### 4. **Preserved Functionality**
- All existing email types still work
- No emails are lost or broken
- Same user experience

## Migration Process

### 1. Run New Seeder
```bash
# Clear existing templates and reseed
php artisan tinker
>>> \App\Models\EmailTemplate::truncate();
>>> exit

php artisan db:seed --class=SimplifiedEmailTemplateSeeder
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Verify Templates
Check admin panel to ensure all templates are configured correctly with either:
- `trigger_events` populated + `send_timing_type: immediate`
- `trigger_events` empty + timing configuration

## Troubleshooting

### Email Not Sending?

#### For Immediate Emails:
1. Check if the event is firing (check logs)
2. Verify `trigger_events` contains the correct event name
3. Ensure `send_timing_type` is set to `'immediate'`
4. Check template is active (`is_active: true`)

#### For Scheduled Emails:
1. Check if cron job is running `emails:process-scheduled`
2. Verify `send_timing_type` is NOT `'immediate'`
3. Ensure `trigger_events` is empty (or doesn't conflict)
4. Check timing calculation is correct
5. Verify bookings exist in the time window

### Common Issues Fixed:

#### ❌ Before: Confusing Configuration
```php
// This was confusing - has trigger AND timing
'name' => '2 Hour Reminder',
'trigger_events' => ['booking.reminder.2h'],  // Non-existent event!
'send_timing_type' => 'before_pickup',        // Conflicts with trigger
```

#### ✅ After: Clear Configuration  
```php
// This is clear - time-based only
'name' => '2 Hour Reminder', 
'trigger_events' => [],                       // Empty = time-based
'send_timing_type' => 'before_pickup',        // Clear timing logic
```

## Future Enhancements

The simplified system makes it easy to add:

1. **New Triggers**: Add real events to the system
2. **New Timing Types**: Like `before_driver_assigned`
3. **Complex Conditions**: Multiple trigger support
4. **A/B Testing**: Different templates for same trigger

## Admin Interface Guidelines

When creating new email templates:

### For Event-Based Emails:
1. Select appropriate trigger from dropdown
2. Set timing type to "Immediate"
3. Leave timing value/unit as default

### For Reminder/Follow-up Emails:
1. Leave triggers empty
2. Select appropriate timing type
3. Set timing value and unit
4. Test with sample booking

This simplified system maintains all existing functionality while making the email system much more logical and maintainable.

## Hardcoded Emails

Some emails remain hardcoded for reliability:

1. **Gratuity/Tip Request** (`OptionalTipEmail`)
   - Sent after trip completion when requesting tips
   - Hardcoded Mailable class
   - Not configurable via admin panel

2. **Email Verification** (`VerificationCodeMail`)
   - Sent during booking process for email verification
   - Hardcoded Mailable class
   - Not configurable via admin panel

## Recent Changes (2025-08-20)

### Trigger System Changes
- Removed all driver-related triggers (assigned, enroute, arrived)
- Removed admin summary triggers (daily, weekly)
- Removed payment.failed and payment.authorized triggers
- Added booking.created trigger for pending bookings (admin notification only)
- Added trip.started trigger for in-progress trips
- Kept gratuity and verification emails as hardcoded
- Admin email changed to admin@luxridesuv.com

### Template Updates
- **New Booking Pending**: Now sends only to admin with full customer information
- Uses yellow/amber warning colors for pending status
- Includes all customer details, trip info, and additional fields
- Shows clickable email/phone links for admin convenience

### Seeder Updates
- Using SimplifiedEmailTemplateSeeder exclusively
- Removed ComprehensiveEmailTemplateSeeder
- Creates 13 templates total (8 triggers + 5 scheduled/admin)