<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\PingCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // https://spatie.be/docs/laravel-health/v1/basic-usage/registering-your-first-check
        Health::checks([
            CacheCheck::new(),
            DatabaseCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            PingCheck::new()->url('https://www.googleapis.com/oauth2/v3/certs')
                ->name('Google')->timeout(10)->retryTimes(2),
            PingCheck::new()->url(config('vesta-client.url', 'https://docchula.com'))
                ->name('Vesta')->timeout(10)->retryTimes(2),
            ScheduleCheck::new()->heartbeatMaxAgeInMinutes(60),
            // Disk space check only works on Linux
            UsedDiskSpaceCheck::new()->failWhenUsedSpaceIsAbovePercentage(95),
        ]);
    }
}
