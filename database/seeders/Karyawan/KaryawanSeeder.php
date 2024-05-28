<?php

namespace Database\Seeders\Karyawan;

use Carbon\Carbon;
use App\Models\Ptkp;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\UnitKerja;
use App\Models\Kompetensi;
use App\Models\TrackRecord;
use App\Models\DataKaryawan;
use App\Models\DataKeluarga;
use App\Models\KelompokGaji;
use Illuminate\Database\Seeder;
use App\Models\TransferKaryawan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cityborn = [
            'Magelang', 'Semarang', 'Salatiga', 'Surakarta', 'Yogyakarta', 'Klaten', 'Sragen',
            'Boyolali', 'Demak', 'Kudus', 'Pati', 'Rembang', 'Blora', 'Grobogan', 'Jepara',
            'Mungkid', 'Purworejo', 'Wonosobo', 'Pekalongan', 'Batang', 'Kajen', 'Pemalang',
            'Tegal', 'Brebes', 'Cilacap', 'Kebumen', 'Purbalingga', 'Banyumas', 'Cilacap',
            'Purwokerto', 'Banjarnegara', 'Wonosobo', 'Temanggung', 'Magelang', 'Boyolali',
            'Banyuwangi', 'Blitar', 'Bondowoso', 'Bojonegoro', 'Jember', 'Jombang', 'Kediri',
            'Lamongan', 'Lumajang', 'Madura', 'Magetan', 'Madiun', 'Malang', 'Mojokerto',
            'Nganjuk', 'Pacitan', 'Pamekasan', 'Pasuruan', 'Ponorogo', 'Probolinggo', 'Sidoarjo',
            'Situbondo', 'Sumenep', 'Surabaya', 'Trenggalek', 'Tuban', 'Tulungagung',
            'Bandung', 'Bekasi', 'Bogor', 'Ciamis', 'Cirebon', 'Depok', 'Garut', 'Indramayu',
            'Karawang', 'Kuningan', 'Majalengka', 'Pangandaran', 'Purwakarta', 'Subang',
            'Sukabumi', 'Sumedang', 'Tasikmalaya', 'Tegal', 'Cirebon', 'Indramayu', 'Majalengka',
            'Subang', 'Kuningan', 'Ciamis', 'Tasikmalaya', 'Garut', 'Sumedang', 'Bandung', 'Cianjur',
        ];

        $statuses = ['Tetap', 'Kontrak', 'Magang'];
        $gelar_dpn = ['Adv.', 'Ar.', 'apt.', 'dr.', 'drg.', 'drh.', 'Ir.', 'Ns.', 'Ak.'];
        $kelamin = ['L', 'P'];
        $agama = ['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Budha', 'Konghucu'];
        $darah = ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $unit_kerja_id = UnitKerja::pluck('id')->all();
        $jabatan_id = Jabatan::pluck('id')->all();
        $kompetensi_id = Kompetensi::pluck('id')->all();
        $kelompok_gaji_id = KelompokGaji::pluck('id')->all();
        $ptkp_id = Ptkp::pluck('id')->all();

        for ($i = 0; $i < 50; $i++) {
            $user = User::create([
                'nama' => 'User ' . $i,
                'password' => 'password' . $i,
                'username' => 'username' . $i,
            ]);
            $user->roles()->attach(rand(3, 4));

            $tgl_masuk = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2018), mktime(0, 0, 0, 12, 31, 2024)));
            $tgl_keluar = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2023), mktime(0, 0, 0, 12, 31, 2024)));
            $tgl_lahir = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 1900), mktime(0, 0, 0, 12, 31, 2008)));
            $tgl_str = date('Y-m-d', rand(mktime(0, 0, 0, 1, 1, 2023), mktime(0, 0, 0, 12, 31, 2028)));
            $dataKaryawan = new DataKaryawan([
                'user_id' => $user->id,
                'email' => 'user' . $i . '@example.com',
                'no_rm' => rand(1214, 5000000),
                'no_manulife' => rand(1214, 5000000),
                'tgl_masuk' => Carbon::parse($tgl_masuk),
                'unit_kerja_id' => $unit_kerja_id[array_rand($unit_kerja_id)],
                'jabatan_id' => $jabatan_id[array_rand($jabatan_id)],
                'kompetensi_id' => $kompetensi_id[array_rand($kompetensi_id)],
                'status_karyawan' => $statuses[array_rand($statuses)],
                'tempat_lahir' => $cityborn[array_rand($cityborn)],
                'tgl_lahir' => $tgl_lahir,
                "nik" => rand(1214, 5000000),
                "nik_ktp" => rand(1214, 500000),
                'kelompok_gaji_id' => $kelompok_gaji_id[array_rand($kelompok_gaji_id)],
                'no_rekening' => rand(152368, 500000000),
                'tunjangan_jabatan' => rand(900000, 10000000),
                'tunjangan_fungsional' => rand(900000, 3500000),
                'tunjangan_khusus' => rand(900000, 2500000),
                'tunjangan_lainnya' => rand(900000, 2500000),
                'uang_makan' => rand(900000, 1500000),
                'uang_lembur' => rand(900000, 1500000),
                'ptkp_id' => $ptkp_id[array_rand($ptkp_id)],

                "tgl_keluar" => Carbon::parse($tgl_keluar),
                "no_kk" => rand(1214, 500000000),
                "alamat" => 'missing impossible coach amount welcome here night trail diameter nervous graph outline shinning perfectly try refer classroom climb burn spider grabbed waste little provide',
                "gelar_depan" => $gelar_dpn[array_rand($gelar_dpn)],
                "no_hp" => rand(1214, 500000000),
                "no_bpjsksh" => rand(1214, 500000000),
                "no_bpjsktk" => rand(1214, 500000000),
                "tgl_diangkat" => $tgl_keluar,
                "masa_kerja" => rand(1, 60),
                "npwp" => rand(1214, 500000000),
                "jenis_kelamin" => $kelamin[array_rand($kelamin)],
                "agama" => $agama[array_rand($agama)],
                "golongan_darah" => $darah[array_rand($darah)],
                "tinggi_badan" => rand(10, 300),
                "berat_badan" => rand(10, 200),
                "no_ijasah" => "IJ/VII/" . rand(1214, 500000000),
                "tahun_lulus" => rand(1800, 2017),
                "no_str" => "STR/01/RA/" . rand(1214, 500000),
                "masa_berlaku_str" => $tgl_str,
                "no_sip" => rand(1214, 500000),
                "masa_berlaku_sip" => $tgl_str,
                "tgl_berakhir_pks" => $tgl_keluar,
                "masa_diklat" => rand(1, 10),
            ]);
            $dataKaryawan->save();

            $dataRekamJejak = new TrackRecord([
                'user_id' => $user->id,
                'tgl_masuk' => $tgl_masuk,
                'tgl_keluar' => $tgl_keluar
            ]);
            $dataRekamJejak->save();
        }
    }
}
