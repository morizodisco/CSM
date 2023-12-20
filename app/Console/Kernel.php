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
        \App\Console\Commands\ScrapingCheckCommand::class,
        \App\Console\Commands\DuplicationCheckCommand::class,
        \App\Console\Commands\HourCheckCommand::class,
        \App\Console\Commands\ForceDeleteCommand::class,
        \App\Console\Commands\ConfirmPriceCheckCommand::class,
        \App\Console\Commands\TotalFiguresCheckCommand::class,
        \App\Console\Commands\TotalFigures_2CheckCommand::class,
        \App\Console\Commands\PastGenreCheckCommand::class,
        \App\Console\Commands\PastPromotionCheckCommand::class,
        \App\Console\Commands\PastPromotionCodeCheckCommand::class,
        \App\Console\Commands\GetAdsCostTodayCommand::class,
        \App\Console\Commands\GetAdsCostYesterdayCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:past_genre_check_command')->monthlyOn(1, '00:01')->runInBackground()->onOneServer();
        $schedule->command('command:past_promotion_check_command')->monthlyOn(1, '00:01')->runInBackground()->onOneServer();
        $schedule->command('command:past_promotion_code_check_command')->monthlyOn(1, '00:01')->runInBackground()->onOneServer();
        $schedule->command('command:get_ads_cost_today_command')->everyFifteenMinutes()->runInBackground()->withoutOverlapping(30)->onOneServer();
        $schedule->command('command:get_ads_cost_yesterday_command')->hourly()->runInBackground()->withoutOverlapping(30)->onOneServer();
        $schedule->command('command:scraping_check_command')->everyMinute()->runInBackground()->onOneServer();
        $schedule->command('command:duplication_check_command')->everyMinute()->runInBackground()->onOneServer();
        $schedule->command('command:hour_check_command')->everyMinute()->runInBackground()->onOneServer();
        $schedule->command('command:force_delete_command')->everyMinute()->runInBackground()->onOneServer();
        $schedule->command('command:confirm_price_check_command')->everyMinute()->runInBackground()->onOneServer();
        $schedule->command('command:total_figures_check_command')->everyFiveMinutes()->runInBackground()->onOneServer();
        $schedule->command('command:total_figures_2_check_command')->everyFiveMinutes()->runInBackground()->onOneServer();
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
