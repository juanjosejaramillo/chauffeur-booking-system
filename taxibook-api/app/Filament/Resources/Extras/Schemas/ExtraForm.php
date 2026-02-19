<?php

namespace App\Filament\Resources\Extras\Schemas;

use App\Models\VehicleType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExtraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Extra Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        TextInput::make('max_quantity')
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(20),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Section::make('Vehicle Availability')
                    ->schema([
                        Toggle::make('apply_to_all_vehicles')
                            ->label('Apply to all vehicles')
                            ->default(true)
                            ->live(),
                        CheckboxList::make('vehicleTypes')
                            ->relationship('vehicleTypes', 'display_name')
                            ->visible(fn ($get) => !$get('apply_to_all_vehicles'))
                            ->columns(2),
                    ]),
            ]);
    }
}
