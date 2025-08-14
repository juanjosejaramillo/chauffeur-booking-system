<div class="p-4 max-w-md mx-auto">
    <!-- Compact Header -->
    <div class="text-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Scan to Add Tip</h3>
    </div>
    
    <!-- QR Code -->
    <div class="flex justify-center mb-4">
        <div class="bg-white p-3 rounded-lg border border-gray-200">
            @if(str_starts_with($qrCode, 'data:image'))
                <img src="{{ $qrCode }}" alt="QR Code" class="w-48 h-48">
            @else
                <div class="w-48 h-48">
                    {!! $qrCode !!}
                </div>
            @endif
        </div>
    </div>
    
    <!-- Booking Number -->
    <div class="text-center mb-4">
        <span class="text-sm text-gray-600">Booking #{{ $bookingNumber }}</span>
    </div>
    
    <!-- Link Section -->
    <div class="bg-gray-50 rounded-lg p-3 mb-3">
        <p class="text-xs text-gray-600 mb-2">Or share this link:</p>
        <div class="flex items-center gap-2">
            <input 
                type="text" 
                value="{{ $url }}" 
                class="flex-1 px-2 py-1.5 bg-white border border-gray-300 rounded text-xs font-mono text-gray-700 focus:outline-none focus:border-blue-500"
                readonly
                onclick="this.select()"
            >
            <button 
                onclick="navigator.clipboard.writeText('{{ $url }}'); this.innerHTML = '✓ Copied'; setTimeout(() => this.innerHTML = 'Copy', 2000);"
                class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors"
            >
                Copy
            </button>
        </div>
    </div>
    
    <!-- Simple Note -->
    <p class="text-xs text-gray-500 text-center">
        Customer can scan this QR code or use the link to add gratuity
    </p>
</div>