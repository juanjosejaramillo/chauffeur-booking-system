<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Upcoming Bookings - Next 7 Days';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->whereBetween('pickup_date', [
                        Carbon::now(),
                        Carbon::now()->addDays(7)
                    ])
                    ->orderBy('pickup_date', 'asc')
            )
            ->columns([
                TextColumn::make('booking_number')
                    ->label('Booking #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->color('primary'),
                    
                TextColumn::make('pickup_date')
                    ->label('Pickup Date & Time')
                    ->dateTime('M j, g:i A')
                    ->sortable()
                    ->description(fn ($record) => $record->pickup_date->diffForHumans())
                    ->color(fn ($record) => 
                        $record->pickup_date->isToday() ? 'danger' :
                        ($record->pickup_date->isTomorrow() ? 'warning' : 'gray')
                    ),
                    
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->customer_first_name . ' ' . $record->customer_last_name)
                    ->searchable(['customer_first_name', 'customer_last_name'])
                    ->description(fn ($record) => $record->customer_phone),
                    
                TextColumn::make('pickup_address')
                    ->label('Pickup')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->pickup_address),
                    
                TextColumn::make('dropoff_address')
                    ->label('Destination')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->dropoff_address),
                    
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                    
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'authorized' => 'info',
                        'captured' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                    
                TextColumn::make('estimated_fare')
                    ->label('Fare')
                    ->money('USD')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => "/admin/bookings/{$record->id}/edit")
                    ->openUrlInNewTab(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No upcoming bookings')
            ->emptyStateDescription('There are no bookings scheduled for the next 7 days.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}