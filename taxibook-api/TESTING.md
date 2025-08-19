# Testing Guide

## Overview
This guide covers testing strategies, procedures, and best practices for the LuxRide Chauffeur Booking System. We use PHPUnit for backend testing and Jest/React Testing Library for frontend testing.

## Testing Philosophy
- **Test Critical Paths First**: Payment flow, booking creation, email sending
- **Prevent Regressions**: Write tests for bugs before fixing
- **Maintain High Coverage**: Aim for 80% code coverage
- **Test User Journeys**: Not just individual functions
- **Fast Feedback**: Keep tests fast and focused

## Backend Testing (Laravel/PHPUnit)

### Test Structure
```
tests/
├── Feature/           # Integration tests
│   ├── Api/          # API endpoint tests
│   ├── Booking/      # Booking flow tests
│   ├── Payment/      # Payment processing tests
│   └── Email/        # Email system tests
├── Unit/             # Unit tests
│   ├── Services/     # Service class tests
│   ├── Models/       # Model tests
│   └── Helpers/      # Helper function tests
└── TestCase.php      # Base test class
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/BookingTest.php

# Run with coverage
php artisan test --coverage

# Run in parallel
php artisan test --parallel

# Stop on first failure
php artisan test --stop-on-failure
```

### Writing Tests

