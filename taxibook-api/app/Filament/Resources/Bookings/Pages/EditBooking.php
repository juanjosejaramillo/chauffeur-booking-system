<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Services\StripeService;
use App\Events\BookingCancelled;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('capturePayment')
                ->label('Capture Payment')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->visible(fn () => $this->record->payment_status === 'authorized')
                ->form([
                    TextInput::make('amount')
                        ->label('Amount to Capture')
                        ->numeric()
                        ->prefix('$')
                        ->default($this->record->estimated_fare)
                        ->helperText('Authorized amount: $' . number_format($this->record->estimated_fare, 2))
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $stripeService = app(StripeService::class);
                        $stripeService->capturePayment($this->record, $data['amount']);
                        
                        // Update booking status to completed
                        $this->record->update(['status' => 'completed']);
                        
                        Notification::make()
                            ->title('Payment Captured')
                            ->body('Payment of $' . number_format($data['amount'], 2) . ' has been captured successfully.')
                            ->success()
                            ->send();
                            
                        $this->refreshFormData(['payment_status', 'status', 'final_fare']);
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
                ->modalDescription('Capture payment for booking ' . $this->record->booking_number),
            
            Action::make('cancelPayment')
                ->label('Cancel Authorization')
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->visible(fn () => in_array($this->record->payment_status, ['pending', 'authorized']))
                ->action(function () {
                    try {
                        $stripeService = app(StripeService::class);
                        $stripeService->cancelPaymentIntent($this->record);
                        
                        // Update booking status to cancelled
                        $this->record->update(['status' => 'cancelled']);
                        
                        // Trigger booking cancelled event
                        event(new BookingCancelled($this->record->fresh(), 'Payment authorization cancelled by admin'));
                        
                        Notification::make()
                            ->title('Payment Cancelled')
                            ->body('Payment authorization has been cancelled.')
                            ->success()
                            ->send();
                            
                        $this->refreshFormData(['payment_status', 'status']);
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
                ->modalDescription('This will cancel the payment authorization for booking ' . $this->record->booking_number),
            
            Action::make('refundPayment')
                ->label('Refund Payment')
                ->icon('heroicon-o-receipt-refund')
                ->color('danger')
                ->visible(fn () => $this->record->payment_status === 'captured')
                ->form([
                    TextInput::make('amount')
                        ->label('Refund Amount')
                        ->numeric()
                        ->prefix('$')
                        ->default($this->record->final_fare ?? $this->record->estimated_fare)
                        ->helperText('Captured amount: $' . number_format($this->record->final_fare ?? $this->record->estimated_fare, 2))
                        ->required(),
                    Textarea::make('reason')
                        ->label('Refund Reason')
                        ->rows(3)
                        ->maxLength(500),
                ])
                ->action(function (array $data) {
                    try {
                        $stripeService = app(StripeService::class);
                        $stripeService->refundPayment(
                            $this->record, 
                            $data['amount'],
                            $data['reason'] ?? null
                        );
                        
                        // Update booking status to cancelled if full refund
                        if ($data['amount'] >= ($this->record->final_fare ?? $this->record->estimated_fare)) {
                            $this->record->update(['status' => 'cancelled']);
                            // Trigger booking cancelled event
                            event(new BookingCancelled($this->record->fresh(), 'Full refund processed'));
                        }
                        
                        Notification::make()
                            ->title('Payment Refunded')
                            ->body('Refund of $' . number_format($data['amount'], 2) . ' has been processed.')
                            ->success()
                            ->send();
                            
                        $this->refreshFormData(['payment_status', 'status']);
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
                ->modalDescription('Process refund for booking ' . $this->record->booking_number),
                
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
