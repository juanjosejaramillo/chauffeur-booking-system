<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'is_active',
        'sort_order',
        'apply_to_all_vehicles',
        'max_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'apply_to_all_vehicles' => 'boolean',
        ];
    }

    public function vehicleTypes()
    {
        return $this->belongsToMany(VehicleType::class, 'extra_vehicle_type');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get active extras available for a specific vehicle type.
     * Returns extras that apply to all vehicles OR are linked via pivot.
     */
    public static function forVehicleType($vehicleTypeId)
    {
        return static::active()
            ->ordered()
            ->where(function ($query) use ($vehicleTypeId) {
                $query->where('apply_to_all_vehicles', true)
                    ->orWhereHas('vehicleTypes', function ($q) use ($vehicleTypeId) {
                        $q->where('vehicle_type_id', $vehicleTypeId);
                    });
            })
            ->get();
    }
}
