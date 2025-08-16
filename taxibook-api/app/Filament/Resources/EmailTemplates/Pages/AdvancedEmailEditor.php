<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\EmailTemplate;
use App\Services\EmailComponentsService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;

class AdvancedEmailEditor extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;
    
    public $previewHtml = '';
    public $isMobilePreview = false;
    public $previewClient = 'default'; // default, gmail, outlook, apple
    public $selectedComponent = null;
    public $editorMode = 'visual'; // visual, html, css
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('switchEditor')
                ->label('Switch to ' . ($this->record->template_type === 'html' ? 'Visual Editor' : 'Code Editor'))
                ->icon('heroicon-o-code-bracket')
                ->color('gray')
                ->action(function () {
                    $this->record->template_type = $this->record->template_type === 'html' ? 'wysiwyg' : 'html';
                    $this->record->save();
                    $this->fillForm();
                }),
                
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->modalHeading('Email Preview Settings')
                ->form([
                    Select::make('preview_client')
                        ->label('Email Client')
                        ->options([
                            'default' => 'Default',
                            'gmail' => 'Gmail',
                            'outlook' => 'Outlook',
                            'apple' => 'Apple Mail',
                        ])
                        ->default($this->previewClient),
                    Toggle::make('mobile_preview')
                        ->label('Mobile View')
                        ->default($this->isMobilePreview),
                ])
                ->action(function (array $data) {
                    $this->previewClient = $data['preview_client'];
                    $this->isMobilePreview = $data['mobile_preview'];
                    $this->refreshPreview();
                }),
                
            Action::make('sendTest')
                ->label('Send Test')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form([
                    TagsInput::make('test_emails')
                        ->label('Test Email Addresses')
                        ->placeholder('Enter email addresses')
                        ->required()
                        ->default($this->record->test_recipients ?? [auth()->user()->email]),
                    Select::make('test_booking')
                        ->label('Use Sample Data From')
                        ->options(function () {
                            return \App\Models\Booking::latest()
                                ->limit(10)
                                ->get()
                                ->mapWithKeys(fn ($booking) => [
                                    $booking->id => "{$booking->booking_number} - {$booking->customer_full_name}"
                                ]);
                        })
                        ->helperText('Select a booking to use real data, or leave empty for sample data'),
                ])
                ->action(function (array $data) {
                    $this->sendTestEmails($data['test_emails'], $data['test_booking'] ?? null);
                }),
                
            Action::make('saveVersion')
                ->label('Save Version')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->form([
                    TextInput::make('change_note')
                        ->label('Version Note')
                        ->placeholder('What changed in this version?')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->saveVersion($data['change_note']);
                    Notification::make()
                        ->title('Version Saved')
                        ->body("Version {$this->record->version_history[0]['version']} has been saved.")
                        ->success()
                        ->send();
                }),
                
            Action::make('viewHistory')
                ->label('Version History')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->modalContent(fn () => view('filament.resources.email-templates.version-history', [
                    'versions' => $this->record->version_history ?? []
                ]))
                ->modalSubmitAction(false),
                
            Action::make('duplicate')
                ->label('Duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->form([
                    TextInput::make('new_name')
                        ->label('New Template Name')
                        ->required()
                        ->default($this->record->name . ' (Copy)'),
                ])
                ->action(function (array $data) {
                    $duplicate = $this->record->duplicate($data['new_name']);
                    Notification::make()
                        ->title('Template Duplicated')
                        ->body("Template has been duplicated as '{$duplicate->name}'")
                        ->success()
                        ->send();
                    return redirect()->route('filament.admin.resources.email-templates.edit', $duplicate);
                }),
                
            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $export = $this->record->export();
                    $filename = "email-template-{$this->record->slug}-" . now()->format('Y-m-d') . ".json";
                    
                    return response()->streamDownload(function () use ($export) {
                        echo json_encode($export, JSON_PRETTY_PRINT);
                    }, $filename);
                }),
                
            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('template_file')
                        ->label('Template File')
                        ->acceptedFileTypes(['application/json'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = $data['template_file'];
                    $content = Storage::disk('local')->get($path);
                    $templateData = json_decode($content, true);
                    
                    if (!$templateData) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body('Invalid template file')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $imported = EmailTemplate::import($templateData);
                    
                    Notification::make()
                        ->title('Template Imported')
                        ->body("Template '{$imported->name}' has been imported successfully")
                        ->success()
                        ->send();
                        
                    return redirect()->route('filament.admin.resources.email-templates.edit', $imported);
                }),
        ];
    }
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Email Content Editor')
                    ->description('Create and edit your email template')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Email Subject')
                            ->required()
                            ->columnSpanFull(),
                            
                        Select::make('template_type')
                            ->label('Editor Type')
                            ->options([
                                'wysiwyg' => 'Visual Editor (WYSIWYG)',
                                'html' => 'HTML/CSS Code Editor',
                                'blade' => 'Blade Template',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->refreshPreview()),
                            
                        // Conditional editors based on template type
                        RichEditor::make('body')
                            ->label('Email Content')
                            ->visible(fn ($get) => $get('template_type') === 'wysiwyg')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'link',
                                'redo',
                                'undo',
                            ])
                            ->columnSpanFull(),
                            
                        Textarea::make('html_body')
                            ->label('HTML Code')
                            ->visible(fn ($get) => $get('template_type') === 'html')
                            ->rows(20)
                            ->extraAttributes([
                                'style' => 'font-family: monospace; font-size: 13px;',
                            ])
                            ->columnSpanFull(),
                            
                        Textarea::make('css_styles')
                            ->label('CSS Styles')
                            ->visible(fn ($get) => $get('template_type') === 'html')
                            ->rows(10)
                            ->extraAttributes([
                                'style' => 'font-family: monospace; font-size: 13px;',
                            ])
                            ->helperText('CSS will be inlined for email compatibility')
                            ->columnSpanFull(),
                            
                        Textarea::make('body')
                            ->label('Blade Template')
                            ->visible(fn ($get) => $get('template_type') === 'blade')
                            ->rows(20)
                            ->extraAttributes([
                                'style' => 'font-family: monospace; font-size: 13px;',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                    
                Section::make('Email Components Library')
                    ->description('Click on any component to insert it into your template')
                    ->schema([
                        ViewField::make('email-components')
                            ->view('filament.resources.email-templates.components-library', [
                                'components' => EmailComponentsService::getComponents()
                            ]),
                    ])
                    ->collapsed(),
                    
                Section::make('Available Variables')
                    ->description('These variables can be used in your template')
                    ->schema([
                        ViewField::make('variables-reference')
                            ->view('filament.resources.email-templates.variables-reference', [
                                'variables' => EmailTemplate::getAvailableVariables()
                            ]),
                    ])
                    ->collapsed(),
                    
                Section::make('Template Settings')
                    ->schema([
                        TextInput::make('name')
                            ->label('Template Name')
                            ->required(),
                            
                        Select::make('category')
                            ->label('Category')
                            ->options([
                                'customer' => 'Customer',
                                'admin' => 'Admin',
                                'driver' => 'Driver',
                            ])
                            ->required(),
                            
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                            
                        Toggle::make('is_active')
                            ->label('Active'),
                    ])
                    ->collapsed(),
                    
                Section::make('Test Recipients')
                    ->schema([
                        TagsInput::make('test_recipients')
                            ->label('Default Test Email Addresses')
                            ->placeholder('Enter email addresses')
                            ->helperText('These emails will be pre-filled when sending test emails'),
                    ])
                    ->collapsed(),
            ]);
    }
    
    protected function sendTestEmails(array $emails, ?int $bookingId)
    {
        try {
            // Save current state
            $this->save();
            
            // Get sample data
            if ($bookingId) {
                $booking = \App\Models\Booking::find($bookingId);
                $sampleData = $this->getDataFromBooking($booking);
            } else {
                $sampleData = $this->getSampleData();
            }
            
            // Save test recipients
            $this->record->test_recipients = $emails;
            $this->record->save();
            
            // Render the email
            $rendered = $this->record->render($sampleData);
            
            // Send to each test email
            foreach ($emails as $email) {
                Mail::html(
                    view('emails.luxe-layout', [
                        'content' => new HtmlString($rendered['body'])
                    ])->render(),
                    function ($message) use ($email, $rendered) {
                        $message->to($email)
                            ->subject('[TEST] ' . $rendered['subject']);
                    }
                );
            }
            
            Notification::make()
                ->title('Test Emails Sent')
                ->body('Test emails have been sent to: ' . implode(', ', $emails))
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Send Test Emails')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function getDataFromBooking($booking): array
    {
        return [
            'booking_number' => $booking->booking_number,
            'customer_name' => $booking->customer_full_name,
            'customer_first_name' => $booking->customer_first_name,
            'customer_last_name' => $booking->customer_last_name,
            'customer_email' => $booking->customer_email,
            'customer_phone' => $booking->customer_phone,
            'pickup_address' => $booking->pickup_address,
            'dropoff_address' => $booking->dropoff_address,
            'pickup_date' => $booking->pickup_date->format('F j, Y'),
            'pickup_time' => $booking->pickup_date->format('g:i A'),
            'vehicle_type' => $booking->vehicleType->display_name ?? 'Luxury Sedan',
            'estimated_fare' => number_format($booking->estimated_fare, 2),
            'final_fare' => number_format($booking->final_fare ?? $booking->estimated_fare, 2),
            'special_instructions' => $booking->special_instructions ?? 'None',
            'admin_notes' => $booking->admin_notes ?? '',
            'company_name' => config('business.name', 'LuxRide'),
            'company_phone' => config('business.phone', '1-800-LUXRIDE'),
            'company_email' => config('business.email', 'support@luxride.com'),
            'support_url' => config('app.url') . '/support',
            'booking_url' => config('app.url') . '/booking/' . $booking->booking_number,
            'current_year' => date('Y'),
        ];
    }
    
    protected function getSampleData(): array
    {
        return [
            'booking_number' => 'ABC123',
            'customer_name' => 'John Doe',
            'customer_first_name' => 'John',
            'customer_last_name' => 'Doe',
            'customer_email' => 'john.doe@example.com',
            'customer_phone' => '(555) 123-4567',
            'pickup_address' => '123 Main St, Miami, FL 33101',
            'dropoff_address' => 'Miami International Airport',
            'pickup_date' => 'December 25, 2025',
            'pickup_time' => '10:30 AM',
            'vehicle_type' => 'Luxury SUV',
            'estimated_fare' => '85.00',
            'final_fare' => '85.00',
            'special_instructions' => 'Please call upon arrival',
            'admin_notes' => '',
            'company_name' => config('business.name', 'LuxRide'),
            'company_phone' => config('business.phone', '1-800-LUXRIDE'),
            'company_email' => config('business.email', 'support@luxride.com'),
            'support_url' => config('app.url') . '/support',
            'booking_url' => config('app.url') . '/booking/ABC123',
            'current_year' => date('Y'),
        ];
    }
    
    public function refreshPreview()
    {
        try {
            $sampleData = $this->getSampleData();
            
            // Save current form data temporarily to preview
            $tempTemplate = new EmailTemplate($this->form->getState());
            
            // Render the email with sample data
            $rendered = $tempTemplate->render($sampleData);
            
            // Apply email client specific styles
            $clientStyles = $this->getEmailClientStyles($this->previewClient);
            
            // Wrap in email layout
            $this->previewHtml = view('emails.luxe-layout', [
                'content' => new HtmlString($rendered['body'])
            ])->render();
            
            // Apply client-specific modifications
            if ($clientStyles) {
                $this->previewHtml = str_replace('</head>', $clientStyles . '</head>', $this->previewHtml);
            }
            
        } catch (\Exception $e) {
            $this->previewHtml = '<div class="p-4 text-red-600">Error rendering preview: ' . $e->getMessage() . '</div>';
        }
    }
    
    protected function getEmailClientStyles(string $client): string
    {
        switch ($client) {
            case 'gmail':
                return '<style>
                    /* Gmail-specific styles */
                    u + .body .gmail-hide { display: none !important; }
                    @media only screen and (max-width: 600px) {
                        .gmail-mobile-forced-width { width: 100% !important; }
                    }
                </style>';
                
            case 'outlook':
                return '<!--[if mso]>
                    <style type="text/css">
                        table { border-collapse: collapse; border-spacing: 0; margin: 0; }
                        div, td { padding: 0; }
                        div { margin: 0 !important; }
                    </style>
                <![endif]-->';
                
            case 'apple':
                return '<style>
                    /* Apple Mail specific */
                    @media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
                        .apple-mobile-forced-width { min-width: 375px !important; }
                    }
                </style>';
                
            default:
                return '';
        }
    }
}