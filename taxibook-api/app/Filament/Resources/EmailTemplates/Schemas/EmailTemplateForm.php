<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use App\Models\EmailTemplate;
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
                            ->label('Available Variables')
                            ->content(function () {
                                $variables = EmailTemplate::getAvailableVariables();
                                $html = '<div class="space-y-2">';
                                $html .= '<p class="text-sm text-gray-600 mb-2">Click to copy any variable below:</p>';
                                $html .= '<div class="grid grid-cols-2 gap-2">';
                                foreach ($variables as $key => $description) {
                                    $html .= "
                                        <div class='text-sm'>
                                            <code 
                                                class='bg-gray-100 px-2 py-1 rounded cursor-pointer hover:bg-gray-200' 
                                                onclick='navigator.clipboard.writeText(\"{{{$key}}}\")' 
                                                title='Click to copy'
                                            >{{{$key}}}</code>
                                            <span class='text-gray-600 ml-2'>- {$description}</span>
                                        </div>
                                    ";
                                }
                                $html .= '</div>';
                                $html .= '</div>';
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