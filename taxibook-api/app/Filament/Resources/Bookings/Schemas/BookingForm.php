<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\VehicleType;
use App\Models\BookingFormField;
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
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->native(false)
                            ->required()
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
                        Placeholder::make('saved_card_indicator')
                            ->label('Saved Payment Method')
                            ->content(function ($record) {
                                if (!$record) return new HtmlString('<span class="text-gray-500">No booking data</span>');
                                if ($record->hasSavedPaymentMethod()) {
                                    return new HtmlString('
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-600 font-semibold">Yes</span>
                                            <span class="text-sm text-gray-600">Customer can be charged for tips without re-entering card details</span>
                                        </div>
                                    ');
                                }
                                return new HtmlString('
                                    <div class="flex items-center gap-3">
                                        <span class="text-red-600 font-semibold">No</span>
                                        <span class="text-sm text-gray-600">Customer must use tip link or QR code to add gratuity</span>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),
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
                    ->description('Fare calculation and trip metrics')
                    ->schema([
                        TextInput::make('estimated_fare')
                            ->label('Estimated Fare')
                            ->prefix('$')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('final_fare')
                            ->label('Final Fare')
                            ->prefix('$')
                            ->numeric()
                            ->visible(fn ($record) => $record && $record->payment_status === 'captured')
                            ->columnSpan(1),
                        TextInput::make('gratuity_amount')
                            ->label('Gratuity')
                            ->prefix('$')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('0.00')
                            ->columnSpan(1),
                        Placeholder::make('total_amount')
                            ->label('Total (Fare + Tip)')
                            ->content(fn ($record) => $record ? '$' . number_format(($record->final_fare ?? $record->estimated_fare) + $record->gratuity_amount, 2) : '$0.00')
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
                    ->description('Stripe payment details and saved card status')
                    ->schema([
                        Placeholder::make('saved_card_status')
                            ->label('Card Status')
                            ->content(function ($record) {
                                if (!$record) return new HtmlString('<span class="text-gray-500">-</span>');
                                if ($record->hasSavedPaymentMethod()) {
                                    return new HtmlString('<span class="text-green-600 font-semibold">Card on File</span>');
                                }
                                return new HtmlString('<span class="text-red-600 font-semibold">No Card Saved</span>');
                            })
                            ->columnSpan(['lg' => 2, 'md' => 2, 'sm' => 2]),
                        TextInput::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent ID')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Not yet created')
                            ->helperText('View in Stripe Dashboard')
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                        TextInput::make('stripe_payment_method_id')
                            ->label('Stripe Payment Method ID')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Not yet created')
                            ->helperText('Customer payment method')
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                        TextInput::make('stripe_customer_id')
                            ->label('Stripe Customer ID')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Not yet created')
                            ->helperText('Customer profile in Stripe')
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                    ])
                    ->columns(['lg' => 2, 'md' => 2, 'sm' => 2])
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
