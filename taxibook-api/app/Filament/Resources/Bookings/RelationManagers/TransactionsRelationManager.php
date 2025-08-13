<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Payment History';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('type')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date/Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Transaction Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'authorization' => 'info',
                        'capture' => 'success',
                        'void' => 'warning',
                        'refund', 'partial_refund' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'authorization' => 'Authorization',
                        'capture' => 'Payment Captured',
                        'void' => 'Cancelled',
                        'refund' => 'Full Refund',
                        'partial_refund' => 'Partial Refund',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'succeeded' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('stripe_transaction_id')
                    ->label('Stripe ID')
                    ->copyable()
                    ->limit(20)
                    ->tooltip(fn ($state) => $state),
                Tables\Columns\TextColumn::make('processed_by')
                    ->label('Processed By')
                    ->default('System'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes/Reason')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                \Filament\Actions\Action::make('viewStripe')
                    ->label('View in Stripe')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => $record->type === 'refund' 
                        ? "https://dashboard.stripe.com/refunds/{$record->stripe_transaction_id}"
                        : "https://dashboard.stripe.com/payments/{$record->stripe_transaction_id}")
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //
            ]);
    }
}