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
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;

class PresensiImportNew implements ToModel, WithHeadingRow, WithValidation
{
    use Importable, RemembersRowNumber;

    private $DataKaryawan;
    private $KategoriPresensi;
    private $lokasiKantor;

    public function __construct()
    {
        $this->DataKaryawan = DataKaryawan::select('id', 'nik', 'user_id')->get();
        $this->KategoriPresensi = KategoriPresensi::select('id', 'label')->get();
        $this->lokasiKantor = LokasiKantor::select('lat', 'long')->first();
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date_format:d-m-Y',
            'jam'     => 'required|date_format:H:i:s',
            'pin'     => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tanggal.required'    => 'Tanggal presensi tidak diperbolehkan kosong.',
            'tanggal.date_format' => 'Format tanggal presensi harus d-m-Y.',
            'jam.required'        => 'Jam presensi tidak diperbolehkan kosong.',
            'jam.date_format'     => 'Format jam presensi harus H:i:s.',
            'pin.required'        => 'PIN karyawan tidak diperbolehkan kosong.',
        ];
    }

    public function model(array $row)
    {
        $this->logImportInfo(
            'Mulai proses row import presensi.',
            $this->buildLogContext($row)
        );

        try {
            // Tahap 1. Ambil payload dasar dari row excel
            $payload = $this->extractRowPayload($row);
            $this->logImportInfo(
                'Payload row berhasil diekstrak.',
                $this->buildLogContext($row, ['payload' => $payload])
            );

            // Tahap 2. Cari karyawan berdasarkan PIN
            $dataKaryawan = $this->resolveDataKaryawan($payload['pin']);
            $this->logImportInfo(
                'Data karyawan ditemukan.',
                $this->buildLogContext($row, [
                    'data_karyawan_id' => $dataKaryawan->id,
                    'user_id' => $dataKaryawan->user_id,
                    'nik' => $dataKaryawan->nik,
                ])
            );

            // Tahap 3. Parsing tanggal dan jam scan
            $tanggalScan = $this->parseTanggal($payload['tanggal']);
            $jamScan = $this->parseJam($payload['jam']);
            $scanDateTime = $this->combineTanggalDanJam($tanggalScan, $jamScan);

            // Tahap 4. Cari jadwal dan shift berdasarkan tanggal scan
            $jadwal = $this->findJadwalByTanggal($dataKaryawan, $tanggalScan);
            $shift = $this->findShiftByJadwal($jadwal);
            $this->logImportInfo(
                'Jadwal dan shift ditemukan.',
                $this->buildLogContext($row, [
                    'jadwal_id' => $jadwal->id,
                    'shift_id' => $shift->id,
                    'shift_nama' => $shift->nama ?? null,
                    'jam_from' => $shift->jam_from,
                    'jam_to' => $shift->jam_to,
                ])
            );

            // Tahap 5. Bentuk window shift untuk klasifikasi scan
            $shiftWindow = $this->buildShiftWindow($tanggalScan, $shift);

            // Tahap 6. Cari presensi existing pada tanggal yang sama
            $existingPresensi = $this->findExistingPresensi($dataKaryawan, $tanggalScan);
            $this->logImportInfo(
                'Presensi existing berhasil dicek.',
                $this->buildLogContext($row, [
                    'existing_presensi_id' => $existingPresensi?->id,
                    'existing_jam_masuk' => $existingPresensi?->jam_masuk,
                    'existing_jam_keluar' => $existingPresensi?->jam_keluar,
                ])
            );

            // Tahap 7. Terapkan scan ke presensi existing
            $scanResult = $this->applyScanToPresensi($existingPresensi, $scanDateTime, $shiftWindow);
            $this->logImportInfo(
                'Scan berhasil dipetakan ke presensi.',
                $this->buildLogContext($row, [
                    'scan_datetime' => $scanDateTime->format('Y-m-d H:i:s'),
                    'hasil_jam_masuk' => $scanResult['jam_masuk']?->format('Y-m-d H:i:s'),
                    'hasil_jam_keluar' => $scanResult['jam_keluar']?->format('Y-m-d H:i:s'),
                    'durasi' => $scanResult['durasi'],
                ])
            );

            // Tahap 8. Bentuk payload presensi final
            $presensiPayload = $this->buildPresensiPayload(
                $dataKaryawan,
                $jadwal,
                $shift,
                $scanResult
            );

            // Tahap 9. Simpan / update presensi
            $presensi = $this->persistPresensi($existingPresensi, $presensiPayload);
            $this->logImportInfo(
                'Presensi berhasil disimpan.',
                $this->buildLogContext($row, [
                    'presensi_id' => $presensi->id,
                    'jam_masuk' => $presensi->jam_masuk,
                    'jam_keluar' => $presensi->jam_keluar,
                    'kategori_presensi_id' => $presensi->kategori_presensi_id,
                    'durasi' => $presensi->durasi,
                ])
            );

            // Tahap 10. Sinkron reward bila data sudah lengkap
            $this->syncRewardStatus($dataKaryawan, $shift, $presensi);
            $this->logImportInfo(
                'Sinkron reward selesai.',
                $this->buildLogContext($row, [
                    'presensi_id' => $presensi->id,
                    'data_karyawan_id' => $dataKaryawan->id,
                ])
            );

            return $presensi;
        } catch (\Throwable $e) {
            $this->logImportError(
                'Gagal memproses row import presensi.',
                $this->buildLogContext($row, [
                    'error_message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ])
            );

            throw $e;
        }
    }

    /**
     * =========================
     * TAHAP 1
     * =========================
     */
    private function extractRowPayload(array $row): array
    {
        return [
            'tanggal' => trim((string) ($row['tanggal'] ?? '')),
            'jam'     => trim((string) ($row['jam'] ?? '')),
            'pin'     => trim((string) ($row['pin'] ?? '')),
        ];
    }

    /**
     * =========================
     * TAHAP 2
     * =========================
     */
    private function resolveDataKaryawan(string $pin): DataKaryawan
    {
        $dataKaryawan = $this->findDataKaryawanByNik($pin);

        if (!$dataKaryawan) {
            throw new \Exception("Karyawan dengan PIN / NIK '{$pin}' tidak ditemukan.");
        }

        return $dataKaryawan;
    }

    /**
     * =========================
     * TAHAP 3
     * =========================
     */
    private function parseTanggal(string $tanggal): Carbon
    {
        return Carbon::createFromFormat('d-m-Y', $tanggal);
    }

    private function parseJam(string $jam): Carbon
    {
        return Carbon::createFromFormat('H:i:s', $jam);
    }

    private function combineTanggalDanJam(Carbon $tanggal, Carbon $jam): Carbon
    {
        return Carbon::parse(
            $tanggal->format('Y-m-d') . ' ' . $jam->format('H:i:s')
        );
    }

    /**
     * =========================
     * TAHAP 4
     * =========================
     */
    private function findJadwalByTanggal(DataKaryawan $dataKaryawan, Carbon $tanggalScan): Jadwal
    {
        $tanggalYmd = $tanggalScan->format('Y-m-d');

        $jadwal = Jadwal::where('user_id', $dataKaryawan->user_id)
            ->whereDate('tgl_mulai', '<=', $tanggalYmd)
            ->where(function ($q) use ($tanggalYmd) {
                $q->whereNull('tgl_selesai')
                    ->orWhereDate('tgl_selesai', '>=', $tanggalYmd);
            })
            ->first();

        if (!$jadwal) {
            throw new \Exception(
                "Jadwal untuk PIN / NIK '{$dataKaryawan->nik}' pada tanggal " .
                    $tanggalScan->format('d-m-Y') .
                    " tidak ditemukan."
            );
        }

        return $jadwal;
    }

    private function findShiftByJadwal(Jadwal $jadwal): Shift
    {
        if (empty($jadwal->shift_id)) {
            throw new \Exception("Shift pada jadwal ID {$jadwal->id} tidak ditemukan.");
        }

        $shift = Shift::find($jadwal->shift_id);

        if (!$shift) {
            throw new \Exception("Shift untuk jadwal ID {$jadwal->id} tidak ditemukan.");
        }

        return $shift;
    }

    /**
     * =========================
     * TAHAP 5
     * =========================
     */
    private function buildShiftWindow(Carbon $tanggalScan, Shift $shift): array
    {
        $shiftStart = Carbon::parse(
            $tanggalScan->format('Y-m-d') . ' ' . $shift->jam_from
        );

        $shiftEnd = Carbon::parse(
            $tanggalScan->format('Y-m-d') . ' ' . $shift->jam_to
        );

        // Antisipasi shift lintas hari
        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        $midpoint = $shiftStart->copy()->addSeconds(
            intdiv($shiftStart->diffInSeconds($shiftEnd), 2)
        );

        return [
            'shift_start' => $shiftStart,
            'shift_end'   => $shiftEnd,
            'midpoint'    => $midpoint,
        ];
    }

    /**
     * =========================
     * TAHAP 6
     * =========================
     */
    private function findExistingPresensi(DataKaryawan $dataKaryawan, Carbon $tanggalScan): ?Presensi
    {
        return Presensi::where('user_id', $dataKaryawan->user_id)
            ->whereDate('jam_masuk', $tanggalScan->format('Y-m-d'))
            ->first();
    }

    /**
     * =========================
     * TAHAP 7
     * =========================
     */
    private function applyScanToPresensi(?Presensi $existingPresensi, Carbon $scanDateTime, array $shiftWindow): array
    {
        $jamMasuk = null;
        $jamKeluar = null;

        if ($existingPresensi) {
            $jamMasuk = $existingPresensi->jam_masuk
                ? Carbon::parse($existingPresensi->jam_masuk)
                : null;

            $jamKeluar = $existingPresensi->jam_keluar
                ? Carbon::parse($existingPresensi->jam_keluar)
                : null;
        }

        // Scan pertama di hari itu dianggap jam masuk
        if (!$jamMasuk) {
            $jamMasuk = $scanDateTime->copy();

            $this->logImportInfo(
                'Scan pertama ditetapkan sebagai jam masuk.',
                [
                    'row_number' => $this->getRowNumber(),
                    'scan_datetime' => $scanDateTime->format('Y-m-d H:i:s'),
                ]
            );

            return $this->buildScanResult($jamMasuk, $jamKeluar);
        }

        $scanType = $this->determineScanType($scanDateTime, $shiftWindow);

        // Kandidat scan masuk: ambil yang paling awal
        if ($scanType === 'masuk') {
            if ($scanDateTime->lt($jamMasuk)) {
                $this->logImportWarning(
                    'Ditemukan scan masuk lebih awal, jam masuk diperbarui.',
                    [
                        'row_number' => $this->getRowNumber(),
                        'old_jam_masuk' => $jamMasuk->format('Y-m-d H:i:s'),
                        'new_jam_masuk' => $scanDateTime->format('Y-m-d H:i:s'),
                    ]
                );

                $jamMasuk = $scanDateTime->copy();
            } else {
                $this->logImportWarning(
                    'Scan masuk dobel terdeteksi, scan diabaikan agar tidak merugikan karyawan.',
                    [
                        'row_number' => $this->getRowNumber(),
                        'existing_jam_masuk' => $jamMasuk->format('Y-m-d H:i:s'),
                        'ignored_scan' => $scanDateTime->format('Y-m-d H:i:s'),
                    ]
                );
            }

            return $this->buildScanResult($jamMasuk, $jamKeluar);
        }

        // Kandidat scan keluar: ambil yang paling akhir dan harus lebih besar dari jam masuk
        if ($this->shouldUpdateCheckOut($scanDateTime, $jamMasuk, $jamKeluar)) {
            $oldJamKeluar = $jamKeluar?->format('Y-m-d H:i:s');

            $jamKeluar = $scanDateTime->copy();

            $this->logImportInfo(
                'Jam keluar diperbarui dari scan terbaru.',
                [
                    'row_number' => $this->getRowNumber(),
                    'old_jam_keluar' => $oldJamKeluar,
                    'new_jam_keluar' => $jamKeluar->format('Y-m-d H:i:s'),
                ]
            );
        } else {
            $this->logImportWarning(
                'Scan keluar tidak dipakai karena tidak valid.',
                [
                    'row_number' => $this->getRowNumber(),
                    'jam_masuk' => $jamMasuk->format('Y-m-d H:i:s'),
                    'jam_keluar_existing' => $jamKeluar?->format('Y-m-d H:i:s'),
                    'ignored_scan' => $scanDateTime->format('Y-m-d H:i:s'),
                ]
            );
        }

        return $this->buildScanResult($jamMasuk, $jamKeluar);
    }

    private function determineScanType(Carbon $scanDateTime, array $shiftWindow): string
    {
        return $scanDateTime->lessThanOrEqualTo($shiftWindow['midpoint'])
            ? 'masuk'
            : 'keluar';
    }

    private function shouldUpdateCheckOut(Carbon $scanDateTime, Carbon $jamMasuk, ?Carbon $jamKeluar): bool
    {
        if ($scanDateTime->lessThanOrEqualTo($jamMasuk)) {
            return false;
        }

        if (is_null($jamKeluar)) {
            return true;
        }

        return $scanDateTime->gt($jamKeluar);
    }

    private function buildScanResult(Carbon $jamMasuk, ?Carbon $jamKeluar): array
    {
        $durasi = null;

        if ($jamKeluar && $jamKeluar->gt($jamMasuk)) {
            $durasi = $jamMasuk->diffInSeconds($jamKeluar);
        }

        return [
            'jam_masuk'  => $jamMasuk,
            'jam_keluar' => $jamKeluar,
            'durasi'     => $durasi,
        ];
    }

    /**
     * =========================
     * TAHAP 8
     * =========================
     */
    private function buildPresensiPayload(
        DataKaryawan $dataKaryawan,
        Jadwal $jadwal,
        Shift $shift,
        array $scanResult
    ): array {
        $jamMasuk = $scanResult['jam_masuk'];
        $jamKeluar = $scanResult['jam_keluar'];
        $durasi = $scanResult['durasi'];

        $jamMasukTime = Carbon::createFromFormat('H:i:s', $jamMasuk->format('H:i:s'));
        $kategoriPresensiId = $this->getKategoriPresensiIdByShift($shift, $jamMasukTime);

        return [
            'user_id'              => $dataKaryawan->user_id,
            'data_karyawan_id'     => $dataKaryawan->id,
            'jadwal_id'            => $jadwal->id,
            'jam_masuk'            => $jamMasuk->format('Y-m-d H:i:s'),
            'jam_keluar'           => $jamKeluar ? $jamKeluar->format('Y-m-d H:i:s') : null,
            'durasi'               => $durasi,
            'lat'                  => $this->lokasiKantor->lat ?? null,
            'long'                 => $this->lokasiKantor->long ?? null,
            'latkeluar'            => $jamKeluar ? ($this->lokasiKantor->lat ?? null) : null,
            'longkeluar'           => $jamKeluar ? ($this->lokasiKantor->long ?? null) : null,
            'kategori_presensi_id' => $kategoriPresensiId,
        ];
    }

    /**
     * =========================
     * TAHAP 9
     * =========================
     */
    private function persistPresensi(?Presensi $existingPresensi, array $presensiPayload): Presensi
    {
        if ($existingPresensi) {
            $existingPresensi->update($presensiPayload);
            return $existingPresensi->fresh();
        }

        return Presensi::create($presensiPayload);
    }

    /**
     * =========================
     * TAHAP 10
     * =========================
     */
    private function syncRewardStatus(DataKaryawan $dataKaryawan, Shift $shift, Presensi $presensi): void
    {
        if (empty($presensi->jam_masuk) || empty($presensi->jam_keluar)) {
            return;
        }

        $jamMasukFull = Carbon::parse($presensi->jam_masuk);
        $jamKeluarFull = Carbon::parse($presensi->jam_keluar);
        $jamKeluarTime = Carbon::createFromFormat('H:i:s', $jamKeluarFull->format('H:i:s'));

        $statusReward = $this->hitungStatusRewardPresensi(
            $dataKaryawan,
            $jamMasukFull,
            $jamKeluarFull,
            (int) $presensi->kategori_presensi_id,
            $shift,
            $jamKeluarTime
        );

        $dataKaryawan->update([
            'status_reward_presensi' => $statusReward,
        ]);
    }

    /**
     * =========================
     * HELPER LAMA
     * =========================
     */
    private function hitungStatusRewardPresensi(
        DataKaryawan $dataKaryawan,
        Carbon $jamMasukFull,
        Carbon $jamKeluarFull,
        int $kategoriPresensiId,
        ?Shift $shift,
        Carbon $jamKeluarTime
    ): bool {
        $kategoriTepat = $this->KategoriPresensi->firstWhere('label', 'Tepat Waktu');
        $kategoriTerlambat = $this->KategoriPresensi->firstWhere('label', 'Terlambat');

        if ($this->cekSebulanIzin($dataKaryawan->user_id, $jamMasukFull, $jamKeluarFull)) {
            return false;
        }

        if ($this->cekSebulanCuti($dataKaryawan->user_id, $jamMasukFull, $jamKeluarFull)) {
            return false;
        }

        if ($kategoriTerlambat && $kategoriPresensiId === $kategoriTerlambat->id) {
            return false;
        }

        if ($shift && $this->presensiMendahului($shift->jam_to, $jamKeluarTime)) {
            return false;
        }

        if ($kategoriTepat && $kategoriPresensiId === $kategoriTepat->id) {
            return $this->cekSebulanPresensi(
                $dataKaryawan->id,
                $jamMasukFull,
                $jamKeluarFull
            );
        }

        return false;
    }

    private function getKategoriPresensiIdByShift(Shift $shift, Carbon $jamMasuk)
    {
        $jamFrom = Carbon::parse($shift->jam_from);
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

        return $presensi->count() == $jumlahHariBulanIni;
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

    private function findDataKaryawanByNik(string $nikPayload): ?DataKaryawan
    {
        $nik = trim((string) $nikPayload);

        if ($nik === '') {
            return null;
        }

        if (!ctype_digit($nik)) {
            throw new \Exception("Format NIK '{$nik}' harus berupa angka.");
        }

        if (strlen($nik) === 8) {
            return $this->DataKaryawan->firstWhere('nik', $nik);
        }

        if (strlen($nik) < 2 || strlen($nik) > 4) {
            throw new \Exception("Format NIK '{$nik}' tidak valid. Gunakan 2–4 digit terakhir atau 8 digit penuh.");
        }

        $suffix = str_pad($nik, 4, '0', STR_PAD_LEFT);

        $matches = $this->DataKaryawan->filter(function ($karyawan) use ($suffix) {
            $nikDb = (string) $karyawan->nik;

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
                "Ditemukan lebih dari satu karyawan dengan NIK berakhiran '{$suffix}'. Harap gunakan NIK 8 digit penuh di file import."
            );
        }

        return $matches->first();
    }

    /**
     * =========================
     * LOGGING
     * =========================
     */
    private function logImportInfo(string $message, array $context = []): void
    {
        Log::channel('import_presensi')->info($message, $context);
    }

    private function logImportWarning(string $message, array $context = []): void
    {
        Log::channel('import_presensi')->warning($message, $context);
    }

    private function logImportError(string $message, array $context = []): void
    {
        Log::channel('import_presensi')->error($message, $context);
    }

    private function buildLogContext(array $row = [], array $extra = []): array
    {
        return array_merge([
            'row_number' => $this->getRowNumber(),
            'tanggal' => $row['tanggal'] ?? null,
            'jam' => $row['jam'] ?? null,
            'pin' => $row['pin'] ?? null,
        ], $extra);
    }
}
