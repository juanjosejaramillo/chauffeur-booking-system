# Analytics Documentation - Microsoft Clarity

## Overview
Microsoft Clarity provides free, comprehensive behavioral analytics for the LuxRide booking system. It offers session recordings, heatmaps, and detailed insights to understand user behavior and optimize conversion rates.

## Integration Details

### Script Configuration
- **Project ID**: `t26s11c8vq`
- **Script Location**: `/index.html` (in `<head>` section)
- **Service Module**: `/src/services/clarityTracking.js`
- **Dashboard URL**: [clarity.microsoft.com](https://clarity.microsoft.com)

### Implementation Architecture
```
index.html (Script Tag)
    ↓
ClarityTracking Service
    ↓
React Components
    ├── BookingWizard
    ├── TripDetailsLuxury
    ├── VehicleSelectionLuxury
    ├── CustomerInfoLuxury
    ├── ReviewBookingLuxury
    ├── PaymentLuxury
    ├── ConfirmationLuxury
    └── TipPayment
```

## Tracked Events

### Booking Funnel Events

#### Step 1: Trip Details
- `trip_details_viewed` - User reached trip details page
- `pickup_field_focused` - User focused on pickup address field
- `dropoff_field_focused` - User focused on dropoff address field
- `address_search_pickup_started` - Pickup address search initiated
- `address_search_pickup_completed` - Pickup address selected
- `address_search_dropoff_started` - Dropoff address search initiated
- `address_search_dropoff_completed` - Dropoff address selected
- `pickup_date_selected` - Date selected for pickup
- `trip_details_submit_attempted` - Form submission attempted
- `trip_details_completed` - Successfully moved to next step

#### Step 2: Vehicle Selection
- `vehicle_prices_displayed` - Prices loaded and displayed
- `vehicle_selected` - User selected a specific vehicle
- `vehicle_selection_completed` - Successfully moved to next step

#### Step 3: Customer Information
- `customer_name_entered` - Name field completed
- `customer_email_entered` - Email field completed
- `customer_phone_entered` - Phone field completed
- `email_verification_requested` - Verification code requested
- `email_verification_resent` - Code resent
- `email_verification_verified` - Email successfully verified
- `email_verification_failed` - Verification failed
- `email_change_requested` - User clicked "Wrong email?"

#### Step 4: Review Booking
- `review_booking_viewed` - Review page displayed
- `legal_terms_clicked` - Terms and conditions opened
- `legal_cancellation_clicked` - Cancellation policy opened
- `terms_agreed` - Checkbox checked
- `terms_disagreed` - Checkbox unchecked
- `booking_creation_attempted` - Booking submission attempted
- `booking_created` - Booking successfully created

#### Step 5: Payment
- `payment_page_viewed` - Payment page displayed
- `begin_checkout` - Payment process started
- `tip_selected` - Gratuity amount selected
- `tip_custom_entered` - Custom tip amount entered
- `save_card_toggled` - Save payment method option changed
- `payment_form_interacted` - User interacted with payment form
- `payment_attempted` - Payment submission attempted
- `payment_succeeded` - Payment successful
- `payment_failed` - Payment failed

#### Step 6: Confirmation
- `booking_completed` - Booking confirmed and completed

### Tip Payment Flow Events
- `tip_page_viewed` - Tip payment page accessed
- `tip_amount_selected` - Tip amount chosen
- `tip_payment_attempted` - Tip payment submitted
- `tip_payment_succeeded` - Tip successfully processed
- `tip_payment_failed` - Tip payment failed

### Navigation Events
- `booking_started` - New booking initiated
- `booking_step_[1-6]_[step_name]` - Step progression tracking
- `navigation_step_change` - User navigated between steps
- `navigation_browser_back` - Browser back button used
- `booking_abandoned_from_confirmation` - User left after confirmation

### Error Events
- `error_trip_details_[type]` - Trip details validation errors
- `error_vehicle_selection_[type]` - Vehicle selection errors
- `error_customer_info_[type]` - Customer info validation errors
- `error_payment_[type]` - Payment processing errors

## Custom Tags

### User Identification
- **User ID**: Hashed email address (privacy-compliant)
- **Session ID**: Unique session identifier
- **Friendly Name**: Customer name (when available)

### Booking Attributes
- `booking_id` - Unique booking reference
- `booking_amount` - Total booking value
- `booking_value` - Categorized as low/medium/high
- `booking_type` - same_day or advance
- `booking_step` - Current step name
- `booking_step_number` - Current step number (1-6)

### Trip Details
- `trip_type` - airport_transfer or point_to_point
- `pickup_type` - airport, venue, or address
- `dropoff_type` - airport, venue, or address
- `pickup_timing` - same_day or future
- `pickup_is_airport` - true/false
- `dropoff_is_airport` - true/false

### Vehicle Information
- `vehicle_type` - Selected vehicle category
- `selected_vehicle` - Specific vehicle name
- `vehicle_price` - Vehicle fare amount
- `vehicle_category` - Vehicle classification

### Payment Details
- `payment_method` - new_card or saved_card
- `save_card` - yes or no
- `has_gratuity` - yes or no
- `gratuity_percent` - Tip percentage selected
- `tip_amount` - Tip dollar amount
- `tip_percentage` - Tip percentage for dedicated tips
- `tip_payment_method` - Payment method for tips
- `tip_source` - qr_code, email_link, etc.

### User Behavior
- `device_type` - mobile or desktop
- `screen_size` - small, medium, or large
- `timezone` - User's timezone
- `referrer` - Traffic source domain
- `nav_method` - button, back_button, or browser_back
- `navigation_direction` - forward or backward

### Validation & Errors
- `error_form` - Form where error occurred
- `error_type` - Type of error
- `error_message` - Truncated error message (max 100 chars)
- `email_verification_status` - Current verification state

## Session Management

### Session Prioritization
Sessions are automatically upgraded (marked as high-priority) when:
1. **Payment Attempted** - User reaches payment submission
2. **Booking Completed** - Successful conversion
3. **High-Value Booking** - Booking amount > $200
4. **Error Recovery** - User recovers from validation errors

### User Identification
Users are identified at two key points:
1. **Email Verification** - When email is successfully verified
2. **Booking Completion** - Final conversion tracking

Identification uses SHA-256 hashed email addresses for privacy compliance.

## Testing Guide

### Local Development Testing

#### 1. Verify Script Loading
```javascript
// Browser console
console.log(typeof window.clarity); // Should output: "function"
console.log(window.clarity.q); // Should show queued commands array
```

#### 2. Test Event Tracking
```javascript
// Manually trigger test events
window.clarity('event', 'test_booking_flow');
window.clarity('set', 'test_tag', 'test_value');
window.clarity('identify', 'test_user_123');
window.clarity('upgrade', 'testing');
```

#### 3. Monitor Console Logs
Enable console logging to see real-time tracking:
- Look for "Clarity: Event tracked - [event_name]"
- Check for "Clarity: Tag set - [key]: [value]"
- Verify "Clarity: User identified" messages

### Step-by-Step Testing Checklist

#### Trip Details Page
- [ ] Page view tracked
- [ ] Pickup address field focus tracked
- [ ] Address search tracked
- [ ] Address selection tracked with location type
- [ ] Date selection tracked
- [ ] Form submission tracked
- [ ] Validation errors tracked

#### Vehicle Selection Page
- [ ] Prices displayed event tracked
- [ ] Vehicle selection tracked with details
- [ ] Proceed to next step tracked

#### Customer Info Page
- [ ] Form field interactions tracked
- [ ] Email verification flow tracked
- [ ] User identification on verification
- [ ] Validation errors tracked

#### Review Booking Page
- [ ] Legal document clicks tracked
- [ ] Terms agreement tracked
- [ ] Booking creation tracked

#### Payment Page
- [ ] Begin checkout tracked
- [ ] Tip selection tracked
- [ ] Payment attempt tracked
- [ ] Session upgraded on attempt
- [ ] Success/failure tracked

#### Confirmation Page
- [ ] Conversion tracked with details
- [ ] User identified
- [ ] Session upgraded

### Production Testing
1. Use incognito/private browsing mode
2. Complete a full test booking
3. Wait 2-5 minutes for data to appear
4. Check Clarity dashboard for:
   - Session recording availability
   - Custom events in timeline
   - Tags properly set
   - User identification working

## Dashboard Usage

### Accessing the Dashboard
1. Navigate to [clarity.microsoft.com](https://clarity.microsoft.com)
2. Sign in with Microsoft account
3. Select "LuxRide Booking" project
4. Project ID: `t26s11c8vq`

### Key Metrics to Monitor

#### Conversion Funnel
- Step completion rates
- Drop-off points
- Average time per step
- Back navigation frequency

#### User Behavior
- Click/tap heatmaps
- Scroll depth
- Rage clicks
- Dead clicks
- Quick backs

#### Technical Issues
- JavaScript errors
- Network failures
- Slow page loads
- Form validation errors

### Creating Filters
Use custom tags to filter sessions:
- High-value bookings: `booking_value = high`
- Mobile users: `device_type = mobile`
- Airport transfers: `trip_type = airport_transfer`
- Payment failures: `payment_status = failed`
- Same-day bookings: `booking_type = same_day`

### Segments to Create
1. **Completed Bookings** - Users who reached confirmation
2. **Payment Abandonment** - Users who left at payment
3. **Mobile Users** - Device type = mobile
4. **High Value** - Booking amount > $200
5. **Error Recovery** - Users who encountered and fixed errors

## Privacy & Compliance

### Data Handling
- **Email Hashing**: All email addresses are SHA-256 hashed before sending
- **No PII in Events**: Personal information excluded from event names
- **Truncated Messages**: Error messages limited to 100 characters
- **Secure Transmission**: All data sent over HTTPS
- **Data Retention**: Follows Microsoft Clarity's retention policy

### GDPR Compliance
- Clarity is GDPR compliant
- No cookies required for basic functionality
- User consent not required for analytics
- Right to deletion supported

### Sensitive Data Exclusion
Never track:
- Full credit card numbers
- CVV codes
- Passwords
- Unencrypted email addresses
- Phone numbers in events
- Personal messages

## Troubleshooting

### Common Issues

#### Events Not Appearing
1. Check ad blockers - may block Clarity
2. Verify script is loading (network tab)
3. Check browser console for errors
4. Ensure project ID matches: `t26s11c8vq`

#### Session Recordings Missing
1. Sessions may take up to 30 minutes to process
2. Check if session was upgraded (priority sessions process faster)
3. Verify JavaScript errors aren't blocking recording

#### User Identification Not Working
1. Confirm email is being hashed
2. Check timing of identify call
3. Verify user ID format is correct

#### Tags Not Showing
1. Check tag value types (must be string or string array)
2. Verify tag key names (no spaces or special characters)
3. Confirm tags are set before session ends

### Debug Commands
```javascript
// Check if Clarity is blocked
if (typeof window.clarity === 'undefined') {
    console.error('Clarity is not loaded - check ad blockers');
}

// View all queued commands
console.log('Clarity Queue:', window.clarity.q);

// Test basic functionality
window.clarity('event', 'debug_test');
window.clarity('set', 'debug', 'true');

// Force session upgrade
window.clarity('upgrade', 'debugging');
```

## Optimization Recommendations

### Based on Analytics Data

#### High Priority
1. Monitor payment step drop-off rate
2. Analyze email verification friction
3. Track mobile vs desktop conversion rates
4. Identify common validation errors

#### Optimization Opportunities
1. Reduce form fields if high abandonment
2. Improve error messages based on confusion
3. Optimize for devices with lower conversion
4. A/B test tip selection options

### Regular Review Checklist
- [ ] Weekly: Check conversion funnel
- [ ] Weekly: Review error rates
- [ ] Monthly: Analyze heatmaps
- [ ] Monthly: Watch session recordings
- [ ] Quarterly: Review and update tracking

## Maintenance

### Adding New Events
1. Add event in component using `ClarityTracking.event()`
2. Document event in this file
3. Test locally with console monitoring
4. Deploy and verify in production

### Updating Tags
1. Modify tag in `ClarityTracking.setTag()`
2. Update documentation
3. Consider backward compatibility
4. Update dashboard filters if needed

### Version Updates
Keep track of significant tracking changes:
- 2025-08-29: Initial Clarity integration
- Replaced Hotjar with Microsoft Clarity
- Added comprehensive booking funnel tracking

## Support Resources

### Microsoft Clarity
- [Documentation](https://docs.microsoft.com/en-us/clarity/)
- [JavaScript API](https://docs.microsoft.com/en-us/clarity/clarity-api)
- [FAQ](https://docs.microsoft.com/en-us/clarity/faq)
- [Support](https://clarity.microsoft.com/support)

### Internal Resources
- Main documentation: `/CLAUDE.md`
- Frontend README: `/README.md`
- Tracking service: `/src/services/clarityTracking.js`

## Contact
- **Project Owner**: LuxRide SUV
- **Admin Email**: admin@luxridesuv.com
- **Support Email**: contact@luxridesuv.com
- **Clarity Project ID**: t26s11c8vq