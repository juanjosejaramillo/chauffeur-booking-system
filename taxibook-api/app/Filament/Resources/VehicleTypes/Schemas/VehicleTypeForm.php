<?php

namespace App\Filament\Resources\VehicleTypes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VehicleTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('display_name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('max_passengers')
                    ->required()
                    ->numeric(),
                TextInput::make('max_luggage')
                    ->required()
                    ->numeric(),
                TextInput::make('base_fare')
                    ->required()
                    ->numeric(),
                TextInput::make('base_miles_included')
                    ->required()
                    ->numeric(),
                TextInput::make('per_minute_rate')
                    ->required()
                    ->numeric(),
                TextInput::make('minimum_fare')
                    ->required()
                    ->numeric(),
                TextInput::make('service_fee_multiplier')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('tax_rate')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('tax_enabled')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('features')
                    ->columnSpanFull(),
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
            ]);
    }
}
