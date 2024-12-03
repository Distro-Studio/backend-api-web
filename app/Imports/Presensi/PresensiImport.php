<?php

namespace App\Imports\Presensi;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\User;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\NonShift;
use App\Models\Presensi;
use App\Models\UnitKerja;
use App\Models\RiwayatIzin;
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
            $existingPresensi->update([
                'durasi' => $durasi,
                'lat' => $lokasi_kantor->lat,
                'long' => $lokasi_kantor->long,
                'latkeluar' => $lokasi_kantor->lat,
                'longkeluar' => $lokasi_kantor->long,
                'kategori_presensi_id' => $kategori_presensi_id ?? $existingPresensi->kategori_presensi_id,
            ]);

            // Pengecekan izin
            $izin = $this->cekSebulanIzin($data_karyawan->user_id, $jam_masuk_full, $jam_keluar_full);
            if ($izin) {
                $data_karyawan->update(['status_reward_presensi' => false]);
                return $existingPresensi;
            }

            // Pengecekan cuti
            $cekCutiSebulan = $this->cekSebulanCuti($data_karyawan->user_id, $jam_masuk_full, $jam_keluar_full);
            if ($cekCutiSebulan) {
                $data_karyawan->update(['status_reward_presensi' => false]);
                return $existingPresensi;
            }

            // Pengecekan kategori presensi
            if (($kategori_presensi_id ?? $existingPresensi->kategori_presensi_id) == 2) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            } else {
                // Cek jika tepat waktu
                if (($kategori_presensi_id ?? $existingPresensi->kategori_presensi_id) == 1) {
                    $cekKehadiranSebulan = $this->cekSebulanPresensi($data_karyawan->id, $jam_masuk_full, $jam_keluar_full);
                    if ($cekKehadiranSebulan) {
                        $data_karyawan->update(['status_reward_presensi' => true]);
                    } else {
                        $data_karyawan->update(['status_reward_presensi' => false]);
                    }
                }
            }

            return $existingPresensi;
        }

        if ($jenis_karyawan === 'shift') {
            if (empty($row['nama_shift'])) {
                throw new \Exception("Nama shift tidak ditemukan untuk karyawan dengan NIK: " . $row['nomor_induk_karyawan']);
            }

            $shift = Shift::where('unit_kerja_id', $unit_kerja->id)
                ->where('nama', $row['nama_shift'])
                ->first();
            if (!$shift) {
                throw new \Exception("Shift dengan nama '{$row['nama_shift']}' tidak ditemukan untuk unit kerja: {$row['unit_kerja']}.");
            }

            // Mendapatkan kategori presensi berdasarkan jam masuk dan shift
            $kategori_presensi_id = $this->getKategoriPresensiId($shift, $jam_masuk);
            if ($kategori_presensi_id == 2 || $this->presensiMendahului($shift->jam_to, $jam_keluar)) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            }

            $cekIzinSebulan = $this->cekSebulanIzin($data_karyawan->user_id, $jam_masuk_full, $jam_keluar_full);
            if ($cekIzinSebulan) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            }

            $cekCutiSebulan = $this->cekSebulanCuti($data_karyawan->user_id, $jam_masuk_full, $jam_keluar_full);
            if ($cekCutiSebulan) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            } else {
                if ($kategori_presensi_id == 1) {
                    $isRewardPresensi = $this->cekSebulanPresensi($data_karyawan->id, $jam_masuk_full, $jam_keluar_full);
                    if ($isRewardPresensi) {
                        $data_karyawan->update(['status_reward_presensi' => true]);
                    } else {
                        $data_karyawan->update(['status_reward_presensi' => false]);
                    }
                }
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

            return new Presensi([
                'user_id' => $data_karyawan->user_id,
                'data_karyawan_id' => $data_karyawan->id,
                'jadwal_id' => $jadwal->id,
                'jam_masuk' => $jam_masuk_full,
                'jam_keluar' => $jam_keluar_full,
                'durasi' => $durasi,
                'lat' => $lokasi_kantor->lat,
                'long' => $lokasi_kantor->long,
                'latkeluar' => $lokasi_kantor->lat,
                'longkeluar' => $lokasi_kantor->long,
                'kategori_presensi_id' => $kategori_presensi_id,
            ]);
        } elseif ($jenis_karyawan === 'non-shift') {
            $hariNama = $tanggal_masuk->format('l');
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
            if ($kategori_presensi_id == 2 || $this->presensiMendahului($non_shift->jam_to, $jam_keluar)) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            }

            $cekIzinSebulan = $this->cekSebulanIzin($data_karyawan->user_id, $jam_masuk_full, $jam_keluar_full);
            if ($cekIzinSebulan) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            }

            $cekCutiSebulan = $this->cekSebulanCuti($data_karyawan->user_id, $jam_masuk_full, $jam_keluar_full);
            if ($cekCutiSebulan) {
                $data_karyawan->update(['status_reward_presensi' => false]);
            } else {
                if ($kategori_presensi_id == 1) {
                    $isRewardPresensi = $this->cekSebulanPresensi($data_karyawan->id, $jam_masuk_full, $jam_keluar_full);
                    if ($isRewardPresensi) {
                        $data_karyawan->update(['status_reward_presensi' => true]);
                    } else {
                        $data_karyawan->update(['status_reward_presensi' => false]);
                    }
                }
            }

            // Mengembalikan instance dari model Presensi dengan data yang sesuai
            return new Presensi([
                'user_id' => $data_karyawan->user_id,
                'data_karyawan_id' => $data_karyawan->id,
                'jadwal_id' => null,
                'jam_masuk' => $jam_masuk_full,
                'jam_keluar' => $jam_keluar_full,
                'durasi' => $durasi,
                'lat' => $lokasi_kantor->lat,
                'long' => $lokasi_kantor->long,
                'latkeluar' => $lokasi_kantor->lat,
                'longkeluar' => $lokasi_kantor->long,
                'kategori_presensi_id' => $kategori_presensi_id,
            ]);
        } else {
            throw new \Exception("Jenis karyawan '{$row['jenis_karyawan']}' tidak dikenal. Pastikan yang anda masukkan adalah 'shift' atau 'non-shift'");
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

    private function presensiMendahului($jam_to, $jam_keluar)
    {
        $jam_to = Carbon::parse($jam_to);
        return $jam_keluar->lessThan($jam_to);
    }

    private function cekSebulanPresensi($data_karyawan_id, $jam_masuk_full, $jam_keluar_full)
    {
        $monthMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->month;
        $yearMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->year;

        $monthKeluar = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->month;
        $yearKeluar = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->year;

        if ($monthMasuk !== $monthKeluar || $yearMasuk !== $yearKeluar) {
            $currentMonth = $monthMasuk;
            $currentYear = $yearMasuk;
        } else {
            $currentMonth = $monthMasuk;
            $currentYear = $yearMasuk;
        }

        $jumlahHariBulanIni = Carbon::createFromDate($currentYear, $currentMonth, 1)->daysInMonth;

        $presensi = Presensi::where('data_karyawan_id', $data_karyawan_id)
            ->whereMonth('jam_masuk', $currentMonth)
            ->whereYear('jam_masuk', $currentYear)
            ->where('kategori_presensi_id', 1)
            ->get();
        if ($presensi->count() == $jumlahHariBulanIni) {
            return true;
        }

        return false;
    }

    private function cekSebulanCuti($user_id, $jam_masuk_full, $jam_keluar_full)
    {
        $monthMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->month;
        $yearMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->year;

        $monthKeluar = Carbon::parse($jam_keluar_full)->month;
        $yearKeluar = Carbon::parse($jam_keluar_full)->year;

        if ($monthMasuk !== $monthKeluar || $yearMasuk !== $yearKeluar) {
            $currentMonth = $monthMasuk;
            $currentYear = $yearMasuk;
        } else {
            $currentMonth = $monthMasuk;
            $currentYear = $yearMasuk;
        }

        $startOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m-d');
        $endOfMonth = Carbon::createFromDate($currentYear, $currentMonth, Carbon::now()->daysInMonth)->format('Y-m-d');

        $cuti = Cuti::where('user_id', $user_id)
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereRaw('STR_TO_DATE(tgl_from, "%d-%m-%Y") BETWEEN ? AND ?', [$startOfMonth, $endOfMonth])
                    ->orWhereRaw('STR_TO_DATE(tgl_to, "%d-%m-%Y") BETWEEN ? AND ?', [$startOfMonth, $endOfMonth]);
            })
            ->where('status_cuti_id', 4)
            ->whereHas('tipe_cutis', function ($query) {
                $query->where('cuti_administratif', 0);
            })
            ->exists();

        return $cuti;
    }

    private function cekSebulanIzin($user_id, $jam_masuk_full, $jam_keluar_full)
    {
        $monthMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->month;
        $yearMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->year;

        $monthKeluar = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->month;
        $yearKeluar = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->year;

        if ($monthMasuk !== $monthKeluar || $yearMasuk !== $yearKeluar) {
            $currentMonth = $monthMasuk;
            $currentYear = $yearMasuk;
        } else {
            $currentMonth = $monthMasuk;
            $currentYear = $yearMasuk;
        }

        $izin = RiwayatIzin::where('user_id', $user_id)
            ->whereMonth('tgl_izin', $currentMonth)
            ->whereYear('tgl_izin', $currentYear)
            ->where('status_izin_id', 2)
            ->exists();

        return $izin;
    }
}
