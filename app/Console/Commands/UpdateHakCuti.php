<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateHakCuti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-hak-cuti';

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
        // Cron job dijalankan perbulan
        // Cuti tahunan konsep = Intinya dapet sesuai masa kerja, contoh A masuk bulan Februari, dia dapet cuti sebanyak 10 hari (karena 12 bulan kurangi 2 bulan (januari februari), selebihnya mengikuti bulan)
        // Cuti besar konsep = Intinya dapet sesuai masa kerja setelah 7 tahun
        // Cuti lainnya tetap di reset per tahun

        // default kuota diambil dari tipe_cutis->kuota
    }
}
