<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You! - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Success Icon -->
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100">
                    <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">Thank You!</h2>
                <p class="mt-2 text-lg text-gray-600">Your tip has been added successfully</p>
            </div>

            <!-- Confirmation Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-2">Tip Amount</p>
                    <p class="text-3xl font-bold text-green-600">${{ number_format($tipAmount, 2) }}</p>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Booking Reference</p>
                        <p class="text-lg font-semibold text-gray-900">#{{ $booking->booking_number }}</p>
                    </div>
                </div>

                <!-- Receipt Info -->
                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">
                        A receipt has been sent to <strong>{{ $booking->customer_email }}</strong>
                    </p>
                </div>
            </div>

            <!-- Thank You Message -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-800">
                            Your gratuity goes directly to your driver as appreciation for their excellent service. Thank you for your generosity!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Close Button -->
            <div class="text-center">
                <p class="text-sm text-gray-600 mb-4">You can now close this window</p>
                <button 
                    onclick="window.close()" 
                    class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Close Window
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto-close after 10 seconds (optional)
        setTimeout(function() {
            // Only try to close if it was opened as a popup
            if (window.opener) {
                window.close();
            }
        }, 10000);
    </script>
</body>
</html>