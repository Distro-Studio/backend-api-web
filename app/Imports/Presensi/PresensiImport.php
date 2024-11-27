<?php

namespace App\Imports\Presensi;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Models\UnitKerja;
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
    private $UnitKerja;
    private $KategoriPresensi;
    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
        $this->DataKaryawan = DataKaryawan::select('id', 'nik', 'user_id')->get();
        $this->UnitKerja = UnitKerja::select('id', 'nama_unit')->get();
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

    public function model(array $row)
    {
        // Mendapatkan data user berdasarkan nama
        $data_karyawan = $this->DataKaryawan->where('nik', $row['nomor_induk_karyawan'])->first();
        if (!$data_karyawan) {
            throw new \Exception("Karyawan dengan NIK '" . $row['nomor_induk_karyawan'] . "' tidak ditemukan.");
        }

        $unit_kerja = $this->UnitKerja->where('nama_unit', $row['unit_kerja'])->first();
        if (!$unit_kerja) {
            throw new \Exception("Unit kerja '" . $row['unit_kerja'] . "' tidak ditemukan.");
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

        // Menggabungkan tanggal dan waktu untuk mendapatkan format lengkap
        $jam_masuk_full = Carbon::parse($tanggal_masuk->format('Y-m-d') . ' ' . $jam_masuk->format('H:i:s'));
        $jam_keluar_full = Carbon::parse($tanggal_masuk->format('Y-m-d') . ' ' . $jam_keluar->format('H:i:s'));

        // Menangani shift yang melewati tengah malam dengan menambah satu hari pada jam_keluar
        if ($jam_keluar_full->lessThan($jam_masuk_full)) {
            $jam_keluar_full->addDay();
        }

        $durasi = $jam_masuk_full->diffInSeconds($jam_keluar_full);

        // Memeriksa apakah data presensi sudah ada
        $existingPresensi = Presensi::where('user_id', $data_karyawan->user_id)
            ->whereDate('jam_masuk', $jam_masuk_full)
            ->whereDate('jam_keluar', $jam_keluar_full)
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

            if (($kategori_presensi_id ?? $existingPresensi->kategori_presensi_id) == 2) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            }
            return $existingPresensi;
        }

        if ($jenis_karyawan === 'shift') {
            $shifts = Shift::where('unit_kerja_id', $unit_kerja->id)->get();
            if ($shifts->isEmpty()) {
                throw new \Exception("Shift tidak ditemukan untuk unit kerja: {$row['unit_kerja']}.");
            }

            $shift = $shifts->filter(function ($shift) use ($jam_masuk, $jam_keluar) {
                $jam_from = Carbon::parse($shift->jam_from);
                $jam_to = Carbon::parse($shift->jam_to);

                // Cek apakah jam masuk berada dalam toleransi setelah jam_from
                $isJamMasukValid = $jam_masuk->greaterThanOrEqualTo($jam_from->copy()->subMinutes(30))
                    && $jam_masuk->lessThanOrEqualTo($jam_to);

                // Cek apakah jam keluar berada dalam toleransi setelah jam_to
                $isJamKeluarValid = $jam_keluar->greaterThanOrEqualTo($jam_from)
                    && $jam_keluar->lessThanOrEqualTo($jam_to->copy()->addMinutes(30));

                return $isJamMasukValid && $isJamKeluarValid;
            })->first();
            if (!$shift) {
                throw new \Exception("Shift tidak ditemukan untuk waktu jam masuk: {$row['jam_masuk']}");
            }

            // Mendapatkan kategori presensi berdasarkan jam masuk dan shift
            $kategori_presensi_id = $this->getKategoriPresensiId($shift, $jam_masuk);

            // Jika kategori presensi adalah 'Terlambat', perbarui status_reward_presensi
            if ($kategori_presensi_id == 2) {
                $data_karyawan->update(['status_reward_presensi' => false]);
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

            // Mengembalikan instance dari model Presensi dengan data yang sesuai
            return new Presensi([
                'user_id' => $data_karyawan->user_id,
                'data_karyawan_id' => $data_karyawan->id,
                'jadwal_id' => $jadwal->id,
                'jam_masuk' => $jam_masuk_full->format('Y-m-d H:i:s'),
                'jam_keluar' => $jam_keluar_full->format('Y-m-d H:i:s'),
                'durasi' => $durasi,
                'lat' => $lokasi_kantor->lat,
                'long' => $lokasi_kantor->long,
                'latkeluar' => $lokasi_kantor->lat,
                'longkeluar' => $lokasi_kantor->long,
                'kategori_presensi_id' => $kategori_presensi_id,
            ]);
        } elseif ($jenis_karyawan === 'non-shift') {
            $hariNama = $tanggal_masuk->format('l'); // Nama hari dalam Inggris
            // dd($hariNama);

            $hariNamaIndonesia = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ][$hariNama] ?? 'Senin';
            $non_shift = NonShift::where('nama', $hariNamaIndonesia)->first();
            // dd($non_shift);

            if (!$non_shift) {
                throw new \Exception("Jadwal Non-shift tidak ditemukan untuk hari: {$hariNamaIndonesia}");
            }

            // Mendapatkan kategori presensi berdasarkan jam masuk dan non-shift
            $kategori_presensi_id = $this->getKategoriPresensiIdNonShift($non_shift, $jam_masuk);

            // Jika kategori presensi adalah 'Terlambat', perbarui status_reward_presensi
            if ($kategori_presensi_id == 2) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            }

            // Mengembalikan instance dari model Presensi dengan data yang sesuai
            return new Presensi([
                'user_id' => $data_karyawan->user_id,
                'data_karyawan_id' => $data_karyawan->id,
                'jadwal_id' => null,
                'jam_masuk' => $jam_masuk_full->format('Y-m-d H:i:s'),
                'jam_keluar' => $jam_keluar_full->format('Y-m-d H:i:s'),
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

        if ($jam_masuk->greaterThan($jam_from)) {
            return $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
        }

        return $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id;
    }

    private function getKategoriPresensiIdNonShift($non_shift, $jam_masuk)
    {
        $jam_from = Carbon::parse($non_shift->jam_from);

        if ($jam_masuk->greaterThan($jam_from)) {
            return $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
        }

        return $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id;
    }
}
