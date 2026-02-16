<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('period')
                    ->label('Period')
                    ->options([
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'this_week' => 'This Week',
                        'last_week' => 'Last Week',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year',
                        'custom' => 'Custom Range',
                    ])
                    ->default('today')
                    ->live(),
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->visible(fn (Get $get): bool => $get('period') === 'custom')
                    ->default(now()->startOfMonth()->format('Y-m-d')),
                DatePicker::make('end_date')
                    ->label('End Date')
                    ->visible(fn (Get $get): bool => $get('period') === 'custom')
                    ->default(now()->format('Y-m-d')),
            ])
            ->columns(3);
    }
}
