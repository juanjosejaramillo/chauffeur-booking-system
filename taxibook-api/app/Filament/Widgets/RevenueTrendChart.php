<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasDateRangeFilter;
use App\Models\Booking;
use App\Models\BookingExpense;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class RevenueTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use HasDateRangeFilter;

    protected ?string $heading = 'Revenue Trend';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        [$start, $end] = $this->getDateRange();
        $grouping = $this->getGroupingFormat();
        $driver = DB::getDriverName();

        $labels = [];
        $fareData = [];
        $tipData = [];
        $expenseData = [];

        if ($grouping === 'hour') {
            $labels = collect(range(0, 23))->map(fn ($h) => sprintf('%02d:00', $h))->toArray();
            $fareData = array_fill(0, 24, 0);
            $tipData = array_fill(0, 24, 0);
            $expenseData = array_fill(0, 24, 0);

            $periodExpr = $driver === 'sqlite'
                ? DB::raw("cast(strftime('%H', pickup_date) as integer) as period")
                : DB::raw('HOUR(pickup_date) as period');

            $fareRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('payment_status', 'captured')
                ->select($periodExpr, DB::raw('COALESCE(SUM(final_fare), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $tipRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('gratuity_amount', '>', 0)
                ->select($periodExpr, DB::raw('COALESCE(SUM(gratuity_amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $expensePeriodExpr = $driver === 'sqlite'
                ? DB::raw("cast(strftime('%H', bookings.pickup_date) as integer) as period")
                : DB::raw('HOUR(bookings.pickup_date) as period');

            $expenseRows = BookingExpense::join('bookings', 'booking_expenses.booking_id', '=', 'bookings.id')
                ->whereBetween('bookings.pickup_date', [$start, $end])
                ->select($expensePeriodExpr, DB::raw('COALESCE(SUM(booking_expenses.amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            foreach ($fareRows as $hour => $total) {
                $fareData[$hour] = round((float) $total, 2);
            }
            foreach ($tipRows as $hour => $total) {
                $tipData[$hour] = round((float) $total, 2);
            }
            foreach ($expenseRows as $hour => $total) {
                $expenseData[$hour] = round((float) $total, 2);
            }
        } elseif ($grouping === 'day') {
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $labels[] = $date->format('M j');
            }
            $fareData = array_fill(0, count($labels), 0);
            $tipData = array_fill(0, count($labels), 0);
            $expenseData = array_fill(0, count($labels), 0);

            $periodExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%m-%d', pickup_date) as period")
                : DB::raw('DATE(pickup_date) as period');

            $fareRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('payment_status', 'captured')
                ->select($periodExpr, DB::raw('COALESCE(SUM(final_fare), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $tipRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('gratuity_amount', '>', 0)
                ->select($periodExpr, DB::raw('COALESCE(SUM(gratuity_amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $expensePeriodExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%m-%d', bookings.pickup_date) as period")
                : DB::raw('DATE(bookings.pickup_date) as period');

            $expenseRows = BookingExpense::join('bookings', 'booking_expenses.booking_id', '=', 'bookings.id')
                ->whereBetween('bookings.pickup_date', [$start, $end])
                ->select($expensePeriodExpr, DB::raw('COALESCE(SUM(booking_expenses.amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $dateMap = [];
            foreach (CarbonPeriod::create($start, $end) as $i => $date) {
                $dateMap[$date->format('Y-m-d')] = $i;
            }

            foreach ($fareRows as $date => $total) {
                if (isset($dateMap[$date])) {
                    $fareData[$dateMap[$date]] = round((float) $total, 2);
                }
            }
            foreach ($tipRows as $date => $total) {
                if (isset($dateMap[$date])) {
                    $tipData[$dateMap[$date]] = round((float) $total, 2);
                }
            }
            foreach ($expenseRows as $date => $total) {
                if (isset($dateMap[$date])) {
                    $expenseData[$dateMap[$date]] = round((float) $total, 2);
                }
            }
        } elseif ($grouping === 'week') {
            $current = $start->copy()->startOfWeek();
            $weekMap = [];
            $i = 0;
            while ($current->lte($end)) {
                $weekEnd = $current->copy()->endOfWeek()->min($end);
                $labels[] = $current->format('M j') . '–' . $weekEnd->format('j');
                $weekMap[$current->format('Y-W')] = $i;
                $current->addWeek();
                $i++;
            }
            $fareData = array_fill(0, count($labels), 0);
            $tipData = array_fill(0, count($labels), 0);
            $expenseData = array_fill(0, count($labels), 0);

            $periodExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%W', pickup_date) as period")
                : DB::raw("DATE_FORMAT(pickup_date, '%x-%v') as period");

            $fareRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('payment_status', 'captured')
                ->select($periodExpr, DB::raw('COALESCE(SUM(final_fare), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $tipRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('gratuity_amount', '>', 0)
                ->select($periodExpr, DB::raw('COALESCE(SUM(gratuity_amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $expensePeriodExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%W', bookings.pickup_date) as period")
                : DB::raw("DATE_FORMAT(bookings.pickup_date, '%x-%v') as period");

            $expenseRows = BookingExpense::join('bookings', 'booking_expenses.booking_id', '=', 'bookings.id')
                ->whereBetween('bookings.pickup_date', [$start, $end])
                ->select($expensePeriodExpr, DB::raw('COALESCE(SUM(booking_expenses.amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            foreach ($fareRows as $week => $total) {
                if (isset($weekMap[$week])) {
                    $fareData[$weekMap[$week]] = round((float) $total, 2);
                }
            }
            foreach ($tipRows as $week => $total) {
                if (isset($weekMap[$week])) {
                    $tipData[$weekMap[$week]] = round((float) $total, 2);
                }
            }
            foreach ($expenseRows as $week => $total) {
                if (isset($weekMap[$week])) {
                    $expenseData[$weekMap[$week]] = round((float) $total, 2);
                }
            }
        } else {
            // monthly
            $current = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->endOfMonth();
            $monthMap = [];
            $i = 0;
            while ($current->lte($endMonth)) {
                $labels[] = $current->format('M Y');
                $monthMap[$current->format('Y-m')] = $i;
                $current->addMonth();
                $i++;
            }
            $fareData = array_fill(0, count($labels), 0);
            $tipData = array_fill(0, count($labels), 0);
            $expenseData = array_fill(0, count($labels), 0);

            $periodExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%m', pickup_date) as period")
                : DB::raw("DATE_FORMAT(pickup_date, '%Y-%m') as period");

            $fareRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('payment_status', 'captured')
                ->select($periodExpr, DB::raw('COALESCE(SUM(final_fare), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $tipRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('gratuity_amount', '>', 0)
                ->select($periodExpr, DB::raw('COALESCE(SUM(gratuity_amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            $expensePeriodExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%m', bookings.pickup_date) as period")
                : DB::raw("DATE_FORMAT(bookings.pickup_date, '%Y-%m') as period");

            $expenseRows = BookingExpense::join('bookings', 'booking_expenses.booking_id', '=', 'bookings.id')
                ->whereBetween('bookings.pickup_date', [$start, $end])
                ->select($expensePeriodExpr, DB::raw('COALESCE(SUM(booking_expenses.amount), 0) as total'))
                ->groupBy('period')
                ->pluck('total', 'period');

            foreach ($fareRows as $month => $total) {
                if (isset($monthMap[$month])) {
                    $fareData[$monthMap[$month]] = round((float) $total, 2);
                }
            }
            foreach ($tipRows as $month => $total) {
                if (isset($monthMap[$month])) {
                    $tipData[$monthMap[$month]] = round((float) $total, 2);
                }
            }
            foreach ($expenseRows as $month => $total) {
                if (isset($monthMap[$month])) {
                    $expenseData[$monthMap[$month]] = round((float) $total, 2);
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Fares',
                    'data' => array_values($fareData),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Tips',
                    'data' => array_values($tipData),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Expenses',
                    'data' => array_values($expenseData),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderDash' => [5, 5],
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
