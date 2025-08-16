<div class="space-y-6">
    @foreach($components as $category => $items)
        <div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 capitalize">
                {{ str_replace('_', ' ', $category) }}
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($items as $key => $component)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                         x-data="{ showCode: false }"
                         @click="insertComponent('{{ $category }}', '{{ $key }}')">
                        
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $component['name'] }}
                            </h4>
                            
                            <button type="button"
                                    @click.stop="showCode = !showCode"
                                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                <span x-show="!showCode">View Code</span>
                                <span x-show="showCode">Hide Code</span>
                            </button>
                        </div>
                        
                        <!-- Component Preview -->
                        <div class="bg-gray-50 dark:bg-gray-800 rounded p-3 mb-2 overflow-hidden"
                             style="max-height: 150px;">
                            <div class="transform scale-75 origin-top-left">
                                {!! $component['html'] !!}
                            </div>
                        </div>
                        
                        <!-- Variables Used -->
                        @if(!empty($component['variables']))
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                <span class="font-semibold">Variables:</span>
                                @foreach($component['variables'] as $var)
                                    <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">@{{ $var }}</code>
                                @endforeach
                            </div>
                        @endif
                        
                        <!-- Code View -->
                        <div x-show="showCode" 
                             x-transition
                             class="mt-3 p-3 bg-gray-900 rounded overflow-x-auto">
                            <pre class="text-xs text-gray-300"><code>{{ $component['html'] }}</code></pre>
                        </div>
                        
                        <!-- Insert Button -->
                        <button type="button"
                                @click.stop="insertComponentToEditor('{{ addslashes($component['html']) }}')"
                                class="mt-3 w-full px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                            Insert Component
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<script>
    function insertComponentToEditor(html) {
        // Get the current editor type
        const templateType = document.querySelector('[name="data.template_type"]')?.value || 'wysiwyg';
        
        if (templateType === 'wysiwyg') {
            // For WYSIWYG editor, insert into the rich editor
            const editor = document.querySelector('.trix-editor');
            if (editor && editor.editor) {
                editor.editor.insertHTML(html);
            }
        } else if (templateType === 'html') {
            // For HTML editor, insert into the textarea
            const htmlField = document.querySelector('[name="data.html_body"]');
            if (htmlField) {
                const cursorPos = htmlField.selectionStart;
                const textBefore = htmlField.value.substring(0, cursorPos);
                const textAfter = htmlField.value.substring(cursorPos);
                htmlField.value = textBefore + '\n' + html + '\n' + textAfter;
                htmlField.setSelectionRange(cursorPos + html.length + 2, cursorPos + html.length + 2);
                htmlField.focus();
                
                // Trigger input event to update Livewire
                htmlField.dispatchEvent(new Event('input'));
            }
        } else if (templateType === 'blade') {
            // For Blade editor, insert into the body textarea
            const bodyField = document.querySelector('[name="data.body"]');
            if (bodyField) {
                const cursorPos = bodyField.selectionStart;
                const textBefore = bodyField.value.substring(0, cursorPos);
                const textAfter = bodyField.value.substring(cursorPos);
                bodyField.value = textBefore + '\n' + html + '\n' + textAfter;
                bodyField.setSelectionRange(cursorPos + html.length + 2, cursorPos + html.length + 2);
                bodyField.focus();
                
                // Trigger input event to update Livewire
                bodyField.dispatchEvent(new Event('input'));
            }
        }
        
        // Show notification
        window.$wireui.notify({
            title: 'Component Inserted',
            description: 'The component has been added to your template',
            icon: 'success'
        });
        
        // Refresh preview after a short delay
        setTimeout(() => {
            @this.refreshPreview();
        }, 500);
    }
</script>