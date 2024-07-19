<?php

namespace App\Console;

use App\Console\Commands\UpdateAutoPublishPenggajian;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\RiwayatPenggajian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\UpdateDataKaryawanTransfer;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        UpdateDataKaryawanTransfer::class,
        UpdateAutoPublishPenggajian::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update transfer karyawan
        $schedule->command('app:update-data-karyawan-transfer')
            ->timezone('Asia/Jakarta')
            ->hourly()
            ->between('01.00', '03.00');

        // Update published payroll
        $schedule->command('app:update-auto-publish-penggajian')
            ->timezone('Asia/Jakarta')
            ->hourly()
            ->between('23.58', '01.00');
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
