<?php

namespace App\Console;

use App\UseCase\Proxy\Source\FreeProxyListNet;
use App\UseCase\Proxy\Source\Geonode;
use App\UseCase\Proxy\Source\HideMyName;
use App\UseCase\Proxy\Source\ProxyScrape;
use App\UseCase\Proxy\Source\ProxySearcher;
use App\UseCase\Proxy\Source\RootJazz;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('proxy:update')->everyThirtyMinutes();
        $schedule->command('proxy:check yandex')->hourly();
        $schedule->command('proxy:check ostrovok')->hourly();
        $schedule->command('proxy:check sutochno')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
