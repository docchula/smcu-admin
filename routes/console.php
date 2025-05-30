<?php

use App\Console\Commands\FetchEmailCommand;
use App\Jobs\NotifyProjectClosureDueJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(FetchEmailCommand::class)
    ->everyFourHours()->withoutOverlapping(28800)->appendOutputTo(storage_path('logs/fetch-email.log'));
Schedule::command(ScheduleCheckHeartbeatCommand::class)->everyFiveMinutes();

Schedule::job(new NotifyProjectClosureDueJob)->daily();
