<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Setting;
use App\Services\StripeService;
use App\Services\TipService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    /**
     * Validate status changes before saving to prevent double-charging
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $originalStatus = $this->record->getOriginal('status');
        $newStatus = $data['status'] ?? $this->record->status;
        $paymentStatus = $this->record->payment_status;
        $paymentMode = Setting::get('payment_mode', 'immediate');

        // Prevent changing status to completed if payment was already captured (prevent double-charge)
        if ($newStatus === 'completed' && $originalStatus !== 'completed') {
            // In post_service mode, only allow completion if payment is pending (will be charged)
            // or if payment was already captured (just status update, no new charge)
            if ($paymentMode === 'post_service' && $paymentStatus === 'captured') {
                // Payment already captured - this is safe, just updating status
            } elseif ($paymentMode === 'post_service' && $paymentStatus !== 'pending' && $paymentStatus !== 'captured') {
                // Payment status is not pending or captured (could be failed, cancelled, etc.)
                throw ValidationException::withMessages([
                    'status' => 'Cannot complete booking - payment status is "' . $paymentStatus . '". Please resolve payment issues first.',
                ]);
            }
        }

        // Prevent changing from completed back to pending if payment was captured (could cause re-charge on next completion)
        if ($newStatus === 'pending' && $originalStatus === 'completed' && $paymentStatus === 'captured') {
            throw ValidationException::withMessages([
                'status' => 'Cannot change a completed booking with captured payment back to pending. This could result in the customer being charged again if marked as completed later.',
            ]);
        }

        // Prevent changing from completed back to confirmed if payment was captured (similar protection)
        if ($newStatus === 'confirmed' && $originalStatus === 'completed' && $paymentStatus === 'captured') {
            throw ValidationException::withMessages([
                'status' => 'Cannot change a completed booking with captured payment back to confirmed. This could result in the customer being charged again if marked as completed later.',
            ]);
        }

        return $data;
    }

    
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
                ->visible(fn () => $this->record->payment_status === 'authorized')
                ->action(function () {
                    try {
                        $stripeService = app(StripeService::class);
                        $stripeService->cancelPaymentIntent($this->record);
                        
                        // Update booking status to cancelled with reason
                        $this->record->update([
                            'status' => 'cancelled',
                            'cancellation_reason' => 'Payment authorization cancelled by admin'
                        ]);
                        // Observer will automatically trigger BookingCancelled event
                        
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
                            $this->record->update([
                                'status' => 'cancelled',
                                'cancellation_reason' => 'Full refund processed: ' . ($data['reason'] ?? 'No reason provided')
                            ]);
                            // Observer will automatically trigger BookingCancelled event
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
            
            // Tip Management Actions
            Action::make('captureWithTip')
                ->label('Capture with Tip')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['confirmed', 'completed']) && $this->record->payment_status === 'captured' && !$this->record->hasTipped() && $this->record->hasSavedPaymentMethod())
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
                ->action(function (array $data) {
                    try {
                        $tipAmount = 0;
                        if ($data['tip_percentage'] === 'custom') {
                            $tipAmount = $data['custom_tip'];
                        } elseif ($data['tip_percentage'] !== '0') {
                            $tipAmount = $this->record->final_fare * ($data['tip_percentage'] / 100);
                        }
                        
                        if ($tipAmount > 0) {
                            $stripeService = app(StripeService::class);
                            $result = $stripeService->chargeTip($this->record, $tipAmount);
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title('Tip Added')
                                    ->body('Tip of $' . number_format($tipAmount, 2) . ' has been charged successfully.')
                                    ->success()
                                    ->send();
                                    
                                $this->refreshFormData(['gratuity_amount']);
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
                ->modalDescription('Add tip for booking ' . $this->record->booking_number . ' (Fare: $' . number_format($this->record->final_fare, 2) . ')'),
            
            Action::make('sendTipLink')
                ->label('Send Tip Link')
                ->icon('heroicon-o-envelope')
                ->color('info')
                ->visible(fn () => in_array($this->record->status, ['confirmed', 'completed']) && $this->record->payment_status === 'captured' && !$this->record->hasTipped())
                ->action(function () {
                    try {
                        $tipService = app(TipService::class);
                        $result = $tipService->sendTipLink($this->record);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Tip Link Sent')
                                ->body('Tip link has been sent to ' . $this->record->customer_email)
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
                ->modalDescription('Send optional tip link to ' . $this->record->customer_email),
            
            Action::make('showTipQr')
                ->label('Show Tip QR')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->visible(fn () => in_array($this->record->status, ['confirmed', 'completed']) && $this->record->payment_status === 'captured' && !$this->record->hasTipped())
                ->modalContent(function () {
                    $tipService = app(TipService::class);
                    $result = $tipService->getQrCode($this->record);
                    
                    return view('filament.resources.bookings.tip-qr-modal', [
                        'qrCode' => $result['qr_code'],
                        'url' => $result['url'],
                        'bookingNumber' => $this->record->booking_number,
                    ]);
                })
                ->modalHeading('Tip Payment QR Code')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
                
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
