<?php

namespace App\Filament\Resources\EmailTemplates\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\EmailTemplate;

class SimpleEmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // 1. Basic Information
                Section::make('Basic Information')
                    ->description('Set up the basic details of your email template')
                    ->schema([
                        TextInput::make('name')
                            ->label('Template Name')
                            ->required()
                            ->placeholder('e.g., Booking Confirmation')
                            ->helperText('A descriptive name to identify this template'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active templates will be sent')
                            ->inline(),
                    ])
                    ->columns(2),
                
                // 2. When to Send
                Section::make('Triggers & Timing')
                    ->description('Define when this email should be sent')
                    ->columns(3)
                    ->schema([
                        CheckboxList::make('trigger_events')
                            ->label('Send this email when:')
                            ->options(EmailTemplate::getAvailableTriggers())
                            ->columns(2)
                            ->required()
                            ->columnSpan(3),
                        
                        Select::make('send_timing_type')
                            ->label('Timing')
                            ->options([
                                'immediate' => 'Send immediately',
                                'before_pickup' => 'Before pickup',
                                'after_pickup' => 'After pickup',
                                'after_booking' => 'After booking',
                                'after_completion' => 'After completion',
                            ])
                            ->default('immediate')
                            ->reactive()
                            ->columnSpan(1),
                        
                        TextInput::make('send_timing_value')
                            ->label('Time')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->visible(fn ($get) => $get('send_timing_type') !== 'immediate')
                            ->required(fn ($get) => $get('send_timing_type') !== 'immediate')
                            ->columnSpan(1),
                        
                        Select::make('send_timing_unit')
                            ->label('Unit')
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
                            ->label('')
                            ->content(function ($get) {
                                $triggers = $get('trigger_events') ?? [];
                                if (empty($triggers)) {
                                    return '⚠️ Please select at least one trigger event above';
                                }
                                
                                $type = $get('send_timing_type');
                                
                                if ($type === 'immediate') {
                                    return '✅ Email will be sent immediately when any selected event occurs';
                                }
                                
                                $value = $get('send_timing_value') ?? 1;
                                $unit = $get('send_timing_unit') ?? 'hours';
                                $unitLabel = $value == 1 ? rtrim($unit, 's') : $unit;
                                
                                switch ($type) {
                                    case 'before_pickup':
                                        return "✅ Email will be sent {$value} {$unitLabel} before pickup time";
                                    case 'after_pickup':
                                        return "✅ Email will be sent {$value} {$unitLabel} after pickup time";
                                    case 'after_booking':
                                        return "✅ Email will be sent {$value} {$unitLabel} after booking is created";
                                    case 'after_completion':
                                        return "✅ Email will be sent {$value} {$unitLabel} after trip is completed";
                                    default:
                                        return '';
                                }
                            })
                            ->columnSpan(3),
                    ]),
                    
                // 3. Recipients
                Section::make('Recipients')
                    ->description('Choose who receives this email')
                    ->columns(3)
                    ->schema([
                        Toggle::make('send_to_customer')
                            ->label('Customer')
                            ->helperText('Booking customer')
                            ->default(true)
                            ->inline(),
                        
                        Toggle::make('send_to_admin')
                            ->label('Admin')
                            ->helperText('Company/admin')
                            ->default(false)
                            ->inline(),
                        
                        Toggle::make('send_to_driver')
                            ->label('Driver')
                            ->helperText('Assigned driver')
                            ->default(false)
                            ->inline(),
                            
                        TextInput::make('cc_emails')
                            ->label('CC (optional)')
                            ->placeholder('email1@example.com, email2@example.com')
                            ->columnSpan(3),
                            
                        TextInput::make('bcc_emails')
                            ->label('BCC (optional)')
                            ->placeholder('email1@example.com, email2@example.com')
                            ->columnSpan(3),
                    ]),
                
                // 4. Email Content
                Section::make('Email Content')
                    ->description('Design your email template')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Email Subject')
                            ->required()
                            ->placeholder('e.g., Booking Confirmed - {{booking_number}}')
                            ->helperText('Use {{variable_name}} for dynamic content')
                            ->columnSpanFull(),
                        
                        Textarea::make('html_body')
                            ->label('HTML Template')
                            ->rows(15)
                            ->required()
                            ->default(self::getDefaultTemplate())
                            ->helperText('Write your HTML email template. Use {{variable_name}} for dynamic content.')
                            ->extraAttributes([
                                'style' => 'font-family: monospace; font-size: 13px;',
                            ])
                            ->columnSpanFull(),
                    ]),
                    
                // 5. Variable Reference
                Section::make('Available Variables')
                    ->description('Copy and paste these variables into your template')
                    ->schema([
                        Placeholder::make('variables')
                            ->content(new \Illuminate\Support\HtmlString(self::getVariablesList())),
                    ])
                    ->collapsed(),
            ]);
    }
    
    protected static function getDefaultTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 0;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: #fff;
        }
        .header { 
            background: #000; 
            color: #fff; 
            padding: 30px; 
            text-align: center; 
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: normal;
            letter-spacing: 2px;
        }
        .content { 
            padding: 40px 30px; 
            background: #fff; 
        }
        .footer { 
            background: #f8f8f8; 
            padding: 20px; 
            text-align: center; 
            font-size: 12px;
            color: #666;
        }
        .button { 
            display: inline-block; 
            background: #000; 
            color: #fff !important; 
            padding: 12px 30px; 
            text-decoration: none; 
            border-radius: 4px; 
        }
        .info-box {
            background: #f8f8f8;
            border-left: 3px solid #000;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{company_name}}</h1>
        </div>
        
        <div class="content">
            <h2>Hello {{customer_first_name}},</h2>
            
            <p>Your content here...</p>
            
            <div class="info-box">
                <strong>Details:</strong><br>
                Reference: {{booking_number}}<br>
                Date: {{pickup_date}}<br>
                Time: {{pickup_time}}
            </div>
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{booking_url}}" class="button">View Details</a>
            </p>
        </div>
        
        <div class="footer">
            <p>{{company_name}}<br>
            {{company_phone}} | {{company_email}}</p>
        </div>
    </div>
