<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
use App\Models\BookingFormField;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;

class SimplifiedEmailTemplateForm
{
    public static function make(): array
    {
        return [
            Section::make('Template Information')
                ->description('Basic information about this email template')
                ->schema([
                    TextInput::make('name')
                        ->label('Template Name')
                        ->placeholder('e.g., 24 Hour Reminder, Booking Confirmation')
                        ->required()
                        ->maxLength(255)
                        ->helperText('A clear, descriptive name for this email template'),
                    
                    Textarea::make('description')
                        ->label('Description')
                        ->placeholder('Describe when and why this email is sent')
                        ->rows(2)
                        ->helperText('Internal notes about this template\'s purpose'),
                    
                    Toggle::make('is_active')
                        ->label('Template Active')
                        ->default(true)
                        ->helperText('Inactive templates will not be sent'),
                ])
                ->columns(1),

            Section::make('When to Send This Email')
                ->description('Choose whether this email is event-triggered (immediate) or time-based (scheduled)')
                ->schema([
                    Radio::make('email_type')
                        ->label('Email Type')
                        ->options([
                            'event' => 'Event-Triggered (Immediate)',
                            'scheduled' => 'Time-Based (Scheduled)',
                        ])
                        ->descriptions([
                            'event' => 'Send immediately when specific events occur (e.g., booking confirmed, payment captured)',
                            'scheduled' => 'Send at a specific time relative to booking (e.g., 24h before pickup, 1 day after trip)',
                        ])
                        ->default('event')
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(function (Set $set, $state) {
                            // Clear conflicting fields when type changes
                            if ($state === 'event') {
                                $set('send_timing_type', 'immediate');
                                $set('trigger_events', []);
                            } else {
                                $set('trigger_events', []);
                                $set('send_timing_type', 'before_pickup');
                            }
                        })
                        ->columnSpan(2),
                    
                    // Event-triggered configuration
                    CheckboxList::make('trigger_events')
                        ->label('Select Events That Trigger This Email')
                        ->options([
                            'Booking Events' => [
                                'booking.confirmed' => 'Booking Confirmed (payment authorized)',
                                'booking.modified' => 'Booking Modified',
                                'booking.cancelled' => 'Booking Cancelled',
                                'booking.completed' => 'Trip Completed',
                            ],
                            'Payment Events' => [
                                'payment.captured' => 'Payment Captured',
                                'payment.refunded' => 'Payment Refunded',
                                'payment.failed' => 'Payment Failed',
                            ],
                            'Driver Events' => [
                                'driver.assigned' => 'Driver Assigned',
                                'driver.enroute' => 'Driver En Route',
                                'driver.arrived' => 'Driver Arrived',
                            ],
                        ])
                        ->columns(2)
                        ->visible(fn (Get $get) => $get('email_type') === 'event')
                        ->required(fn (Get $get) => $get('email_type') === 'event')
                        ->helperText('Select one or more events. Email sends immediately when ANY selected event occurs.')
                        ->columnSpan(2),
                    
                    // Time-based configuration
                    Select::make('send_timing_type')
                        ->label('Send Email...')
                        ->options([
                            'before_pickup' => 'Before Pickup Time',
                            'after_pickup' => 'After Pickup Time',
                            'after_booking' => 'After Booking Created',
                            'after_completion' => 'After Trip Completed',
                        ])
                        ->visible(fn (Get $get) => $get('email_type') === 'scheduled')
                        ->required(fn (Get $get) => $get('email_type') === 'scheduled')
                        ->reactive()
                        ->default('before_pickup')
                        ->helperText('Choose the reference point for timing')
                        ->columnSpan(2),
                    
                    TextInput::make('send_timing_value')
                        ->label('How Long?')
                        ->numeric()
                        ->default(24)
                        ->minValue(1)
                        ->visible(fn (Get $get) => $get('email_type') === 'scheduled')
                        ->required(fn (Get $get) => $get('email_type') === 'scheduled')
                        ->columnSpan(1),
                    
                    Select::make('send_timing_unit')
                        ->label('Time Unit')
                        ->options([
                            'minutes' => 'Minutes',
                            'hours' => 'Hours',
                            'days' => 'Days',
                        ])
                        ->default('hours')
                        ->visible(fn (Get $get) => $get('email_type') === 'scheduled')
                        ->required(fn (Get $get) => $get('email_type') === 'scheduled')
                        ->columnSpan(1),
                    
                    // Clear timing preview
                    Placeholder::make('timing_preview')
                        ->label('📧 Email Will Be Sent:')
                        ->content(function (Get $get) {
                            $emailType = $get('email_type');
                            
                            if ($emailType === 'event') {
                                $triggers = $get('trigger_events') ?? [];
                                if (empty($triggers)) {
                                    return '⚠️ Please select at least one event above.';
                                }
                                
                                $count = count($triggers);
                                if ($count === 1) {
                                    $eventName = EmailTemplate::getAvailableTriggers()[$triggers[0]] ?? $triggers[0];
                                    return "✅ **Immediately** when: {$eventName}";
                                } else {
                                    return "✅ **Immediately** when ANY of these {$count} events occur";
                                }
                            } else {
                                $type = $get('send_timing_type');
                                $value = $get('send_timing_value') ?? 0;
                                $unit = $get('send_timing_unit') ?? 'hours';
                                
                                if (!$type || !$value) {
                                    return '⚠️ Please configure timing above.';
                                }
                                
                                $unitLabel = $value == 1 ? rtrim($unit, 's') : $unit;
                                
                                switch ($type) {
                                    case 'before_pickup':
                                        return "✅ **{$value} {$unitLabel} before** pickup time (scheduled)";
                                    case 'after_pickup':
                                        return "✅ **{$value} {$unitLabel} after** pickup time (scheduled)";
                                    case 'after_booking':
                                        return "✅ **{$value} {$unitLabel} after** booking is created (scheduled)";
                                    case 'after_completion':
                                        return "✅ **{$value} {$unitLabel} after** trip is completed (scheduled)";
                                    default:
                                        return '';
                                }
                            }
                        })
                        ->columnSpan(2),
                ])
                ->columns(2),

            Section::make('Recipients')
                ->description('Choose who receives this email')
                ->schema([
                    Toggle::make('send_to_customer')
                        ->label('Send to Customer')
                        ->helperText('Send to the booking customer\'s email')
                        ->default(true)
                        ->inline(false),
                    
                    Toggle::make('send_to_admin')
                        ->label('Send to Admin')
                        ->helperText('Send to all admin email addresses')
                        ->default(false)
                        ->inline(false),
                    
                    Toggle::make('send_to_driver')
                        ->label('Send to Driver')
                        ->helperText('Send to assigned driver (when available)')
                        ->default(false)
                        ->disabled()
                        ->hint('Coming soon')
                        ->inline(false),
                    
                    TextInput::make('cc_emails')
                        ->label('CC Recipients')
                        ->placeholder('email1@example.com, email2@example.com')
                        ->helperText('Additional CC recipients (comma-separated)'),
                    
                    TextInput::make('bcc_emails')
                        ->label('BCC Recipients')
                        ->placeholder('email1@example.com, email2@example.com')
                        ->helperText('Hidden recipients (comma-separated)'),
                ])
                ->columns(3),

            Section::make('Email Content')
                ->description('Compose your email message')
                ->schema([
                    TextInput::make('subject')
                        ->label('Email Subject')
                        ->required()
                        ->placeholder('Use {{variables}} for dynamic content')
                        ->helperText('Example: "Booking Confirmed - {{booking_number}}"')
                        ->columnSpan(2),
                    
                    Select::make('template_type')
                        ->label('Content Type')
                        ->options([
                            'wysiwyg' => 'Rich Text Editor (Recommended)',
                            'html' => 'HTML Code',
                            'blade' => 'Plain Text',
                        ])
                        ->default('wysiwyg')
                        ->reactive()
                        ->helperText('Choose how you want to edit the email content')
                        ->columnSpan(2),
                    
                    RichEditor::make('body')
                        ->label('Email Body')
                        ->required()
                        ->visible(fn (Get $get) => $get('template_type') === 'wysiwyg')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'orderedList',
                            'bulletList',
                            'link',
                            'redo',
                            'undo',
                        ])
                        ->helperText('Use {{variables}} to insert dynamic content')
                        ->columnSpan(2),
                    
                    Textarea::make('html_body')
                        ->label('HTML Content')
                        ->required()
                        ->visible(fn (Get $get) => $get('template_type') === 'html')
                        ->rows(15)
                        ->helperText('Enter raw HTML. Use {{variables}} for dynamic content.')
                        ->columnSpan(2),
                    
                    Textarea::make('body')
                        ->label('Plain Text Content')
                        ->required()
                        ->visible(fn (Get $get) => $get('template_type') === 'blade')
                        ->rows(10)
                        ->helperText('Plain text email. Use {{variables}} for dynamic content.')
                        ->columnSpan(2),
                ])
                ->columns(2),

