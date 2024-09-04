<?php

namespace Database\Seeders\Karyawan;

use App\Models\Ptkp;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Kompetensi;
use App\Models\DataKaryawan;
use App\Models\KelompokGaji;
use App\Models\KategoriAgama;
use App\Models\KategoriDarah;
use App\Models\StatusKaryawan;
use Illuminate\Database\Seeder;
use App\Models\TransferKaryawan;
use App\Models\KategoriPendidikan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cityborn = [
            'Magelang',
            'Semarang',
            'Salatiga',
            'Surakarta',
            'Yogyakarta',
            'Klaten',
            'Sragen',
            'Boyolali',
            'Demak',
            'Kudus',
            'Pati',
            'Rembang',
            'Blora',
            'Grobogan',
            'Jepara',
            'Purworejo',
            'Wonosobo',
            'Pekalongan',
            'Batang',
            'Kajen',
            'Pemalang',
            'Tegal',
            'Brebes',
            'Cilacap',
            'Kebumen',
            'Purbalingga',
            'Banyumas',
            'Cilacap',
            'Purwokerto',
            'Banjarnegara',
            'Wonosobo',
            'Temanggung',
            'Magelang',
            'Boyolali',
            'Banyuwangi',
            'Blitar',
            'Bondowoso',
            'Bojonegoro',
            'Jember',
            'Jombang',
            'Kediri',
            'Lamongan',
            'Lumajang',
            'Madura',
            'Magetan',
            'Madiun',
            'Malang',
            'Mojokerto',
            'Nganjuk',
            'Pacitan',
            'Pamekasan',
            'Pasuruan',
            'Ponorogo',
            'Probolinggo',
            'Sidoarjo',
            'Situbondo',
            'Sumenep',
            'Surabaya',
            'Trenggalek',
            'Tuban',
            'Tulungagung',
            'Bandung',
            'Bekasi',
            'Bogor',
            'Ciamis',
            'Cirebon',
            'Depok',
            'Garut',
            'Indramayu',
            'Karawang',
            'Kuningan',
            'Majalengka',
            'Pangandaran',
            'Purwakarta',
            'Subang',
            'Sukabumi',
            'Sumedang',
            'Tasikmalaya',
            'Tegal',
            'Cirebon',
            'Indramayu',
            'Majalengka',
            'Subang',
            'Kuningan',
            'Ciamis',
            'Tasikmalaya',
            'Garut',
            'Sumedang',
            'Bandung',
            'Cianjur',
        ];

        $gelar_dpn = ['Adv.', 'Ar.', 'apt.', 'dr.', 'drg.', 'drh.', 'Ir.', 'Ns.', 'Ak.'];
        $unit_kerja_id = UnitKerja::pluck('id')->all();
        $jabatan_id = Jabatan::pluck('id')->all();
        $kompetensi_id = Kompetensi::pluck('id')->all();
        $kelompok_gaji_id = KelompokGaji::pluck('id')->all();
        $ptkp_id = Ptkp::pluck('id')->all();
        $kategori_agama_id = KategoriAgama::pluck('id')->all();
        $status_karyawan_id = StatusKaryawan::pluck('id')->all();
        $kategori_darah_id = KategoriDarah::pluck('id')->all();
        $kategori_pendidikan_id = KategoriPendidikan::pluck('id')->all();

        for ($i = 0; $i < 50; $i++) {
            $user = User::create([
                'nama' => 'User ' . $i,
                'password' => 'password' . $i,
                'status_aktif' => 2,
                'data_completion_step' => 0,
            ]);
            $user->roles()->attach(rand(2, 4));

            $tgl_masuk = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2007), mktime(0, 0, 0, 12, 31, 2022)));
            $tgl_keluar = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2023), mktime(0, 0, 0, 12, 31, 2024)));
            $tgl_lahir = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 1900), mktime(0, 0, 0, 12, 31, 2003)));
            $tgl_str = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2023), mktime(0, 0, 0, 12, 31, 2028)));

            // Create DataKaryawan
            $dataKaryawan = DataKaryawan::create([
                'user_id' => $user->id,
                'email' => 'user' . $i . '@example.com',
                'no_rm' => rand(1214, 5000000),
                'no_manulife' => rand(1214, 5000000),
                'tgl_masuk' => $tgl_masuk,
                'unit_kerja_id' => $unit_kerja_id[array_rand($unit_kerja_id)],
                'jabatan_id' => $jabatan_id[array_rand($jabatan_id)],
                'kompetensi_id' => $kompetensi_id[array_rand($kompetensi_id)],
                'status_karyawan_id' => $status_karyawan_id[array_rand($status_karyawan_id)],
                'tempat_lahir' => $cityborn[array_rand($cityborn)],
                'tgl_lahir' => $tgl_lahir,
                "nik" => rand(1214, 50000000),
                "nik_ktp" => rand(1214, 50000000),
                'kelompok_gaji_id' => $kelompok_gaji_id[array_rand($kelompok_gaji_id)],
                'no_rekening' => rand(152368, 500000000),
                'tunjangan_fungsional' => rand(70000, 250000),
                'tunjangan_khusus' => rand(0, 120000),
                'tunjangan_lainnya' => rand(250000, 1000000),
                'uang_makan' => rand(0, 70000),
                'uang_lembur' => rand(0, 120000),
                'ptkp_id' => $ptkp_id[array_rand($ptkp_id)],
                "tgl_keluar" => $tgl_keluar,
                "no_kk" => rand(1214, 500000000),
                "alamat" => 'be former rear pool driver porch meal bottle meet cloud same',
                "gelar_depan" => $gelar_dpn[array_rand($gelar_dpn)],
                "no_hp" => rand(1214, 500000000),
                "no_bpjsksh" => rand(1214, 500000000),
                "no_bpjsktk" => rand(1214, 500000000),
                "tgl_diangkat" => $tgl_keluar,
                "masa_kerja" => rand(1, 40),
                "npwp" => rand(1214, 500000000),
                "jenis_kelamin" => rand(0, 1),
                "kategori_agama_id" => $kategori_agama_id[array_rand($kategori_agama_id)],
                "kategori_darah_id" => $kategori_darah_id[array_rand($kategori_darah_id)],
                "pendidikan_terakhir" => $kategori_pendidikan_id[array_rand($kategori_pendidikan_id)],
                "tinggi_badan" => rand(10, 300),
                "berat_badan" => rand(10, 200),
                "no_ijazah" => "IJ/VII/" . rand(1214, 500000000),
                "tahun_lulus" => rand(1800, 2017),
                "no_str" => "STR/01/RA/" . rand(1214, 500000),
                "masa_berlaku_str" => $tgl_str,
                "no_sip" => "SIP/01/VI/" . rand(1214, 500000),
                "masa_berlaku_sip" => $tgl_str,
                "tgl_berakhir_pks" => $tgl_keluar,
                "masa_diklat" => null,
            ]);

            // Create Berkas records and associate them with DataKaryawan
            // $berkasFields = [
            //     'file_ktp' => 'KTP',
            //     'file_kk' => 'KK',
            //     'file_sip' => 'SIP',
            //     'file_bpjsksh' => 'BPJS Kesehatan',
            //     'file_bpjsktk' => 'BPJS Ketenagakerjaan',
            //     'file_ijazah' => 'Ijazah',
            //     'file_sertifikat' => 'STR',
            // ];

            // foreach ($berkasFields as $field => $namaBerkas) {
            //     $berkas = Berkas::create([
            //         'user_id' => $user->id,
            //         'file_id' => (string) Str::uuid(),
            //         'nama' => $namaBerkas . ' - ' . $user->nama,
            //         'kategori_berkas_id' => 1,
            //         'status_berkas_id' => 1,
            //         'path' => '/path/to/personal/berkas/' . strtolower(str_replace(' ', '_', $namaBerkas)) . '/' . $user->nama,
            //         'tgl_upload' => now(),
            //         'nama_file' => strtolower(str_replace(' ', '_', $namaBerkas)),
            //         'ext' => 'application/pdf',
            //         'size' => rand(1000, 2000),
            //     ]);
            //     $dataKaryawan->{$field} = $berkas->id;
            // }

            // $dataKaryawan->save();

            // Perbarui user dengan data_karyawan_id
            $user->data_karyawan_id = $dataKaryawan->id;
            $user->save();
        }
    }
}
