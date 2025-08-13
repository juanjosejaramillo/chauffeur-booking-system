<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $thisWeek = Carbon::now()->endOfWeek();
        
        return [
            Stat::make('Today\'s Bookings', Booking::whereDate('pickup_date', $today)
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->count())
                ->description('Scheduled for today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('danger')
                ->chart([7, 3, 4, 5, 6, 8, 6]),
                
            Stat::make('Tomorrow\'s Bookings', Booking::whereDate('pickup_date', $tomorrow)
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->count())
                ->description('Scheduled for tomorrow')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
                
            Stat::make('This Week', Booking::whereBetween('pickup_date', [$today, $thisWeek])
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->count())
                ->description('Total bookings this week')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
                
            Stat::make('Revenue Today', '$' . number_format(
                    Booking::whereDate('pickup_date', $today)
                        ->where('payment_status', 'captured')
                        ->sum('final_fare') ?: 
                    Booking::whereDate('pickup_date', $today)
                        ->where('payment_status', 'authorized')
                        ->sum('estimated_fare'), 2))
                ->description('Expected revenue')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}