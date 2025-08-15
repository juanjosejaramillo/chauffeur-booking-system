<?php

namespace App\Filament\Resources\BookingFormFields\Pages;

use App\Filament\Resources\BookingFormFields\BookingFormFieldResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookingFormField extends CreateRecord
{
    protected static string $resource = BookingFormFieldResource::class;
}
