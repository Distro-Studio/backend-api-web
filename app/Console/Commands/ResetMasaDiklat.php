<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\DataKaryawan;
use Illuminate\Console\Command;

class ResetMasaDiklat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-masa-diklat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDate = Carbon::now('Asia/Jakarta')->format('d-m-Y');
        DataKaryawan::query()->update(['masa_diklat' => null]);
        $this->info('Kolom masa_diklat telah di-reset menjadi null pada: ' . $currentDate);
    }
}
