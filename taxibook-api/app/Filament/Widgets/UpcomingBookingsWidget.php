<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasDateRangeFilter;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingBookingsWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    use HasDateRangeFilter;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return $this->getPeriodLabel() . ' Bookings';
    }

    public function table(Table $table): Table
    {
        [$start, $end] = $this->getDateRange();

        return $table
            ->query(
                Booking::query()
                    ->whereBetween('pickup_date', [$start, $end])
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
                        'in_progress' => 'primary',
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
            ->emptyStateHeading('No bookings found')
            ->emptyStateDescription('There are no bookings in the selected period.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
