<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\VehicleType;
use App\Services\MapboxService;
use App\Services\PricingService;
use App\Services\StripeService;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    private MapboxService $mapboxService;
    private PricingService $pricingService;
    private StripeService $stripeService;

    public function __construct(
        MapboxService $mapboxService,
        PricingService $pricingService,
        StripeService $stripeService
    ) {
        $this->mapboxService = $mapboxService;
        $this->pricingService = $pricingService;
        $this->stripeService = $stripeService;
    }

    public function validateRoute(Request $request)
    {
        \Log::info('ValidateRoute called', ['request' => $request->all()]);
        
        try {
            $validated = $request->validate([
                'pickup_lat' => 'required|numeric|between:-90,90',
                'pickup_lng' => 'required|numeric|between:-180,180',
                'dropoff_lat' => 'required|numeric|between:-90,90',
                'dropoff_lng' => 'required|numeric|between:-180,180',
                'pickup_date' => 'required|date|after:now',
            ]);
            
            \Log::info('Validation passed', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        $pickupDate = Carbon::parse($validated['pickup_date']);
        
        // Check 2-hour advance booking requirement
        if ($pickupDate->isBefore(now()->addHours(2))) {
            \Log::warning('Booking too soon', [
                'pickup_date' => $pickupDate->toDateTimeString(),
                'minimum_date' => now()->addHours(2)->toDateTimeString()
            ]);
            return response()->json([
                'error' => 'Bookings must be made at least 2 hours in advance.',
            ], 422);
        }

        // Get route information
        \Log::info('Calling Mapbox service...');
        $startTime = microtime(true);
        
        $route = $this->mapboxService->getRoute(
            $validated['pickup_lat'],
            $validated['pickup_lng'],
            $validated['dropoff_lat'],
            $validated['dropoff_lng']
        );
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        \Log::info('Mapbox service completed', ['duration_ms' => $duration, 'has_route' => !empty($route)]);

        if (!$route) {
            \Log::error('No route returned from Mapbox');
            return response()->json([
                'error' => 'Unable to calculate route. Please try again.',
            ], 422);
        }

        \Log::info('Route validated successfully', ['distance' => $route['distance'] ?? null]);
        
        return response()->json([
            'valid' => true,
            'route' => $route,
        ]);
    }

    public function calculatePrices(Request $request)
    {
        $validated = $request->validate([
            'pickup_lat' => 'required|numeric|between:-90,90',
            'pickup_lng' => 'required|numeric|between:-180,180',
            'dropoff_lat' => 'required|numeric|between:-90,90',
            'dropoff_lng' => 'required|numeric|between:-180,180',
        ]);

        try {
            $prices = $this->pricingService->calculatePrices(
                $validated['pickup_lat'],
                $validated['pickup_lng'],
                $validated['dropoff_lat'],
                $validated['dropoff_lng']
            );

            return response()->json($prices);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to calculate prices. Please try again.',
            ], 422);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'customer_first_name' => 'required|string|max:255',
            'customer_last_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'pickup_address' => 'required|string',
            'pickup_lat' => 'required|numeric|between:-90,90',
            'pickup_lng' => 'required|numeric|between:-180,180',
            'dropoff_address' => 'required|string',
            'dropoff_lat' => 'required|numeric|between:-90,90',
            'dropoff_lng' => 'required|numeric|between:-180,180',
            'pickup_date' => 'required|date|after:now',
            'special_instructions' => 'nullable|string|max:500',
        ]);

        $pickupDate = Carbon::parse($validated['pickup_date']);
        
        // Check 2-hour advance booking requirement
        if ($pickupDate->isBefore(now()->addHours(2))) {
            return response()->json([
                'error' => 'Bookings must be made at least 2 hours in advance.',
            ], 422);
        }

        // Calculate route and pricing
        $route = $this->mapboxService->getRoute(
            $validated['pickup_lat'],
            $validated['pickup_lng'],
            $validated['dropoff_lat'],
            $validated['dropoff_lng']
        );

        if (!$route) {
            return response()->json([
                'error' => 'Unable to calculate route. Please try again.',
            ], 422);
        }

        $vehicleType = VehicleType::findOrFail($validated['vehicle_type_id']);
        $estimatedFare = $vehicleType->calculateFare($route['distance'], $route['duration']);

        DB::beginTransaction();

        try {
            // Find user by email (should exist from verification)
            $user = User::where('email', $validated['customer_email'])->first();
            
            // Create booking
            $booking = Booking::create([
                'user_id' => $user ? $user->id : null,
                'vehicle_type_id' => $validated['vehicle_type_id'],
                'customer_first_name' => $validated['customer_first_name'],
                'customer_last_name' => $validated['customer_last_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'pickup_address' => $validated['pickup_address'],
                'pickup_latitude' => $validated['pickup_lat'],
                'pickup_longitude' => $validated['pickup_lng'],
                'dropoff_address' => $validated['dropoff_address'],
                'dropoff_latitude' => $validated['dropoff_lat'],
                'dropoff_longitude' => $validated['dropoff_lng'],
                'pickup_date' => $pickupDate,
                'estimated_distance' => $route['distance'],
                'estimated_duration' => $route['duration'],
                'route_polyline' => $route['polyline'],
                'estimated_fare' => $estimatedFare,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'booking' => $booking->load('vehicleType'),
                'message' => 'Booking created successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Failed to create booking. Please try again.',
            ], 500);
        }
    }

    public function show($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)
            ->with(['vehicleType', 'transactions'])
            ->firstOrFail();

        // Check if user has permission to view this booking
        if (auth()->check() && $booking->user_id && $booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        return response()->json($booking);
    }

    public function createPaymentIntent(Request $request, $bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        // Check if booking can be authorized
        if (!$booking->canBeAuthorized()) {
            return response()->json([
                'error' => 'This booking cannot be authorized at this time.',
            ], 422);
        }

        try {
            $paymentIntent = $this->stripeService->createPaymentIntent($booking);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create payment authorization.',
            ], 500);
        }
    }

    public function confirmPayment(Request $request, $bookingNumber)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        if ($booking->stripe_payment_intent_id !== $validated['payment_intent_id']) {
            return response()->json([
                'error' => 'Invalid payment intent.',
            ], 422);
        }

        try {
            $this->stripeService->confirmPaymentIntent($booking, $validated['payment_intent_id']);

            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'authorized',
            ]);

            // Trigger booking confirmed event
            event(new BookingConfirmed($booking->fresh()));

            return response()->json([
                'booking' => $booking->fresh()->load('vehicleType'),
                'message' => 'Payment authorized successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to authorize payment.',
            ], 500);
        }
    }

    public function userBookings(Request $request)
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with('vehicleType')
            ->orderBy('pickup_date', 'desc')
            ->paginate(10);

        return response()->json($bookings);
    }
}