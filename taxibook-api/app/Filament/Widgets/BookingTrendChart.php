<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasDateRangeFilter;
use App\Models\Booking;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class BookingTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use HasDateRangeFilter;

    protected ?string $heading = 'Booking Trends';

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
        $allCounts = [];
        $completedCounts = [];

        if ($grouping === 'hour') {
            $labels = collect(range(0, 23))->map(fn ($h) => sprintf('%02d:00', $h))->toArray();
            $allCounts = array_fill(0, 24, 0);
            $completedCounts = array_fill(0, 24, 0);

            $hourExpr = $driver === 'sqlite'
                ? DB::raw("cast(strftime('%H', pickup_date) as integer) as period")
                : DB::raw('HOUR(pickup_date) as period');

            $allRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->select($hourExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            $completedRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('status', 'completed')
                ->select($hourExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            foreach ($allRows as $hour => $count) {
                $allCounts[$hour] = $count;
            }
            foreach ($completedRows as $hour => $count) {
                $completedCounts[$hour] = $count;
            }
        } elseif ($grouping === 'day') {
            $period = CarbonPeriod::create($start, $end);
            foreach ($period as $date) {
                $labels[] = $date->format('M j');
            }
            $allCounts = array_fill(0, count($labels), 0);
            $completedCounts = array_fill(0, count($labels), 0);

            $dayExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%m-%d', pickup_date) as period")
                : DB::raw('DATE(pickup_date) as period');

            $allRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->select($dayExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            $completedRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('status', 'completed')
                ->select($dayExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            $dateMap = [];
            foreach (CarbonPeriod::create($start, $end) as $i => $date) {
                $dateMap[$date->format('Y-m-d')] = $i;
            }

            foreach ($allRows as $date => $count) {
                if (isset($dateMap[$date])) {
                    $allCounts[$dateMap[$date]] = $count;
                }
            }
            foreach ($completedRows as $date => $count) {
                if (isset($dateMap[$date])) {
                    $completedCounts[$dateMap[$date]] = $count;
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
            $allCounts = array_fill(0, count($labels), 0);
            $completedCounts = array_fill(0, count($labels), 0);

            $weekExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%W', pickup_date) as period")
                : DB::raw("DATE_FORMAT(pickup_date, '%x-%v') as period");

            $allRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->select($weekExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            $completedRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('status', 'completed')
                ->select($weekExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            foreach ($allRows as $week => $count) {
                if (isset($weekMap[$week])) {
                    $allCounts[$weekMap[$week]] = $count;
                }
            }
            foreach ($completedRows as $week => $count) {
                if (isset($weekMap[$week])) {
                    $completedCounts[$weekMap[$week]] = $count;
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
            $allCounts = array_fill(0, count($labels), 0);
            $completedCounts = array_fill(0, count($labels), 0);

            $monthExpr = $driver === 'sqlite'
                ? DB::raw("strftime('%Y-%m', pickup_date) as period")
                : DB::raw("DATE_FORMAT(pickup_date, '%Y-%m') as period");

            $allRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->select($monthExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            $completedRows = Booking::whereBetween('pickup_date', [$start, $end])
                ->where('status', 'completed')
                ->select($monthExpr, DB::raw('COUNT(*) as count'))
                ->groupBy('period')
                ->pluck('count', 'period');

            foreach ($allRows as $month => $count) {
                if (isset($monthMap[$month])) {
                    $allCounts[$monthMap[$month]] = $count;
                }
            }
            foreach ($completedRows as $month => $count) {
                if (isset($monthMap[$month])) {
                    $completedCounts[$monthMap[$month]] = $count;
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'All Bookings',
                    'data' => array_values($allCounts),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Completed',
                    'data' => array_values($completedCounts),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