#### Feature Test Example
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_create_booking()
    {
        // Arrange
        $vehicle = VehicleType::factory()->create();
        
        $bookingData = [
            'vehicle_type_id' => $vehicle->id,
            'customer_first_name' => 'John',
            'customer_last_name' => 'Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '+1234567890',
            'pickup_address' => '123 Main St',
            'pickup_latitude' => 27.9506,
            'pickup_longitude' => -82.4572,
            'dropoff_address' => 'Airport',
            'dropoff_latitude' => 27.9756,
            'dropoff_longitude' => -82.5333,
            'pickup_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'estimated_distance' => 15.5,
            'estimated_duration' => 25,
            'estimated_fare' => 75.00,
        ];

        // Act
        $response = $this->postJson('/api/bookings', $bookingData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'booking_number',
                    'verification_required',
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'customer_email' => 'john@example.com',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function booking_requires_email_verification()
    {
        // Test email verification flow
    }

    /** @test */
    public function verified_booking_can_process_payment()
    {
        // Test payment processing
    }
}
```

#### Unit Test Example
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PricingService;
use App\Models\VehicleType;

class PricingServiceTest extends TestCase
{
    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingService();
    }

    /** @test */
    public function calculates_fare_correctly()
    {
        // Arrange
        $vehicle = VehicleType::factory()->make([
            'base_price' => 50.00,
            'per_mile_rate' => 2.50,
        ]);
        
        $distance = 10.5; // miles
        $duration = 20; // minutes

        // Act
        $fare = $this->service->calculateFare(
            $vehicle,
            $distance,
            $duration
        );

        // Assert
        $expectedFare = 50.00 + (10.5 * 2.50); // 76.25
        $this->assertEquals($expectedFare, $fare['total']);
        $this->assertArrayHasKey('breakdown', $fare);
    }

    /** @test */
    public function applies_minimum_fare()
    {
        // Test minimum fare logic
    }

    /** @test */
    public function applies_airport_surcharge()
    {
        // Test airport fee calculation
    }
}
```

### Test Factories

```php
// database/factories/BookingFactory.php
namespace Database\Factories;

use App\Models\Booking;
use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'booking_number' => 'BK' . $this->faker->unique()->randomNumber(8),
            'vehicle_type_id' => VehicleType::factory(),
            'customer_first_name' => $this->faker->firstName,
            'customer_last_name' => $this->faker->lastName,
            'customer_email' => $this->faker->safeEmail,
            'customer_phone' => $this->faker->phoneNumber,
            'pickup_address' => $this->faker->address,
            'pickup_latitude' => $this->faker->latitude,
            'pickup_longitude' => $this->faker->longitude,
            'dropoff_address' => $this->faker->address,
            'dropoff_latitude' => $this->faker->latitude,
            'dropoff_longitude' => $this->faker->longitude,
            'pickup_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'estimated_distance' => $this->faker->randomFloat(2, 5, 50),
            'estimated_duration' => $this->faker->numberBetween(10, 90),
            'estimated_fare' => $this->faker->randomFloat(2, 30, 300),
            'status' => 'pending',
        ];
    }

    public function confirmed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'email_verified_at' => now(),
        ]);
    }

    public function withPayment(): self
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'captured',
            'stripe_payment_intent_id' => 'pi_' . $this->faker->uuid,
        ]);
    }
}
```

### Testing Stripe Integration

```php
use Stripe\StripeClient;
use Mockery;

/** @test */
public function processes_payment_successfully()
{
    // Mock Stripe client
    $stripeMock = Mockery::mock(StripeClient::class);
    $stripeMock->shouldReceive('paymentIntents->create')
        ->once()
        ->andReturn((object)[
            'id' => 'pi_test_123',
            'client_secret' => 'secret_123',
            'status' => 'requires_payment_method',
        ]);

    $this->app->instance(StripeClient::class, $stripeMock);

    // Test payment creation
    $booking = Booking::factory()->confirmed()->create();
    $response = $this->postJson("/api/bookings/{$booking->id}/payment", [
        'payment_method_id' => 'pm_card_visa',
    ]);

    $response->assertSuccessful();
}
```

### Testing Email Sending

```php
use Illuminate\Support\Facades\Mail;

/** @test */
public function sends_booking_confirmation_email()
{
    Mail::fake();

    // Create booking
    $booking = Booking::factory()->confirmed()->create();

    // Trigger email
    $this->notificationService->sendBookingConfirmation($booking);

    // Assert email was sent
    Mail::assertSent(BookingConfirmation::class, function ($mail) use ($booking) {
        return $mail->hasTo($booking->customer_email) &&
               $mail->booking->id === $booking->id;
    });
}
```

## Frontend Testing (React/Jest)

### Test Structure
```
taxibook-frontend/
├── src/
│   └── __tests__/
│       ├── components/    # Component tests
│       ├── pages/         # Page tests
│       ├── services/      # API service tests
│       └── utils/         # Utility tests
├── jest.config.js         # Jest configuration
└── setupTests.js          # Test setup
```

### Running Frontend Tests

```bash
# Run all tests
npm test

# Run with coverage
npm test -- --coverage

# Run in watch mode
npm test -- --watch

# Run specific test file
npm test BookingForm.test.js

# Update snapshots
npm test -- -u
```

### Writing Frontend Tests

#### Component Test Example
```javascript
// src/__tests__/components/BookingForm.test.jsx
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import BookingForm from '../../components/BookingForm';
import { BookingProvider } from '../../context/BookingContext';

describe('BookingForm', () => {
  it('renders all form steps', () => {
    render(
      <BookingProvider>
        <BookingForm />
      </BookingProvider>
    );

    expect(screen.getByText('Select Route')).toBeInTheDocument();
    expect(screen.getByText('Choose Vehicle')).toBeInTheDocument();
    expect(screen.getByText('Customer Info')).toBeInTheDocument();
    expect(screen.getByText('Payment')).toBeInTheDocument();
  });

  it('validates required fields', async () => {
    render(
      <BookingProvider>
        <BookingForm />
      </BookingProvider>
    );

    const submitButton = screen.getByText('Continue');
    fireEvent.click(submitButton);

    await waitFor(() => {
      expect(screen.getByText('Pickup address is required')).toBeInTheDocument();
    });
  });

  it('calculates price on route selection', async () => {
    // Test price calculation
  });
});
```

#### API Service Test
```javascript
// src/__tests__/services/api.test.js
import api from '../../services/api';
import axios from 'axios';

jest.mock('axios');

describe('API Service', () => {
  it('creates booking successfully', async () => {
    const mockResponse = {
      data: {
        success: true,
        data: { booking_number: 'BK12345678' }
      }
    };

    axios.post.mockResolvedValueOnce(mockResponse);

    const bookingData = {
      customer_email: 'test@example.com',
      // ... other fields
    };

    const result = await api.createBooking(bookingData);
    
    expect(result.booking_number).toBe('BK12345678');
    expect(axios.post).toHaveBeenCalledWith(
      '/api/bookings',
      bookingData
    );
  });

  it('handles API errors gracefully', async () => {
    // Test error handling
  });
});
```

#### Store Test (Zustand)
```javascript
// src/__tests__/store/bookingStore.test.js
import { renderHook, act } from '@testing-library/react';
import useBookingStore from '../../store/bookingStore';

describe('Booking Store', () => {
  it('updates form data correctly', () => {
    const { result } = renderHook(() => useBookingStore());

    act(() => {
      result.current.updateFormData({
        customer_email: 'test@example.com'
      });
    });

    expect(result.current.formData.customer_email).toBe('test@example.com');
  });

  it('resets store state', () => {
    const { result } = renderHook(() => useBookingStore());

    act(() => {
      result.current.updateFormData({ customer_email: 'test@example.com' });
      result.current.reset();
    });

    expect(result.current.formData.customer_email).toBeUndefined();
  });
});
```

## E2E Testing (Cypress - Future)

### Test Scenarios
```javascript
// cypress/integration/booking-flow.spec.js
describe('Complete Booking Flow', () => {
  it('completes guest booking successfully', () => {
    cy.visit('/');
    
    // Step 1: Route
    cy.get('[data-testid="pickup-address"]').type('123 Main St');
    cy.get('[data-testid="dropoff-address"]').type('Airport');
    cy.get('[data-testid="continue-btn"]').click();
    
    // Step 2: Vehicle
    cy.get('[data-testid="vehicle-sedan"]').click();
    cy.get('[data-testid="continue-btn"]').click();
    
    // Step 3: Customer Info
    cy.get('[data-testid="first-name"]').type('John');
    cy.get('[data-testid="last-name"]').type('Doe');
    cy.get('[data-testid="email"]').type('john@example.com');
    cy.get('[data-testid="phone"]').type('+1234567890');
    cy.get('[data-testid="continue-btn"]').click();
    
    // Step 4: Verification
    cy.get('[data-testid="verification-code"]').type('123456');
    cy.get('[data-testid="verify-btn"]').click();
    
    // Step 5: Payment
    cy.get('[data-testid="card-element"]').within(() => {
      cy.fillCardDetails('4242424242424242', '12/25', '123');
    });
    cy.get('[data-testid="book-btn"]').click();
    
    // Confirmation
    cy.get('[data-testid="booking-confirmation"]').should('be.visible');
    cy.get('[data-testid="booking-number"]').should('contain', 'BK');
  });
});
```

## Test Data

### Stripe Test Cards
```
# Successful payment
4242 4242 4242 4242 - Visa
5555 5555 5555 4444 - Mastercard

# Requires authentication
4000 0025 0000 3155 - 3D Secure

# Decline
4000 0000 0000 9995 - Insufficient funds
4000 0000 0000 0002 - Card declined
```

### Test Users
```php
// database/seeders/TestUserSeeder.php
User::create([
    'name' => 'Test Customer',
    'email' => 'customer@test.com',
    'password' => bcrypt('password123'),
]);

User::create([
    'name' => 'Test Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('admin123'),
    'is_admin' => true,
]);
```

## Testing Checklist

### Before Deployment
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] API endpoints tested
- [ ] Payment flow tested with test cards
- [ ] Email sending tested
- [ ] Form validation tested
- [ ] Error handling tested
- [ ] Mobile responsiveness tested
- [ ] Browser compatibility tested
- [ ] Performance benchmarks met

