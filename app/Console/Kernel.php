<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register custom commands.
     */
    protected $commands = [
        \App\Console\Commands\MarkExpiredBoosts::class,
        \App\Console\Commands\MigrateAdvertImagesToSpatie::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('boosts:expire')->everySixHours();
        $schedule->command('feed:google')->everySixHours();
        $schedule->command('queue:work --stop-when-empty --max-time=50')
             ->everyMinute()
             ->withoutOverlapping();

    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

