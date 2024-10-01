<?php

namespace App\Imports\Presensi;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Models\DataKaryawan;
use App\Models\LokasiKantor;
use App\Models\KategoriPresensi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PresensiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $User;
    private $DataKaryawan;
    private $KategoriPresensi;
    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
        $this->DataKaryawan = DataKaryawan::select('id', 'nik', 'user_id')->get();
        $this->KategoriPresensi = KategoriPresensi::select('id', 'label')->get();
    }

    public function rules(): array
    {
        return [
            'nomor_induk_karyawan' => 'required',
            'jam_masuk' => 'required',
            'jam_keluar' => 'required',
            'tanggal_masuk' => 'required',
            'jenis_karyawan' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nomor_induk_karyawan.required' => 'Nomor induk karyawan tidak diperbolehkan kosong.',
            'jam_masuk.required' => 'Jam presensi masuk tidak diperbolehkan kosong.',
            'jam_keluar.required' => 'Jam presensi keluar tidak diperbolehkan kosong.',
            'tanggal_masuk.required' => 'Tanggal presensi masuk tidak diperbolehkan kosong.',
            'jenis_karyawan.required' => 'Jenis karyawan tidak diperbolehkan kosong, dan hanya dapat diisi shift atau non-shift.',
        ];
    }

    // public function model(array $row)
    // {
    //     // Mendapatkan data user berdasarkan nama
    //     $user = $this->User->where('nama', $row['nama'])->first();
    //     if (!$user) {
    //         throw new \Exception("Nama karyawan '{$row['nama']}' tidak tersedia dalam database.");
    //     }

    //     // Mendapatkan data karyawan berdasarkan user_id dan NIK KTP
    //     $data_karyawan = DataKaryawan::where('user_id', $user->id)
    //         ->where('nik_ktp', $row['nik_ktp'])
    //         ->first();
    //     if (!$data_karyawan) {
    //         throw new \Exception("NIK KTP dari karyawan '{$row['nama']}' tidak sesuai dengan database.");
    //     }

    //     // Memeriksa jenis karyawan
    //     $jenis_karyawan = strtolower($row['jenis_karyawan']);
    //     $tanggal_masuk = Carbon::createFromFormat('d-m-Y', $row['tanggal_masuk']);

    //     if ($jenis_karyawan === 'shift') {
    //         // Parsing waktu jam masuk dan jam keluar
    //         $jam_masuk = Carbon::createFromFormat('H:i:s', $row['jam_masuk']);
    //         $jam_keluar = Carbon::createFromFormat('H:i:s', $row['jam_keluar']);
    //         $durasi = $jam_keluar->diffInSeconds($jam_masuk);

    //         // Mendapatkan shift yang cocok berdasarkan jam masuk
    //         $shift = Shift::all()->filter(function ($shift) use ($jam_masuk) {
    //             $jam_from = Carbon::parse($shift->jam_from);
    //             $jam_to = Carbon::parse($shift->jam_to);

    //             if ($jam_from->lessThan($jam_to)) {
    //                 return $jam_masuk->between($jam_from, $jam_to);
    //             } else { // Untuk shift malam yang melewati tengah malam
    //                 return $jam_masuk->greaterThanOrEqualTo($jam_from) || $jam_masuk->lessThanOrEqualTo($jam_to);
    //             }
    //         })->first();

    //         if (!$shift) {
    //             throw new \Exception("Shift tidak ditemukan untuk waktu jam masuk: {$row['jam_masuk']}");
    //         }

    //         // Mendapatkan jadwal berdasarkan user, shift, dan tanggal masuk dari excel
    //         $jadwal = Jadwal::where('user_id', $user->id)
    //             ->where('shift_id', $shift->id)
    //             ->whereDate('tgl_mulai', '<=', $tanggal_masuk)
    //             ->whereDate('tgl_selesai', '>=', $tanggal_masuk)
    //             ->first();

    //         // Jika jadwal tidak ditemukan, buat jadwal baru
    //         if (!$jadwal) {
    //             $jadwal = Jadwal::create([
    //                 'user_id' => $user->id,
    //                 'shift_id' => $shift->id,
    //                 'tgl_mulai' => $tanggal_masuk->format('Y-m-d'),
    //                 'tgl_selesai' => $tanggal_masuk->format('Y-m-d')
    //             ]);
    //         }

    //         // Mendapatkan kategori presensi berdasarkan jam masuk dan shift
    //         $kategori_presensi_id = $this->getKategoriPresensiId($shift, $jam_masuk);
    //     } elseif ($jenis_karyawan === 'non-shift') {
    //         // Parsing waktu jam masuk dan jam keluar
    //         $jam_masuk = Carbon::createFromFormat('H:i:s', $row['jam_masuk']);
    //         $jam_keluar = Carbon::createFromFormat('H:i:s', $row['jam_keluar']);
    //         $durasi = $jam_keluar->diffInSeconds($jam_masuk);

    //         // Mendapatkan non-shift yang cocok berdasarkan jam masuk
    //         $non_shift = NonShift::all()->filter(function ($non_shift) use ($jam_masuk) {
    //             $jam_from = Carbon::parse($non_shift->jam_from);
    //             $jam_to = Carbon::parse($non_shift->jam_to);

    //             if ($jam_from->lessThan($jam_to)) {
    //                 return $jam_masuk->between($jam_from, $jam_to);
    //             } else { // Untuk non-shift yang melewati tengah malam
    //                 return $jam_masuk->greaterThanOrEqualTo($jam_from) || $jam_masuk->lessThanOrEqualTo($jam_to);
    //             }
    //         })->first();

    //         if (!$non_shift) {
    //             throw new \Exception("Non-shift tidak ditemukan untuk waktu jam masuk: {$row['jam_masuk']}");
    //         }

    //         // Mendapatkan jadwal berdasarkan user dan tanggal masuk dari excel
    //         $jadwal = Jadwal::where('user_id', $user->id)
    //             ->whereDate('tgl_mulai', '<=', $tanggal_masuk)
    //             ->whereDate('tgl_selesai', '>=', $tanggal_masuk)
    //             ->first();

    //         // Jika jadwal tidak ditemukan, buat jadwal baru
    //         if (!$jadwal) {
    //             $jadwal = Jadwal::create([
    //                 'user_id' => $user->id,
    //                 'shift_id' => 0,
    //                 'tgl_mulai' => $tanggal_masuk->format('Y-m-d'),
    //                 'tgl_selesai' => $tanggal_masuk->format('Y-m-d')
    //             ]);
    //         }

    //         // Mendapatkan kategori presensi berdasarkan jam masuk dan non-shift
    //         $kategori_presensi_id = $this->getKategoriPresensiIdNonShift($non_shift, $jam_masuk);
    //     } else {
    //         throw new \Exception("Jenis karyawan '{$row['jenis_karyawan']}' tidak dikenal.");
    //     }

    //     // Ambil lokasi kantor untuk koordinat
    //     $lokasi_kantor = LokasiKantor::first();

    //     // Mengembalikan instance dari model Presensi dengan data yang sesuai
    //     return new Presensi([
    //         'user_id' => $user->id,
    //         'data_karyawan_id' => $data_karyawan->id,
    //         'jadwal_id' => $jadwal->id,
    //         'jam_masuk' => $jam_masuk->format('H:i:s'),
    //         'jam_keluar' => $jam_keluar->format('H:i:s'),
    //         'durasi' => $durasi,
    //         'lat' => $lokasi_kantor->lat,
    //         'long' => $lokasi_kantor->long,
    //         'latkeluar' => $lokasi_kantor->lat,
    //         'longkeluar' => $lokasi_kantor->long,
    //         'kategori_presensi_id' => $kategori_presensi_id,
    //     ]);
    // }

    // private function getKategoriPresensiId($shift, $jam_masuk)
    // {
    //     $jam_from = Carbon::parse($shift->jam_from);

    //     if ($jam_masuk->greaterThanOrEqualTo($jam_from)) {
    //         return $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
    //     } else {
    //         return $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id;
    //     }
    // }

    // private function getKategoriPresensiIdNonShift($non_shift, $jam_masuk)
    // {
    //     $jam_from = Carbon::parse($non_shift->jam_from);

    //     if ($jam_masuk->greaterThanOrEqualTo($jam_from)) {
    //         return $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
    //     } else {
    //         return $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id;
    //     }
    // }

    public function model(array $row)
    {
        // Mendapatkan data user berdasarkan nama
        $data_karyawan = $this->DataKaryawan->where('nik', $row['nomor_induk_karyawan'])->first();
        if (!$data_karyawan) {
            throw new \Exception("Karyawan dengan NIK '" . $row['nomor_induk_karyawan'] . "' tidak ditemukan.");
        }

        // Mendapatkan data lokasi kantor
        $lokasi_kantor = LokasiKantor::first();
        if (!$lokasi_kantor) {
            throw new \Exception("Data lokasi kantor tidak ditemukan.");
        }

        // Memeriksa jenis karyawan
        $jenis_karyawan = strtolower($row['jenis_karyawan']);
        $tanggal_masuk = Carbon::createFromFormat('d-m-Y', $row['tanggal_masuk']);
        $jam_masuk = Carbon::createFromFormat('H:i:s', $row['jam_masuk']);
        $jam_keluar = Carbon::createFromFormat('H:i:s', $row['jam_keluar']);
        $durasi = $jam_keluar->diffInSeconds($jam_masuk);

        // Memeriksa apakah data presensi sudah ada
        $existingPresensi = Presensi::where('user_id', $data_karyawan->user_id)
            ->where('jam_masuk', $jam_masuk->format('H:i:s'))
            ->where('jam_keluar', $jam_keluar->format('H:i:s'))
            ->first();

        if ($existingPresensi) {
            // Jika data presensi sudah ada, lakukan update
            $existingPresensi->update([
                'durasi' => $durasi,
                'lat' => $lokasi_kantor->lat,
                'long' => $lokasi_kantor->long,
                'latkeluar' => $lokasi_kantor->lat,
                'longkeluar' => $lokasi_kantor->long,
                'kategori_presensi_id' => $kategori_presensi_id ?? $existingPresensi->kategori_presensi_id,
            ]);
            return $existingPresensi;
        }

        if ($jenis_karyawan === 'shift') {
            // Mendapatkan shift yang cocok berdasarkan jam masuk
            $shift = Shift::all()->filter(function ($shift) use ($jam_masuk) {
                $jam_from = Carbon::parse($shift->jam_from);
                $jam_to = Carbon::parse($shift->jam_to);

                if ($jam_from->lessThan($jam_to)) {
                    return $jam_masuk->between($jam_from, $jam_to);
                } else { // Untuk shift malam yang melewati tengah malam
                    return $jam_masuk->greaterThanOrEqualTo($jam_from) || $jam_masuk->lessThanOrEqualTo($jam_to);
                }
            })->first();

            if (!$shift) {
                throw new \Exception("Shift tidak ditemukan untuk waktu jam masuk: {$row['jam_masuk']}");
            }

            // Mendapatkan jadwal berdasarkan user, shift, dan tanggal masuk dari excel
            $jadwal = Jadwal::where('user_id', $data_karyawan->user_id)
                ->where('shift_id', $shift->id)
                ->whereDate('tgl_mulai', '<=', $tanggal_masuk)
                ->whereDate('tgl_selesai', '>=', $tanggal_masuk)
                ->first();

            // Jika jadwal tidak ditemukan, buat jadwal baru
            if (!$jadwal) {
                $jadwal = Jadwal::create([
                    'user_id' => $data_karyawan->user_id,
                    'shift_id' => $shift->id,
                    'tgl_mulai' => $tanggal_masuk->format('Y-m-d'),
                    'tgl_selesai' => $tanggal_masuk->format('Y-m-d')
                ]);
            }

            // Mendapatkan kategori presensi berdasarkan jam masuk dan shift
            $kategori_presensi_id = $this->getKategoriPresensiId($shift, $jam_masuk);

            // Mengembalikan instance dari model Presensi dengan data yang sesuai
            return new Presensi([
                'user_id' => $data_karyawan->user_id,
                'data_karyawan_id' => $data_karyawan->id,
                'jadwal_id' => $jadwal->id,
                'jam_masuk' => $jam_masuk->format('H:i:s'),
                'jam_keluar' => $jam_keluar->format('H:i:s'),
                'durasi' => $durasi,
                'lat' => $lokasi_kantor->lat,
                'long' => $lokasi_kantor->long,
                'latkeluar' => $lokasi_kantor->lat,
                'longkeluar' => $lokasi_kantor->long,
                'kategori_presensi_id' => $kategori_presensi_id,
            ]);
        } elseif ($jenis_karyawan === 'non-shift') {
            // Mendapatkan non-shift yang cocok berdasarkan jam masuk
            $non_shift = NonShift::all()->filter(function ($non_shift) use ($jam_masuk) {
                $jam_from = Carbon::parse($non_shift->jam_from);
                $jam_to = Carbon::parse($non_shift->jam_to);

                if ($jam_from->lessThan($jam_to)) {
                    return $jam_masuk->between($jam_from, $jam_to);
                } else { // Untuk non-shift yang melewati tengah malam
                    return $jam_masuk->greaterThanOrEqualTo($jam_from) || $jam_masuk->lessThanOrEqualTo($jam_to);
                }
            })->first();

            if (!$non_shift) {
                throw new \Exception("Non-shift tidak ditemukan untuk waktu jam masuk: {$row['jam_masuk']}");
            }

            // Mendapatkan kategori presensi berdasarkan jam masuk dan non-shift
            $kategori_presensi_id = $this->getKategoriPresensiIdNonShift($non_shift, $jam_masuk);

            // Mengembalikan instance dari model Presensi dengan data yang sesuai
            return new Presensi([
                'user_id' => $data_karyawan->user_id,
                'data_karyawan_id' => $data_karyawan->id,
                'jadwal_id' => null,
                'jam_masuk' => $jam_masuk->format('H:i:s'),
                'jam_keluar' => $jam_keluar->format('H:i:s'),
                'durasi' => $durasi,
                'lat' => $lokasi_kantor->lat,
                'long' => $lokasi_kantor->long,
                'latkeluar' => $lokasi_kantor->lat,
                'longkeluar' => $lokasi_kantor->long,
                'kategori_presensi_id' => $kategori_presensi_id,
            ]);
        } else {
            throw new \Exception("Jenis karyawan '{$row['jenis_karyawan']}' tidak dikenal.");
        }
    }

    private function getKategoriPresensiId($shift, $jam_masuk)
    {
        $jam_from = Carbon::parse($shift->jam_from);

        if ($jam_masuk->greaterThanOrEqualTo($jam_from)) {
            return $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
        } else {
            return $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id;
        }
    }

    private function getKategoriPresensiIdNonShift($non_shift, $jam_masuk)
    {
        $jam_from = Carbon::parse($non_shift->jam_from);

        if ($jam_masuk->greaterThanOrEqualTo($jam_from)) {
            return $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
        } else {
            return $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id;
        }
    }
}
