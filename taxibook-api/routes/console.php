<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule time-based email triggers
Schedule::command('emails:send-scheduled')->everyFiveMinutes();

// Process scheduled emails based on timing configuration
Schedule::command('emails:process-scheduled')->everyFifteenMinutes();
