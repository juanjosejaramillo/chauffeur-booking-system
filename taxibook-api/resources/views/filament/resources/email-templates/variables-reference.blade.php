<div class="space-y-4">
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">
                    How to Use Variables
                </p>
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    Click on any variable to copy it to your clipboard. Variables should be wrapped in double curly braces like <code class="bg-white dark:bg-gray-800 px-1 py-0.5 rounded">@{{variable_name}}</code>
                </p>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($variables as $variable => $description)
            <div class="group relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:shadow-md transition-all cursor-pointer"
                 onclick="copyVariable('{{ $variable }}')"
                 title="Click to copy">
                
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <code class="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-blue-600 dark:text-blue-400">
                            @{{ $variable }}
                        </code>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                            {{ $description }}
                        </p>
                    </div>
                    
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                        <x-heroicon-o-clipboard class="w-4 h-4 text-gray-400" />
                    </div>
                </div>
                
                <!-- Copied indicator -->
                <div class="absolute inset-0 bg-green-500 text-white rounded-lg flex items-center justify-center opacity-0 pointer-events-none transition-opacity"
                     id="copied-{{ $variable }}">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-check class="w-5 h-5" />
                        <span class="text-sm font-medium">Copied!</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="mt-6 space-y-4">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Common Variable Combinations</h4>
        
        <div class="space-y-2">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Full Booking Summary:</p>
                <code class="text-xs font-mono text-gray-600 dark:text-gray-400">
                    Booking #@{{booking_number}} - @{{pickup_date}} at @{{pickup_time}}<br>
                    From: @{{pickup_address}}<br>
                    To: @{{dropoff_address}}<br>
                    Vehicle: @{{vehicle_type}}
                </code>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Customer Greeting:</p>
                <code class="text-xs font-mono text-gray-600 dark:text-gray-400">
                    Dear @{{customer_first_name}},
                </code>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Information:</p>
                <code class="text-xs font-mono text-gray-600 dark:text-gray-400">
                    For support, contact us at @{{company_phone}} or @{{company_email}}
                </code>
            </div>
        </div>
    </div>
</div>

<script>
    function copyVariable(variable) {
        const textToCopy = '{{' + variable + '}}';
        
        // Copy to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showCopiedIndicator(variable);
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showCopiedIndicator(variable);
        }
    }
    
    function showCopiedIndicator(variable) {
        const indicator = document.getElementById('copied-' + variable);
        if (indicator) {
            indicator.style.opacity = '1';
            setTimeout(() => {
                indicator.style.opacity = '0';
            }, 1500);
        }
    }
</script>