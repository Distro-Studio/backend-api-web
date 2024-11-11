<?php

namespace App\Console;

use App\Console\Commands\NotificationSIP;
use App\Console\Commands\ResetMasaDiklat;
use App\Console\Commands\UpdateAndResetReward;
use App\Console\Commands\UpdateAutoAlfaPresensi;
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
        ResetMasaDiklat::class,
        NotificationSIP::class,
        UpdateAutoAlfaPresensi::class
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update transfer karyawan
        $schedule->command('app:update-data-karyawan-transfer')
            ->timezone('Asia/Jakarta')
            ->dailyAt('23:59');

        // Update published payroll
        $schedule->command('app:update-auto-publish-penggajian')
            ->timezone('Asia/Jakarta')
            ->dailyAt('23:59');

        // Update & reset reward presensi
        $schedule->command('app:update-and-reset-reward-presensi')
            ->timezone('Asia/Jakarta')
            ->monthlyOn(Carbon::now()->endOfMonth()->day, '00:00');

        // Reset masa diklat
        $schedule->command('app:reset-masa-diklat')
            ->timezone('Asia/Jakarta')
            ->yearlyOn(12, 31, '23:59');

        // Notification SIP
        $schedule->command('app:notification-warning-sip')
            ->timezone('Asia/Jakarta')
            ->dailyAt('01:00');

        // Update alfa presensi
        $schedule->command('app:update-auto-alfa-presensi')
            ->timezone('Asia/Jakarta')
            ->dailyAt('00:00');

        // Backup DB
        $schedule->command('backup:clean')->monthly()->at('01:00');
        $schedule->command('backup:run')->monthly()->at('01:30');
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
