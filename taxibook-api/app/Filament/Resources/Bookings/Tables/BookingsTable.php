<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Services\StripeService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->label('Booking #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('pickup_date')
                    ->label('Pickup Date/Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->customer_first_name . ' ' . $record->customer_last_name)
                    ->searchable(['customer_first_name', 'customer_last_name'])
                    ->description(fn ($record) => $record->customer_phone),
                TextColumn::make('pickup_address')
                    ->label('Pickup')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->pickup_address),
                TextColumn::make('dropoff_address')
                    ->label('Destination')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->dropoff_address),
                TextColumn::make('vehicleType.name')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estimated_fare')
                    ->label('Fare')
                    ->money('USD')
                    ->sortable()
                    ->description(fn ($record) => $record->final_fare ? 'Final: $' . number_format($record->final_fare, 2) : null),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'authorized' => 'info',
                        'captured' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('special_instructions')
                    ->label('Instructions')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Booked At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('capturePayment')
                        ->label('Capture Payment')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->visible(fn ($record) => $record->payment_status === 'authorized')
                        ->form([
                            TextInput::make('amount')
                                ->label('Amount to Capture')
                                ->numeric()
                                ->prefix('$')
                                ->default(fn ($record) => $record->estimated_fare)
                                ->helperText(fn ($record) => 'Authorized amount: $' . number_format($record->estimated_fare, 2))
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                $stripeService = app(StripeService::class);
                                $stripeService->capturePayment($record, $data['amount']);
                                
                                // Update booking status to completed if payment is captured
                                $record->update(['status' => 'completed']);
                                
                                Notification::make()
                                    ->title('Payment Captured')
                                    ->body('Payment of $' . number_format($data['amount'], 2) . ' has been captured successfully.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Payment Capture Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Capture Payment')
                        ->modalDescription(fn ($record) => 'Capture payment for booking ' . $record->booking_number),
                    
                    Action::make('cancelPayment')
                        ->label('Cancel Authorization')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn ($record) => in_array($record->payment_status, ['pending', 'authorized']))
                        ->action(function ($record) {
                            try {
                                $stripeService = app(StripeService::class);
                                $stripeService->cancelPaymentIntent($record);
                                
                                // Update booking status to cancelled
                                $record->update(['status' => 'cancelled']);
                                
                                Notification::make()
                                    ->title('Payment Cancelled')
                                    ->body('Payment authorization has been cancelled.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Cancellation Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Payment Authorization')
                        ->modalDescription(fn ($record) => 'This will cancel the payment authorization for booking ' . $record->booking_number),
                    
                    Action::make('refundPayment')
                        ->label('Refund Payment')
                        ->icon('heroicon-o-receipt-refund')
                        ->color('danger')
                        ->visible(fn ($record) => $record->payment_status === 'captured')
                        ->form([
                            TextInput::make('amount')
                                ->label('Refund Amount')
                                ->numeric()
                                ->prefix('$')
                                ->default(fn ($record) => $record->final_fare ?? $record->estimated_fare)
                                ->helperText(fn ($record) => 'Captured amount: $' . number_format($record->final_fare ?? $record->estimated_fare, 2))
                                ->required(),
                            Textarea::make('reason')
                                ->label('Refund Reason')
                                ->rows(3)
                                ->maxLength(500),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                $stripeService = app(StripeService::class);
                                $stripeService->refundPayment(
                                    $record, 
                                    $data['amount'],
                                    $data['reason'] ?? null
                                );
                                
                                // Update booking status to cancelled if full refund
                                if ($data['amount'] >= ($record->final_fare ?? $record->estimated_fare)) {
                                    $record->update(['status' => 'cancelled']);
                                }
                                
                                Notification::make()
                                    ->title('Payment Refunded')
                                    ->body('Refund of $' . number_format($data['amount'], 2) . ' has been processed.')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Refund Failed')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Refund Payment')
                        ->modalDescription(fn ($record) => 'Process refund for booking ' . $record->booking_number),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
