<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePricingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'from_mile',
        'to_mile',
        'per_mile_rate',
    ];

    protected function casts(): array
    {
        return [
            'from_mile' => 'decimal:2',
            'to_mile' => 'decimal:2',
            'per_mile_rate' => 'decimal:2',
        ];
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }
}