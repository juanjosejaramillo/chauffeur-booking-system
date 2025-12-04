<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\VehicleType;
use App\Models\BookingFormField;
use App\Models\Setting;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Booking Information')
                    ->description('Core booking details and status')
                    ->schema([
                        TextInput::make('booking_number')
                            ->label('Booking Number')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('Booking Status')
                            ->options(function ($record) {
                                $options = [
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ];

                                // If payment was already captured and status is completed, disable going back
                                if ($record && $record->status === 'completed' && $record->payment_status === 'captured') {
                                    // Mark pending and confirmed as unavailable
                                    $options['pending'] = 'Pending (Not Available)';
                                    $options['confirmed'] = 'Confirmed (Not Available)';
                                }

                                return $options;
                            })
                            ->disableOptionWhen(function (string $value, $record) {
                                // Disable going back to pending/confirmed if payment is captured and status is completed
                                if ($record && $record->status === 'completed' && $record->payment_status === 'captured') {
                                    return in_array($value, ['pending', 'confirmed']);
                                }
                                return false;
                            })
                            ->native(false)
                            ->required()
                            ->helperText(function ($record, Get $get) {
                                $currentSelection = $get('status');
                                $paymentMode = Setting::get('payment_mode', 'immediate');

                                if (!$record) return null;

                                // Warning when selecting completed with saved card in post_service mode
                                if ($currentSelection === 'completed'
                                    && $record->status !== 'completed'
                                    && $paymentMode === 'post_service'
                                    && $record->payment_status === 'pending'
                                    && $record->hasSavedPaymentMethod()
                                ) {
                                    $amount = $record->final_fare ?? $record->estimated_fare;
                                    return new HtmlString('<span class="text-amber-600 dark:text-amber-400 font-semibold">⚠️ Card will be charged $' . number_format($amount, 2) . ' when saved</span>');
                                }

                                // Warning about status restrictions when payment is captured
                                if ($record->status === 'completed' && $record->payment_status === 'captured') {
                                    return new HtmlString('<span class="text-gray-500 dark:text-gray-400">Status cannot be changed back - payment has been processed</span>');
                                }

                                return null;
                            })
                            ->reactive()
                            ->columnSpan(1),
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'authorized' => 'Authorized',
                                'captured' => 'Captured',
                                'failed' => 'Failed',
                                'refunded' => 'Fully Refunded',
                                'cancelled' => 'Cancelled',
                            ])
                            ->native(false)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Payment status is managed through Stripe. Use action buttons to capture, cancel, or refund.')
                            ->columnSpan(1),
                        Placeholder::make('refund_status_indicator')
                            ->label('Refund Status')
                            ->content(function ($record) {
                                if (!$record) return '';
                                if ($record->payment_status === 'captured' && $record->total_refunded > 0) {
                                    $originalAmount = $record->final_fare ?? $record->estimated_fare;
                                    $percentage = round(($record->total_refunded / $originalAmount) * 100);
                                    return new HtmlString('
                                        <div class="flex items-center gap-3">
                                            <span class="text-orange-600 font-semibold">Partially Refunded</span>
                                            <span class="text-sm text-gray-600">($' . number_format($record->total_refunded, 2) . ' of $' . number_format($originalAmount, 2) . ' - ' . $percentage . '%)</span>
                                        </div>
                                    ');
                                } elseif ($record->payment_status === 'refunded') {
                                    return new HtmlString('<span class="text-gray-600 font-semibold">Fully Refunded</span>');
                                }
                                return new HtmlString('<span class="text-gray-500">No refunds</span>');
                            })
                            ->visible(fn ($record) => $record && in_array($record->payment_status, ['captured', 'refunded']))
                            ->columnSpan(1),
                        ])
                    ->columns(2),

                Section::make('Customer Details')
                    ->description('Customer contact information')
                    ->schema([
                        TextInput::make('customer_first_name')
                            ->label('First Name')
                            ->required(),
                        TextInput::make('customer_last_name')
                            ->label('Last Name')
                            ->required(),
                        TextInput::make('customer_email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('customer_phone')
                            ->label('Phone')
                            ->tel()
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Trip Details')
                    ->description('Pickup, dropoff, and vehicle information')
                    ->schema([
                        Textarea::make('pickup_address')
                            ->label('Pickup Address')
                            ->required()
                            ->rows(2)
                            ->columnSpan(1),
                        Textarea::make('dropoff_address')
                            ->label('Dropoff Address')
                            ->required()
                            ->rows(2)
                            ->columnSpan(1),
                        DateTimePicker::make('pickup_date')
                            ->label('Pickup Date & Time')
                            ->required()
                            ->native(false)
                            ->displayFormat('M j, Y g:i A')
                            ->columnSpan(1),
                        Select::make('vehicle_type_id')
                            ->label('Vehicle Type')
                            ->options(VehicleType::pluck('display_name', 'id'))
                            ->native(false)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Pricing & Distance')
                    ->description(function ($record) {
                        if (!$record) return 'Fare calculation and trip metrics';
                        $paymentMode = Setting::get('payment_mode', 'immediate');
                        if ($paymentMode === 'post_service' && $record->payment_status === 'pending' && $record->hasSavedPaymentMethod()) {
                            return 'Set the final fare and tip before marking as completed';
                        }
                        return 'Fare calculation and trip metrics';
                    })
                    ->schema([
                        TextInput::make('estimated_fare')
                            ->label('Estimated Fare')
                            ->prefix('$')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('final_fare')
                            ->label(function ($record) {
                                if (!$record) return 'Final Fare';
                                $paymentMode = Setting::get('payment_mode', 'immediate');
                                if ($paymentMode === 'post_service' && $record->payment_status === 'pending') {
                                    return 'Final Fare (Amount to Charge)';
                                }
                                return 'Final Fare';
                            })
                            ->prefix('$')
                            ->numeric()
                            ->placeholder(fn ($record) => $record ? number_format($record->estimated_fare, 2) : '0.00')
                            ->helperText(function ($record) {
                                if (!$record) return null;
                                $paymentMode = Setting::get('payment_mode', 'immediate');
                                if ($paymentMode === 'post_service' && $record->payment_status === 'pending' && $record->hasSavedPaymentMethod()) {
                                    return 'Leave empty to charge the estimated fare, or enter a different amount';
                                }
                                return null;
                            })
                            ->visible(fn ($record) => $record && (
                                $record->payment_status === 'captured' ||
                                (Setting::get('payment_mode', 'immediate') === 'post_service' && $record->payment_status === 'pending')
                            ))
                            ->live(onBlur: true)
                            ->columnSpan(1),
                        TextInput::make('gratuity_amount')
                            ->label('Gratuity / Tip')
                            ->prefix('$')
                            ->numeric()
                            ->default(0)
                            ->placeholder('0.00')
                            ->helperText(function ($record) {
                                if (!$record) return null;
                                $paymentMode = Setting::get('payment_mode', 'immediate');
                                if ($paymentMode === 'post_service' && $record->payment_status === 'pending' && $record->hasSavedPaymentMethod()) {
                                    return 'Add tip to be included in the charge';
                                }
                                if ($record->payment_status === 'captured') {
                                    return 'Use "Capture with Tip" action to add tip';
                                }
                                return null;
                            })
                            ->disabled(fn ($record) => !$record || $record->payment_status === 'captured')
                            ->dehydrated()
                            ->live(onBlur: true)
                            ->columnSpan(1),
                        Placeholder::make('total_to_charge')
                            ->label('Total to Charge')
                            ->content(function ($record, Get $get) {
                                if (!$record) return '$0.00';
                                $fare = $get('final_fare') ?: ($record->final_fare ?? $record->estimated_fare);
                                $tip = $get('gratuity_amount') ?: $record->gratuity_amount ?? 0;
                                $total = floatval($fare) + floatval($tip);
                                return new HtmlString('<span class="text-lg font-bold text-amber-400">$' . number_format($total, 2) . '</span>');
                            })
                            ->visible(fn ($record) => $record && Setting::get('payment_mode', 'immediate') === 'post_service' && $record->payment_status === 'pending' && $record->hasSavedPaymentMethod())
                            ->columnSpan(1),
                        Placeholder::make('total_charged')
                            ->label('Total Charged')
                            ->content(fn ($record) => $record ? new HtmlString('<span class="text-lg font-bold text-green-400">$' . number_format(($record->final_fare ?? $record->estimated_fare) + ($record->gratuity_amount ?? 0), 2) . '</span>') : '$0.00')
                            ->visible(fn ($record) => $record && $record->payment_status === 'captured')
                            ->columnSpan(1),
                        TextInput::make('total_refunded')
                            ->label('Total Refunded')
                            ->prefix('$')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record && $record->total_refunded > 0)
                            ->columnSpan(1),
                        Placeholder::make('net_amount')
                            ->label('Net Amount (After Refunds)')
                            ->content(fn ($record) => $record ? '$' . number_format($record->net_amount, 2) : '$0.00')
                            ->visible(fn ($record) => $record && $record->total_refunded > 0)
                            ->columnSpan(1),
                        TextInput::make('estimated_distance')
                            ->label('Distance (miles)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),
                        TextInput::make('estimated_duration')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->formatStateUsing(fn ($state) => $state ? round($state / 60) : 0)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Section::make('Payment Information')
                    ->description(function ($record) {
                        if (!$record) return 'Stripe payment details';
                        $paymentMode = Setting::get('payment_mode', 'immediate');
                        if ($record->hasSavedPaymentMethod()) {
                            if ($record->payment_status === 'captured') {
                                return 'Payment captured - Card saved for tips';
                            } elseif ($paymentMode === 'post_service' && $record->payment_status === 'pending') {
                                return 'Card saved - Will charge on completion';
                            }
                            return 'Card saved on file';
                        }
                        return 'No card on file';
                    })
                    ->icon(function ($record) {
                        if (!$record) return 'heroicon-o-credit-card';
                        if ($record->hasSavedPaymentMethod()) {
                            if ($record->payment_status === 'captured') {
                                return 'heroicon-o-check-circle';
                            }
                            return 'heroicon-o-credit-card';
                        }
                        return 'heroicon-o-x-circle';
                    })
                    ->iconColor(function ($record) {
                        if (!$record) return 'gray';
                        if ($record->hasSavedPaymentMethod()) {
                            if ($record->payment_status === 'captured') {
                                return 'success';
                            }
                            $paymentMode = Setting::get('payment_mode', 'immediate');
                            if ($paymentMode === 'post_service' && $record->payment_status === 'pending') {
                                return 'warning';
                            }
                            return 'info';
                        }
                        return 'danger';
                    })
                    ->schema([
                        // Payment Status Summary Banner
                        Placeholder::make('payment_summary')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) return new HtmlString('');

                                $paymentMode = Setting::get('payment_mode', 'immediate');
                                $hasCard = $record->hasSavedPaymentMethod();
                                $paymentStatus = $record->payment_status;
                                $amount = $record->final_fare ?? $record->estimated_fare;

                                // Calculate total with tip
                                $tip = $record->gratuity_amount ?? 0;
                                $totalAmount = $amount + $tip;

                                // Build status display
                                if ($paymentStatus === 'captured') {
                                    $icon = '✅';
                                    $statusText = 'Payment Captured';
                                    $statusClass = 'text-green-400';
                                    $message = $hasCard ? 'Card saved for tips' : 'Payment processed';
                                    $amountText = '$' . number_format($totalAmount, 2) . ' charged';
                                    $amountClass = 'text-green-400';
                                } elseif ($paymentStatus === 'pending' && $hasCard && $paymentMode === 'post_service') {
                                    $icon = '⚠️';
                                    $statusText = 'Pay After Service';
                                    $statusClass = 'text-amber-400';
                                    $message = $tip > 0 ? 'Fare + tip will be charged' : 'Card will be charged when completed';
                                    $amountText = '$' . number_format($totalAmount, 2) . ' to charge';
                                    $amountClass = 'text-amber-400';
                                } elseif ($paymentStatus === 'authorized') {
                                    $icon = '💳';
                                    $statusText = 'Authorized';
                                    $statusClass = 'text-blue-400';
                                    $message = 'Ready to capture';
                                    $amountText = '$' . number_format($amount, 2) . ' authorized';
                                    $amountClass = 'text-blue-400';
                                } elseif ($hasCard) {
                                    $icon = '💳';
                                    $statusText = 'Card on File';
                                    $statusClass = 'text-blue-400';
                                    $message = 'Saved for future charges';
                                    $amountText = '$' . number_format($amount, 2) . ' estimated';
                                    $amountClass = 'text-gray-400';
                                } else {
                                    $icon = '❌';
                                    $statusText = 'No Card';
                                    $statusClass = 'text-gray-500';
                                    $message = 'No payment method';
                                    $amountText = '$' . number_format($amount, 2) . ' estimated';
                                    $amountClass = 'text-gray-500';
                                }

                                return new HtmlString('
                                    <div class="flex items-center justify-between gap-4 py-1">
                                        <div class="flex items-center gap-2">
                                            <span>' . $icon . '</span>
                                            <span class="font-medium ' . $statusClass . '">' . $statusText . '</span>
                                            <span class="text-gray-500">·</span>
                                            <span class="text-sm text-gray-400">' . $message . '</span>
                                        </div>
                                        <span class="font-semibold ' . $amountClass . '">' . $amountText . '</span>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),
                        TextInput::make('stripe_payment_intent_id')
                            ->label('Payment Intent ID')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Not yet created')
                            ->columnSpan(1),
                        TextInput::make('stripe_payment_method_id')
                            ->label('Payment Method ID')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Not yet created')
                            ->columnSpan(1),
                        TextInput::make('stripe_customer_id')
                            ->label('Customer ID')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Not yet created')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Additional Information')
                    ->description('Special instructions and notes')
                    ->schema([
                        Textarea::make('special_instructions')
                            ->label('Customer Instructions')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3)
                            ->helperText('Internal notes not visible to customer')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible(),

                Section::make('Customer Responses')
                    ->description('Dynamic form fields filled by the customer')
                    ->schema(function ($record) {
                        if (!$record || empty($record->additional_data)) {
                            return [
                                Placeholder::make('no_responses')
                                    ->label('')
                                    ->content(new HtmlString('<span class="text-gray-500">No additional responses provided</span>'))
                                    ->columnSpanFull()
                            ];
                        }

                        $fields = [];
                        $formFields = BookingFormField::enabled()->ordered()->get();
                        
                        // Add flight number if exists
                        if ($record->flight_number) {
                            $fields[] = Placeholder::make('flight_number_display')
                                ->label('Flight Number')
                                ->content(new HtmlString('<span class="font-semibold text-gray-700">' . e($record->flight_number) . '</span>'));
                        }
                        
                        foreach ($record->additional_data as $key => $value) {
                            if (empty($value)) continue;
                            
                            $formField = $formFields->firstWhere('key', $key);
                            $label = $formField ? $formField->label : ucfirst(str_replace('_', ' ', $key));
                            
                            // Format the value based on field type
                            $displayValue = $value;
                            if ($formField) {
                                if ($formField->type === 'select' && $formField->options) {
                                    foreach ($formField->options as $option) {
                                        if ($option['value'] === $value) {
                                            $displayValue = $option['label'];
                                            break;
                                        }
                                    }
                                } elseif ($formField->type === 'checkbox') {
                                    $displayValue = $value ? 'Yes' : 'No';
                                    $displayValue = $value 
                                        ? '<span class="text-green-600 font-semibold">Yes</span>' 
                                        : '<span class="text-gray-500">No</span>';
                                } elseif ($key === 'number_of_bags') {
                                    $displayValue = $value . ' bag' . ($value != 1 ? 's' : '');
                                }
                            }
                            
                            $fields[] = Placeholder::make('dynamic_field_' . $key)
                                ->label($label)
                                ->content(new HtmlString('<span class="font-semibold text-gray-700">' . e($displayValue) . '</span>'))
                                ->columnSpan(1);
                        }
                        
                        return $fields ?: [
                            Placeholder::make('no_responses')
                                ->label('')
                                ->content(new HtmlString('<span class="text-gray-500">No additional responses provided</span>'))
                                ->columnSpanFull()
                        ];
                    })
                    ->columns(2)
                    ->visible(fn ($record) => $record && ($record->flight_number || !empty($record->additional_data)))
                    ->collapsed(false)
                    ->collapsible(),

                Section::make('Cancellation Details')
                    ->description('Reason and timing for cancelled bookings')
                    ->schema([
                        Textarea::make('cancellation_reason')
                            ->label('Cancellation Reason')
                            ->rows(2)
                            ->columnSpan(1),
                        DateTimePicker::make('cancelled_at')
                            ->label('Cancelled At')
                            ->native(false)
                            ->displayFormat('M j, Y g:i A')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->visible(fn (Get $get) => $get('status') === 'cancelled')
                    ->collapsed(fn (Get $get) => $get('status') !== 'cancelled')
                    ->collapsible(),
            ]);
    }
}
