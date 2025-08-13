<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $todayBookings = Booking::whereDate('created_at', $today)->count();
        
        $todayRevenue = Booking::whereDate('created_at', $today)
            ->where('payment_status', 'captured')
            ->sum('final_fare');
            
        $upcomingTrips = Booking::where('pickup_date', '>', now())
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();
            
        $pendingBookings = Booking::where('status', 'pending')->count();
        
        return [
            Stat::make('Today\'s Bookings', $todayBookings)
                ->description('New bookings today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Today\'s Revenue', '$' . number_format($todayRevenue, 2))
                ->description('Captured payments')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
                
            Stat::make('Upcoming Trips', $upcomingTrips)
                ->description('Future bookings')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
                
            Stat::make('Pending Bookings', $pendingBookings)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}