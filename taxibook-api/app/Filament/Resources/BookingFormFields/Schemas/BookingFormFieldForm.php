<?php

namespace App\Filament\Resources\BookingFormFields\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use App\Models\BookingFormField;

class BookingFormFieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Field Configuration')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->helperText('Unique identifier (e.g., flight_number)')
                            ->disabled(fn (?BookingFormField $record) => $record !== null),
                        
                        TextInput::make('label')
                            ->required()
                            ->helperText('Display label for the field'),
                        
                        TextInput::make('placeholder')
                            ->helperText('Placeholder text for input fields'),
                        
                        Select::make('type')
                            ->required()
                            ->options([
                                'text' => 'Text',
                                'number' => 'Number',
                                'email' => 'Email',
                                'tel' => 'Phone',
                                'select' => 'Dropdown',
                                'checkbox' => 'Checkbox',
                                'textarea' => 'Text Area',
                                'date' => 'Date',
                                'time' => 'Time',
                            ])
                            ->reactive(),
                        
                        Toggle::make('required')
                            ->label('Required Field')
                            ->default(false),
                        
                        Toggle::make('enabled')
                            ->label('Enabled')
                            ->default(true)
                            ->helperText('Disabled fields won\'t show in the booking form'),
                        
                        TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order (lower numbers appear first)'),
                        
                        TextInput::make('group')
                            ->helperText('Group related fields together'),
                        
                        Textarea::make('helper_text')
                            ->rows(2)
                            ->helperText('Help text displayed below the field'),
                    ])
                    ->columns(2),
                
                Section::make('Options')
                    ->schema([
                        Repeater::make('options')
                            ->schema([
                                TextInput::make('value')
                                    ->required(),
                                TextInput::make('label')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->helperText('Options for select/dropdown fields')
                            ->visible(fn (Get $get) => $get('type') === 'select'),
                    ])
                    ->visible(fn (Get $get) => $get('type') === 'select'),
                
                Section::make('Conditional Display')
                    ->description('Configure when this field should be shown based on booking details')
                    ->schema([
                        Repeater::make('conditions')
                            ->label('Display Conditions')
                            ->schema([
                                Select::make('field')
                                    ->label('Field Name')
                                    ->options([
                                        'is_airport' => 'Is Airport (pickup OR dropoff)',
                                        'is_airport_pickup' => 'Is Airport Pickup',
                                        'is_airport_dropoff' => 'Is Airport Dropoff',
                                    ])
                                    ->helperText('Select the condition to check')
                                    ->required(),
                                
                                Select::make('operator')
                                    ->options([
                                        '==' => 'Equals',
                                        '!=' => 'Not Equals',
                                    ])
                                    ->default('==')
                                    ->required(),
                                
                                Select::make('value')
                                    ->label('Value')
                                    ->options([
                                        'true' => 'True (Yes)',
                                        'false' => 'False (No)',
                                    ])
                                    ->helperText('Condition value')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->helperText('Show this field only when conditions are met'),
                    ])
                    ->collapsible(),
                
                Section::make('Validation Rules')
                    ->schema([
                        KeyValue::make('validation_rules')
                            ->keyLabel('Rule')
                            ->valueLabel('Value')
                            ->helperText('Custom validation rules (e.g., min: 0, max: 20)'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
