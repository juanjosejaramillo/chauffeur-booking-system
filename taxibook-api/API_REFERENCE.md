# API Reference

## Base URL
- **Production**: `https://book.luxridesuv.com/api`
- **Local**: `http://localhost:8000/api`

## Authentication
The API uses Laravel Sanctum for authentication. Most booking endpoints are public, while user-specific endpoints require authentication.

### Headers
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}  # For protected routes
```

## Endpoints

### Authentication

#### Register User
```http
POST /register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "1|laravel_sanctum_token..."
}
```

#### Login
```http
POST /login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "2|laravel_sanctum_token..."
}
```

#### Logout
```http
POST /logout
```
**Authentication:** Required

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

#### Get Current User
```http
GET /user
```
**Authentication:** Required

**Response (200):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2024-01-01T00:00:00Z"
}
```

### Settings

#### Get Public Settings
```http
GET /settings/public
```

**Response (200):**
```json
{
  "stripe_enabled": true,
  "stripe_publishable_key": "pk_test_...",
  "google_maps_enabled": true,
  "google_maps_api_key": "AIza...",
  "business_name": "LuxRide",
  "business_phone": "1-800-LUXRIDE",
  "business_email": "contact@luxridesuv.com",
  "booking_form_fields": [
    {
      "key": "flight_number",
      "label": "Flight Number",
      "type": "text",
      "required": false,
      "placeholder": "e.g., AA1234"
    }
  ]
}
```

### Bookings

#### Validate Route
```http
POST /bookings/validate-route
```

**Request Body:**
```json
{
  "pickup_address": "123 Main St, City, State",
  "dropoff_address": "456 Oak Ave, City, State"
}
```

**Response (200):**
```json
{
  "valid": true,
  "pickup": {
    "address": "123 Main St, City, State",
    "latitude": 25.7617,
    "longitude": -80.1918
  },
  "dropoff": {
    "address": "456 Oak Ave, City, State",
    "latitude": 25.7650,
    "longitude": -80.1950
  },
  "route": {
    "distance": 5.2,
    "duration": 15,
    "polyline": "encoded_polyline_string"
  },
  "is_airport_pickup": false,
  "is_airport_dropoff": true
}
```

#### Calculate Prices
```http
POST /bookings/calculate-prices
```

**Request Body:**
```json
{
  "pickup_address": "123 Main St",
  "dropoff_address": "Airport",
  "pickup_latitude": 25.7617,
  "pickup_longitude": -80.1918,
  "dropoff_latitude": 25.7650,
  "dropoff_longitude": -80.1950,
  "distance": 5.2,
  "duration": 15
}
```

**Response (200):**
```json
{
  "vehicles": [
    {
      "id": 1,
      "name": "Luxury Sedan",
      "description": "Mercedes S-Class or similar",
      "base_price": 50.00,
      "per_mile_rate": 3.50,
      "estimated_fare": 68.20,
      "image_url": "/images/sedan.jpg",
      "max_passengers": 3,
      "max_luggage": 3
    },
    {
      "id": 2,
      "name": "Luxury SUV",
      "description": "Cadillac Escalade or similar",
      "base_price": 70.00,
      "per_mile_rate": 4.50,
      "estimated_fare": 93.40,
      "image_url": "/images/suv.jpg",
      "max_passengers": 6,
      "max_luggage": 6
    }
  ]
}
```

#### Send Email Verification
```http
POST /bookings/send-verification
```

**Request Body:**
```json
{
  "email": "customer@example.com",
  "booking_data": {
    "pickup_address": "123 Main St",
    "dropoff_address": "Airport",
    "pickup_date": "2024-12-25 10:00:00"
  }
}
```

**Response (200):**
```json
{
  "message": "Verification code sent",
  "expires_in": 600
}
```

#### Verify Email Code
```http
POST /bookings/verify-email
```

**Request Body:**
```json
{
  "email": "customer@example.com",
  "code": "429832"
}
```

**Response (200):**
```json
{
  "valid": true,
  "message": "Email verified successfully"
}
```

#### Create Booking
```http
POST /bookings
```

**Request Body:**
```json
{
  "vehicle_type_id": 1,
  "customer_first_name": "John",
  "customer_last_name": "Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+1234567890",
  "pickup_address": "123 Main St",
  "pickup_latitude": 25.7617,
  "pickup_longitude": -80.1918,
  "dropoff_address": "Airport",
  "dropoff_latitude": 25.7650,
  "dropoff_longitude": -80.1950,
  "pickup_date": "2024-12-25 10:00:00",
  "estimated_distance": 5.2,
  "estimated_duration": 15,
  "estimated_fare": 68.20,
  "special_instructions": "Please call upon arrival",
  "additional_data": {
    "flight_number": "AA1234",
    "number_of_bags": "2",
    "child_seats": "1",
    "meet_and_greet": true
  }
}
```

**Response (201):**
```json
{
  "booking": {
    "id": 123,
    "booking_number": "BK20241225001",
    "status": "pending",
    "payment_status": "pending",
    "customer_name": "John Doe",
    "pickup_address": "123 Main St",
    "dropoff_address": "Airport",
    "pickup_date": "2024-12-25 10:00:00",
    "estimated_fare": 68.20,
    "vehicle_type": "Luxury Sedan"
  }
}
```

