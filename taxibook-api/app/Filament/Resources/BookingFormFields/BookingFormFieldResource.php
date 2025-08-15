<?php

namespace App\Filament\Resources\BookingFormFields;

use App\Filament\Resources\BookingFormFields\Pages\CreateBookingFormField;
use App\Filament\Resources\BookingFormFields\Pages\EditBookingFormField;
use App\Filament\Resources\BookingFormFields\Pages\ListBookingFormFields;
use App\Filament\Resources\BookingFormFields\Schemas\BookingFormFieldForm;
use App\Filament\Resources\BookingFormFields\Tables\BookingFormFieldsTable;
use App\Models\BookingFormField;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BookingFormFieldResource extends Resource
{
    protected static ?string $model = BookingFormField::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    
    protected static ?string $navigationLabel = 'Booking Form Fields';
    
    protected static string | \UnitEnum | null $navigationGroup = 'System';
    
    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return BookingFormFieldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingFormFieldsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingFormFields::route('/'),
            'create' => CreateBookingFormField::route('/create'),
            'edit' => EditBookingFormField::route('/{record}/edit'),
        ];
    }
}
