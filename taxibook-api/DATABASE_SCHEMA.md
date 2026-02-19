# Database Schema

## Overview
The database uses MySQL 8.0 with InnoDB engine for transaction support and foreign key constraints. All tables include soft deletes and timestamp fields for audit trails.

## Entity Relationship Diagram

```
users ──────────────┐
                    │
                    ▼
bookings ◄──────── transactions
    │
    ├──► vehicle_types ──► vehicle_pricing_tiers
    │
    ├──► booking_expenses
    │
    └──► email_logs ◄──── email_templates

booking_form_fields (standalone)
settings (key-value store)
personal_access_tokens (auth)
sessions (user sessions)
```

## Tables

### 1. **users**
User accounts for customers and admins.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | Full name |
| email | varchar(255) | Unique email |
| email_verified_at | timestamp | Email verification |
| password | varchar(255) | Hashed password (nullable for guest bookings) |
| remember_token | varchar(100) | Remember me token |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- UNIQUE: `email`

### 2. **bookings**
Core booking records with all trip details.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| booking_number | varchar(20) | Unique booking reference |
| user_id | bigint | Foreign key to users (nullable) |
| vehicle_type_id | bigint | Foreign key to vehicle_types |
| customer_first_name | varchar(255) | Customer first name |
| customer_last_name | varchar(255) | Customer last name |
| customer_email | varchar(255) | Customer email |
| customer_phone | varchar(255) | Customer phone |
| email_verification_code | varchar(6) | 6-digit verification code |
| email_verified_at | timestamp | When email was verified |
| verification_expires_at | timestamp | Code expiration |
| verification_attempts | int | Failed verification attempts |
| pickup_address | text | Pickup location |
| pickup_latitude | decimal(10,8) | Pickup coordinates |
| pickup_longitude | decimal(11,8) | Pickup coordinates |
| dropoff_address | text | Dropoff location |
| dropoff_latitude | decimal(10,8) | Dropoff coordinates |
| dropoff_longitude | decimal(11,8) | Dropoff coordinates |
| pickup_date | datetime | Scheduled pickup time |
| estimated_distance | decimal(8,2) | Distance in miles |
| estimated_duration | int | Duration in minutes |
| estimated_fare | decimal(10,2) | Calculated fare |
| final_fare | decimal(10,2) | Actual charged amount |
| fare_breakdown | json | Detailed fare calculation |
| gratuity_amount | decimal(10,2) | Tip amount |
| gratuity_added_at | timestamp | When tip was added |
| tip_link_token | varchar(64) | Unique token for tip link |
| tip_link_sent_at | timestamp | When tip link was sent |
| total_refunded | decimal(10,2) | Total refund amount |
| status | enum | pending, confirmed, in_progress, completed, cancelled |
| payment_status | enum | pending, authorized, captured, partially_refunded, refunded, failed |
| stripe_payment_intent_id | varchar(255) | Stripe payment intent |
| stripe_payment_method_id | varchar(255) | Stripe payment method |
| stripe_customer_id | varchar(255) | Stripe customer |
| save_payment_method | boolean | Save card for future |
| qr_code_data | text | QR code for tip |
| special_instructions | text | Customer notes |
| flight_number | varchar(50) | Flight information |
| is_airport_pickup | boolean | Airport pickup flag |
| is_airport_dropoff | boolean | Airport dropoff flag |
| additional_data | json | Dynamic form fields data |
| admin_notes | text | Internal notes |
| cancellation_reason | text | Why booking was cancelled |
| cancelled_at | timestamp | Cancellation time |
| created_at | timestamp | Booking creation |
| updated_at | timestamp | Last update |
| deleted_at | timestamp | Soft delete |

**Indexes:**
- PRIMARY: `id`
- UNIQUE: `booking_number`
- INDEX: `user_id`
- INDEX: `vehicle_type_id`
- INDEX: `status`
- INDEX: `payment_status`
- INDEX: `pickup_date`
- UNIQUE: `tip_link_token`

### 3. **vehicle_types**
Vehicle categories available for booking.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | Vehicle name |
| slug | varchar(255) | URL-friendly name |
| description | text | Vehicle description |
| base_price | decimal(10,2) | Base fare |
| per_mile_rate | decimal(8,2) | Rate per mile |
| per_minute_rate | decimal(8,2) | Rate per minute |
| minimum_fare | decimal(10,2) | Minimum charge |
| max_passengers | int | Passenger capacity |
| max_luggage | int | Luggage capacity |
| image_url | varchar(255) | Vehicle image |
| is_active | boolean | Available for booking |
| display_order | int | Sort order |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |
| deleted_at | timestamp | Soft delete |

