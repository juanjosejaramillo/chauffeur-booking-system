<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\EmailTemplate;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class SimpleEmailEditor extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview Email')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalContent(fn () => view('filament.email-preview', [
                    'html' => $this->getRenderedEmail()
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
                
            Action::make('sendTest')
                ->label('Send Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form([
                    TextInput::make('test_email')
                        ->label('Send test email to:')
                        ->email()
                        ->required()
                        ->default(auth()->user()->email ?? ''),
                ])
                ->action(function (array $data) {
                    $this->sendTestEmail($data['test_email']);
                }),
        ];
    }
    
    protected function getFormSchema(): array
    {
        return [
            // Basic Info at the top
            \Filament\Forms\Components\Section::make('Template Settings')
                ->schema([
                    TextInput::make('name')
                        ->label('Template Name')
                        ->required(),
                        
                    Toggle::make('is_active')
                        ->label('Active')
                        ->helperText('Only active templates will be sent')
                        ->default(true)
                        ->inline(),
                ])
                ->columns(2),
            
            // When (Triggers & Timing)
            \Filament\Forms\Components\Section::make('When to Send')
                ->description('Configure triggers and timing')
                ->columns(3)
                ->schema([
                    CheckboxList::make('trigger_events')
                        ->label('Send when these events occur:')
                        ->options(EmailTemplate::getAvailableTriggers())
                        ->columns(2)
                        ->required()
                        ->columnSpan(3),
                    
                    Select::make('send_timing_type')
                        ->label('Timing')
                        ->options([
                            'immediate' => 'Immediately',
                            'before_pickup' => 'Before pickup',
                            'after_pickup' => 'After pickup',
                            'after_booking' => 'After booking',
                            'after_completion' => 'After completion',
                        ])
                        ->default('immediate')
                        ->reactive(),
                    
                    TextInput::make('send_timing_value')
                        ->label('Time')
                        ->numeric()
                        ->default(1)
                        ->visible(fn ($get) => $get('send_timing_type') !== 'immediate'),
                    
                    Select::make('send_timing_unit')
                        ->label('Unit')
                        ->options([
                            'minutes' => 'Minutes',
                            'hours' => 'Hours',
                            'days' => 'Days',
                        ])
                        ->default('hours')
                        ->visible(fn ($get) => $get('send_timing_type') !== 'immediate'),
                ]),
            
            // Who (Recipients)
            \Filament\Forms\Components\Section::make('Recipients')
                ->description('Choose who receives this email')
                ->columns(3)
                ->schema([
                    Toggle::make('send_to_customer')
                        ->label('Customer')
                        ->default(true)
                        ->inline(),
                    
                    Toggle::make('send_to_admin')
                        ->label('Admin')
                        ->default(false)
                        ->inline(),
                    
                    Toggle::make('send_to_driver')
                        ->label('Driver')
                        ->default(false)
                        ->inline(),
                ]),
                
            // What (Email Content)
            \Filament\Forms\Components\Section::make('Email Content')
                ->description('Design your email')
                ->schema([
                    TextInput::make('subject')
                        ->label('Subject Line')
                        ->required()
                        ->placeholder('e.g., Booking Confirmed - {{booking_number}}')
                        ->columnSpanFull(),
                        
                    Textarea::make('html_body')
                        ->label('HTML Template')
                        ->rows(12)
                        ->required()
                        ->default($this->getDefaultHtmlTemplate())
                        ->extraAttributes([
                            'style' => 'font-family: monospace; font-size: 13px;',
                        ])
                        ->columnSpanFull(),
                        
                    Placeholder::make('variables_hint')
                        ->content('💡 Use variables like {{booking_number}}, {{customer_first_name}}, {{pickup_date}}, etc. in your template'),
                ]),
        ];
    }
    
    protected function getDefaultHtmlTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #000; color: #fff; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #fff; }
        .footer { background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; }
        .button { display: inline-block; background: #000; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{company_name}}</h1>
        </div>
        <div class="content">
            <h2>Hello {{customer_first_name}},</h2>
            <p>Your booking has been confirmed!</p>
            
            <p><strong>Booking Details:</strong></p>
            <ul>
                <li>Booking Number: {{booking_number}}</li>
                <li>Date: {{pickup_date}}</li>
                <li>Time: {{pickup_time}}</li>
                <li>Pickup: {{pickup_address}}</li>
                <li>Dropoff: {{dropoff_address}}</li>
                <li>Vehicle: {{vehicle_type}}</li>
                <li>Fare: ${{estimated_fare}}</li>
            </ul>
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{booking_url}}" class="button">View Booking</a>
            </p>
        </div>
        <div class="footer">
            <p>{{company_name}} | {{company_phone}} | {{company_email}}</p>
        </div>
    </div>
</body>
</html>';
    }
    
    protected function getRenderedEmail(): string
    {
        try {
            $template = $this->record;
            $sampleData = $this->getSampleData();
            
            // Get the HTML body
            $html = $template->html_body ?: $template->body;
            
            // Replace variables with sample data
            foreach ($sampleData as $key => $value) {
                $html = str_replace('{{' . $key . '}}', $value, $html);
            }
            
            return $html;
        } catch (\Exception $e) {
            return '<div style="color: red; padding: 20px;">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    protected function sendTestEmail(string $email)
    {
        try {
            $html = $this->getRenderedEmail();
            $subject = $this->record->subject;
            
            // Replace variables in subject
            $sampleData = $this->getSampleData();
            foreach ($sampleData as $key => $value) {
                $subject = str_replace('{{' . $key . '}}', $value, $subject);
            }
            
            Mail::html($html, function ($message) use ($email, $subject) {
                $message->to($email)
                    ->subject('[TEST] ' . $subject);
            });
            
            Notification::make()
                ->title('Test Email Sent!')
                ->body("Test email has been sent to {$email}")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Send')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function getSampleData(): array
    {
        return [
            'booking_number' => 'TEST-123',
            'customer_name' => 'John Doe',
            'customer_first_name' => 'John',
            'customer_last_name' => 'Doe',
            'customer_email' => 'john.doe@example.com',
            'customer_phone' => '(555) 123-4567',
            'pickup_address' => '123 Main St, Miami, FL',
            'dropoff_address' => 'Miami International Airport',
            'pickup_date' => date('F j, Y'),
            'pickup_time' => '10:30 AM',
            'vehicle_type' => 'Luxury SUV',
            'estimated_fare' => '85.00',
            'final_fare' => '85.00',
            'special_instructions' => 'Please call upon arrival',
            'company_name' => config('business.name', 'LuxRide'),
            'company_phone' => config('business.phone', '1-800-LUXRIDE'),
            'company_email' => config('business.email', 'support@luxride.com'),
            'booking_url' => config('app.url') . '/booking/TEST-123',
        ];
    }
}