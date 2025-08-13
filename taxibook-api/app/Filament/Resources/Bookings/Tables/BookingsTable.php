<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vehicle_type_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('customer_first_name')
                    ->searchable(),
                TextColumn::make('customer_last_name')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->searchable(),
                TextColumn::make('pickup_address')
                    ->searchable(),
                TextColumn::make('pickup_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pickup_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dropoff_address')
                    ->searchable(),
                TextColumn::make('dropoff_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dropoff_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pickup_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('estimated_distance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estimated_duration')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('route_polyline')
                    ->searchable(),
                TextColumn::make('estimated_fare')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('final_fare')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->searchable(),
                TextColumn::make('stripe_payment_intent_id')
                    ->searchable(),
                TextColumn::make('stripe_payment_method_id')
                    ->searchable(),
                TextColumn::make('cancellation_reason')
                    ->searchable(),
                TextColumn::make('cancelled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
