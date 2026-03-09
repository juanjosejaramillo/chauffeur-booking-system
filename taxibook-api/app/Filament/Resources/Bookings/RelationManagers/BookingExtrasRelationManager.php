<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use App\Models\Extra;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookingExtrasRelationManager extends RelationManager
{
    protected static string $relationship = 'bookingExtras';

    protected static ?string $title = 'Extras';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('extra_id')
                    ->label('Select Extra')
                    ->options(Extra::active()->ordered()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $extra = Extra::find($state);
                            if ($extra) {
                                $set('name', $extra->name);
                                $set('unit_price', $extra->price);
                                $quantity = 1;
                                $set('quantity', $quantity);
                                $set('total_price', number_format($extra->price * $quantity, 2, '.', ''));
                            }
                        }
                    })
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->step(0.01)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $quantity = $get('quantity') ?: 1;
                        $set('total_price', number_format(floatval($state) * intval($quantity), 2, '.', ''));
                    }),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->step(1)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $unitPrice = $get('unit_price') ?: 0;
                        $set('total_price', number_format(floatval($unitPrice) * intval($state), 2, '.', ''));
                    }),
                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('unit_price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->money('USD')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Extra')
                    ->after(fn () => $this->recalculateExtrasTotal()),
            ])
            ->actions([
                EditAction::make()
                    ->after(fn () => $this->recalculateExtrasTotal()),
                DeleteAction::make()
                    ->after(fn () => $this->recalculateExtrasTotal()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn () => $this->recalculateExtrasTotal()),
                ]),
            ]);
    }

    protected function recalculateExtrasTotal(): void
    {
        $booking = $this->getOwnerRecord();
        $total = $booking->bookingExtras()->sum('total_price');

        $updates = ['extras_total' => $total];

        // Recalculate tax when extras change
        if ($booking->vehicleType) {
            $fareSubtotal = $booking->final_fare ?? $booking->estimated_fare;
            $updates['tax_amount'] = $booking->vehicleType->calculateTax((float) $fareSubtotal, (float) $total);
        }

        $booking->update($updates);
    }
}
