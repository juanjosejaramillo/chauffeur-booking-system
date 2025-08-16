<div class="space-y-4">
    @if(empty($versions))
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <x-heroicon-o-archive-box class="w-12 h-12 mx-auto mb-3 opacity-50" />
            <p>No version history available</p>
            <p class="text-sm mt-1">Save versions of your template to track changes over time</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($versions as $version)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Version {{ $version['version'] }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($version['saved_at'])->diffForHumans() }}
                                </span>
                            </div>
                            
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">Saved by:</span> {{ $version['saved_by'] }}
                            </p>
                            
                            @if($version['change_note'])
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Note:</span> {{ $version['change_note'] }}
                                </p>
                            @endif
                            
                            <div class="mt-3 flex items-center gap-2">
                                <button type="button"
                                        wire:click="restoreVersion({{ $version['version'] }})"
                                        class="text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Restore This Version
                                </button>
                                
                                <button type="button"
                                        x-data="{ open: false }"
                                        @click="open = !open"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    Preview Changes
                                </button>
                            </div>
                        </div>
                        
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            {{ \Carbon\Carbon::parse($version['saved_at'])->format('M d, Y g:i A') }}
                        </span>
                    </div>
                    
                    <!-- Preview Section (Hidden by default) -->
                    <div x-data="{ open: false }" x-show="open" x-transition class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subject:</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                    {{ $version['subject'] }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Template Type:</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $version['template_type'] ?? 'blade' }}
                                </span>
                            </div>
                            
                            @if(!empty($version['body']))
                                <div>
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Body Preview:</p>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-3 rounded max-h-40 overflow-y-auto">
                                        <pre class="whitespace-pre-wrap">{{ Str::limit(strip_tags($version['body']), 500) }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>