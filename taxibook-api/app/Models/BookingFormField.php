<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'placeholder',
        'type',
        'required',
        'enabled',
        'options',
        'validation_rules',
        'conditions',
        'order',
        'helper_text',
        'group'
    ];

    protected $casts = [
        'required' => 'boolean',
        'enabled' => 'boolean',
        'options' => 'array',
        'validation_rules' => 'array',
        'conditions' => 'array',
    ];

    /**
     * Get only enabled fields
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Get fields ordered by their order value
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Check if field should be shown based on conditions
     */
    public function shouldShow($context = [])
    {
        if (empty($this->conditions)) {
            return true;
        }

        // Check each condition
        foreach ($this->conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '==';
            $value = $condition['value'] ?? null;
            $contextValue = $context[$field] ?? null;

            switch ($operator) {
                case '==':
                    if ($contextValue != $value) return false;
                    break;
                case '!=':
                    if ($contextValue == $value) return false;
                    break;
                case 'contains':
                    if (!str_contains($contextValue, $value)) return false;
                    break;
                case 'in':
                    if (!in_array($contextValue, (array)$value)) return false;
                    break;
            }
        }

        return true;
    }
}