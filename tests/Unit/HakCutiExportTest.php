<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\TipeCuti;
use App\Models\HakCuti;
use App\Models\Cuti;
use App\Models\User;
use Carbon\Carbon;
use App\Exports\Jadwal\HakCutiExport;

class HakCutiExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_used_and_remaining_quota_based_on_date_range_filters()
    {
        // create minimal related records
        $user = User::factory()->create();
        $dataKaryawan = \App\Models\DataKaryawan::create([
            'user_id' => $user->id,
        ]);

        $tipe = TipeCuti::create([
            'nama' => 'Test',
            'kuota' => 10,
            'cuti_administratif' => 0,
            'is_unlimited' => 0,
        ]);

        $hak = HakCuti::create([
            'data_karyawan_id' => $dataKaryawan->id,
            'tipe_cuti_id' => $tipe->id,
        ]);

        // approved cuti within range
        Cuti::create([
            'user_id' => $user->id,
            'tipe_cuti_id' => $tipe->id,
            'hak_cuti_id' => $hak->id,
            'tgl_from' => '15-01-2025',
            'tgl_to'   => '16-01-2025',
            'durasi'   => 2,
            'status_cuti_id' => 1,
            'verifikator_1' => 1,
            'verifikator_2' => 1,
        ]);

        // another approved cuti outside range
        Cuti::create([
            'user_id' => $user->id,
            'tipe_cuti_id' => $tipe->id,
            'hak_cuti_id' => $hak->id,
            'tgl_from' => '01-01-2024',
            'tgl_to'   => '02-01-2024',
            'durasi'   => 2,
            'status_cuti_id' => 1,
            'verifikator_1' => 1,
            'verifikator_2' => 1,
        ]);

        $filters = [
            'tgl_mulai' => '01-01-2025',
            'tgl_selesai' => '31-12-2025',
        ];

        $export = new HakCutiExport($filters);

        $mapped = $export->map($hak);

        // index 4 corresponds to used quota and 5 to remaining
        $this->assertEquals(2, $mapped[4]);
        $this->assertEquals(8, $mapped[5]);
    }

    /** @test */
    public function it_defaults_to_current_year_when_no_dates_provided()
    {
        // similar setup
        $user = User::factory()->create();
        $dataKaryawan = \App\Models\DataKaryawan::create([
            'user_id' => $user->id,
        ]);

        $tipe = TipeCuti::create([
            'nama' => 'Test',
            'kuota' => 5,
            'cuti_administratif' => 0,
            'is_unlimited' => 0,
        ]);

        $hak = HakCuti::create([
            'data_karyawan_id' => $dataKaryawan->id,
            'tipe_cuti_id' => $tipe->id,
        ]);

        // create one cuti in current year and one in last year
        $year = Carbon::now()->year;
        Cuti::create([
            'user_id' => $user->id,
            'tipe_cuti_id' => $tipe->id,
            'hak_cuti_id' => $hak->id,
            'tgl_from' => "01-01-{$year}",
            'tgl_to'   => "02-01-{$year}",
            'durasi'   => 1,
            'status_cuti_id' => 1,
            'verifikator_1' => 1,
            'verifikator_2' => 1,
        ]);
        Cuti::create([
            'user_id' => $user->id,
            'tipe_cuti_id' => $tipe->id,
            'hak_cuti_id' => $hak->id,
            'tgl_from' => "01-01-" . ($year - 1),
            'tgl_to'   => "02-01-" . ($year - 1),
            'durasi'   => 1,
            'status_cuti_id' => 1,
            'verifikator_1' => 1,
            'verifikator_2' => 1,
        ]);

        $export = new HakCutiExport([]);
        $mapped = $export->map($hak);

        $this->assertEquals(1, $mapped[4]);
        $this->assertEquals(4, $mapped[5]);
    }
}
