<x-filament-panels::page>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Editor Section -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Email Template Editor
                    </h2>
                    
                    <form wire:submit.prevent="save">
                        {{ $this->form }}
                        
                        <div class="mt-6 flex items-center gap-4">
                            <x-filament::button
                                type="submit"
                                icon="heroicon-o-check"
                            >
                                Save Template
                            </x-filament::button>
                            
                            <x-filament::button
                                type="button"
                                color="gray"
                                icon="heroicon-o-arrow-path"
                                wire:click="refreshPreview"
                            >
                                Refresh Preview
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Variable Reference Card -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">
                    Quick Variable Reference
                </h3>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    @foreach(\App\Models\EmailTemplate::getAvailableVariables() as $variable => $description)
                        <div class="flex items-center gap-2">
                            <code 
                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors"
                                onclick="navigator.clipboard.writeText('{{ '{{' . $variable . '}}' }}')"
                                title="Click to copy"
                            >
                                @{{ $variable }}
                            </code>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Preview Section -->
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Email Preview
                        </h2>
                        
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="toggleMobilePreview"
                                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                title="{{ $isMobilePreview ? 'Switch to Desktop' : 'Switch to Mobile' }}"
                            >
                                @if($isMobilePreview)
                                    <x-heroicon-o-computer-desktop class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                @else
                                    <x-heroicon-o-device-phone-mobile class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                @endif
                            </button>
                            
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $isMobilePreview ? 'Mobile' : 'Desktop' }} View
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 bg-gray-100 dark:bg-gray-900 min-h-[600px]">
                    <div 
                        class="mx-auto transition-all duration-300 {{ $isMobilePreview ? 'max-w-sm' : 'max-w-3xl' }}"
                    >
                        <!-- Email Preview Frame -->
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                            <!-- Email Client Header Mock -->
                            <div class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-3">
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="font-semibold">From:</span>
                                    <span>{{ config('business.name', 'LuxRide') }} &lt;{{ config('business.email', 'noreply@luxride.com') }}&gt;</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    <span class="font-semibold">To:</span>
                                    <span>John Doe &lt;john.doe@example.com&gt;</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    <span class="font-semibold">Subject:</span>
                                    <span class="text-gray-900 dark:text-gray-100">
                                        @php
                                            $subject = $this->record->subject ?? 'Email Subject Preview';
                                            if ($this->record->subject) {
                                                $sampleData = $this->getSampleData();
                                                foreach ($sampleData as $key => $value) {
                                                    $subject = str_replace('{{' . $key . '}}', $value, $subject);
                                                }
                                            }
                                        @endphp
                                        {{ $subject }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Email Content -->
                            <div class="email-preview-content" wire:ignore>
                                <iframe 
                                    id="email-preview-iframe"
                                    srcdoc="{{ htmlspecialchars($previewHtml ?? '<div class="p-4">Loading preview...</div>') }}"
                                    class="w-full"
                                    style="height: 600px; border: none;"
                                    sandbox="allow-same-origin"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Preview Info -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-yellow-800 dark:text-yellow-200">
                        <p class="font-semibold mb-1">Preview Mode</p>
                        <p>This preview uses sample data. Variables are automatically replaced with example values. The actual email will use real booking data.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // Listen for preview updates
        window.addEventListener('preview-updated', function(e) {
            const iframe = document.getElementById('email-preview-iframe');
            if (iframe && @this.previewHtml) {
                iframe.srcdoc = @this.previewHtml;
            }
        });
        
        // Auto-refresh preview on content change
        let debounceTimer;
        document.addEventListener('input', function(e) {
            if (e.target.closest('[name*="subject"]') || e.target.closest('.trix-editor')) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    @this.refreshPreview();
                }, 1000);
            }
        });
        
        // Refresh preview on initial load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                @this.refreshPreview();
            }, 500);
        });
    </script>
    @endpush
    
    @push('styles')
    <style>
        .email-preview-content iframe {
            background: white;
        }
        
        /* Dark mode adjustments */
        .dark .email-preview-content iframe {
            filter: invert(1) hue-rotate(180deg);
        }
        
        /* Mobile preview styling */
        @media (max-width: 640px) {
            .email-preview-content iframe {
                transform: scale(0.8);
                transform-origin: top left;
            }
        }
    </style>
    @endpush
</x-filament-panels::page>