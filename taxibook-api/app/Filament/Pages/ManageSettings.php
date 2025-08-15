<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Cache;

class ManageSettings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'System Settings';
    protected static ?int $navigationSort = 100;
    protected static string | \UnitEnum | null $navigationGroup = 'System';
    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->data = $settings;
        $this->form->fill($this->data);
    }
    
    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment('end')
                    ->fullWidth(false),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Business Information')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make('General Information')
                                    ->description('Basic information about your business')
                                    ->schema([
                                        TextInput::make('business_name')
                                            ->label('Business Name')
                                            ->required()
                                            ->minLength(2)
                                            ->maxLength(255)
                                            ->default('LuxRide')
                                            ->validationMessages([
                                                'required' => 'Business name is required for email communications',
                                                'min' => 'Business name must be at least 2 characters'
                                            ])
                                            ->helperText('This name will appear in all customer communications'),
                                        
                                        TextInput::make('business_tagline')
                                            ->label('Business Tagline')
                                            ->maxLength(255)
                                            ->default('Premium Transportation Service')
                                            ->helperText('A short description of your business'),
                                        
                                        Textarea::make('business_address')
                                            ->label('Business Address')
                                            ->rows(3)
                                            ->helperText('Your full business address'),
                                    ])
                                    ->columns(1),
                                
                                Section::make('Contact Information')
                                    ->description('How customers can reach your business')
                                    ->schema([
                                        TextInput::make('business_phone')
                                            ->label('Business Phone')
                                            ->tel()
                                            ->required()
                                            ->default('1-800-LUXRIDE')
                                            ->helperText('Main business phone number'),
                                        
                                        TextInput::make('business_email')
                                            ->label('Business Email')
                                            ->email()
                                            ->required()
                                            ->default('info@luxride.com')
                                            ->helperText('Main business email address'),
                                        
                                        TextInput::make('support_phone')
                                            ->label('Support Phone')
                                            ->tel()
                                            ->helperText('Customer support phone (leave empty to use business phone)'),
                                        
                                        TextInput::make('support_email')
                                            ->label('Support Email')
                                            ->email()
                                            ->helperText('Customer support email (leave empty to use business email)'),
                                        
                                        TextInput::make('website_url')
                                            ->label('Website URL')
                                            ->url()
                                            ->prefix('https://')
                                            ->helperText('Your business website URL'),
                                    ])
                                    ->columns(2),
                                
                                Section::make('Administrative Settings')
                                    ->description('Important administrative contact information')
                                    ->schema([
                                        TextInput::make('admin_email')
                                            ->label('Admin Email')
                                            ->email()
                                            ->required()
                                            ->default('admin@luxride.com')
                                            ->validationMessages([
                                                'required' => 'Admin email is required for system notifications',
                                                'email' => 'Please enter a valid email address'
                                            ])
                                            ->helperText('Email address for receiving admin notifications (bookings, cancellations, etc.)'),
                                        
                                        TextInput::make('admin_name')
                                            ->label('Admin Name')
                                            ->required()
                                            ->default('Administrator')
                                            ->validationMessages([
                                                'required' => 'Admin name is required for email communications'
                                            ])
                                            ->helperText('Name to use for admin email notifications'),
                                    ])
                                    ->columns(2),
                            ]),
                        
                        Tab::make('Stripe Settings')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('Stripe API Configuration')
                                    ->description('Configure your Stripe payment gateway')
                                    ->schema([
                                        Toggle::make('stripe_enabled')
                                            ->label('Enable Stripe Payments')
                                            ->default(true)
                                            ->reactive()
                                            ->helperText('Enable or disable Stripe payment processing'),
                                        
                                        Select::make('stripe_mode')
                                            ->label('Stripe Mode')
                                            ->options([
                                                'test' => 'Test Mode',
                                                'live' => 'Live Mode',
                                            ])
                                            ->default('test')
                                            ->required()
                                            ->visible(fn (Get $get) => $get('stripe_enabled'))
                                            ->helperText('Choose between test and live mode'),
                                        
                                        Section::make('Test Credentials')
                                            ->schema([
                                                TextInput::make('stripe_test_publishable_key')
                                                    ->label('Test Publishable Key')
                                                    ->password()
                                                    ->revealable()
                                                    ->regex('/^pk_test_[a-zA-Z0-9]+$/')
                                                    ->validationMessages([
                                                        'regex' => 'Must be a valid Stripe test publishable key (pk_test_...)'
                                                    ])
                                                    ->helperText('Your Stripe test publishable key (pk_test_...)'),
                                                
                                                TextInput::make('stripe_test_secret_key')
                                                    ->label('Test Secret Key')
                                                    ->password()
                                                    ->revealable()
                                                    ->regex('/^sk_test_[a-zA-Z0-9]+$/')
                                                    ->validationMessages([
                                                        'regex' => 'Must be a valid Stripe test secret key (sk_test_...)'
                                                    ])
                                                    ->helperText('Your Stripe test secret key (sk_test_...)'),
                                            ])
                                            ->columns(1)
                                            ->visible(fn (Get $get) => $get('stripe_enabled')),
                                        
                                        Section::make('Live Credentials')
                                            ->schema([
                                                TextInput::make('stripe_live_publishable_key')
                                                    ->label('Live Publishable Key')
                                                    ->password()
                                                    ->revealable()
                                                    ->regex('/^pk_live_[a-zA-Z0-9]+$/')
                                                    ->validationMessages([
                                                        'regex' => 'Must be a valid Stripe live publishable key (pk_live_...)'
                                                    ])
                                                    ->helperText('Your Stripe live publishable key (pk_live_...)'),
                                                
                                                TextInput::make('stripe_live_secret_key')
                                                    ->label('Live Secret Key')
                                                    ->password()
                                                    ->revealable()
                                                    ->regex('/^sk_live_[a-zA-Z0-9]+$/')
                                                    ->validationMessages([
                                                        'regex' => 'Must be a valid Stripe live secret key (sk_live_...)'
                                                    ])
                                                    ->helperText('Your Stripe live secret key (sk_live_...)'),
                                            ])
                                            ->columns(1)
                                            ->visible(fn (Get $get) => $get('stripe_enabled') && $get('stripe_mode') === 'live'),
                                        
                                        TextInput::make('stripe_webhook_secret')
                                            ->label('Webhook Secret')
                                            ->password()
                                            ->revealable()
                                            ->visible(fn (Get $get) => $get('stripe_enabled'))
                                            ->regex('/^whsec_[a-zA-Z0-9]+$/')
                                            ->validationMessages([
                                                'regex' => 'Must be a valid Stripe webhook secret (whsec_...)'
                                            ])
                                            ->helperText('Your Stripe webhook signing secret (whsec_...)'),
                                    ])
                                    ->columns(1),
                            ]),
                        
                        Tab::make('Mapbox Settings')
                            ->icon('heroicon-o-map')
                            ->schema([
                                Section::make('Mapbox API Configuration')
                                    ->description('Configure your Mapbox integration for maps and geocoding')
                                    ->schema([
                                        Toggle::make('mapbox_enabled')
                                            ->label('Enable Mapbox')
                                            ->default(true)
                                            ->reactive()
                                            ->helperText('Enable or disable Mapbox integration'),
                                        
                                        TextInput::make('mapbox_public_token')
                                            ->label('Mapbox Public Token')
                                            ->password()
                                            ->revealable()
                                            ->required(fn (Get $get) => $get('mapbox_enabled'))
                                            ->visible(fn (Get $get) => $get('mapbox_enabled'))
                                            ->regex('/^pk\.[a-zA-Z0-9._-]+$/')
                                            ->validationMessages([
                                                'regex' => 'Must be a valid Mapbox public token (pk....)'
                                            ])
                                            ->helperText('Your Mapbox public access token (pk....)'),
                                        
                                        TextInput::make('mapbox_secret_token')
                                            ->label('Mapbox Secret Token')
                                            ->password()
                                            ->revealable()
                                            ->visible(fn (Get $get) => $get('mapbox_enabled'))
                                            ->regex('/^sk\.[a-zA-Z0-9._-]+$/')
                                            ->validationMessages([
                                                'regex' => 'Must be a valid Mapbox secret token (sk....)'
                                            ])
                                            ->helperText('Your Mapbox secret access token (sk....) - Optional, for server-side operations'),
                                        
                                        Select::make('mapbox_map_style')
                                            ->label('Default Map Style')
                                            ->options([
                                                'mapbox://styles/mapbox/streets-v12' => 'Streets',
                                                'mapbox://styles/mapbox/light-v11' => 'Light',
                                                'mapbox://styles/mapbox/dark-v11' => 'Dark',
                                                'mapbox://styles/mapbox/satellite-v9' => 'Satellite',
                                                'mapbox://styles/mapbox/satellite-streets-v12' => 'Satellite Streets',
                                                'mapbox://styles/mapbox/navigation-day-v1' => 'Navigation Day',
                                                'mapbox://styles/mapbox/navigation-night-v1' => 'Navigation Night',
                                            ])
                                            ->default('mapbox://styles/mapbox/streets-v12')
                                            ->visible(fn (Get $get) => $get('mapbox_enabled'))
                                            ->helperText('Choose the default map style for your application'),
                                        
                                        TextInput::make('mapbox_default_latitude')
                                            ->label('Default Latitude')
                                            ->numeric()
                                            ->default('40.7128')
                                            ->visible(fn (Get $get) => $get('mapbox_enabled'))
                                            ->helperText('Default map center latitude'),
                                        
                                        TextInput::make('mapbox_default_longitude')
                                            ->label('Default Longitude')
                                            ->numeric()
                                            ->default('-74.0060')
                                            ->visible(fn (Get $get) => $get('mapbox_enabled'))
                                            ->helperText('Default map center longitude'),
                                        
                                        TextInput::make('mapbox_default_zoom')
                                            ->label('Default Zoom Level')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(20)
                                            ->default(12)
                                            ->visible(fn (Get $get) => $get('mapbox_enabled'))
                                            ->helperText('Default map zoom level (1-20)'),
                                    ])
                                    ->columns(2),
                            ]),
                        
                        Tab::make('Email Settings')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make('Email Configuration')
                                    ->description('Configure email sending settings')
                                    ->schema([
                                        TextInput::make('mail_from_address')
                                            ->label('From Email Address')
                                            ->email()
                                            ->required()
                                            ->default('noreply@luxride.com')
                                            ->helperText('Default "from" email address for system emails'),
                                        
                                        TextInput::make('mail_from_name')
                                            ->label('From Name')
                                            ->required()
                                            ->default('LuxRide')
                                            ->helperText('Default "from" name for system emails'),
                                        
                                        TextInput::make('mail_reply_to')
                                            ->label('Reply-To Address')
                                            ->email()
                                            ->helperText('Reply-to email address (leave empty to use from address)'),
                                        
                                        Toggle::make('email_notifications_enabled')
                                            ->label('Enable Email Notifications')
                                            ->default(true)
                                            ->helperText('Master switch for all email notifications'),
                                        
                                        Toggle::make('email_bcc_admin')
                                            ->label('BCC Admin on All Emails')
                                            ->default(false)
                                            ->helperText('Send a copy of all emails to the admin email address'),
                                    ])
                                    ->columns(2),
                            ]),
                        
                        Tab::make('System Settings')
                            ->icon('heroicon-o-server-stack')
                            ->schema([
                                Section::make('Application Settings')
                                    ->description('General application configuration')
                                    ->schema([
                                        TextInput::make('app_timezone')
                                            ->label('Timezone')
                                            ->default('America/New_York')
                                            ->helperText('System timezone for date/time operations'),
                                        
                                        Select::make('app_locale')
                                            ->label('Locale')
                                            ->options([
                                                'en' => 'English',
                                                'es' => 'Spanish',
                                                'fr' => 'French',
                                            ])
                                            ->default('en')
                                            ->helperText('Default language for the application'),
                                        
                                        Select::make('currency')
                                            ->label('Currency')
                                            ->options([
                                                'USD' => 'USD - US Dollar',
                                                'EUR' => 'EUR - Euro',
                                                'GBP' => 'GBP - British Pound',
                                                'CAD' => 'CAD - Canadian Dollar',
                                            ])
                                            ->default('USD')
                                            ->helperText('Default currency for pricing'),
                                        
                                        Toggle::make('maintenance_mode')
                                            ->label('Maintenance Mode')
                                            ->default(false)
                                            ->helperText('Enable maintenance mode to prevent customer bookings'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        try {
            // Get the form data
            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $this->saveSetting($key, $value);
                }
            }

            // Clear all caches
            Cache::flush();

            Notification::make()
                ->title('Settings saved successfully')
                ->success()
                ->send();
        } catch (Halt $exception) {
            return;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error saving settings')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function saveSetting($key, $value): void
    {
        $settingConfig = $this->getSettingConfig($key);
        
        Setting::updateOrCreate(
            ['key' => $key],
            array_merge($settingConfig, ['value' => $value])
        );
    }

    protected function getSettingConfig($key): array
    {
        $configs = [
            // Business Information
            'business_name' => [
                'group' => 'business',
                'display_name' => 'Business Name',
                'type' => 'text',
                'description' => 'Your business name',
                'order' => 1,
            ],
            'business_tagline' => [
                'group' => 'business',
                'display_name' => 'Business Tagline',
                'type' => 'text',
                'description' => 'Business tagline or slogan',
                'order' => 2,
            ],
            'business_address' => [
                'group' => 'business',
                'display_name' => 'Business Address',
                'type' => 'text',
                'description' => 'Full business address',
                'order' => 3,
            ],
            'business_phone' => [
                'group' => 'business',
                'display_name' => 'Business Phone',
                'type' => 'tel',
                'description' => 'Main business phone number',
                'order' => 4,
            ],
            'business_email' => [
                'group' => 'business',
                'display_name' => 'Business Email',
                'type' => 'email',
                'description' => 'Main business email',
                'order' => 5,
            ],
            'support_phone' => [
                'group' => 'business',
                'display_name' => 'Support Phone',
                'type' => 'tel',
                'description' => 'Customer support phone',
                'order' => 6,
            ],
            'support_email' => [
                'group' => 'business',
                'display_name' => 'Support Email',
                'type' => 'email',
                'description' => 'Customer support email',
                'order' => 7,
            ],
            'website_url' => [
                'group' => 'business',
                'display_name' => 'Website URL',
                'type' => 'url',
                'description' => 'Business website URL',
                'order' => 8,
            ],
            'admin_email' => [
                'group' => 'business',
                'display_name' => 'Admin Email',
                'type' => 'email',
                'description' => 'Administrator email for system notifications',
                'order' => 9,
            ],
            'admin_name' => [
                'group' => 'business',
                'display_name' => 'Admin Name',
                'type' => 'text',
                'description' => 'Administrator name',
                'order' => 10,
            ],
            
            // Stripe Settings
            'stripe_enabled' => [
                'group' => 'stripe',
                'display_name' => 'Stripe Enabled',
                'type' => 'boolean',
                'description' => 'Enable Stripe payments',
                'order' => 1,
            ],
            'stripe_mode' => [
                'group' => 'stripe',
                'display_name' => 'Stripe Mode',
                'type' => 'text',
                'description' => 'Stripe mode (test/live)',
                'order' => 2,
            ],
            'stripe_test_publishable_key' => [
                'group' => 'stripe',
                'display_name' => 'Test Publishable Key',
                'type' => 'password',
                'description' => 'Stripe test publishable key',
                'is_encrypted' => true,
                'order' => 3,
            ],
            'stripe_test_secret_key' => [
                'group' => 'stripe',
                'display_name' => 'Test Secret Key',
                'type' => 'password',
                'description' => 'Stripe test secret key',
                'is_encrypted' => true,
                'order' => 4,
            ],
            'stripe_live_publishable_key' => [
                'group' => 'stripe',
                'display_name' => 'Live Publishable Key',
                'type' => 'password',
                'description' => 'Stripe live publishable key',
                'is_encrypted' => true,
                'order' => 5,
            ],
            'stripe_live_secret_key' => [
                'group' => 'stripe',
                'display_name' => 'Live Secret Key',
                'type' => 'password',
                'description' => 'Stripe live secret key',
                'is_encrypted' => true,
                'order' => 6,
            ],
            'stripe_webhook_secret' => [
                'group' => 'stripe',
                'display_name' => 'Webhook Secret',
                'type' => 'password',
                'description' => 'Stripe webhook secret',
                'is_encrypted' => true,
                'order' => 7,
            ],
            
            // Mapbox Settings
            'mapbox_enabled' => [
                'group' => 'mapbox',
                'display_name' => 'Mapbox Enabled',
                'type' => 'boolean',
                'description' => 'Enable Mapbox integration',
                'order' => 1,
            ],
            'mapbox_public_token' => [
                'group' => 'mapbox',
                'display_name' => 'Public Token',
                'type' => 'password',
                'description' => 'Mapbox public access token',
                'is_encrypted' => true,
                'order' => 2,
            ],
            'mapbox_secret_token' => [
                'group' => 'mapbox',
                'display_name' => 'Secret Token',
                'type' => 'password',
                'description' => 'Mapbox secret access token',
                'is_encrypted' => true,
                'order' => 3,
            ],
            'mapbox_map_style' => [
                'group' => 'mapbox',
                'display_name' => 'Map Style',
                'type' => 'text',
                'description' => 'Default map style',
                'order' => 4,
            ],
            'mapbox_default_latitude' => [
                'group' => 'mapbox',
                'display_name' => 'Default Latitude',
                'type' => 'number',
                'description' => 'Default map center latitude',
                'order' => 5,
            ],
            'mapbox_default_longitude' => [
                'group' => 'mapbox',
                'display_name' => 'Default Longitude',
                'type' => 'number',
                'description' => 'Default map center longitude',
                'order' => 6,
            ],
            'mapbox_default_zoom' => [
                'group' => 'mapbox',
                'display_name' => 'Default Zoom',
                'type' => 'number',
                'description' => 'Default map zoom level',
                'order' => 7,
            ],
            
            // Email Settings
            'mail_from_address' => [
                'group' => 'email',
                'display_name' => 'From Address',
                'type' => 'email',
                'description' => 'Default from email address',
                'order' => 1,
            ],
            'mail_from_name' => [
                'group' => 'email',
                'display_name' => 'From Name',
                'type' => 'text',
                'description' => 'Default from name',
                'order' => 2,
            ],
            'mail_reply_to' => [
                'group' => 'email',
                'display_name' => 'Reply-To Address',
                'type' => 'email',
                'description' => 'Reply-to email address',
                'order' => 3,
            ],
            'email_notifications_enabled' => [
                'group' => 'email',
                'display_name' => 'Notifications Enabled',
                'type' => 'boolean',
                'description' => 'Enable email notifications',
                'order' => 4,
            ],
            'email_bcc_admin' => [
                'group' => 'email',
                'display_name' => 'BCC Admin',
                'type' => 'boolean',
                'description' => 'BCC admin on all emails',
                'order' => 5,
            ],
            
            // System Settings
            'app_timezone' => [
                'group' => 'system',
                'display_name' => 'Timezone',
                'type' => 'text',
                'description' => 'Application timezone',
                'order' => 1,
            ],
            'app_locale' => [
                'group' => 'system',
                'display_name' => 'Locale',
                'type' => 'text',
                'description' => 'Application locale',
                'order' => 2,
            ],
            'currency' => [
                'group' => 'system',
                'display_name' => 'Currency',
                'type' => 'text',
                'description' => 'Default currency',
                'order' => 3,
            ],
            'maintenance_mode' => [
                'group' => 'system',
                'display_name' => 'Maintenance Mode',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode',
                'order' => 4,
            ],
        ];

        return $configs[$key] ?? [
            'group' => 'general',
            'display_name' => ucwords(str_replace('_', ' ', $key)),
            'type' => 'text',
            'description' => '',
            'order' => 999,
        ];
    }
}