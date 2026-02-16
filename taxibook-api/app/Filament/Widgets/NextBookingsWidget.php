<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class NextBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    protected function getTableHeading(): string
    {
        $count = Booking::where('pickup_date', '>=', Carbon::now())
            ->where('pickup_date', '<=', Carbon::now()->addHours(24))
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        return "Next Up — {$count} booking" . ($count !== 1 ? 's' : '') . ' in the next 24 hours';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->where('pickup_date', '>=', Carbon::now())
                    ->where('pickup_date', '<=', Carbon::now()->addHours(48))
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->orderBy('pickup_date', 'asc')
            )
            ->columns([
                TextColumn::make('time_until')
                    ->label('Time Until Pickup')
                    ->getStateUsing(function ($record) {
                        $diff = Carbon::now()->diff($record->pickup_date);
                        if ($diff->days > 0) {
                            return $diff->days . 'd ' . $diff->h . 'h';
                        }
                        if ($diff->h > 0) {
                            return $diff->h . 'h ' . $diff->i . 'm';
                        }
                        return $diff->i . ' min';
                    })
                    ->badge()
                    ->color(function ($record) {
                        $hours = Carbon::now()->diffInHours($record->pickup_date, false);
                        if ($hours <= 2) {
                            return 'danger';
                        }
                        if ($hours <= 6) {
                            return 'warning';
                        }
                        return 'gray';
                    }),

                TextColumn::make('pickup_date')
                    ->label('Pickup Time')
                    ->dateTime('M j, g:i A')
                    ->sortable(),

                TextColumn::make('booking_number')
                    ->label('Booking #')
                    ->weight('bold')
                    ->copyable()
                    ->color('primary'),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->customer_first_name . ' ' . $record->customer_last_name)
                    ->description(fn ($record) => $record->customer_phone),

                TextColumn::make('pickup_address')
                    ->label('Pickup')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->pickup_address),

                TextColumn::make('dropoff_address')
                    ->label('Destination')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->dropoff_address),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'in_progress' => 'primary',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => "/admin/bookings/{$record->id}/edit")
                    ->openUrlInNewTab(),
            ])
            ->paginated(false)
            ->emptyStateHeading('No upcoming bookings')
            ->emptyStateDescription('There are no bookings in the next 48 hours. You\'re all clear!')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