**Indexes:**
- PRIMARY: `id`
- UNIQUE: `slug`
- INDEX: `is_active`
- INDEX: `display_order`

### 4. **vehicle_pricing_tiers**
Distance-based pricing tiers for vehicles.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| vehicle_type_id | bigint | Foreign key to vehicle_types |
| min_distance | decimal(8,2) | Minimum miles for tier |
| max_distance | decimal(8,2) | Maximum miles for tier |
| base_price | decimal(10,2) | Base price for tier |
| per_mile_rate | decimal(8,2) | Per mile rate for tier |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- INDEX: `vehicle_type_id`
- INDEX: `min_distance`, `max_distance`

### 5. **email_templates**
Email templates with variable replacement.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | Template name |
| slug | varchar(255) | Unique identifier |
| description | text | Template purpose |
| subject | varchar(255) | Email subject line |
| body | text | Plain text body |
| html_body | text | HTML body |
| template_type | enum | text, html, wysiwyg, blade |
| css_styles | text | Custom CSS |
| trigger_events | json | Events that trigger email |
| send_timing_type | enum | immediate, before_pickup, after_pickup, after_booking |
| send_timing_value | int | Timing value (hours/minutes) |
| send_timing_unit | enum | minutes, hours, days |
| send_to_customer | boolean | Send to customer |
| send_to_admin | boolean | Send to admin |
| send_to_driver | boolean | Send to driver |
| cc_emails | text | CC recipients |
| bcc_emails | text | BCC recipients |
| attach_receipt | boolean | Attach receipt PDF |
| attach_booking_details | boolean | Attach booking PDF |
| priority | int | Sending priority |
| is_active | boolean | Template enabled |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |
| deleted_at | timestamp | Soft delete |

**Indexes:**
- PRIMARY: `id`
- UNIQUE: `slug`
- INDEX: `is_active`
- INDEX: `trigger_events`

### 6. **email_logs**
Audit trail of all sent emails.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| booking_id | bigint | Foreign key to bookings |
| user_id | bigint | Foreign key to users |
| template_slug | varchar(255) | Template used |
| recipient_email | varchar(255) | Recipient address |
| recipient_name | varchar(255) | Recipient name |
| cc_emails | text | CC recipients |
| bcc_emails | text | BCC recipients |
| subject | varchar(255) | Email subject |
| body | text | Email content |
| attachments | json | Attached files |
| status | enum | pending, sent, failed |
| sent_at | timestamp | When sent |
| failed_at | timestamp | When failed |
| error_message | text | Error details |
| metadata | json | Additional data |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- INDEX: `booking_id`
- INDEX: `user_id`
- INDEX: `template_slug`
- INDEX: `status`
- INDEX: `sent_at`

### 7. **booking_form_fields**
Dynamic form field configuration.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | varchar(50) | Field identifier |
| label | varchar(255) | Display label |
| type | enum | text, email, tel, number, select, checkbox, radio, textarea, date, time |
| placeholder | varchar(255) | Placeholder text |
| helper_text | varchar(255) | Help text |
| validation_rules | json | Validation rules |
| options | json | Options for select/radio |
| default_value | varchar(255) | Default value |
| required | boolean | Is required |
| enabled | boolean | Is active |
| show_conditions | json | Conditional display |
| order | int | Display order |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- UNIQUE: `key`
- INDEX: `enabled`
- INDEX: `order`

### 8. **transactions**
Payment transaction records.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| booking_id | bigint | Foreign key to bookings |
| type | enum | payment, refund, tip |
| amount | decimal(10,2) | Transaction amount |
| currency | varchar(3) | Currency code |
| stripe_transaction_id | varchar(255) | Stripe ID |
| stripe_payment_method | varchar(255) | Payment method |
| status | enum | pending, processing, succeeded, failed |
| metadata | json | Additional data |
| processed_at | timestamp | Processing time |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- INDEX: `booking_id`
- INDEX: `type`
- INDEX: `status`
- INDEX: `stripe_transaction_id`

### 9. **booking_expenses**
Expense tracking per booking.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| booking_id | bigint | Foreign key to bookings |
| description | varchar(255) | Expense description (e.g., driver pay, tolls, fuel) |
| amount | decimal(10,2) | Expense amount |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- INDEX: `booking_id`