### Critical Path Tests
1. **Booking Creation**
   - Guest can create booking
   - Email verification works
   - Payment processes correctly
   - Confirmation email sent

2. **Admin Operations**
   - Admin can login
   - View all bookings
   - Edit booking status
   - Process refunds
   - Manage settings

3. **Email System**
   - Templates render correctly
   - Variables replaced properly
   - Attachments included
   - Queue processes emails

4. **Payment Processing**
   - Stripe integration works
   - Webhooks received
   - Refunds process
   - Tips can be added

## Performance Testing

### Load Testing
```bash
# Using Apache Bench
ab -n 1000 -c 10 https://book.luxridesuv.com/api/settings/public

# Using Artillery
artillery quick --count 50 --num 10 https://book.luxridesuv.com
```

### Benchmarks
- Homepage load: < 2 seconds
- API response: < 200ms
- Payment processing: < 3 seconds
- Email sending: < 1 second (queued)

## Security Testing

### Vulnerability Scanning
```bash
# Check for known vulnerabilities
composer audit

# NPM packages
npm audit

# Fix vulnerabilities
npm audit fix
```

### Penetration Testing
- SQL injection tests
- XSS vulnerability tests
- CSRF protection tests
- Authentication bypass tests
- Rate limiting tests

## Continuous Integration

### GitHub Actions Workflow
```yaml
name: Tests
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test
      - name: Generate Coverage
        run: php artisan test --coverage
```

## Test Maintenance

### Regular Tasks
- Update test data monthly
- Review failing tests weekly
- Update factories after model changes
- Maintain test coverage above 80%
- Remove obsolete tests

### Best Practices
1. **Keep tests fast** - Mock external services
2. **Test behavior, not implementation** - Focus on outcomes
3. **Use descriptive names** - Clear test intentions
4. **One assertion per test** - When practical
5. **Isolate tests** - No dependencies between tests
6. **Use factories** - Don't hardcode test data
7. **Test edge cases** - Not just happy paths

## Debugging Tests

### Debug Helpers
```php
// Dump and die in tests
dd($response->json());

// Log for inspection
\Log::info('Test data:', $data);

// Pause execution
$this->artisan('tinker');

// Database state
$this->assertDatabaseHas('bookings', [...]);
$this->assertDatabaseMissing('bookings', [...]);
```

### Common Issues
- **Factory not found**: Run `composer dump-autoload`
- **Database not refreshing**: Check RefreshDatabase trait
- **Stripe tests failing**: Verify test keys in .env.testing
- **Email assertions failing**: Ensure Mail::fake() is called

## Resources
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [React Testing Library](https://testing-library.com/docs/react-testing-library/intro/)
- [Stripe Testing](https://stripe.com/docs/testing)