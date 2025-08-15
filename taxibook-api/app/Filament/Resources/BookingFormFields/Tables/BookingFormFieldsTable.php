<?php

namespace App\Filament\Resources\BookingFormFields\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class BookingFormFieldsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'gray',
                        'number' => 'warning',
                        'select' => 'success',
                        'checkbox' => 'info',
                        default => 'secondary',
                    }),
                
                IconColumn::make('required')
                    ->boolean(),
                
                IconColumn::make('enabled')
                    ->boolean(),
                
                TextColumn::make('group')
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('conditions')
                    ->label('Has Conditions')
                    ->formatStateUsing(fn ($state) => !empty($state) ? 'Yes' : 'No')
                    ->badge()
                    ->color(fn ($state) => !empty($state) ? 'warning' : 'gray'),
                
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
                SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'number' => 'Number',
                        'select' => 'Dropdown',
                        'checkbox' => 'Checkbox',
                    ]),
                
                TernaryFilter::make('enabled'),
                TernaryFilter::make('required'),
                
                SelectFilter::make('group')
                    ->options([
                        'travel_details' => 'Travel Details',
                        'airport_services' => 'Airport Services',
                        'preferences' => 'Preferences',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }
}
