<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        'bounced' => 'danger',
                        'complained' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('template_slug')
                    ->label('Template')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => str_replace('-', ' ', ucwords($state, '-'))),
                TextColumn::make('recipient_email')
                    ->label('To')
                    ->searchable()
                    ->copyable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->recipient_email),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->subject),
                TextColumn::make('booking.booking_number')
                    ->label('Booking')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->booking_id ? BookingResource::getUrl('edit', ['record' => $record->booking_id]) : null)
                    ->placeholder('-'),
                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime('M j g:i A')
                    ->sortable()
                    ->placeholder('Queued')
                    ->description(fn ($record) => $record->sent_at ? null : 'In Queue'),
                TextColumn::make('opened_at')
                    ->label('Opened')
                    ->dateTime('M j g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('open_count')
                    ->label('Opens')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->error_message)
                    ->color('danger'),
                TextColumn::make('created_at')
                    ->label('Queued At')
                    ->dateTime('M j g:i A')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending (In Queue)',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                        'bounced' => 'Bounced',
                        'complained' => 'Complained',
                    ])
                    ->multiple()
                    ->preload(),
                SelectFilter::make('template_slug')
                    ->label('Template')
                    ->options(function () {
                        return \App\Models\EmailLog::distinct()
                            ->pluck('template_slug')
                            ->mapWithKeys(fn ($slug) => [$slug => str_replace('-', ' ', ucwords($slug, '-'))])
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalContent(fn ($record) => view('filament.resources.email-logs.view-email', ['record' => $record]))
                    ->modalHeading(fn ($record) => 'Email Details: ' . $record->subject),
                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['failed', 'bounced']))
                    ->action(function ($record) {
                        // Mark as pending to retry
                        $record->update(['status' => 'pending']);
                        
                        // Dispatch job to send email
                        if ($record->booking_id) {
                            $notificationService = app(\App\Services\NotificationService::class);
                            $notificationService->sendEmailNotification(
                                $record->template_slug,
                                $record->booking,
                                json_decode($record->variables_used ?? '{}', true)
                            );
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Resend Email')
                    ->modalDescription('Are you sure you want to resend this email?'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->emptyStateHeading('No emails found')
            ->emptyStateDescription('Emails will appear here once they are queued for sending.')
            ->emptyStateIcon('heroicon-o-envelope')
            ->poll('10s'); // Auto-refresh every 10 seconds
    }
}