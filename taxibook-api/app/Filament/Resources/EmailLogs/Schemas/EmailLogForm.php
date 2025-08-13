<?php

namespace App\Filament\Resources\EmailLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EmailLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_id')
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('template_slug'),
                TextInput::make('recipient_email')
                    ->email()
                    ->required(),
                TextInput::make('recipient_name'),
                TextInput::make('cc_emails')
                    ->email(),
                TextInput::make('bcc_emails')
                    ->email(),
                TextInput::make('subject')
                    ->required(),
                Textarea::make('body')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('variables_used')
                    ->columnSpanFull(),
                Textarea::make('attachments')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('message_id'),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                DateTimePicker::make('sent_at'),
                DateTimePicker::make('opened_at'),
                TextInput::make('open_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
