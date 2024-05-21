<?php

use App\Console\Commands\FetchEmailCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(FetchEmailCommand::class)
    ->everyFourHours()->withoutOverlapping(28800)->appendOutputTo(storage_path('logs/fetch-email.log'));;
