<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use App\Models\BookingFormField;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Template Information')
                    ->description('Basic information about this email template')
                    ->schema([
                        TextInput::make('name')
                            ->label('Template Name')
                            ->placeholder('e.g., Welcome Email, Booking Confirmation')
                            ->required()
                            ->maxLength(255)
                            ->helperText('A descriptive name for this email template')
                            ->columnSpan(2),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Describe when and why this email is sent')
                            ->rows(2)
                            ->helperText('Internal notes about this template\'s purpose')
                            ->columnSpan(2),
                        
                        Toggle::make('is_active')
                            ->label('Template Active')
                            ->default(true)
                            ->helperText('Inactive templates will not be sent even when triggered')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make('Recipients')
                    ->description('Choose who should receive this email')
                    ->schema([
                        Toggle::make('send_to_customer')
                            ->label('Send to Customer')
                            ->helperText('Email the booking customer')
                            ->default(false)
                            ->columnSpan(1),
                        
                        Toggle::make('send_to_admin')
                            ->label('Send to Admin')
                            ->helperText('Email all admin addresses')
                            ->default(false)
                            ->columnSpan(1),
                        
                        Toggle::make('send_to_driver')
                            ->label('Send to Driver')
                            ->helperText('Email assigned driver (when available)')
                            ->default(false)
                            ->disabled()
                            ->hint('Coming soon')
                            ->columnSpan(1),
                        
                        TextInput::make('cc_emails')
                            ->label('Additional CC Recipients')
                            ->placeholder('email1@example.com, email2@example.com')
                            ->helperText('Comma-separated list of additional CC recipients')
                            ->columnSpan(['default' => 3, 'md' => 2]),
                        
                        TextInput::make('bcc_emails')
                            ->label('Additional BCC Recipients')
                            ->placeholder('email1@example.com, email2@example.com')
                            ->helperText('Comma-separated list of additional BCC recipients')
                            ->columnSpan(['default' => 3, 'md' => 1]),
                    ])
                    ->columns(3),

                Section::make('When to Send')
                    ->description('Configure the timing for this email')
                    ->schema([
                        CheckboxList::make('trigger_events')
                            ->label('Step 1: Select Trigger Events')
                            ->options(EmailTemplate::getAvailableTriggers())
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->required()
                            ->helperText('Choose which events can trigger this email to be sent')
                            ->columnSpan(2),
                        
                        Select::make('send_timing_type')
                            ->label('Step 2: When to Send (relative to trigger)')
                            ->options([
                                'immediate' => 'Send immediately when event occurs',
                                'before_pickup' => 'Send before pickup time',
                                'after_pickup' => 'Send after pickup time',
                                'after_booking' => 'Send after booking is created',
                                'after_completion' => 'Send after trip is completed',
                            ])
                            ->default('immediate')
                            ->reactive()
                            ->helperText('Determine the timing relative to the event')
                            ->columnSpan(2),
                        
                        TextInput::make('send_timing_value')
                            ->label('Time Amount')
                            ->numeric()
                            ->default(24)
                            ->minValue(0)
                            ->visible(fn ($get) => $get('send_timing_type') !== 'immediate')
                            ->required(fn ($get) => $get('send_timing_type') !== 'immediate')
                            ->columnSpan(1),
                        
                        Select::make('send_timing_unit')
                            ->label('Time Unit')
                            ->options([
                                'minutes' => 'Minutes',
                                'hours' => 'Hours',
                                'days' => 'Days',
                            ])
                            ->default('hours')
                            ->visible(fn ($get) => $get('send_timing_type') !== 'immediate')
                            ->required(fn ($get) => $get('send_timing_type') !== 'immediate')
                            ->columnSpan(1),
                        
                        Placeholder::make('timing_preview')
                            ->label('Summary')
                            ->content(function ($get) {
                                $triggers = $get('trigger_events') ?? [];
                                $type = $get('send_timing_type');
                                
                                if (empty($triggers)) {
                                    return '⚠️ Please select at least one trigger event above.';
                                }
                                
                                $triggerCount = count($triggers);
                                $triggerText = $triggerCount === 1 ? 'the selected event occurs' : 'any of the ' . $triggerCount . ' selected events occur';
                                
                                if ($type === 'immediate') {
                                    return "✅ Email will be sent immediately when {$triggerText}.";
                                }
                                
                                $value = $get('send_timing_value') ?? 0;
                                $unit = $get('send_timing_unit') ?? 'hours';
                                $unitLabel = $value === 1 ? rtrim($unit, 's') : $unit;
                                
                                switch ($type) {
                                    case 'before_pickup':
                                        return "✅ Email will be sent {$value} {$unitLabel} before the pickup time when {$triggerText}.";
                                    case 'after_pickup':
                                        return "✅ Email will be sent {$value} {$unitLabel} after the pickup time when {$triggerText}.";
                                    case 'after_booking':
                                        return "✅ Email will be sent {$value} {$unitLabel} after the booking is created when {$triggerText}.";
                                    case 'after_completion':
                                        return "✅ Email will be sent {$value} {$unitLabel} after the trip is completed when {$triggerText}.";
                                    default:
                                        return '';
                                }
                            })
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make('Dynamic Fields Status')
                    ->description('Currently available dynamic form fields that customers can fill')
                    ->schema([
                        Placeholder::make('dynamic_fields_status')
                            ->label('')
                            ->content(function () {
                                $fields = BookingFormField::ordered()->get();
                                
                                if ($fields->isEmpty()) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="text-gray-500">No dynamic form fields configured. Add fields in Booking Form Fields section.</div>'
                                    );
                                }
                                
                                $html = '<div class="space-y-3">';
                                $html .= '<div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">';
                                $html .= '<p class="text-sm text-blue-700"><strong>Tip:</strong> These fields are filled by customers during booking. Use their shortcodes in your templates to personalize emails.</p>';
                                $html .= '</div>';
                                
                                $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
                                
                                foreach ($fields as $field) {
                                    $statusColor = $field->enabled ? 'green' : 'gray';
                                    $statusText = $field->enabled ? 'Active' : 'Disabled';
                                    $statusIcon = $field->enabled ? '✓' : '✗';
                                    $requiredBadge = $field->required ? '<span class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Required</span>' : '<span class="ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Optional</span>';
                                    
                                    $shortcode = '{{field_' . $field->key . '}}';
                                    $displayShortcode = '';
                                    if (in_array($field->type, ['select', 'checkbox'])) {
                                        $displayShortcode = '<br><code class="text-xs bg-gray-100 px-1">{{field_' . $field->key . '_display}}</code>';
                                    }
                                    
                                    $html .= "
                                    <div class='p-3 border rounded-lg " . ($field->enabled ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50') . "'>
                                        <div class='flex items-start justify-between'>
                                            <div class='flex-1'>
                                                <div class='flex items-center'>
                                                    <span class='text-{$statusColor}-500 mr-2'>{$statusIcon}</span>
                                                    <strong class='text-sm'>{$field->label}</strong>
                                                    {$requiredBadge}
                                                </div>
                                                <div class='mt-2'>
                                                    <code class='text-xs bg-gray-100 px-1 py-0.5 rounded cursor-pointer' onclick='copyShortcode(\"{$shortcode}\")' title='Click to copy'>{$shortcode}</code>
                                                    {$displayShortcode}
                                                </div>
                                                <div class='text-xs text-gray-600 mt-1'>Type: {$field->type}</div>";
                                    
                                    if ($field->helper_text) {
                                        $html .= "<div class='text-xs text-gray-500 mt-1 italic'>{$field->helper_text}</div>";
                                    }
                                    
                                    if (!$field->enabled) {
                                        $html .= "<div class='text-xs text-red-600 mt-1'>⚠️ This field is disabled and won't appear in forms</div>";
                                    }
                                    
                                    $html .= "
                                            </div>
                                            <span class='text-xs px-2 py-1 rounded bg-{$statusColor}-100 text-{$statusColor}-800'>{$statusText}</span>
                                        </div>
                                    </div>";
                                }
                                
                                $html .= '</div>';
                                $html .= '<div class="mt-4 p-3 bg-gray-100 rounded">';
                                $html .= '<p class="text-xs text-gray-600"><strong>Note:</strong> Only enabled fields will be shown to customers. Disabled field shortcodes will return empty values in emails.</p>';
                                $html .= '</div>';
                                $html .= '</div>';
                                
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpan(2),
                    ])
                    ->collapsed()
                    ->collapsible(),

                Section::make('Email Content')
                    ->description('Compose your email subject and body')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Email Subject')
                            ->required()
                            ->placeholder('Use {{variables}} for dynamic content')
                            ->helperText('Example: "Booking Confirmed - {{booking_number}}"')
                            ->columnSpan(2),
                        
                        RichEditor::make('body')
                            ->label('Email Body')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'orderedList',
                                'bulletList',
                                'link',
                                'redo',
                                'undo',
                            ])
                            ->helperText('Use {{variables}} to insert dynamic content')
                            ->columnSpan(2),
                        
                        Placeholder::make('available_variables_display')
                            ->label('Available Shortcodes')
                            ->content(function () {
                                $groupedVariables = EmailTemplate::getGroupedAvailableVariables();
                                $html = '<div class="space-y-4">';
                                $html .= '<p class="text-sm text-gray-600 mb-3">Click any shortcode to copy it to your clipboard. Use these in the subject and body fields.</p>';
                                
                                // Add tab navigation for groups
                                $html .= '<div class="border-b border-gray-200">';
                                $html .= '<nav class="-mb-px flex space-x-4" aria-label="Tabs">';
                                $first = true;
                                foreach ($groupedVariables as $group => $vars) {
                                    if (empty($vars)) continue;
                                    $activeClass = $first ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                                    $html .= "<button 
                                        type='button'
                                        onclick='showShortcodeGroup(\"" . str_replace(' ', '_', $group) . "\", this)' 
                                        class='shortcode-tab whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {$activeClass}'
                                    >{$group}</button>";
                                    $first = false;
                                }
                                $html .= '</nav>';
                                $html .= '</div>';
                                
                                // Add content for each group
                                $first = true;
                                foreach ($groupedVariables as $group => $vars) {
                                    if (empty($vars)) continue;
                                    $groupId = str_replace(' ', '_', $group);
                                    $displayStyle = $first ? 'block' : 'none';
                                    
                                    $html .= "<div id='group_{$groupId}' class='shortcode-group mt-4' style='display: {$displayStyle};'>";
                                    
                                    // Special notice for dynamic fields
                                    if ($group === 'Dynamic Fields') {
                                        $html .= '<div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-3">';
                                        $html .= '<p class="text-sm text-blue-700">These fields are filled by customers during booking. They will only appear in emails when the customer provides values.</p>';
                                        $html .= '</div>';
                                    }
                                    
                                    $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-2">';
                                    foreach ($vars as $key => $description) {
                                        $isConditional = str_starts_with($key, 'has_') || str_starts_with($key, 'is_');
                                        $isDynamic = str_starts_with($key, 'field_');
                                        
                                        $badgeClass = '';
                                        $badge = '';
                                        if ($isConditional) {
                                            $badgeClass = 'bg-yellow-100 text-yellow-800';
                                            $badge = '<span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' . $badgeClass . '">Boolean</span>';
                                        } elseif ($isDynamic) {
                                            $badgeClass = 'bg-green-100 text-green-800';
                                            $badge = '<span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' . $badgeClass . '">Dynamic</span>';
                                        }
                                        
                                        $html .= "
                                            <div class='flex items-start space-x-2 p-2 rounded hover:bg-gray-50'>
                                                <code 
                                                    class='bg-gray-100 px-2 py-1 rounded cursor-pointer hover:bg-gray-200 text-xs' 
                                                    onclick='copyShortcode(\"{{{$key}}}\")' 
                                                    title='Click to copy'
                                                >{{{$key}}}</code>
                                                <div class='flex-1'>
                                                    <span class='text-xs text-gray-600'>{$description}</span>
                                                    {$badge}
                                                </div>
                                            </div>
                                        ";
                                    }
                                    $html .= '</div>';
                                    $html .= '</div>';
                                    $first = false;
                                }
                                
                                $html .= '</div>';
                                
                                // Add JavaScript for tab switching and copy functionality
                                $html .= "
                                <script>
                                    function showShortcodeGroup(groupId, tabElement) {
                                        // Hide all groups
                                        document.querySelectorAll('.shortcode-group').forEach(el => {
                                            el.style.display = 'none';
                                        });
                                        
                                        // Show selected group
                                        const groupEl = document.getElementById('group_' + groupId);
                                        if (groupEl) {
                                            groupEl.style.display = 'block';
                                        }
                                        
                                        // Update tab styles
                                        document.querySelectorAll('.shortcode-tab').forEach(tab => {
                                            tab.className = tab.className.replace('border-indigo-500 text-indigo-600', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300');
                                        });
                                        tabElement.className = tabElement.className.replace('border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'border-indigo-500 text-indigo-600');
                                    }
                                    
                                    function copyShortcode(shortcode) {
                                        navigator.clipboard.writeText(shortcode).then(function() {
                                            // Show success message (optional)
                                            const notification = document.createElement('div');
                                            notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                                            notification.textContent = 'Copied: ' + shortcode;
                                            document.body.appendChild(notification);
                                            setTimeout(() => notification.remove(), 2000);
                                        });
                                    }
                                </script>
                                ";
                                
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make('Attachments & Settings')
                    ->description('Additional email settings')
                    ->schema([
                        Toggle::make('attach_receipt')
                            ->label('Attach Payment Receipt')
                            ->helperText('Include PDF receipt when payment is captured')
                            ->columnSpan(1),
                        
                        Toggle::make('attach_booking_details')
                            ->label('Attach Booking Details')
                            ->helperText('Include PDF with full booking information')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}