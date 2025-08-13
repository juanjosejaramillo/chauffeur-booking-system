<?php

namespace App\Filament\Resources\EmailTemplates\Tables;

use App\Models\EmailTemplate;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EmailTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'customer',
                        'success' => 'admin',
                        'warning' => 'driver',
                    ]),
                
                TagsColumn::make('trigger_events')
                    ->label('Triggers')
                    ->limit(2),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                
                TextColumn::make('priority')
                    ->sortable()
                    ->badge(),
                
                TextColumn::make('emailLogs_count')
                    ->label('Emails Sent')
                    ->counts('emailLogs')
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'customer' => 'Customer',
                        'admin' => 'Admin',
                        'driver' => 'Driver',
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label('Active'),
                
                SelectFilter::make('trigger_events')
                    ->label('Has Trigger')
                    ->options(EmailTemplate::getAvailableTriggers())
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereJsonContains('trigger_events', $data['value']);
                        }
                    }),
            ])
            ->actions([
                Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->form([
                        TextInput::make('test_email')
                            ->label('Send test to')
                            ->email()
                            ->default(auth()->user()->email ?? '')
                            ->required(),
                        
                        Select::make('test_booking_id')
                            ->label('Use data from booking (optional)')
                            ->options(function () {
                                return \App\Models\Booking::latest()
                                    ->take(10)
                                    ->get()
                                    ->pluck('booking_number', 'id');
                            })
                            ->searchable()
                            ->helperText('Select a booking to use its data for variables'),
                    ])
                    ->action(function (EmailTemplate $record, array $data) {
                        $booking = null;
                        if ($data['test_booking_id']) {
                            $booking = \App\Models\Booking::find($data['test_booking_id']);
                        }
                        
                        $notificationService = app(NotificationService::class);
                        $result = $notificationService->sendEmailNotification(
                            $record->slug,
                            $booking,
                            ['recipient_email' => $data['test_email'], 'recipient_name' => 'Test User']
                        );
                        
                        if ($result) {
                            Notification::make()
                                ->title('Test email sent')
                                ->body("Test email sent to {$data['test_email']}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to send test email')
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (EmailTemplate $record) {
                        $newTemplate = $record->replicate();
                        $newTemplate->slug = $record->slug . '-copy-' . time();
                        $newTemplate->name = $record->name . ' (Copy)';
                        $newTemplate->is_active = false;
                        $newTemplate->save();
                        
                        Notification::make()
                            ->title('Template duplicated')
                            ->success()
                            ->send();
                    }),
                
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }
}