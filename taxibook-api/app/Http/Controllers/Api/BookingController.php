<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\BookingExtra;
use App\Models\Extra;
use App\Models\Transaction;
use App\Services\GoogleMapsService;
use App\Services\PricingService;
use App\Services\StripeService;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    private GoogleMapsService $mapsService;
    private PricingService $pricingService;
    private StripeService $stripeService;

    public function __construct(
        GoogleMapsService $mapsService,
        PricingService $pricingService,
        StripeService $stripeService
    ) {
        $this->mapsService = $mapsService;
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
        \Log::info('Calling Google Maps service...');
        $startTime = microtime(true);
        
        $route = $this->mapsService->getRoute(
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
            'booking_type' => 'nullable|string|in:one_way,hourly',
            'pickup_lat' => 'required|numeric|between:-90,90',
            'pickup_lng' => 'required|numeric|between:-180,180',
            'dropoff_lat' => 'nullable|numeric|between:-90,90',
            'dropoff_lng' => 'nullable|numeric|between:-180,180',
            'pickup_date' => 'nullable|date',
            'pickup_time' => 'nullable|string',
            'duration_hours' => 'nullable|integer|min:1|max:24',
        ]);

        $bookingType = $validated['booking_type'] ?? 'one_way';

        try {
            // Handle hourly bookings
            if ($bookingType === 'hourly') {
                if (!isset($validated['duration_hours'])) {
                    return response()->json([
                        'error' => 'Duration in hours is required for hourly bookings.',
                    ], 422);
                }

                $prices = $this->pricingService->calculateHourlyPrices(
                    $validated['duration_hours']
                );

                return response()->json($prices);
            }

            // Handle one-way bookings (original logic)
            if (!isset($validated['dropoff_lat']) || !isset($validated['dropoff_lng'])) {
                return response()->json([
                    'error' => 'Dropoff location is required for one-way bookings.',
                ], 422);
            }

            // Combine pickup date and time if provided
            $pickupDateTime = null;
            if (isset($validated['pickup_date'])) {
                $pickupDateTime = $validated['pickup_date'];
                if (isset($validated['pickup_time'])) {
                    $pickupDateTime .= ' ' . $validated['pickup_time'];
                }
            }

            $prices = $this->pricingService->calculatePrices(
                $validated['pickup_lat'],
                $validated['pickup_lng'],
                $validated['dropoff_lat'],
                $validated['dropoff_lng'],
                $pickupDateTime
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
            'booking_type' => 'nullable|string|in:one_way,hourly',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'customer_first_name' => 'required|string|max:255',
            'customer_last_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'pickup_address' => 'required|string',
            'pickup_lat' => 'required|numeric|between:-90,90',
            'pickup_lng' => 'required|numeric|between:-180,180',
            'dropoff_address' => 'nullable|string',
            'dropoff_lat' => 'nullable|numeric|between:-90,90',
            'dropoff_lng' => 'nullable|numeric|between:-180,180',
            'pickup_date' => 'required|date|after:now',
            'duration_hours' => 'nullable|integer|min:1|max:24',
            'special_instructions' => 'nullable|string|max:500',
            'flight_number' => 'nullable|string|max:50',
            'is_airport_pickup' => 'boolean',
            'is_airport_dropoff' => 'boolean',
            'additional_fields' => 'nullable|array',
            'payment_method_id' => 'nullable|string', // Stripe payment method ID (optional for initial booking)
            'gratuity_amount' => 'nullable|numeric|min:0', // Optional tip at booking
            'save_payment_method' => 'boolean', // Save card for future use
            'extras' => 'nullable|array',
            'extras.*.extra_id' => 'required|exists:extras,id',
            'extras.*.quantity' => 'required|integer|min:1',
        ]);

        $bookingType = $validated['booking_type'] ?? 'one_way';
        $pickupDate = Carbon::parse($validated['pickup_date']);

        // Check 2-hour advance booking requirement
        if ($pickupDate->isBefore(now()->addHours(2))) {
            return response()->json([
                'error' => 'Bookings must be made at least 2 hours in advance.',
            ], 422);
        }

        $vehicleType = VehicleType::findOrFail($validated['vehicle_type_id']);
        $route = null;
        $estimatedFare = 0;

        // Handle booking type specific logic
        if ($bookingType === 'hourly') {
            // Validate hourly booking requirements
            if (!isset($validated['duration_hours'])) {
                return response()->json([
                    'error' => 'Duration in hours is required for hourly bookings.',
                ], 422);
            }

            if (!$vehicleType->hourly_enabled) {
                return response()->json([
                    'error' => 'This vehicle type does not support hourly bookings.',
                ], 422);
            }

            $hours = $validated['duration_hours'];
            if ($hours < $vehicleType->minimum_hours || $hours > $vehicleType->maximum_hours) {
                return response()->json([
                    'error' => sprintf('Hourly booking must be between %d and %d hours for this vehicle.',
                        $vehicleType->minimum_hours, $vehicleType->maximum_hours),
                ], 422);
            }

            $estimatedFare = $vehicleType->calculateHourlyFare($hours);
        } else {
            // One-way booking - validate dropoff location
            if (!isset($validated['dropoff_lat']) || !isset($validated['dropoff_lng'])) {
                return response()->json([
                    'error' => 'Dropoff location is required for one-way bookings.',
                ], 422);
            }

            // Calculate route and pricing
            $route = $this->mapsService->getRoute(
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

            $estimatedFare = $vehicleType->calculateFare($route['distance'], $route['duration']);
        }

        DB::beginTransaction();

        try {
            // Find user by email (should exist from verification)
            $user = User::where('email', $validated['customer_email'])->first();
            
            // Calculate extras total
            $extrasTotal = 0;
            $extrasData = $validated['extras'] ?? [];
            if (!empty($extrasData)) {
                foreach ($extrasData as $extraItem) {
                    $extra = Extra::findOrFail($extraItem['extra_id']);
                    $extrasTotal += $extra->price * $extraItem['quantity'];
                }
            }

            // Calculate total with optional tip
            $tipAmount = $validated['gratuity_amount'] ?? 0;
            $totalCharge = $estimatedFare + $extrasTotal + $tipAmount;
            $saveCard = $validated['save_payment_method'] ?? false;

            // Create booking
            $booking = Booking::create([
                'booking_type' => $bookingType,
                'user_id' => $user ? $user->id : null,
                'vehicle_type_id' => $validated['vehicle_type_id'],
                'customer_first_name' => $validated['customer_first_name'],
                'customer_last_name' => $validated['customer_last_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'pickup_address' => $validated['pickup_address'],
                'pickup_latitude' => $validated['pickup_lat'],
                'pickup_longitude' => $validated['pickup_lng'],
                'dropoff_address' => $validated['dropoff_address'] ?? null,
                'dropoff_latitude' => $validated['dropoff_lat'] ?? null,
                'dropoff_longitude' => $validated['dropoff_lng'] ?? null,
                'pickup_date' => $pickupDate,
                'duration_hours' => $bookingType === 'hourly' ? $validated['duration_hours'] : null,
                'estimated_distance' => $route ? $route['distance'] : null,
                'estimated_duration' => $route ? $route['duration'] : null,
                'estimated_fare' => $estimatedFare,
                'extras_total' => $extrasTotal,
                'final_fare' => $estimatedFare,
                'gratuity_amount' => $tipAmount,
                'gratuity_added_at' => $tipAmount > 0 ? now() : null,
                'save_payment_method' => $saveCard,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'flight_number' => $validated['flight_number'] ?? null,
                'is_airport_pickup' => $validated['is_airport_pickup'] ?? false,
                'is_airport_dropoff' => $validated['is_airport_dropoff'] ?? false,
                'additional_data' => $validated['additional_fields'] ?? [],
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);
            
            // Create booking extras (snapshot name + unit_price)
            if (!empty($extrasData)) {
                foreach ($extrasData as $extraItem) {
                    $extra = Extra::find($extraItem['extra_id']);
                    if ($extra) {
                        BookingExtra::create([
                            'booking_id' => $booking->id,
                            'extra_id' => $extra->id,
                            'name' => $extra->name,
                            'unit_price' => $extra->price,
                            'quantity' => $extraItem['quantity'],
                            'total_price' => $extra->price * $extraItem['quantity'],
                        ]);
                    }
                }
            }

            // If payment method provided, process payment immediately
            if (!empty($validated['payment_method_id'])) {
                $paymentResult = $this->stripeService->chargeBooking(
                    $booking,
                    $validated['payment_method_id'],
                    $totalCharge,
                    $saveCard
                );
                
                if (!$paymentResult['success']) {
                    throw new \Exception($paymentResult['error']);
                }
                
                // Update booking with payment details
                $booking->update([
                    'payment_status' => 'captured',
                    'status' => 'confirmed',
                    'stripe_payment_intent_id' => $paymentResult['payment_intent_id'],
                    'stripe_payment_method_id' => $saveCard ? $paymentResult['payment_method_id'] : null,
                    'stripe_customer_id' => $saveCard ? $paymentResult['customer_id'] : null,
                ]);
                
                // Create transaction record
                Transaction::create([
                    'booking_id' => $booking->id,
                    'type' => 'payment',
                    'amount' => $totalCharge,
                    'status' => 'succeeded',
                    'stripe_transaction_id' => $paymentResult['payment_intent_id'],
                    'notes' => $tipAmount > 0 ? "Includes $" . number_format($tipAmount, 2) . " tip" : null,
                ]);
                
                // Event will be triggered by BookingObserver when status changes
            }

            DB::commit();
            
            return response()->json([
                'booking' => $booking->load('vehicleType'),
                'message' => 'Booking created successfully',
                'payment_required' => empty($validated['payment_method_id']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'validated' => $validated ?? []
            ]);

            return response()->json([
                'error' => 'Failed to create booking: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function processPayment(Request $request, $bookingNumber)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|string',
            'gratuity_amount' => 'nullable|numeric|min:0',
            'save_payment_method' => 'boolean',
        ]);

        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        // Check if booking is already paid
        if ($booking->payment_status === 'captured') {
            return response()->json([
                'error' => 'This booking has already been paid.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Update gratuity if provided
            if (isset($validated['gratuity_amount'])) {
                $booking->gratuity_amount = $validated['gratuity_amount'];
                $booking->gratuity_added_at = $validated['gratuity_amount'] > 0 ? now() : null;
            }

            // Calculate total charge
            $totalCharge = $booking->estimated_fare + $booking->extras_total + $booking->gratuity_amount;
            $saveCard = $validated['save_payment_method'] ?? false;

            // Process payment
            $paymentResult = $this->stripeService->chargeBooking(
                $booking,
                $validated['payment_method_id'],
                $totalCharge,
                $saveCard
            );

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['error']);
            }

            // Update booking with payment details
            $booking->update([
                'payment_status' => 'captured',
                'status' => 'confirmed',
                'stripe_payment_intent_id' => $paymentResult['payment_intent_id'],
                'stripe_payment_method_id' => $saveCard ? $paymentResult['payment_method_id'] : null,
                'stripe_customer_id' => $saveCard ? $paymentResult['customer_id'] : null,
                'save_payment_method' => $saveCard,
            ]);

            // Create transaction record
            Transaction::create([
                'booking_id' => $booking->id,
                'type' => 'payment',
                'amount' => $totalCharge,
                'status' => 'succeeded',
                'stripe_transaction_id' => $paymentResult['payment_intent_id'],
                'notes' => $booking->gratuity_amount > 0 ? "Includes $" . number_format($booking->gratuity_amount, 2) . " tip" : null,
            ]);

            DB::commit();

            // Event will be triggered by BookingObserver when status changes

            return response()->json([
                'booking' => $booking->load('vehicleType'),
                'message' => 'Payment processed successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage() ?: 'Failed to process payment. Please try again.',
            ], 500);
        }
    }

    public function show($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)
            ->with(['vehicleType', 'transactions', 'bookingExtras'])
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

            // Event will be triggered by BookingObserver when status changes

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

    /**
     * Search for addresses using Google Places Autocomplete
     */
    public function searchAddresses(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2|max:100',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        try {
            $results = $this->mapsService->autocomplete(
                $validated['query'],
                $validated['lat'] ?? null,
                $validated['lng'] ?? null
            );

            if (!$results) {
                return response()->json([
                    'suggestions' => []
                ]);
            }

            // Format results for frontend
            $suggestions = array_map(function ($result) {
                return [
                    'place_id' => $result['place_id'],
                    'name' => $result['name'],
                    'address' => $result['address'],
                    'full_description' => $result['full_description'],
                    'is_venue' => $result['is_venue'],
                    'is_airport' => $result['is_airport'] ?? false,
                    'types' => $result['types'] ?? [],
                    'latitude' => $result['latitude'] ?? null,
                    'longitude' => $result['longitude'] ?? null,
                    'rating' => $result['rating'] ?? null,
                ];
            }, $results);

            return response()->json([
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            \Log::error('Address search error', [
                'error' => $e->getMessage(),
                'query' => $validated['query']
            ]);

            return response()->json([
                'error' => 'Failed to search addresses',
                'suggestions' => []
            ], 500);
        }
    }

    /**
     * Get place details from Google Places
     */
    public function getPlaceDetails(Request $request)
    {
        $validated = $request->validate([
            'place_id' => 'required|string',
        ]);

        try {
            $details = $this->mapsService->getPlaceDetails($validated['place_id']);

            if (!$details) {
                return response()->json([
                    'error' => 'Place not found'
                ], 404);
            }

            return response()->json([
                'place' => $details
            ]);
        } catch (\Exception $e) {
            \Log::error('Place details error', [
                'error' => $e->getMessage(),
                'place_id' => $validated['place_id']
            ]);

            return response()->json([
                'error' => 'Failed to get place details'
            ], 500);
        }
    }

    /**
     * Create a Setup Intent for saving card without charging.
     * Used when payment_mode is 'post_service'.
     */
    public function createSetupIntent(Request $request, $bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        // Check if booking is in valid state
        if ($booking->payment_status !== 'pending') {
            return response()->json([
                'error' => 'This booking already has a payment method or has been paid.',
            ], 422);
        }

        try {
            $result = $this->stripeService->createSetupIntent($booking);

            if (!$result['success']) {
                return response()->json([
                    'error' => $result['error'],
                ], 500);
            }

            return response()->json([
                'client_secret' => $result['client_secret'],
                'setup_intent_id' => $result['setup_intent_id'],
                'customer_id' => $result['customer_id'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Setup Intent creation failed', [
                'booking_number' => $bookingNumber,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create setup intent.',
            ], 500);
        }
    }

    /**
     * Complete Setup Intent and save card after frontend confirmation.
     * Used when payment_mode is 'post_service'.
     */
    public function completeSetupIntent(Request $request, $bookingNumber)
    {
        $validated = $request->validate([
            'setup_intent_id' => 'required|string',
            'gratuity_amount' => 'nullable|numeric|min:0',
        ]);

        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

        DB::beginTransaction();

        try {
            $result = $this->stripeService->completeSetupIntent(
                $booking,
                $validated['setup_intent_id']
            );

            if (!$result['success']) {
                throw new \Exception($result['error']);
            }

            // Update gratuity if provided
            $updateData = [
                'status' => 'confirmed',
                'payment_status' => 'pending', // Still pending - will charge after ride
            ];

            if (isset($validated['gratuity_amount'])) {
                $updateData['gratuity_amount'] = $validated['gratuity_amount'];
                $updateData['gratuity_added_at'] = $validated['gratuity_amount'] > 0 ? now() : null;
            }

            // Update booking status - card saved, awaiting service completion
            $booking->update($updateData);

            DB::commit();

            // Event will be triggered by BookingObserver when status changes

            return response()->json([
                'booking' => $booking->fresh()->load('vehicleType'),
                'card' => $result['card'],
                'message' => 'Card saved successfully. You will be charged after your ride is completed.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Complete Setup Intent failed', [
                'booking_number' => $bookingNumber,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => $e->getMessage() ?: 'Failed to save payment method.',
            ], 500);
        }
    }

    /**
     * Get payment mode setting to determine if we charge immediately or save card.
     */
    public function getPaymentMode()
    {
        $paymentMode = Setting::get('payment_mode', 'immediate');
        $cancellationPolicyUrl = Setting::get('cancellation_policy_url', 'https://luxridesuv.com/cancellation-policy');

        return response()->json([
            'payment_mode' => $paymentMode,
            'cancellation_policy_url' => $cancellationPolicyUrl,
        ]);
    }
}