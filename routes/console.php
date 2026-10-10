<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly database backup (needs "php artisan schedule:work", or a cron / Task Scheduler entry
// running "php artisan schedule:run" every minute).
Schedule::command('db:backup')->dailyAt('02:00')->withoutOverlapping();
