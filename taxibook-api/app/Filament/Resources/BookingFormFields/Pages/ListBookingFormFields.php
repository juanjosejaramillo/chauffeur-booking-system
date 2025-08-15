<?php

namespace App\Filament\Resources\BookingFormFields\Pages;

use App\Filament\Resources\BookingFormFields\BookingFormFieldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingFormFields extends ListRecords
{
    protected static string $resource = BookingFormFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