#### Get Booking Details
```http
GET /bookings/{bookingNumber}
```

**Response (200):**
```json
{
  "booking": {
    "id": 123,
    "booking_number": "BK20241225001",
    "status": "confirmed",
    "payment_status": "paid",
    "customer_first_name": "John",
    "customer_last_name": "Doe",
    "customer_email": "john@example.com",
    "customer_phone": "+1234567890",
    "pickup_address": "123 Main St",
    "dropoff_address": "Airport",
    "pickup_date": "2024-12-25 10:00:00",
    "estimated_fare": 68.20,
    "final_fare": 68.20,
    "vehicle_type": {
      "id": 1,
      "name": "Luxury Sedan",
      "description": "Mercedes S-Class or similar"
    },
    "additional_data": {
      "flight_number": "AA1234"
    }
  }
}
```

#### Create Payment Intent
```http
POST /bookings/{bookingNumber}/payment-intent
```

**Request Body:**
```json
{
  "save_payment_method": false
}
```

**Response (200):**
```json
{
  "client_secret": "pi_1234_secret_5678",
  "amount": 6820,
  "currency": "usd",
  "stripe_publishable_key": "pk_test_..."
}
```

#### Confirm Payment
```http
POST /bookings/{bookingNumber}/confirm-payment
```

**Request Body:**
```json
{
  "payment_intent_id": "pi_1234567890",
  "payment_method_id": "pm_1234567890"
}
```

**Response (200):**
```json
{
  "success": true,
  "booking": {
    "booking_number": "BK20241225001",
    "status": "confirmed",
    "payment_status": "paid"
  }
}
```

#### Get User Bookings
```http
GET /user/bookings
```
**Authentication:** Required

**Query Parameters:**
- `status` (optional): Filter by status (pending, confirmed, completed, cancelled)
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 10)

**Response (200):**
```json
{
  "data": [
    {
      "id": 123,
      "booking_number": "BK20241225001",
      "status": "confirmed",
      "pickup_date": "2024-12-25 10:00:00",
      "pickup_address": "123 Main St",
      "dropoff_address": "Airport",
      "estimated_fare": 68.20
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 47
  }
}
```

### Tips/Gratuity

#### Get Booking for Tip
```http
GET /tip/{token}
```

**Response (200):**
```json
{
  "booking": {
    "booking_number": "BK20241225001",
    "customer_name": "John Doe",
    "pickup_date": "2024-12-25 10:00:00",
    "driver_name": "Mike Smith",
    "final_fare": 68.20,
    "can_add_tip": true
  }
}
```

#### Create Tip Payment Intent
```http
POST /tip/{token}/payment-intent
```

**Request Body:**
```json
{
  "amount": 15.00
}
```

**Response (200):**
```json
{
  "client_secret": "pi_tip_secret_123",
  "amount": 1500,
  "currency": "usd",
  "stripe_publishable_key": "pk_test_..."
}
```

#### Process Tip
```http
POST /tip/{token}/process
```

**Request Body:**
```json
{
  "amount": 15.00,
  "payment_intent_id": "pi_tip_123",
  "payment_method_id": "pm_card_123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Thank you for your gratuity!",
  "tip_amount": 15.00,
  "total_paid": 83.20
}
```

### Webhooks

#### Stripe Webhook
```http
POST /stripe/webhook
```

**Headers:**
```http
Stripe-Signature: t=timestamp,v1=signature
```

**Request Body:**
Stripe event object (payment_intent.succeeded, payment_intent.failed, etc.)

**Response (200):**
```json
{
  "success": true
}
```

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "phone": ["The phone format is invalid."]
  }
}
```

### Not Found (404)
```json
{
  "message": "Booking not found"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated"
}
```

### Server Error (500)
```json
{
  "message": "An error occurred processing your request"
}
```

## Rate Limiting

API endpoints are rate-limited to prevent abuse:
- **Public endpoints**: 60 requests per minute
- **Authenticated endpoints**: 120 requests per minute

Rate limit headers:
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1640995200
```

## Webhooks Configuration

### Stripe Webhooks
Configure in Stripe Dashboard:
- **Endpoint URL**: `https://book.luxridesuv.com/api/stripe/webhook`
- **Events to listen**:
  - `payment_intent.succeeded`
  - `payment_intent.failed`
  - `charge.refunded`

## Testing

### Test Credit Cards (Stripe Test Mode)
- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **3D Secure**: `4000 0025 0000 3155`

### Test Environment
Use test API keys and test mode in settings:
- Stripe test keys start with `pk_test_` and `sk_test_`
- Enable test mode in admin settings

## SDK Examples

### JavaScript/Axios
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://book.luxridesuv.com/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Create booking
const createBooking = async (bookingData) => {
  try {
    const response = await api.post('/bookings', bookingData);
    return response.data;
  } catch (error) {
    console.error('Booking failed:', error.response.data);
  }
};
```

### PHP/Guzzle
```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://book.luxridesuv.com/api/',
    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ]
]);

// Get public settings
$response = $client->get('settings/public');
$settings = json_decode($response->getBody(), true);
```

### cURL
```bash
# Create booking
curl -X POST https://book.luxridesuv.com/api/bookings \
  -H "Content-Type: application/json" \
  -d '{
    "customer_first_name": "John",
    "customer_email": "john@example.com",
    ...
  }'
```