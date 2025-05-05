<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GenerateProductForecasts::class,
        Commands\CheckLowStockAndReorder::class,
        Commands\CheckExpiringBatches::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run AI-based demand forecasting daily at midnight
        $schedule->command('inventory:generate-forecasts')
                 ->dailyAt('00:00')
                 ->appendOutputTo(storage_path('logs/forecasts.log'));
        
        // Check for low stock and create auto-reorder requests daily at 1 AM
        $schedule->command('inventory:check-and-reorder --consolidated')
                 ->dailyAt('01:00')
                 ->appendOutputTo(storage_path('logs/auto-reorder.log'));
        
        // Check for expiring batches daily at 2 AM
        $schedule->command('inventory:check-expiring-batches')
                 ->dailyAt('02:00')
                 ->appendOutputTo(storage_path('logs/expiring-batches.log'));
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
