<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Tipped - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Info Icon -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-blue-100">
                    <svg class="h-12 w-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">Already Tipped</h2>
                <p class="mt-2 text-lg text-gray-600">A tip has already been added for this trip</p>
            </div>

            <!-- Tip Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Tip Amount</p>
                    <p class="text-3xl font-bold text-gray-900">${{ number_format($booking['tip_amount'], 2) }}</p>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Booking Reference</p>
                                <p class="text-lg font-semibold text-gray-900">#{{ $booking['booking_number'] }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Trip Date</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $booking['pickup_date'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Message -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-sm text-gray-600 text-center">
                    Thank you for your generosity! The tip has been processed and will go directly to your driver.
                </p>
            </div>

            <!-- Close Button -->
            <div class="text-center">
                <button 
                    onclick="window.close()" 
                    class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Close Window
                </button>
            </div>
        </div>
    </div>
</body>
</html>