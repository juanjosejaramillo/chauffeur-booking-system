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
                    
                // 6. PDF Attachments
                Section::make('PDF Attachments')
                    ->description('Attach PDF documents to this email')
                    ->schema([
                        Toggle::make('attach_receipt')
                            ->label('Attach PDF Receipt')
                            ->helperText('Include PDF payment receipt (when available)')
                            ->default(false)
                            ->inline(),
                        
                        Toggle::make('attach_booking_details')
                            ->label('Attach PDF Booking Details')
                            ->helperText('Include PDF with full booking information')
                            ->default(false)
                            ->inline(),
                    ])
                    ->columns(2),
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
        // Get grouped variables from the EmailTemplate model
        $groupedVariables = EmailTemplate::getGroupedAvailableVariables();
        
        $html = '<div style="max-height: 500px; overflow-y: auto;">';
        $html .= '<p style="color: #6b7280; font-size: 14px; margin-bottom: 16px;">Click any variable to copy it to your clipboard:</p>';
        
        // Create a styled tabs container
        $html .= '<div style="border-bottom: 1px solid #e5e7eb; margin-bottom: 20px;">';
        $html .= '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
        
        $first = true;
        $tabIndex = 0;
        foreach ($groupedVariables as $group => $vars) {
            if (empty($vars)) continue;
            $groupId = 'tab_' . $tabIndex++;
            $activeStyle = $first ? 'background: #4f46e5; color: white;' : 'background: #f3f4f6; color: #4b5563;';
            
            $html .= "<button 
                type='button'
                onclick='showVarGroup(\"$groupId\", this)' 
                style='padding: 8px 16px; border: none; border-radius: 6px 6px 0 0; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s; $activeStyle'
                onmouseover='if(this.style.backgroundColor !== \"rgb(79, 70, 229)\") this.style.backgroundColor=\"#e5e7eb\"'
                onmouseout='if(this.style.backgroundColor !== \"rgb(79, 70, 229)\") this.style.backgroundColor=\"#f3f4f6\"'
            >$group</button>";
            $first = false;
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        // Create content containers for each group
        $first = true;
        $tabIndex = 0;
        foreach ($groupedVariables as $group => $vars) {
            if (empty($vars)) continue;
            $groupId = 'tab_' . $tabIndex++;
            $display = $first ? 'block' : 'none';
            
            $html .= "<div id='content_$groupId' class='var-content' style='display: $display; margin-top: 20px;'>";
            
            // Special notice for dynamic fields
            if ($group === 'Dynamic Fields') {
                $html .= '<div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px; margin-bottom: 16px; border-radius: 4px;">';
                $html .= '<p style="color: #1e40af; font-size: 13px; margin: 0;"><strong>Note:</strong> These fields are filled by customers during booking. They will only appear in emails when values are provided.</p>';
                $html .= '</div>';
            }
            
            // System & Date notice
            if ($group === 'System & Date') {
                $html .= '<div style="background: #f3e8ff; border-left: 4px solid #9333ea; padding: 12px; margin-bottom: 16px; border-radius: 4px;">';
                $html .= '<p style="color: #6b21a8; font-size: 13px; margin: 0;"><strong>Tip:</strong> These variables are automatically filled with current values when the email is sent.</p>';
                $html .= '</div>';
            }
            
            $html .= '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 8px;">';
            
            foreach ($vars as $key => $desc) {
                $varName = '{{' . $key . '}}';
                $isConditional = str_starts_with($key, 'has_') || str_starts_with($key, 'is_');
                $isDynamic = str_starts_with($key, 'field_');
                
                // Style badges
                $badge = '';
                if ($isConditional) {
                    $badge = '<span style="display: inline-block; margin-left: 4px; padding: 2px 6px; background: #fef3c7; color: #92400e; font-size: 10px; border-radius: 3px; font-weight: 600;">IF</span>';
                } elseif ($isDynamic) {
                    $badge = '<span style="display: inline-block; margin-left: 4px; padding: 2px 6px; background: #dcfce7; color: #14532d; font-size: 10px; border-radius: 3px; font-weight: 600;">FIELD</span>';
                }
                
                // Create a nicely styled variable card
                $html .= '
                    <div style="
                        display: flex; 
                        align-items: flex-start; 
                        gap: 8px; 
                        padding: 10px; 
                        background: #f9fafb; 
                        border: 1px solid #e5e7eb; 
                        border-radius: 6px; 
                        transition: all 0.2s;
                        cursor: pointer;
                    "
                    onmouseover="this.style.backgroundColor=\'#f3f4f6\'; this.style.borderColor=\'#9ca3af\';"
                    onmouseout="this.style.backgroundColor=\'#f9fafb\'; this.style.borderColor=\'#e5e7eb\';"
                    onclick="copyVar(\'' . htmlspecialchars($varName, ENT_QUOTES) . '\')"
                    >
                        <div style="flex-shrink: 0;">
                            <code style="
                                display: inline-block; 
                                padding: 4px 8px; 
                                background: #4f46e5; 
                                color: white; 
                                font-size: 11px; 
                                font-family: monospace; 
                                border-radius: 4px;
                                font-weight: 500;
                            ">' . htmlspecialchars($varName) . '</code>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="color: #4b5563; font-size: 12px; line-height: 1.4;">
                                ' . htmlspecialchars($desc) . '
                                ' . $badge . '
                            </div>
                        </div>
                    </div>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
            $first = false;
        }
        
        $html .= '</div>';
        
        // Add improved JavaScript
        $html .= "
        <script>
            function showVarGroup(groupId, button) {
                // Hide all content
                document.querySelectorAll('.var-content').forEach(el => {
                    el.style.display = 'none';
                });
                
                // Show selected content
                const content = document.getElementById('content_' + groupId);
                if (content) {
                    content.style.display = 'block';
                }
                
                // Update button styles
                button.parentElement.querySelectorAll('button').forEach(btn => {
                    btn.style.backgroundColor = '#f3f4f6';
                    btn.style.color = '#4b5563';
                });
                button.style.backgroundColor = '#4f46e5';
                button.style.color = 'white';
            }
            
            function copyVar(variable) {
                // Copy to clipboard
                const textArea = document.createElement('textarea');
                textArea.value = variable;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                // Show toast notification
                const existing = document.getElementById('copy-toast');
                if (existing) existing.remove();
                
                const toast = document.createElement('div');
                toast.id = 'copy-toast';
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #10b981;
                    color: white;
                    padding: 12px 20px;
                    border-radius: 6px;
                    font-size: 14px;
                    font-weight: 500;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                    z-index: 99999;
                    animation: slideIn 0.3s ease;
                `;
                toast.innerHTML = '✓ Copied: <code style=\"background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 3px;\">' + variable + '</code>';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }
            
            // Add animation styles if not already present
            if (!document.getElementById('var-animations')) {
                const style = document.createElement('style');
                style.id = 'var-animations';
                style.innerHTML = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOut {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
        </script>
        ";
        
        return $html;
    }
}