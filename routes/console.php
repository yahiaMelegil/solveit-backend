<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('privacy:cleanup')->hourly()->withoutOverlapping();

Schedule::command('case-documents:cleanup')->hourly()->withoutOverlapping();

Schedule::command('catalog:review-cases')->everyFiveMinutes()->withoutOverlapping();

Schedule::command('invitations:expire')->everyMinute()->withoutOverlapping();

Schedule::command('commerce:expire')->everyMinute()->withoutOverlapping();

Schedule::command('finance:expire')->everyMinute()->withoutOverlapping();

Schedule::command('finance:reconcile')->everyTenMinutes()->withoutOverlapping();
