<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\VehicleType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
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
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'authorized' => 'Authorized',
                                'captured' => 'Captured',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                                'cancelled' => 'Cancelled',
                            ])
                            ->native(false)
                            ->required()
                            ->disabled(fn ($record) => $record && in_array($record->payment_status, ['captured', 'refunded']))
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                    ])
                    ->columns(['lg' => 3, 'md' => 2, 'sm' => 2]),

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
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                        TextInput::make('final_fare')
                            ->label('Final Fare')
                            ->prefix('$')
                            ->numeric()
                            ->visible(fn ($record) => $record && $record->payment_status === 'captured')
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                        TextInput::make('estimated_distance')
                            ->label('Distance (miles)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                        TextInput::make('estimated_duration')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->formatStateUsing(fn ($state) => $state ? round($state / 60) : 0)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 2]),
                    ])
                    ->columns(['lg' => 4, 'md' => 2, 'sm' => 2]),

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
