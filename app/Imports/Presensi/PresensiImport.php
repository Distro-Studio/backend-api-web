<?php

namespace App\Imports\Presensi;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\DataKaryawan;
use App\Models\LokasiKantor;
use App\Models\StatusPresensi;
use App\Models\KategoriPresensi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PresensiImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $User;
    private $KategoriPresensi;
    public function __construct()
    {
        $this->User = User::select('id', 'nama')->get();
        $this->KategoriPresensi = KategoriPresensi::select('id', 'label')->get();
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:225',
            'nik_ktp' => 'required|numeric',
            'jam_masuk' => 'required|date',
            'jam_keluar' => 'required|date',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama karyawan tidak diperbolehkan kosong.',
            'nama.string' => 'Nama karyawan tidak diperbolehkan mengandung angka.',
            'nama.max' => 'Nama karyawan melebihi batas maksimum panjang karakter.',
            'nik_ktp.required' => 'NIK KTP karyawan tidak diperbolehkan kosong.',
            'nik_ktp.numeric' => 'NIK KTP karyawan tidak diperbolehkan mengandung huruf.',
            'jam_masuk.required' => 'Tanggal presensi masuk tidak diperbolehkan kosong.',
            'jam_masuk.date' => 'Format tanggal presensi masuk tidak sesuai.',
            'jam_keluar.required' => 'Tanggal presensi keluar tidak diperbolehkan kosong.',
            'jam_keluar.date' => 'Format tanggal presensi keluar tidak sesuai.',
        ];
    }

    public function model(array $row)
    {
        $user = $this->User->where('nama', $row['nama'])->first();
        // dd($user);
        if (!$user) {
            throw new \Exception("Nama karyawan {$row['nama']} tidak tersedia dalam database.");
        }

        $data_karyawan = DataKaryawan::where('user_id', $user->id)->where('nik_ktp', $row['nik_ktp'])->first();
        // dd($data_karyawan);
        if (!$data_karyawan) {
            throw new \Exception("NIK KTP dari karyawan {$row['nama']} tidak sesuai dengan database.");
        }

        // Hitung durasi kerja berdasarkan jam masuk dan keluar
        $jam_masuk = Carbon::createFromFormat('d-m-Y H:i:s', $row['jam_masuk']);
        $jam_keluar = Carbon::createFromFormat('d-m-Y H:i:s', $row['jam_keluar']);
        $durasi = $jam_keluar->diffInSeconds($jam_masuk);
        // dd($durasi);

        // Mendapatkan jadwal_id berdasarkan user_id dan range tanggal
        $jadwal = Jadwal::where('user_id', $user->id)
            ->whereDate('tgl_mulai', '<=', $jam_masuk)
            ->whereDate('tgl_selesai', '>=', $jam_masuk)
            ->first();
        // dd($jadwal);
        if (!$jadwal) {
            throw new \Exception("Jadwal tidak ditemukan untuk user ID: {$user->id} pada tanggal: {$row['jam_masuk']}");
        }

        // Mendapatkan shift berdasarkan jadwal
        $shift = $jadwal->shifts;
        // dd($shift);

        // Mendapatkan kategori presensi berdasarkan jam masuk dan shift
        $kategori_presensi_id = $this->getKategoriPresensiId($shift, $jam_masuk);
        // dd($kategori_presensi_id);

        // Ambil lokasi kantor untuk koordinat
        $lokasi_kantor = LokasiKantor::first();
        // dd($lokasi_kantor);

        return new Presensi([
            'user_id' => $user->id,
            'data_karyawan_id' => $data_karyawan->id,
            'jadwal_id' => $jadwal->id, // ambil dari tabel jadwal berdasarkan id karyawan dan tgl hari ini
            'jam_masuk' => $jam_masuk->format('Y-m-d H:i:s'),
            'jam_keluar' => $jam_keluar->format('Y-m-d H:i:s'),
            'durasi' => $durasi,
            'lat' => $lokasi_kantor->lat,
            'long' => $lokasi_kantor->long,
            'latkeluar' => $lokasi_kantor->lat,
            'longkeluar' => $lokasi_kantor->long,
            'kategori_presensi_id' => $kategori_presensi_id,
        ]);
    }

    private function getKategoriPresensiId($shift, $jam_masuk)
    {
        if ($shift->nama == 'Pagi') {
            return ($jam_masuk->hour < 7 || ($jam_masuk->hour == 7 && $jam_masuk->minute == 0)) ?
                $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id :
                $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
        } elseif ($shift->nama == 'Sore') {
            return ($jam_masuk->hour < 17 || ($jam_masuk->hour == 17 && $jam_masuk->minute == 0)) ?
                $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id :
                $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
        } elseif ($shift->nama == 'Malam') {
            return ($jam_masuk->hour < 22 || ($jam_masuk->hour == 22 && $jam_masuk->minute == 0)) ?
                $this->KategoriPresensi->where('label', 'Tepat Waktu')->first()->id :
                $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
        }

        return $this->KategoriPresensi->where('label', 'Terlambat')->first()->id;
    }
}
