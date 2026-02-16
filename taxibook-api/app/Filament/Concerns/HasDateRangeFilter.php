<?php

namespace App\Filament\Concerns;

use Carbon\Carbon;

trait HasDateRangeFilter
{
    protected function getDateRange(): array
    {
        $period = $this->filters['period'] ?? 'today';
        $customStart = $this->filters['start_date'] ?? null;
        $customEnd = $this->filters['end_date'] ?? null;

        return match ($period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'this_year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'custom' => [
                $customStart ? Carbon::parse($customStart)->startOfDay() : Carbon::today(),
                $customEnd ? Carbon::parse($customEnd)->endOfDay() : Carbon::today()->endOfDay(),
            ],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };
    }

    protected function getPeriodLabel(): string
    {
        $period = $this->filters['period'] ?? 'today';

        return match ($period) {
            'today' => "Today's",
            'yesterday' => "Yesterday's",
            'this_week' => "This Week's",
            'last_week' => "Last Week's",
            'this_month' => "This Month's",
            'last_month' => "Last Month's",
            'this_year' => "This Year's",
            'custom' => 'Custom Range',
            default => "Today's",
        };
    }

    protected function getGroupingFormat(): string
    {
        $period = $this->filters['period'] ?? 'today';

        return match ($period) {
            'today', 'yesterday' => 'hour',
            'this_week', 'last_week' => 'day',
            'this_month', 'last_month' => 'week',
            'this_year' => 'month',
            'custom' => $this->guessCustomGrouping(),
            default => 'hour',
        };
    }

    private function guessCustomGrouping(): string
    {
        [$start, $end] = $this->getDateRange();
        $days = $start->diffInDays($end);

        if ($days <= 2) {
            return 'hour';
        }

        if ($days <= 14) {
            return 'day';
        }

        if ($days <= 90) {
            return 'week';
        }

        return 'month';
    }
}
