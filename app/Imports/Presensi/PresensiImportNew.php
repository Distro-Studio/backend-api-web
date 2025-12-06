<?php

namespace App\Imports\Presensi;

use Carbon\Carbon;
use App\Models\Cuti;
use App\Models\Shift;
use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\RiwayatIzin;
use App\Models\DataKaryawan;
use App\Models\LokasiKantor;
use App\Models\KategoriPresensi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PresensiImportNew implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    private $DataKaryawan;
    private $KategoriPresensi;
    public function __construct()
    {
        $this->DataKaryawan = DataKaryawan::select('id', 'nik', 'user_id')->get();
        $this->KategoriPresensi = KategoriPresensi::select('id', 'label')->get();
    }

    public function rules(): array
    {
        return [
            'nik'            => 'required',
            'jam_masuk'      => 'required|date_format:H:i:s',
            'jam_keluar'     => 'required|date_format:H:i:s',
            'tanggal_masuk'  => 'required|date_format:d-m-Y',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nik.required'               => 'NIK karyawan tidak diperbolehkan kosong.',
            'jam_masuk.required'         => 'Jam presensi masuk tidak diperbolehkan kosong.',
            'jam_masuk.date_format'      => 'Format jam presensi masuk harus H:i:s.',
            'jam_keluar.required'        => 'Jam presensi keluar tidak diperbolehkan kosong.',
            'jam_keluar.date_format'     => 'Format jam presensi keluar harus H:i:s.',
            'tanggal_masuk.required'     => 'Tanggal presensi masuk tidak diperbolehkan kosong.',
            'tanggal_masuk.date_format'  => 'Format tanggal presensi masuk harus d-m-Y.',
        ];
    }

    public function model(array $row)
    {
        // 1. Cari karyawan berdasarkan NIK
        $dataKaryawan = $this->findDataKaryawanByNik($row['nik']);

        if (!$dataKaryawan) {
            throw new \Exception("Karyawan dengan NIK '{$row['nik']}' tidak ditemukan.");
        }

        // 2. Parsing tanggal & jam dari Excel
        $tanggalMasuk   = Carbon::createFromFormat('d-m-Y', $row['tanggal_masuk']);
        $jamMasukTime   = Carbon::createFromFormat('H:i:s', $row['jam_masuk']);
        $jamKeluarTime  = Carbon::createFromFormat('H:i:s', $row['jam_keluar']);

        // Gabungkan tanggal + jam
        $jamMasukFull   = Carbon::parse($tanggalMasuk->format('Y-m-d') . ' ' . $jamMasukTime->format('H:i:s'));
        $jamKeluarFull  = Carbon::parse($tanggalMasuk->format('Y-m-d') . ' ' . $jamKeluarTime->format('H:i:s'));

        // Kalau jam keluar lewat tengah malam → tambah 1 hari
        if ($jamKeluarFull->lessThan($jamMasukFull)) {
            $jamKeluarFull->addDay();
        }

        $durasi      = $jamMasukFull->diffInSeconds($jamKeluarFull);
        $tanggalYmd  = $tanggalMasuk->format('Y-m-d');

        // 3. Cari jadwal berdasarkan user + tanggal_masuk
        $jadwal = Jadwal::where('user_id', $dataKaryawan->user_id)
            ->whereDate('tgl_mulai', '<=', $tanggalYmd)
            ->where(function ($q) use ($tanggalYmd) {
                $q->whereNull('tgl_selesai')
                    ->orWhereDate('tgl_selesai', '>=', $tanggalYmd);
            })
            ->first();

        if (!$jadwal) {
            throw new \Exception(
                "Jadwal untuk NIK '{$row['nik']}' pada tanggal " .
                    $tanggalMasuk->format('d-m-Y') . " tidak ditemukan."
            );
        }

        // 4. Ambil shift dari jadwal
        $shift = null;
        if (!empty($jadwal->shift_id)) {
            $shift = Shift::find($jadwal->shift_id);
        }

        if (!$shift) {
            throw new \Exception("Shift untuk jadwal ID {$jadwal->id} tidak ditemukan.");
        }

        // 5. Tentukan kategori presensi (Tepat Waktu / Terlambat)
        $kategoriPresensiId = $this->getKategoriPresensiIdByShift($shift, $jamMasukTime);

        // 6. Ambil lokasi kantor (opsional, boleh kosong)
        $lokasiKantor = LokasiKantor::first();

        // 7. Cek apakah presensi untuk tanggal tsb sudah ada
        $existingPresensi = Presensi::where('user_id', $dataKaryawan->user_id)
            ->whereDate('jam_masuk', $tanggalYmd)
            ->first();

        if ($existingPresensi) {
            // Update presensi yang sudah ada
            $existingPresensi->update([
                'data_karyawan_id'     => $dataKaryawan->id,
                'jadwal_id'            => $jadwal->id,
                'jam_masuk'            => $jamMasukFull,
                'jam_keluar'           => $jamKeluarFull,
                'durasi'               => $durasi,
                'lat'                  => $lokasiKantor->lat ?? null,
                'long'                 => $lokasiKantor->long ?? null,
                'latkeluar'            => $lokasiKantor->lat ?? null,
                'longkeluar'           => $lokasiKantor->long ?? null,
                'kategori_presensi_id' => $kategoriPresensiId,
            ]);

            // 8. Hitung & update status_reward_presensi
            $statusReward = $this->hitungStatusRewardPresensi(
                $dataKaryawan,
                $jamMasukFull,
                $jamKeluarFull,
                $kategoriPresensiId,
                $shift,
                $jamKeluarTime
            );

            $dataKaryawan->update([
                'status_reward_presensi' => $statusReward,
            ]);

            return $existingPresensi;
        }

        // 9. Jika belum ada presensi → buat baru
        $statusReward = $this->hitungStatusRewardPresensi(
            $dataKaryawan,
            $jamMasukFull,
            $jamKeluarFull,
            $kategoriPresensiId,
            $shift,
            $jamKeluarTime
        );

        $dataKaryawan->update([
            'status_reward_presensi' => $statusReward,
        ]);

        return new Presensi([
            'user_id'            => $dataKaryawan->user_id,
            'data_karyawan_id'   => $dataKaryawan->id,
            'jadwal_id'          => $jadwal->id,
            'jam_masuk'          => $jamMasukFull,
            'jam_keluar'         => $jamKeluarFull,
            'durasi'             => $durasi,
            'lat'                => $lokasiKantor->lat ?? null,
            'long'               => $lokasiKantor->long ?? null,
            'latkeluar'          => $lokasiKantor->lat ?? null,
            'longkeluar'         => $lokasiKantor->long ?? null,
            'kategori_presensi_id' => $kategoriPresensiId,
        ]);
    }

    private function hitungStatusRewardPresensi(
        DataKaryawan $dataKaryawan,
        Carbon $jamMasukFull,
        Carbon $jamKeluarFull,
        int $kategoriPresensiId,
        ?Shift $shift,
        Carbon $jamKeluarTime
    ): bool {
        // Ambil id kategori Tepat Waktu & Terlambat
        $kategoriTepat     = $this->KategoriPresensi->firstWhere('label', 'Tepat Waktu');
        $kategoriTerlambat = $this->KategoriPresensi->firstWhere('label', 'Terlambat');

        // 1. Kalau ada izin sebulan → tidak dapat reward
        if ($this->cekSebulanIzin($dataKaryawan->user_id, $jamMasukFull, $jamKeluarFull)) {
            return false;
        }

        // 2. Kalau ada cuti sebulan (non administratif) → tidak dapat reward
        if ($this->cekSebulanCuti($dataKaryawan->user_id, $jamMasukFull, $jamKeluarFull)) {
            return false;
        }

        // 3. Kalau kategori presensi = Terlambat → tidak dapat reward
        if ($kategoriTerlambat && $kategoriPresensiId === $kategoriTerlambat->id) {
            return false;
        }

        // 4. Kalau pulang sebelum jam_to shift → tidak dapat reward
        if ($shift && $this->presensiMendahului($shift->jam_to, $jamKeluarTime)) {
            return false;
        }

        // 5. Kalau kategori Tepat Waktu → cek kehadiran 1 bulan penuh
        if ($kategoriTepat && $kategoriPresensiId === $kategoriTepat->id) {
            return $this->cekSebulanPresensi(
                $dataKaryawan->id,
                $jamMasukFull,
                $jamKeluarFull
            );
        }

        // Default: tidak dapat reward
        return false;
    }

    private function getKategoriPresensiIdByShift(Shift $shift, Carbon $jamMasuk)
    {
        $jamFrom = Carbon::parse($shift->jam_from);

        // Jika jam masuk > jam_from → Terlambat, selain itu Tepat Waktu
        $label = $jamMasuk->greaterThan($jamFrom) ? 'Terlambat' : 'Tepat Waktu';

        $kategori = $this->KategoriPresensi->firstWhere('label', $label);

        if (!$kategori) {
            throw new \Exception("Kategori presensi dengan label '{$label}' tidak ditemukan di tabel kategori_presensis.");
        }

        return $kategori->id;
    }

    private function presensiMendahului($jam_to, Carbon $jamKeluar)
    {
        $jamTo = Carbon::parse($jam_to);
        return $jamKeluar->lessThan($jamTo);
    }

    private function cekSebulanPresensi($data_karyawan_id, $jam_masuk_full, $jam_keluar_full)
    {
        $monthMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->month;
        $yearMasuk  = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->year;

        $monthKeluar = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->month;
        $yearKeluar  = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->year;

        if ($monthMasuk !== $monthKeluar || $yearMasuk !== $yearKeluar) {
            $currentMonth = $monthMasuk;
            $currentYear  = $yearMasuk;
        } else {
            $currentMonth = $monthMasuk;
            $currentYear  = $yearMasuk;
        }

        $jumlahHariBulanIni = Carbon::createFromDate($currentYear, $currentMonth, 1)->daysInMonth;

        $presensi = Presensi::where('data_karyawan_id', $data_karyawan_id)
            ->whereMonth('jam_masuk', $currentMonth)
            ->whereYear('jam_masuk', $currentYear)
            ->where('kategori_presensi_id', 1) // 1 = Tepat Waktu
            ->get();

        return $presensi->count() == $jumlahHariBulanIni;
    }

    private function cekSebulanCuti($user_id, $jam_masuk_full, $jam_keluar_full)
    {
        $monthMasuk = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->month;
        $yearMasuk  = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->year;

        $monthKeluar = Carbon::parse($jam_keluar_full)->month;
        $yearKeluar  = Carbon::parse($jam_keluar_full)->year;

        if ($monthMasuk !== $monthKeluar || $yearMasuk !== $yearKeluar) {
            $currentMonth = $monthMasuk;
            $currentYear  = $yearMasuk;
        } else {
            $currentMonth = $monthMasuk;
            $currentYear  = $yearMasuk;
        }

        $startOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m-d');
        $endOfMonth   = Carbon::createFromDate($currentYear, $currentMonth, Carbon::now()->daysInMonth)->format('Y-m-d');

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
        $yearMasuk  = Carbon::parse($jam_masuk_full, 'Asia/Jakarta')->year;

        $monthKeluar = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->month;
        $yearKeluar  = Carbon::parse($jam_keluar_full, 'Asia/Jakarta')->year;

        if ($monthMasuk !== $monthKeluar || $yearMasuk !== $yearKeluar) {
            $currentMonth = $monthMasuk;
            $currentYear  = $yearMasuk;
        } else {
            $currentMonth = $monthMasuk;
            $currentYear  = $yearMasuk;
        }

        $izin = RiwayatIzin::where('user_id', $user_id)
            ->whereMonth('tgl_izin', $currentMonth)
            ->whereYear('tgl_izin', $currentYear)
            ->where('status_izin_id', 2)
            ->exists();

        return $izin;
    }

    private function findDataKaryawanByNik(string $nikPayload): ?DataKaryawan
    {
        $nik = trim((string) $nikPayload);

        if ($nik === '') {
            return null;
        }

        if (!ctype_digit($nik)) {
            throw new \Exception("Format NIK '{$nik}' harus berupa angka.");
        }

        // Jika sudah 8 digit → langsung cari full
        if (strlen($nik) === 8) {
            return $this->DataKaryawan->firstWhere('nik', $nik);
        }

        // Hanya mengizinkan 2–4 digit untuk mode “pendek”
        if (strlen($nik) < 2 || strlen($nik) > 4) {
            throw new \Exception("Format NIK '{$nik}' tidak valid. Gunakan 2–4 digit terakhir atau 8 digit penuh.");
        }

        // Pad ke 4 digit: 15 → 0015, 115 → 0115, 1234 → 1234
        $suffix = str_pad($nik, 4, '0', STR_PAD_LEFT);

        $matches = $this->DataKaryawan->filter(function ($karyawan) use ($suffix) {
            $nikDb = (string) $karyawan->nik;

            // Pastikan NIK di DB minimal 4 digit
            if (strlen($nikDb) < 4) {
                return false;
            }

            return substr($nikDb, -4) === $suffix;
        });

        if ($matches->isEmpty()) {
            return null;
        }

        if ($matches->count() > 1) {
            throw new \Exception(
                "Ditemukan lebih dari satu karyawan dengan NIK berakhiran '{$suffix}'. " .
                    "Harap gunakan NIK 8 digit penuh di file import."
            );
        }

        return $matches->first();
    }
}
