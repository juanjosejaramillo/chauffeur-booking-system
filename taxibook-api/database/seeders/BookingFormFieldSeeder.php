<?php

namespace Database\Seeders;

use App\Models\BookingFormField;
use Illuminate\Database\Seeder;

class BookingFormFieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            [
                'key' => 'flight_number',
                'label' => 'Flight Number',
                'placeholder' => 'e.g., AA1234',
                'type' => 'text',
                'required' => false,
                'enabled' => true,
                'conditions' => [
                    ['field' => 'is_airport', 'operator' => '==', 'value' => true]
                ],
                'order' => 10,
                'helper_text' => 'Your flight number helps us track arrival times',
                'group' => 'travel_details'
            ],
            [
                'key' => 'number_of_bags',
                'label' => 'Number of Bags',
                'placeholder' => 'How many bags?',
                'type' => 'number',
                'required' => false,
                'enabled' => true,
                'validation_rules' => ['min' => 0, 'max' => 20],
                'order' => 20,
                'helper_text' => 'Include all luggage and carry-on items',
                'group' => 'travel_details'
            ],
            [
                'key' => 'child_seats',
                'label' => 'Child Seats Required',
                'type' => 'select',
                'required' => false,
                'enabled' => true,
                'options' => [
                    ['value' => 'none', 'label' => 'No child seats needed'],
                    ['value' => 'infant', 'label' => '1 Infant seat (rear-facing)'],
                    ['value' => 'toddler', 'label' => '1 Toddler seat (forward-facing)'],
                    ['value' => 'booster', 'label' => '1 Booster seat'],
                    ['value' => 'multiple', 'label' => 'Multiple seats (specify in notes)']
                ],
                'order' => 30,
                'helper_text' => 'We provide complimentary child seats',
                'group' => 'travel_details'
            ],
            [
                'key' => 'meet_and_greet',
                'label' => 'Airport Meet & Greet Service',
                'type' => 'checkbox',
                'required' => false,
                'enabled' => true,
                'conditions' => [
                    ['field' => 'is_airport_pickup', 'operator' => '==', 'value' => true]
                ],
                'order' => 40,
                'helper_text' => 'Your chauffeur will meet you at baggage claim with a name sign',
                'group' => 'airport_services'
            ],
            [
                'key' => 'special_occasion',
                'label' => 'Special Occasion',
                'type' => 'select',
                'required' => false,
                'enabled' => true,
                'options' => [
                    ['value' => 'none', 'label' => 'No special occasion'],
                    ['value' => 'birthday', 'label' => 'Birthday'],
                    ['value' => 'anniversary', 'label' => 'Anniversary'],
                    ['value' => 'wedding', 'label' => 'Wedding'],
                    ['value' => 'business', 'label' => 'Business/Corporate'],
                    ['value' => 'date_night', 'label' => 'Date Night'],
                    ['value' => 'other', 'label' => 'Other (specify in notes)']
                ],
                'order' => 50,
                'helper_text' => 'Let us help make your occasion extra special',
                'group' => 'preferences'
            ],
            [
                'key' => 'preferred_temperature',
                'label' => 'Preferred Vehicle Temperature',
                'type' => 'select',
                'required' => false,
                'enabled' => false, // Disabled by default
                'options' => [
                    ['value' => 'cool', 'label' => 'Cool (68-70°F)'],
                    ['value' => 'moderate', 'label' => 'Moderate (70-72°F)'],
                    ['value' => 'warm', 'label' => 'Warm (72-74°F)']
                ],
                'order' => 60,
                'group' => 'preferences'
            ]
        ];

        foreach ($fields as $field) {
            BookingFormField::updateOrCreate(
                ['key' => $field['key']],
                $field
            );
        }
    }
}