### 10. **settings**
Key-value configuration store.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | varchar(255) | Setting key |
| value | text | Setting value |
| type | varchar(50) | Data type |
| group | varchar(100) | Setting group |
| description | text | Setting description |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- UNIQUE: `key`
- INDEX: `group`

### 11. **personal_access_tokens**
Sanctum API tokens.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| tokenable_type | varchar(255) | Model type |
| tokenable_id | bigint | Model ID |
| name | varchar(255) | Token name |
| token | varchar(64) | Token hash |
| abilities | text | Token permissions |
| last_used_at | timestamp | Last usage |
| expires_at | timestamp | Expiration |
| created_at | timestamp | Creation date |
| updated_at | timestamp | Last update |

**Indexes:**
- PRIMARY: `id`
- UNIQUE: `token`
- INDEX: `tokenable_type`, `tokenable_id`

### 12. **sessions**
User session storage.

| Column | Type | Description |
|--------|------|-------------|
| id | varchar(255) | Session ID |
| user_id | bigint | User ID (nullable) |
| ip_address | varchar(45) | IP address |
| user_agent | text | Browser info |
| payload | longtext | Session data |
| last_activity | int | Unix timestamp |

**Indexes:**
- PRIMARY: `id`
- INDEX: `user_id`
- INDEX: `last_activity`

## Relationships

### One-to-Many
- `users` → `bookings` (A user can have many bookings)
- `vehicle_types` → `bookings` (A vehicle type can have many bookings)
- `vehicle_types` → `vehicle_pricing_tiers` (A vehicle can have multiple pricing tiers)
- `bookings` → `transactions` (A booking can have multiple transactions)
- `bookings` → `booking_expenses` (A booking can have multiple expenses)
- `bookings` → `email_logs` (A booking can have multiple email logs)
- `email_templates` → `email_logs` (A template can be used for many emails)

### Many-to-One
- `bookings` → `users` (optional - guest bookings allowed)
- `bookings` → `vehicle_types`
- `transactions` → `bookings`
- `booking_expenses` → `bookings`
- `email_logs` → `bookings`
- `email_logs` → `users`

## JSON Field Structures

### bookings.additional_data
```json
{
  "flight_number": "AA1234",
  "number_of_bags": "3",
  "child_seats": "1",
  "meet_and_greet": true,
  "special_occasion": "anniversary",
  "preferred_temperature": "cool"
}
```

### bookings.fare_breakdown
```json
{
  "base_fare": 50.00,
  "distance_charge": 18.20,
  "time_charge": 0,
  "airport_fee": 5.00,
  "service_fee": 2.50,
  "subtotal": 75.70,
  "tax": 6.30,
  "total": 82.00
}
```

### email_templates.trigger_events
```json
[
  "booking.confirmed",
  "booking.reminder.24h",
  "booking.cancelled"
]
```

### booking_form_fields.options
```json
[
  {"value": "0", "label": "No bags"},
  {"value": "1", "label": "1 bag"},
  {"value": "2", "label": "2 bags"},
  {"value": "3+", "label": "3 or more bags"}
]
```

### booking_form_fields.show_conditions
```json
{
  "field": "is_airport_transfer",
  "operator": "equals",
  "value": true
}
```

## Migration History
1. Initial Laravel setup (users, password_resets, etc.)
2. Create bookings table
3. Create vehicle_types and pricing tiers
4. Create email_templates table
5. Add email verification to bookings
6. Create email_logs table
7. Add gratuity fields to bookings
8. Create settings table
9. Add refund tracking
10. Create booking_form_fields table
11. Add dynamic fields to bookings
12. Enhance email templates
13. Migrate to Google Maps settings
14. Add email verification toggle setting
15. Add hourly booking fields and settings
16. Add payment mode setting
17. Add booking reserved email template
18. Create booking_expenses table

## Database Optimization

### Indexes Strategy
- Foreign keys are automatically indexed
- Frequently queried fields have indexes (status, dates)
- Composite indexes for related queries
- Unique constraints where applicable

### Performance Considerations
- Use eager loading to prevent N+1 queries
- JSON fields for flexible data without schema changes
- Soft deletes to maintain data history
- Appropriate column types and sizes

## Backup Strategy
- Daily automated backups
- Transaction logs for point-in-time recovery
- Test restore procedures regularly
- Off-site backup storage