<?php

namespace App\Console;

use App\UseCase\Proxy\Source\Geonode;
use App\UseCase\Proxy\Source\HideMyName;
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
         $this->updateProxy($schedule);
         $this->checkProxy($schedule);
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

    // обновление прокси с сайтов
    private function updateProxy(Schedule $schedule): void
    {
        $schedule->command('proxy:update ' . HideMyName::SOURCE)->daily();
        $schedule->command('proxy:update ' . Geonode::SOURCE)->hourly();
        $schedule->command('proxy:update ' . RootJazz::SOURCE)->hourly();
    }

    // проверка полученных прокси на пригодность
    private function checkProxy(Schedule $schedule): void
    {
        $schedule->command('proxy:check yandex')->hourly();
    }
}
