<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasDateRangeFilter;
use App\Models\Booking;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use HasDateRangeFilter;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        [$start, $end] = $this->getDateRange();
        $label = $this->getPeriodLabel();

        // Booking counts
        $totalBookings = Booking::whereBetween('pickup_date', [$start, $end])->count();
        $completedCount = Booking::whereBetween('pickup_date', [$start, $end])
            ->where('status', 'completed')->count();
        $cancelledCount = Booking::whereBetween('pickup_date', [$start, $end])
            ->where('status', 'cancelled')->count();
        $activeCount = $totalBookings - $cancelledCount;

        // Revenue - captured fares (completed payments)
        $capturedFares = (float) Booking::whereBetween('pickup_date', [$start, $end])
            ->where('payment_status', 'captured')
            ->sum('final_fare');

        // Authorized fares (card held, not yet captured)
        $authorizedFares = (float) Booking::whereBetween('pickup_date', [$start, $end])
            ->where('payment_status', 'authorized')
            ->sum('estimated_fare');

        // Gratuity (tips from customers)
        $gratuityTotal = (float) Booking::whereBetween('pickup_date', [$start, $end])
            ->sum('gratuity_amount');

        // Refunded amount
        $refundedTotal = (float) Booking::whereBetween('pickup_date', [$start, $end])
            ->sum('total_refunded');

        // Total revenue = captured fares + gratuity
        $totalRevenue = $capturedFares + $gratuityTotal;

        // Completion rate
        $nonCancelledTotal = $totalBookings - $cancelledCount;
        $completionRate = $nonCancelledTotal > 0
            ? round(($completedCount / $nonCancelledTotal) * 100)
            : 0;

        // Cancellation rate
        $cancellationRate = $totalBookings > 0
            ? round(($cancelledCount / $totalBookings) * 100)
            : 0;

        return [
            Stat::make("{$label} Bookings", $activeCount)
                ->description("{$completedCount} completed of {$totalBookings} total")
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('$' . number_format($capturedFares, 2) . ' fares + $' . number_format($gratuityTotal, 2) . ' tips')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Pending Revenue', '$' . number_format($authorizedFares, 2))
                ->description($refundedTotal > 0
                    ? 'Authorized, not captured · $' . number_format($refundedTotal, 2) . ' refunded'
                    : 'Authorized, awaiting capture')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($authorizedFares > 0 ? 'warning' : 'gray'),

            Stat::make('Cancelled', $cancelledCount)
                ->description("{$cancellationRate}% cancellation rate")
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($cancellationRate > 20 ? 'danger' : ($cancelledCount > 0 ? 'warning' : 'success')),

            Stat::make('Completion Rate', "{$completionRate}%")
                ->description("{$completedCount} of {$nonCancelledTotal} trips completed")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 50 ? 'warning' : 'danger')),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
