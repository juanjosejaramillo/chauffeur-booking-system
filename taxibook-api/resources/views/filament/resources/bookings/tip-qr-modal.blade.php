<div class="text-center p-4">
    <h3 class="text-lg font-semibold mb-4">Scan to Add Tip</h3>
    
    <div class="flex justify-center mb-4">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            {!! $qrCode !!}
        </div>
    </div>
    
    <p class="text-sm text-gray-600 mb-2">Booking #{{ $bookingNumber }}</p>
    
    <div class="mt-4 p-3 bg-gray-100 rounded">
        <p class="text-xs text-gray-500 mb-2">Or share this link:</p>
        <div class="flex items-center justify-center gap-2">
            <input 
                type="text" 
                value="{{ $url }}" 
                class="text-xs px-2 py-1 border rounded flex-1 max-w-md"
                readonly
                onclick="this.select()"
            >
            <button 
                onclick="navigator.clipboard.writeText('{{ $url }}')"
                class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
            >
                Copy
            </button>
        </div>
    </div>
    
    <p class="text-xs text-gray-500 mt-4">
        Customer can scan this QR code or use the link to add gratuity
    </p>
</div>