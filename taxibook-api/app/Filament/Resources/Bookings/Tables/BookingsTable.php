<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Services\StripeService;
use App\Services\TipService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
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
                TextColumn::make('gratuity_amount')
                    ->label('Tip')
                    ->money('USD')
                    ->sortable()
                    ->default('-')
                    ->color(fn ($record) => $record->gratuity_amount > 0 ? 'success' : 'gray'),
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
                    
                    // Tip Management Actions
                    Action::make('captureWithTip')
                        ->label('Capture with Tip')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->visible(fn ($record) => in_array($record->status, ['confirmed', 'completed']) && $record->payment_status === 'captured' && !$record->hasTipped() && $record->hasSavedPaymentMethod())
                        ->form([
                            Select::make('tip_percentage')
                                ->label('Gratuity')
                                ->options([
                                    '0' => 'No tip',
                                    '15' => '15%',
                                    '20' => '20%',
                                    '25' => '25%',
                                    '30' => '30%',
                                    'custom' => 'Custom amount',
                                ])
                                ->default('20')
                                ->reactive()
                                ->required(),
                            TextInput::make('custom_tip')
                                ->label('Custom Tip Amount')
                                ->numeric()
                                ->prefix('$')
                                ->visible(fn ($get) => $get('tip_percentage') === 'custom')
                                ->required(fn ($get) => $get('tip_percentage') === 'custom'),
                        ])
                        ->action(function ($record, array $data) {
                            try {
                                $tipAmount = 0;
                                if ($data['tip_percentage'] === 'custom') {
                                    $tipAmount = $data['custom_tip'];
                                } elseif ($data['tip_percentage'] !== '0') {
                                    $tipAmount = $record->final_fare * ($data['tip_percentage'] / 100);
                                }
                                
                                if ($tipAmount > 0) {
                                    $stripeService = app(StripeService::class);
                                    $result = $stripeService->chargeTip($record, $tipAmount);
                                    
                                    if ($result['success']) {
                                        Notification::make()
                                            ->title('Tip Added')
                                            ->body('Tip of $' . number_format($tipAmount, 2) . ' has been charged successfully.')
                                            ->success()
                                            ->send();
                                    } else {
                                        throw new \Exception($result['error']);
                                    }
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to Add Tip')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->modalHeading('Add Gratuity')
                        ->modalDescription(fn ($record) => 'Add tip for booking ' . $record->booking_number . ' (Fare: $' . number_format($record->final_fare, 2) . ')'),
                    
                    Action::make('sendTipLink')
                        ->label('Send Tip Link')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->visible(fn ($record) => in_array($record->status, ['confirmed', 'completed']) && $record->payment_status === 'captured' && !$record->hasTipped())
                        ->action(function ($record) {
                            try {
                                $tipService = app(TipService::class);
                                $result = $tipService->sendTipLink($record);
                                
                                if ($result['success']) {
                                    Notification::make()
                                        ->title('Tip Link Sent')
                                        ->body('Tip link has been sent to ' . $record->customer_email)
                                        ->success()
                                        ->send();
                                } else {
                                    throw new \Exception($result['message']);
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Failed to Send Tip Link')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Send Tip Link')
                        ->modalDescription(fn ($record) => 'Send optional tip link to ' . $record->customer_email),
                    
                    Action::make('showTipQr')
                        ->label('Show Tip QR')
                        ->icon('heroicon-o-qr-code')
                        ->color('gray')
                        ->visible(fn ($record) => in_array($record->status, ['confirmed', 'completed']) && $record->payment_status === 'captured' && !$record->hasTipped())
                        ->modalContent(function ($record) {
                            $tipService = app(TipService::class);
                            $result = $tipService->getQrCode($record);
                            
                            return view('filament.resources.bookings.tip-qr-modal', [
                                'qrCode' => $result['qr_code'],
                                'url' => $result['url'],
                                'bookingNumber' => $record->booking_number,
                            ]);
                        })
                        ->modalHeading('Tip Payment QR Code')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close'),
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
