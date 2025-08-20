<?php

namespace App\Providers;

use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\BookingCancelled;
use App\Events\BookingCompleted;
use App\Events\BookingModified;
use App\Events\TripStarted;
use App\Events\PaymentCaptured;
use App\Events\PaymentRefunded;
use App\Listeners\SendTriggeredEmails;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // All events now use the unified SendTriggeredEmails listener
        // which checks database triggers to determine which emails to send
        BookingCreated::class => [
            SendTriggeredEmails::class,
        ],
        BookingConfirmed::class => [
            SendTriggeredEmails::class,
        ],
        BookingCancelled::class => [
            SendTriggeredEmails::class,
        ],
        BookingCompleted::class => [
            SendTriggeredEmails::class,
        ],
        BookingModified::class => [
            SendTriggeredEmails::class,
        ],
        TripStarted::class => [
            SendTriggeredEmails::class,
        ],
        PaymentCaptured::class => [
            SendTriggeredEmails::class,
        ],
        PaymentRefunded::class => [
            SendTriggeredEmails::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}