            Section::make('Available Variables')
                ->description('Click any variable to copy it for use in your email')
                ->schema([
                    Placeholder::make('variables_reference')
                        ->label('')
                        ->content(function () {
                            $html = '<div class="space-y-4">';
                            
                            // Common variables section
                            $html .= '<div class="bg-blue-50 border-l-4 border-blue-400 p-4">';
                            $html .= '<h4 class="font-semibold text-blue-900 mb-2">Most Common Variables</h4>';
                            $html .= '<div class="grid grid-cols-2 md:grid-cols-3 gap-2">';
                            
                            $commonVars = [
                                'booking_number' => 'Booking reference',
                                'customer_name' => 'Customer full name',
                                'customer_first_name' => 'First name only',
                                'pickup_date' => 'Pickup date',
                                'pickup_time' => 'Pickup time',
                                'pickup_address' => 'Pickup location',
                                'dropoff_address' => 'Dropoff location',
                                'vehicle_type' => 'Vehicle selected',
                                'estimated_fare' => 'Total fare',
                                'company_name' => 'Your company name',
                                'company_phone' => 'Your phone number',
                            ];
                            
                            foreach ($commonVars as $var => $desc) {
                                $html .= sprintf(
                                    '<div class="flex flex-col">
                                        <code class="bg-white px-2 py-1 rounded cursor-pointer hover:bg-blue-100 text-xs" 
                                              onclick="navigator.clipboard.writeText(\'{{%s}}\'); showCopied(this);">
                                            {{%s}}
                                        </code>
                                        <span class="text-xs text-gray-600 mt-1">%s</span>
                                    </div>',
                                    $var, $var, $desc
                                );
                            }
                            
                            $html .= '</div></div>';
                            
                            // Conditional variables section
                            $html .= '<details class="mt-4">';
                            $html .= '<summary class="cursor-pointer font-semibold text-gray-700 hover:text-gray-900">Advanced Variables & Conditionals</summary>';
                            $html .= '<div class="mt-3 space-y-3">';
                            
                            // Conditionals
                            $html .= '<div class="bg-yellow-50 p-3 rounded">';
                            $html .= '<p class="text-sm font-semibold text-yellow-900 mb-2">Conditional Blocks</p>';
                            $html .= '<pre class="bg-white p-2 rounded text-xs overflow-x-auto">';
                            $html .= '{{#if has_special_instructions}}
Special Instructions: {{special_instructions}}
{{/if}}

{{#if field_flight_number}}
Flight: {{field_flight_number}}
{{/if}}</pre>';
                            $html .= '</div>';
                            
                            // All variables link
                            $html .= '<div class="text-sm">';
                            $html .= '<a href="#" onclick="event.preventDefault(); document.getElementById(\'all-vars\').classList.toggle(\'hidden\');" class="text-blue-600 hover:text-blue-800">View all available variables →</a>';
                            $html .= '</div>';
                            
                            $html .= '</div></details>';
                            
                            // Hidden section with all variables
                            $html .= '<div id="all-vars" class="hidden mt-4 p-4 bg-gray-50 rounded">';
                            $groupedVars = EmailTemplate::getGroupedAvailableVariables();
                            foreach ($groupedVars as $group => $vars) {
                                if (empty($vars)) continue;
                                $html .= "<h5 class='font-semibold text-gray-700 mb-2'>{$group}</h5>";
                                $html .= '<div class="grid grid-cols-2 gap-2 mb-4">';
                                foreach ($vars as $var => $desc) {
                                    $html .= sprintf(
                                        '<div class="text-xs">
                                            <code class="bg-white px-1 py-0.5 rounded cursor-pointer hover:bg-gray-200" 
                                                  onclick="navigator.clipboard.writeText(\'{{%s}}\');">{{%s}}</code>
                                            <span class="text-gray-600 ml-1">%s</span>
                                        </div>',
                                        $var, $var, $desc
                                    );
                                }
                                $html .= '</div>';
                            }
                            $html .= '</div>';
                            
                            $html .= '</div>';
                            
                            // Add JavaScript for copy feedback
                            $html .= '<script>
                                function showCopied(element) {
                                    const original = element.innerText;
                                    element.innerText = "✓ Copied!";
                                    element.classList.add("bg-green-100");
                                    setTimeout(() => {
                                        element.innerText = original;
                                        element.classList.remove("bg-green-100");
                                    }, 1500);
                                }
                            </script>';
                            
                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->columnSpan(2),
                ])
                ->collapsed()
                ->collapsible(),

            Section::make('Additional Settings')
                ->description('Optional email attachments')
                ->schema([
                    Toggle::make('attach_receipt')
                        ->label('Attach Payment Receipt')
                        ->helperText('Include PDF receipt (when available)'),
                    
                    Toggle::make('attach_booking_details')
                        ->label('Attach Booking Details')
                        ->helperText('Include PDF with full booking information'),
                    
                    TextInput::make('priority')
                        ->label('Priority')
                        ->numeric()
                        ->default(5)
                        ->minValue(1)
                        ->maxValue(10)
                        ->helperText('1 = Highest, 10 = Lowest'),
                ])
                ->columns(3)
                ->collapsed()
                ->collapsible(),
        ];
    }

    /**
     * Process form data before saving
     */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure proper configuration based on email type
        if (($data['email_type'] ?? 'event') === 'event') {
            // Event-triggered email
            $data['send_timing_type'] = 'immediate';
            $data['send_timing_value'] = 0;
            $data['send_timing_unit'] = 'minutes';
            // Keep trigger_events as selected
        } else {
            // Time-based email
            $data['trigger_events'] = []; // Clear all triggers
            // Keep timing configuration as selected
        }
        
        // Remove the temporary email_type field
        unset($data['email_type']);
        
        return $data;
    }

    /**
     * Process form data when loading for editing
     */
    public static function mutateFormDataBeforeFill(array $data): array
    {
        // Determine email type based on configuration
        if ($data['send_timing_type'] === 'immediate' && !empty($data['trigger_events'])) {
            $data['email_type'] = 'event';
        } else {
            $data['email_type'] = 'scheduled';
        }
        
        return $data;
    }
}