<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



app()->booted(function () {
    $schedule = app(Schedule::class);

    // ✅ Your working test
    // $schedule->call(function () {
    //     \Illuminate\Support\Facades\Log::info('✅ Task executed at: ' . now());
    // })->everyMinute();

    // ✅ Your real commands
    /** Check the Company wise plan expire */
    $schedule->command('app:company-subscription-plan-expired')->dailyAt('00:00');

    /** Sync biometric attendance based on machine sync intervals */
    $schedule->command('app:sync-biometric-attendance')->everyMinute();
    // $schedule->command('app:sync-biometric-attendance')
    //     ->everyMinute()
    //     ->withoutOverlapping(5) // 5 minute timeout to prevent stuck locks
    //     ->appendOutputTo(storage_path('logs/biometric-sync-' . now()->format('Y-m-d') . '.log'))
    //     ->onSuccess(function () {
    //         \Illuminate\Support\Facades\Log::info('Biometric sync scheduled task executed successfully at ' . now());
    //     })
    //     ->onFailure(function () {
    //         \Illuminate\Support\Facades\Log::error('Biometric sync scheduled task failed at ' . now());
    //     });

    /** Auto Punch Out - Commented out: TeamAttendance model not found */
    // $schedule->command('app:auto-punch-out')->everySecond();
    // $schedule->command('app:auto-punch-out')->everyMinute()->withoutOverlapping();
});
