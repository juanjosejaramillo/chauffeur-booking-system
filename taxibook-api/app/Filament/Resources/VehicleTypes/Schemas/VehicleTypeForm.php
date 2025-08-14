<?php

namespace App\Filament\Resources\VehicleTypes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('display_name')
                            ->label('Display Name')
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        TextInput::make('max_passengers')
                            ->label('Maximum Passengers')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('max_luggage')
                            ->label('Maximum Luggage')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Textarea::make('features')
                            ->label('Features (comma separated)')
                            ->helperText('Enter features separated by commas: e.g., AC, WiFi, Leather Seats')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Base Pricing')
                    ->description('Set the base fare and included miles')
                    ->schema([
                        TextInput::make('base_fare')
                            ->label('Base Fare ($)')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->helperText('The starting price for this vehicle'),
                        TextInput::make('base_miles_included')
                            ->label('Miles Included in Base Fare')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Number of miles covered by the base fare'),
                        TextInput::make('minimum_fare')
                            ->label('Minimum Fare ($)')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->helperText('The minimum charge regardless of distance'),
                    ])
                    ->columns(3),

                Section::make('Distance Pricing Tiers')
                    ->description('Define pricing for different distance ranges. Tiers should start after the base miles included.')
                    ->schema([
                        Repeater::make('pricingTiers')
                            ->relationship()
                            ->schema([
                                TextInput::make('from_mile')
                                    ->label('From Mile')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->helperText('Start of this tier'),
                                TextInput::make('to_mile')
                                    ->label('To Mile')
                                    ->numeric()
                                    ->nullable()
                                    ->minValue(fn ($get) => ($get('from_mile') ?? 0) + 1)
                                    ->helperText('End of tier (leave empty for unlimited)'),
                                TextInput::make('per_mile_rate')
                                    ->label('Price per Mile')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->minValue(0)
                                    ->helperText('Rate per mile in this tier'),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Pricing Tier')
                            ->reorderable(false)
                            ->grid(3)
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['from_mile']) 
                                    ? "Miles {$state['from_mile']}" . ($state['to_mile'] ? "-{$state['to_mile']}" : "+") . ": \${$state['per_mile_rate']}/mile"
                                    : null
                            )
                    ]),

                Section::make('Time-Based Pricing')
                    ->description('Set the per-minute rate for time-based charges')
                    ->schema([
                        TextInput::make('per_minute_rate')
                            ->label('Price per Minute ($)')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->helperText('Charge per minute of travel time'),
                    ]),

                Section::make('Taxes & Fees')
                    ->description('Configure optional taxes and fees')
                    ->schema([
                        Toggle::make('tax_enabled')
                            ->label('Enable Tax')
                            ->reactive()
                            ->helperText('Toggle to apply tax to the fare'),
                        TextInput::make('tax_rate')
                            ->label('Tax Rate (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->visible(fn ($get) => $get('tax_enabled'))
                            ->required(fn ($get) => $get('tax_enabled'))
                            ->helperText('Percentage to add as tax'),
                        TextInput::make('service_fee_multiplier')
                            ->label('Service Fee Multiplier')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('Multiplier for service fees (1 = no extra fee, 1.1 = 10% fee)')
                            ->visible(false), // Hidden for now as per requirements
                    ])
                    ->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Whether this vehicle type is available for booking'),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(2),

                Section::make('Image')
                    ->schema([
                        FileUpload::make('image_url')
                            ->image()
                            ->disk('public')
                            ->directory('vehicle-images')
                            ->visibility('public')
                            ->maxSize(10240) // 10MB max
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Maximum file size: 10MB. Accepted formats: JPEG, PNG, WebP')
                            ->validationMessages([
                                'maxSize' => 'The image must not be larger than 10MB.',
                                'acceptedFileTypes' => 'Only JPEG, PNG, and WebP images are accepted.',
                            ])
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null, // Free crop
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