</body>
</html>';
    }
    
    protected static function getVariablesList(): string
    {
        $variables = [
            '{{booking_number}}' => 'Booking reference number',
            '{{customer_name}}' => 'Customer full name',
            '{{customer_first_name}}' => 'Customer first name',
            '{{customer_last_name}}' => 'Customer last name',
            '{{customer_email}}' => 'Customer email',
            '{{customer_phone}}' => 'Customer phone',
            '{{pickup_address}}' => 'Pickup location',
            '{{dropoff_address}}' => 'Dropoff location',
            '{{pickup_date}}' => 'Pickup date',
            '{{pickup_time}}' => 'Pickup time',
            '{{vehicle_type}}' => 'Vehicle type',
            '{{estimated_fare}}' => 'Estimated fare',
            '{{final_fare}}' => 'Final fare amount',
            '{{special_instructions}}' => 'Special instructions',
            '{{company_name}}' => 'Company name',
            '{{company_phone}}' => 'Company phone',
            '{{company_email}}' => 'Company email',
            '{{booking_url}}' => 'Booking details link',
        ];
        
        $html = '<div class="space-y-2">';
        $html .= '<p class="text-sm text-gray-600 mb-3">Copy and paste these variables into your template:</p>';
        $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
        
        foreach ($variables as $var => $desc) {
            $html .= '
                <div class="flex items-start space-x-2">
                    <code class="inline-block bg-gray-100 px-2 py-1 rounded text-xs font-mono text-blue-600 whitespace-nowrap">' 
                    . htmlspecialchars($var) . 
                    '</code>
                    <span class="text-xs text-gray-600">' . $desc . '</span>
                </div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
}