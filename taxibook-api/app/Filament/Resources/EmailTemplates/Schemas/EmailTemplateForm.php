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
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Template Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        TextInput::make('slug')
                            ->label('Slug (unique identifier)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(255)
                            ->columnSpan(1),
                        
                        Select::make('category')
                            ->options([
                                'customer' => 'Customer',
                                'admin' => 'Admin',
                                'driver' => 'Driver',
                            ])
                            ->required()
                            ->columnSpan(1),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive templates will not be sent')
                            ->columnSpan(1),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make('Triggers & Recipients')
                    ->schema([
                        CheckboxList::make('trigger_events')
                            ->label('When should this email be sent?')
                            ->options(EmailTemplate::getAvailableTriggers())
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->columnSpan(2),
                        
                        Repeater::make('recipient_config')
                            ->label('Recipients Configuration')
                            ->schema([
                                Select::make('type')
                                    ->label('Recipient Type')
                                    ->options([
                                        'customer' => 'Customer (from booking)',
                                        'admin' => 'All Admin Emails',
                                        'specific_admin' => 'Specific Admin Emails',
                                        'driver' => 'Assigned Driver',
                                        'custom' => 'Custom Email Address',
                                    ])
                                    ->required()
                                    ->reactive(),
                                
                                Toggle::make('enabled')
                                    ->label('Enabled')
                                    ->default(true),
                                
                                Select::make('send_as')
                                    ->label('Send As')
                                    ->options([
                                        'to' => 'To',
                                        'cc' => 'CC',
                                        'bcc' => 'BCC',
                                    ])
                                    ->default('to'),
                                
                                TagsInput::make('emails')
                                    ->label('Email Addresses')
                                    ->placeholder('Add email addresses')
                                    ->visible(fn ($get) => $get('type') === 'specific_admin'),
                                
                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->visible(fn ($get) => $get('type') === 'custom'),
                                
                                TextInput::make('name')
                                    ->label('Recipient Name')
                                    ->visible(fn ($get) => $get('type') === 'custom'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->columnSpan(2),
                        
                        TextInput::make('cc_emails')
                            ->label('Additional CC Emails (comma-separated)')
                            ->placeholder('email1@example.com, email2@example.com')
                            ->columnSpan(1),
                        
                        TextInput::make('bcc_emails')
                            ->label('Additional BCC Emails (comma-separated)')
                            ->placeholder('email1@example.com, email2@example.com')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Email Content')
                    ->schema([
                        TextInput::make('subject')
                            ->label('Email Subject')
                            ->required()
                            ->placeholder('Use {{variables}} for dynamic content')
                            ->helperText('Available variables will be shown below')
                            ->columnSpan(2),
                        
                        RichEditor::make('body')
                            ->label('Email Body')
                            ->required()
                            ->columnSpan(2),
                        
                        Placeholder::make('available_variables_display')
                            ->label('Available Variables')
                            ->content(function () {
                                $variables = EmailTemplate::getAvailableVariables();
                                $html = '<div class="grid grid-cols-2 gap-2">';
                                foreach ($variables as $key => $description) {
                                    $html .= "<div class='text-sm'><code class='bg-gray-100 px-1 rounded'>{{{$key}}}</code> - {$description}</div>";
                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('attach_receipt')
                            ->label('Attach Payment Receipt PDF')
                            ->columnSpan(1),
                        
                        Toggle::make('attach_booking_details')
                            ->label('Attach Booking Details PDF')
                            ->columnSpan(1),
                        
                        TextInput::make('delay_minutes')
                            ->label('Delay (minutes)')
                            ->numeric()
                            ->default(0)
                            ->helperText('Delay sending this email by X minutes')
                            ->columnSpan(1),
                        
                        TextInput::make('priority')
                            ->label('Priority')
                            ->numeric()
                            ->default(5)
                            ->helperText('Higher priority emails are sent first (1-10)')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }
}