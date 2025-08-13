<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_number')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('vehicle_type_id')
                    ->required()
                    ->numeric(),
                TextInput::make('customer_first_name')
                    ->required(),
                TextInput::make('customer_last_name')
                    ->required(),
                TextInput::make('customer_email')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel()
                    ->required(),
                TextInput::make('pickup_address')
                    ->required(),
                TextInput::make('pickup_latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('pickup_longitude')
                    ->required()
                    ->numeric(),
                TextInput::make('dropoff_address')
                    ->required(),
                TextInput::make('dropoff_latitude')
                    ->required()
                    ->numeric(),
                TextInput::make('dropoff_longitude')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('pickup_date')
                    ->required(),
                TextInput::make('estimated_distance')
                    ->required()
                    ->numeric(),
                TextInput::make('estimated_duration')
                    ->required()
                    ->numeric(),
                TextInput::make('route_polyline'),
                TextInput::make('estimated_fare')
                    ->required()
                    ->numeric(),
                TextInput::make('final_fare')
                    ->numeric(),
                Textarea::make('fare_breakdown')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('stripe_payment_intent_id'),
                TextInput::make('stripe_payment_method_id'),
                Textarea::make('special_instructions')
                    ->columnSpanFull(),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
                TextInput::make('cancellation_reason'),
                DateTimePicker::make('cancelled_at'),
            ]);
    }
}
