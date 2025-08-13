<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class TodaysBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Today\'s Bookings - Immediate Attention';
    
    protected static string $color = 'danger';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->whereDate('pickup_date', Carbon::today())
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->orderBy('pickup_date', 'asc')
            )
            ->columns([
                TextColumn::make('pickup_date')
                    ->label('Time')
                    ->dateTime('g:i A')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => 
                        $record->pickup_date->isPast() ? 'danger' :
                        ($record->pickup_date->diffInHours(now()) < 2 ? 'warning' : 'success')
                    )
                    ->description(fn ($record) => 
                        $record->pickup_date->isPast() ? 'OVERDUE' : 
                        $record->pickup_date->diffForHumans()
                    ),
                    
                TextColumn::make('booking_number')
                    ->label('Booking #')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                    
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->customer_first_name . ' ' . $record->customer_last_name)
                    ->description(fn ($record) => $record->customer_phone)
                    ->searchable(['customer_first_name', 'customer_last_name']),
                    
                TextColumn::make('pickup_address')
                    ->label('From')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->pickup_address),
                    
                TextColumn::make('dropoff_address')
                    ->label('To')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->dropoff_address),
                    
                TextColumn::make('vehicleType.name')
                    ->label('Vehicle')
                    ->badge(),
                    
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        default => 'gray',
                    }),
                    
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'authorized' => 'success',
                        default => 'gray',
                    }),
                    
                TextColumn::make('special_instructions')
                    ->label('Notes')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->tooltip(fn ($record) => $record->special_instructions),
            ])
            ->actions([
                Action::make('view')
                    ->label('Manage')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->url(fn ($record) => "/admin/bookings/{$record->id}/edit"),
            ])
            ->paginated(false)
            ->emptyStateHeading('No bookings for today')
            ->emptyStateDescription('There are no bookings scheduled for today.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
    
    public static function canView(): bool
    {
        // Only show if there are bookings today
        return Booking::whereDate('pickup_date', Carbon::today())
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();
    }
}