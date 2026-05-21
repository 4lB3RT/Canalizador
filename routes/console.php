<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('youtube:sync-last-video', [config('services.youtube.channel_id')])
    ->hourly()
    ->withoutOverlapping();

Schedule::command('youtube:sync-channels')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
