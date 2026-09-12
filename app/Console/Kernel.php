<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Register your custom command here
        \App\Console\Commands\TinkerWithPlanData::class,
        \App\Console\Commands\MasterTinker::class,

        \App\Console\Commands\CompanySubscriptionPlanExpired::class,
        // \App\Console\Commands\AutoPunchOut::class, // Commented out: TeamAttendance model not found
        \App\Console\Commands\SyncBiometricAttendance::class,
        \App\Console\Commands\AttendanceAutoPunchOut::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Auto-sync biometric attendance based on machine sync intervals
        // Runs every minute to check all machines and sync those whose interval has elapsed
        // Configured for web route cron compatibility (removed runInBackground)
        $schedule->command('biometric:sync-attendance')
            ->everyMinute()
            ->withoutOverlapping(5) // 5 minute timeout to prevent stuck locks
            ->appendOutputTo(storage_path('logs/biometric-sync-' . now()->format('Y-m-d') . '.log'))
            ->onSuccess(function () {
                Log::info('Biometric sync scheduled task executed successfully at ' . now());
            })
            ->onFailure(function () {
                Log::error('Biometric sync scheduled task failed at ' . now());
            });
        
        // Auto-punch out employees who forgot to punch out yesterday (Disabled per user request)
        // $schedule->command('attendance:auto-punch-out')->dailyAt('00:00');

        // $schedule->command('import:india-mart-inquiry')->everyMinute()->withoutOverlapping()->evenInMaintenanceMode();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
