<?php

namespace App\Filament\Resources\VehicleTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('max_passengers')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_luggage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('base_fare')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('base_miles_included')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('per_minute_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('minimum_fare')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('service_fee_multiplier')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_rate')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('tax_enabled')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image_url'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
