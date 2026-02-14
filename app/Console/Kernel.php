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
        Commands\TaskRenovarCufd::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly()->everyMinute();

        // tarea programada para realizarse todos los días, a las 0 horas, con fecha y hora de La Paz-Bolivia. 
        // $schedule->command('taskcufd:renovar')->everyMinute();
        $schedule->command('taskcufd:renovar')->dailyAt('00:00')->timezone('America/La_Paz');

        // $schedule->command('inspire')
        //          ->hourly();
        /*$schedule->call(function () {
            $controller = new \App\Http\Controllers\AttendanceController();
            $controller->reset();
        })->everyMinute();*/
        $schedule->call(function(){
            $exitCode = Artisan::call('view:clear');
        })->daily();
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
