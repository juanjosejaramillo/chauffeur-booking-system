<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Gratuity - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">{{ config('app.name') }}</h2>
                <p class="mt-2 text-sm text-gray-600">Add Gratuity for Your Trip</p>
            </div>

            <!-- Trip Details Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="border-b pb-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Trip Details</h3>
                    <div class="mt-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Booking:</span>
                            <span class="font-medium">#{{ $booking['booking_number'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-medium">{{ $booking['pickup_date'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Vehicle:</span>
                            <span class="font-medium">{{ $booking['vehicle_type'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Fare Paid:</span>
                            <span class="font-medium">${{ number_format($booking['fare_paid'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tip Selection -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900">Select Gratuity Amount</h4>
                    
                    <!-- Tip Options -->
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($booking['suggested_tips'] as $tip)
                        <button 
                            onclick="selectTip({{ $tip['percentage'] }}, {{ $tip['amount'] }})"
                            class="tip-button border-2 border-gray-300 rounded-lg py-3 px-4 text-center hover:border-indigo-500 hover:bg-indigo-50 transition-colors"
                            data-percentage="{{ $tip['percentage'] }}"
                        >
                            <div class="text-lg font-semibold">{{ $tip['percentage'] }}%</div>
                            <div class="text-sm text-gray-600">${{ number_format($tip['amount'], 2) }}</div>
                        </button>
                        @endforeach
                    </div>

                    <!-- Custom Amount -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Or enter custom amount
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                            <input 
                                type="number" 
                                id="customAmount" 
                                step="0.01" 
                                min="0" 
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="0.00"
                                onchange="selectCustomAmount(this.value)"
                            >
                        </div>
                    </div>

                    <!-- Selected Amount Display -->
                    <div class="bg-gray-50 rounded-lg p-4 mt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Selected Tip:</span>
                            <span id="selectedTipAmount" class="text-xl font-bold text-gray-900">$0.00</span>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form id="payment-form" class="mt-6">
                        @if(!$booking['has_saved_card'])
                        <!-- Card Element -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Card Information
                            </label>
                            <div id="card-element" class="p-3 border border-gray-300 rounded-lg"></div>
                            <div id="card-errors" class="text-red-500 text-sm mt-2"></div>
                        </div>

                        <!-- Save Card Checkbox -->
                        <div class="flex items-center mb-4">
                            <input 
                                type="checkbox" 
                                id="saveCard" 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            >
                            <label for="saveCard" class="ml-2 text-sm text-gray-700">
                                Save card for future use
                            </label>
                        </div>
                        @else
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-sm text-blue-800">
                                Using saved card ending in {{ $booking['saved_card_last4'] ?? '****' }}
                            </p>
                        </div>
                        @endif

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            id="submit-button"
                            class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                        >
                            Add Tip - $<span id="submitAmount">0.00</span>
                        </button>
                    </form>

                    <!-- Skip Button -->
                    <div class="text-center mt-4">
                        <a href="/" class="text-sm text-gray-500 hover:text-gray-700">
                            Skip - No tip at this time
                        </a>
                    </div>
                </div>
            </div>

            <!-- Optional Note -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-800">
                    <strong>Note:</strong> This gratuity is optional and goes directly to your driver as appreciation for their service.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Initialize Stripe
        const stripe = Stripe('{{ $stripePublicKey }}');
        const elements = stripe.elements();
        
        @if(!$booking['has_saved_card'])
        // Create card element
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        cardElement.mount('#card-element');

        // Handle card errors
        cardElement.addEventListener('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        @endif

        let selectedTipAmount = 0;

        function selectTip(percentage, amount) {
            // Clear custom amount
            document.getElementById('customAmount').value = '';
            
            // Update UI
            document.querySelectorAll('.tip-button').forEach(btn => {
                btn.classList.remove('border-indigo-500', 'bg-indigo-50');
                btn.classList.add('border-gray-300');
            });
            
            event.target.closest('.tip-button').classList.remove('border-gray-300');
            event.target.closest('.tip-button').classList.add('border-indigo-500', 'bg-indigo-50');
            
            updateSelectedAmount(amount);
        }

        function selectCustomAmount(amount) {
            // Clear preset selections
            document.querySelectorAll('.tip-button').forEach(btn => {
                btn.classList.remove('border-indigo-500', 'bg-indigo-50');
                btn.classList.add('border-gray-300');
            });
            
            updateSelectedAmount(parseFloat(amount) || 0);
        }

        function updateSelectedAmount(amount) {
            selectedTipAmount = amount;
            document.getElementById('selectedTipAmount').textContent = '$' + amount.toFixed(2);
            document.getElementById('submitAmount').textContent = amount.toFixed(2);
            
            // Enable/disable submit button
            document.getElementById('submit-button').disabled = amount <= 0;
        }

        // Handle form submission
        document.getElementById('payment-form').addEventListener('submit', async function(event) {
            event.preventDefault();
            
            if (selectedTipAmount <= 0) {
                alert('Please select a tip amount');
                return;
            }
            
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
            
            try {
                @if($booking['has_saved_card'])
                // Use saved card
                const response = await fetch('/api/tip/{{ $token }}/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: selectedTipAmount
                    })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    window.location.href = '/tip/{{ $token }}/success';
                } else {
                    throw new Error(result.error || 'Payment failed');
                }
                @else
                // Create payment intent first
                const intentResponse = await fetch('/api/tip/{{ $token }}/payment-intent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: selectedTipAmount
                    })
                });
                
                const intentData = await intentResponse.json();
                
                if (!intentResponse.ok) {
                    throw new Error(intentData.error || 'Failed to create payment intent');
                }
                
                // Confirm payment with Stripe
                const {error, paymentIntent} = await stripe.confirmCardPayment(intentData.client_secret, {
                    payment_method: {
                        card: cardElement,
                    },
                    setup_future_usage: document.getElementById('saveCard').checked ? 'off_session' : null
                });
                
                if (error) {
                    throw new Error(error.message);
                }
                
                // Process tip on backend
                const processResponse = await fetch('/api/tip/{{ $token }}/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: selectedTipAmount,
                        payment_method_id: paymentIntent.payment_method
                    })
                });
                
                const processResult = await processResponse.json();
                
                if (processResponse.ok) {
                    window.location.href = '/tip/{{ $token }}/success';
                } else {
                    throw new Error(processResult.error || 'Failed to process tip');
                }
                @endif
            } catch (error) {
                console.error('Payment error:', error);
                alert('Payment failed: ' + error.message);
                submitButton.disabled = false;
                submitButton.innerHTML = 'Add Tip - $<span id="submitAmount">' + selectedTipAmount.toFixed(2) + '</span>';
            }
        });
    </script>
</body>
</html>