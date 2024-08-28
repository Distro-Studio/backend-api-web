<?php

namespace App\Console;

use App\Console\Commands\UpdateAndResetReward;
use App\Console\Commands\UpdateAutoPublishPenggajian;
use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\UpdateDataKaryawanTransfer;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        UpdateDataKaryawanTransfer::class,
        UpdateAutoPublishPenggajian::class,
        UpdateAndResetReward::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update transfer karyawan
        $schedule->command('app:update-data-karyawan-transfer')
            ->timezone('Asia/Jakarta')
            ->dailyAt('01:00');

        // Update published payroll
        $schedule->command('app:update-auto-publish-penggajian')
            ->timezone('Asia/Jakarta')
            ->dailyAt('23:59');

        // Update & reset reward presensi
        $schedule->command('app:update-and-reset-reward-presensi')
            ->timezone('Asia/Jakarta')
            ->monthlyOn(Carbon::now()->endOfMonth()->day, '00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function scheduleTimezone(): DateTimeZone|string|null
    {
        return 'Asia/Jakarta';
    }
}
