<?php

namespace App\Jobs\Penggajian;

use App\Helpers\RandomHelper;
use Carbon\Carbon;
use App\Models\DetailGaji;
use App\Models\Penggajian;
use App\Models\PenyesuaianGaji;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateGajiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data_karyawan_ids;
    protected $sertakan_bor;
    protected $riwayat_penggajian_id;

    public function __construct($data_karyawan_ids, $sertakan_bor, $riwayat_penggajian_id)
    {
        $this->data_karyawan_ids = $data_karyawan_ids;
        $this->sertakan_bor = $sertakan_bor;
        $this->riwayat_penggajian_id = $riwayat_penggajian_id;
    }

    // Ini v2 (detail gajis)
    public function handle(): void
    {
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;

        // Ambil nilai status dari tabel status_gajis
        $statusBelumDipublikasi = DB::table('status_gajis')->where('label', 'Belum Dipublikasi')->value('id');
        $statusSudahDipublikasi = DB::table('status_gajis')->where('label', 'Sudah Dipublikasi')->value('id');
        $kategori_penghasilan_dasar = DB::table('kategori_gajis')->where('label', 'Penghasilan Dasar')->value('id');
        $kategori_penambah = DB::table('kategori_gajis')->where('label', 'Penambah')->value('id');
        $kategori_pengurang = DB::table('kategori_gajis')->where('label', 'Pengurang')->value('id');

        // Cek apakah ada THR untuk periode saat ini
        $thrExists = DB::table('run_thrs')
            ->whereMonth('tgl_run_thr', $currentMonth)
            ->whereYear('tgl_run_thr', $currentYear)
            ->exists();
        Log::info("THR Exists: " . $thrExists);

        // Ambil semua data_karyawan_id dari tabel run_thrs untuk periode saat ini
        $thrKaryawanIds = [];
        if ($thrExists) {
            $thrKaryawanIds = DB::table('run_thrs')
                ->whereMonth('tgl_run_thr', $currentMonth)
                ->whereYear('tgl_run_thr', $currentYear)
                ->pluck('data_karyawan_id')
                ->toArray();
        }
        // Log::info("THR Karyawan IDs: " . implode(', ', $thrKaryawanIds));

        $query = DB::table('data_karyawans')
            ->join('kelompok_gajis', 'data_karyawans.kelompok_gaji_id', '=', 'kelompok_gajis.id')
            ->leftJoin('penggajians', 'data_karyawans.id', '=', 'penggajians.data_karyawan_id')
            ->join('status_karyawans', 'data_karyawans.status_karyawan_id', '=', 'status_karyawans.id')
            ->select(
                'data_karyawans.id as data_karyawan_id',
                DB::raw('COALESCE(kelompok_gajis.besaran_gaji, 0) as gaji_pokok'),
                DB::raw('COALESCE(data_karyawans.tunjangan_jabatan, 0) as tunjangan_jabatan'),
                DB::raw('COALESCE(data_karyawans.tunjangan_fungsional, 0) as tunjangan_fungsional'),
                DB::raw('COALESCE(data_karyawans.tunjangan_khusus, 0) as tunjangan_khusus'),
                DB::raw('COALESCE(data_karyawans.tunjangan_lainnya, 0) as tunjangan_lainnya'),
                DB::raw('COALESCE(data_karyawans.uang_makan, 0) as uang_makan'),
                DB::raw('COALESCE(data_karyawans.uang_lembur, 0) as uang_lembur'),
                'data_karyawans.ptkp_id as ptkp_id',
                'status_karyawans.label as status_karyawan',
                'data_karyawans.tgl_masuk as tgl_masuk'
            );

        if (!empty($this->data_karyawan_ids)) {
            $query->whereIn('data_karyawans.id', $this->data_karyawan_ids);
        }

        $dataKaryawans = $query->get();

        // Ambil jadwal penggajian dari tabel jadwal_penggajians
        $jadwalPenggajian = DB::table('jadwal_penggajians')
            ->select('tgl_mulai')
            ->orderBy('tgl_mulai', 'desc')
            ->first();

        $tgl_mulai = Carbon::create($currentYear, $currentMonth, $jadwalPenggajian->tgl_mulai);

        foreach ($dataKaryawans as $dataKaryawan) {
            $data_karyawan_id = $dataKaryawan->data_karyawan_id;

            // Hitung reward (BOR dan Bonus Presensi)
            $rewardBOR = $this->calculatedRewardBOR($data_karyawan_id, $this->sertakan_bor);
            $rewardBonusPresensi = $this->calculatedRewardPresensi($data_karyawan_id);
            $totalReward = $rewardBOR + $rewardBonusPresensi;

            // Tentukan apakah THR perlu dihitung
            $penghasilanTHR = in_array($data_karyawan_id, $thrKaryawanIds) ? $this->calculatedTHR($dataKaryawan) : 0;

            // Hitung penghasilan THR, bruto, total tunjangan, dan total premi
            // $penghasilanTHR = $this->calculatedTHR($dataKaryawan);
            $penghasilanBruto = $this->calculatedPenghasilanBruto($dataKaryawan, $totalReward, $penghasilanTHR);
            $totalTunjangan = $this->calculatedTotalTunjangan($dataKaryawan);
            $totalPremi = $this->calculatedPremi($data_karyawan_id, $penghasilanBruto, $dataKaryawan->gaji_pokok);

            // Tentukan status penggajian
            // $status_penggajian = $currentDate->greaterThanOrEqualTo($tgl_mulai) ? Penggajian::STATUS_PUBLISHED : Penggajian::STATUS_CREATED;
            $status_penggajian = $currentDate->greaterThanOrEqualTo($tgl_mulai) ? $statusSudahDipublikasi : $statusBelumDipublikasi;

            // Hitung PPh 21 bulanan dan PPh 21 Desember
            $currentMonth = Carbon::now()->month;
            $penggajianData = [
                'riwayat_penggajian_id' => $this->riwayat_penggajian_id,
                'data_karyawan_id' => $data_karyawan_id,
                'tgl_penggajian' => Carbon::now(),
                'gaji_pokok' => $dataKaryawan->gaji_pokok,
                'total_tunjangan' => $totalTunjangan,
                'reward' => $totalReward,
                'gaji_bruto' => $penghasilanBruto,
                'total_premi' => $totalPremi,
                'status_gaji_id' => $status_penggajian
            ];

            if ($currentMonth >= 1 && $currentMonth <= 11) {
                // Januari - November
                $pph21Bulanan = $this->calculatedPPH21ForMonths($penghasilanBruto, $dataKaryawan->ptkp_id);
                $takeHomePay = $penghasilanBruto - $totalPremi - $pph21Bulanan;
                $penggajianData['pph_21'] = $pph21Bulanan;
                $penggajianData['take_home_pay'] = $takeHomePay;

                $penggajian = Penggajian::updateOrCreate(
                    [
                        'data_karyawan_id' => $data_karyawan_id,
                        'tgl_penggajian' => Carbon::now(),
                    ],
                    $penggajianData
                );

                Log::info("| TAKE HOME PAY | Karyawan ID {$data_karyawan_id} bulan [{$currentMonth}] adalah {$takeHomePay}.");
            } elseif ($currentMonth == 12) {
                // Desember
                $pph21Desember = $this->calculatedPPH21ForDecember($dataKaryawan, $totalReward);
                $takeHomePayDesember = $penghasilanBruto - $totalPremi - $pph21Desember;
                $penggajianData['pph_21'] = $pph21Desember;
                $penggajianData['take_home_pay'] = $takeHomePayDesember;

                $penggajian = Penggajian::updateOrCreate(
                    [
                        'data_karyawan_id' => $data_karyawan_id,
                        'tgl_penggajian' => Carbon::now(),
                    ],
                    $penggajianData
                );

                Log::info("| TAKE HOME PAY DESEMBER | Karyawan ID {$data_karyawan_id} bulan Desember adalah {$takeHomePayDesember}.");
            } else {
                Log::error("Perhitungan tidak valid untuk karyawan ID {$data_karyawan_id}.");
            }

            // calculatedPenyesuaianPenambah menambah take_home_pay
            $penyesuaianPenambahDetails = $this->calculatedPenyesuaianPenambah($kategori_penambah, $penggajian->id, $penggajianData['take_home_pay']);
            // calculatedPenyesuaianPengurang mengurangi take_home_pay
            $penyesuaianPengurangDetails = $this->calculatedPenyesuaianPengurang($kategori_pengurang, $penggajian->id, $penggajianData['take_home_pay']);

            $penggajian->update(['take_home_pay' => $penggajianData['take_home_pay']]);

            $details = [
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penghasilan_dasar,
                    'nama_detail' => 'Gaji Pokok',
                    'besaran' => $dataKaryawan->gaji_pokok == 0 ? null : $dataKaryawan->gaji_pokok
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Tunjangan Jabatan',
                    'besaran' => $dataKaryawan->tunjangan_jabatan == 0 ? null : $dataKaryawan->tunjangan_jabatan
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Tunjangan Fungsional',
                    'besaran' => $dataKaryawan->tunjangan_fungsional == 0 ? null : $dataKaryawan->tunjangan_fungsional
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Tunjangan Khusus',
                    'besaran' => $dataKaryawan->tunjangan_khusus == 0 ? null : $dataKaryawan->tunjangan_khusus
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Tunjangan Lainnya',
                    'besaran' => $dataKaryawan->tunjangan_lainnya == 0 ? null : $dataKaryawan->tunjangan_lainnya
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Uang Lembur',
                    'besaran' => $dataKaryawan->uang_lembur == 0 ? null : $dataKaryawan->uang_lembur
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Uang Makan',
                    'besaran' => $dataKaryawan->uang_makan == 0 ? null : $dataKaryawan->uang_makan
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Bonus BOR',
                    'besaran' => $rewardBOR == 0 ? null : $rewardBOR
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Bonus Presensi',
                    'besaran' => $rewardBonusPresensi == 0 ? null : $rewardBonusPresensi
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'THR',
                    'besaran' => $penghasilanTHR == 0 ? null : $penghasilanTHR
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_pengurang,
                    'nama_detail' => 'PPH21',
                    'besaran' => $currentMonth == 12 ? ($pph21Desember == 0 ? null : $pph21Desember) : ($pph21Bulanan == 0 ? null : $pph21Bulanan)
                ]
            ];

            // detail premi
            $premis = DB::table('pengurang_gajis')
                ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
                ->where('pengurang_gajis.data_karyawan_id', $data_karyawan_id)
                ->select('premis.*')
                ->get();

            foreach ($premis as $premi) {
                $premiAmount = $this->calculatedPremiDetail($premi, $penghasilanBruto, $dataKaryawan->gaji_pokok);
                $details[] = [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_pengurang,
                    'nama_detail' => $premi->nama_premi,
                    'besaran' => $premiAmount == 0 ? null : $premiAmount
                ];
            }

            // Gabungkan details penyesuaian gaji penambah dan pengurang ke dalam details gaji
            $details = array_merge($details, $penyesuaianPenambahDetails, $penyesuaianPengurangDetails);

            foreach ($details as $detail) {
                DetailGaji::create($detail);
            }
        }
    }

    /* =========================== Calculated ============================= */
    private function calculatedTHR($dataKaryawan)
    {
        $thr = 0;
        // $tglMulaiKerja = Carbon::parse($dataKaryawan->tgl_masuk);
        $tglMulaiKerja = Carbon::parse(RandomHelper::convertToDateString($dataKaryawan->tgl_masuk));
        $masaKerja = $tglMulaiKerja->diffInMonths(Carbon::now());

        if ($dataKaryawan->status_karyawan == "Tetap") {
            if ($masaKerja <= 12) {
                $thr = ($masaKerja / 12) * $dataKaryawan->gaji_pokok;
            } else {
                $thr = $dataKaryawan->gaji_pokok;
            }
        }
        Log::info("Karyawan dengan masa kerja: $masaKerja dari {$tglMulaiKerja}, dengan thr $thr");

        return $thr;
    }

    private function calculatedPremi($data_karyawan_id, $penghasilanBruto, $gajiPokok)
    {
        // Ambil data premi yang dipilih untuk karyawan ini dari tabel pengurang_gajis
        $premis = DB::table('pengurang_gajis')
            ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
            ->where('pengurang_gajis.data_karyawan_id', $data_karyawan_id)
            ->select('premis.*')
            ->get();

        $totalPremi = 0;

        foreach ($premis as $premi) {
            $premiAmount = 0;
            $basisGaji = $penghasilanBruto;
            if ($premi->kategori_potongan_id == 2) { // gaji pokok
                $basisPengkali = $gajiPokok;
            } else {
                $basisPengkali = $basisGaji;
            }

            // Terapkan minimal maksimal rate jika ada
            if (!is_null($premi->minimal_rate)) {
                $basisPengkaliRate = max($basisPengkali, $premi->minimal_rate);
                Log::info("Minimal rate: {$premi->minimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
            }
            if (!is_null($premi->maksimal_rate)) {
                $basisPengkaliRate = min($basisPengkali, $premi->maksimal_rate);
                Log::info("Maksimal rate: {$premi->maksimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
            }

            // Cek has_custom_formula true
            if ($premi->has_custom_formula) {
                if ($premi->jenis_premi == 0) { // Persentase
                    $premiAmount = ($premi->besaran_premi / 100) * $basisPengkaliRate;
                } else {
                    $premiAmount = $premi->besaran_premi;
                }
                Log::info("Calculated custom premi: {$premiAmount} premi ID: {$premi->id}");
            } else {
                if ($premi->jenis_premi == 0) { // Persentase
                    $premiAmount = ($premi->besaran_premi / 100) * $basisPengkali;
                } else {
                    $premiAmount = $premi->besaran_premi;
                }
                Log::info("Calculated premi: {$premiAmount} premi ID: {$premi->id}");
            }
            $totalPremi += $premiAmount;
        }

        return $totalPremi;
    }

    // buat itung detail gajis
    private function calculatedPremiDetail($premi, $penghasilanBruto, $gajiPokok)
    {
        $premiAmount = 0;
        $basisGaji = $penghasilanBruto;
        if ($premi->kategori_potongan_id == 2) { // gaji pokok
            $basisPengkali = $gajiPokok;
        } else {
            $basisPengkali = $basisGaji;
        }

        // Terapkan minimal maksimal rate jika ada
        if (!is_null($premi->minimal_rate)) {
            $basisPengkaliRate = max($basisPengkali, $premi->minimal_rate);
            Log::info("Minimal rate: {$premi->minimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
        }
        if (!is_null($premi->maksimal_rate)) {
            $basisPengkaliRate = min($basisPengkali, $premi->maksimal_rate);
            Log::info("Maksimal rate: {$premi->maksimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
        }

        // Cek has_custom_formula true
        if ($premi->has_custom_formula) {
            if ($premi->jenis_premi == 0) { // Persentase
                $premiAmount = ($premi->besaran_premi / 100) * $basisPengkaliRate;
            } else {
                $premiAmount = $premi->besaran_premi;
            }
            Log::info("Calculated custom premi: {$premiAmount} premi ID: {$premi->id}");
        } else {
            if ($premi->jenis_premi == 0) { // Persentase
                $premiAmount = ($premi->besaran_premi / 100) * $basisPengkali;
            } else {
                $premiAmount = $premi->besaran_premi;
            }
            Log::info("Calculated premi: {$premiAmount} premi ID: {$premi->id}");
        }

        return $premiAmount;
    }

    private function calculatedPPH21ForMonths($penghasilanBruto, $ptkp_id)
    {
        // Langkah 1: Ambil data PTKP dari data_karyawans
        $ptkp = DB::table('ptkps')->where('id', $ptkp_id)->first();

        // Langkah 2: Cocokkan kategori_ter_id pada tabel ptkps dengan id kategori ter pada tabel kategori_ters
        $kategoriTer = DB::table('kategori_ters')->where('id', $ptkp->kategori_ter_id)->first();

        // Langkah 3: Ambil nilai percentage pada tabel ters dengan syarat kategori_ter_id dan gaji bruto antara from_ter dan to_ter
        $ters = DB::table('ters')
            ->select('percentage')
            ->where('kategori_ter_id', $kategoriTer->id)
            ->where('from_ter', '<=', $penghasilanBruto)
            ->where('to_ter', '>=', $penghasilanBruto)
            ->first();

        $pph21Bulanan = ($ters->percentage / 100) * $penghasilanBruto;
        return $pph21Bulanan;
    }

    private function calculatedPPH21ForDecember($dataKaryawan, $reward)
    {
        // 1. Hitung bruto dan premi Desember
        $penghasilanBrutoDesember = $this->calculatedPenghasilanBruto($dataKaryawan, $reward);
        $totalPremiDesember = $this->calculatedPremi($dataKaryawan->data_karyawan_id, $penghasilanBrutoDesember, $dataKaryawan->gaji_pokok);
        $currentYear = Carbon::now()->year;

        // 2. Jumlahkan bruto dan premi dari Januari hingga Desember
        $totalBruto = DB::table('penggajians')
            ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
            ->whereYear('tgl_penggajian', $currentYear)
            ->sum('gaji_bruto') + $penghasilanBrutoDesember;

        $totalPremi = DB::table('penggajians')
            ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
            ->whereYear('tgl_penggajian', $currentYear)
            ->sum('total_premi') + $totalPremiDesember;

        // 3. Kurangi total bruto dengan total premi
        $penghasilanNeto = $totalBruto - $totalPremi;

        // 4. Kurangi dengan biaya jabatan (5% dari total bruto)
        $biayaJabatan = 0.05 * $totalBruto;
        $biayaJabatan = min($biayaJabatan, 500000); // Batas maksimum Rp500.000
        $penghasilanNetoSetelahBiayaJabatan = $penghasilanNeto - $biayaJabatan;

        // 5. Kurangi dengan nilai PTKP
        $nilaiPTKP = DB::table('ptkps')
            ->where('id', $dataKaryawan->ptkp_id)
            ->value('nilai');
        $penghasilanKenaPajak = $penghasilanNetoSetelahBiayaJabatan - $nilaiPTKP;

        // 6. Kalikan dengan tarif pajak 2021
        $pph21Tahunan = $this->calculatedPenghasilanKenaPajak($penghasilanKenaPajak);

        // 7. Kurangi dengan jumlah PPh bulanan dari Januari hingga November
        $pph21BulananTotal = DB::table('penggajians')
            ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
            ->whereBetween('tgl_penggajian', [Carbon::create($currentYear, 1, 1), Carbon::create($currentYear, 11, 30)])
            ->sum('pph_21');
        $pph21Desember = $pph21Tahunan - $pph21BulananTotal;
        return $pph21Desember;
    }

    private function calculatedRewardBOR($data_karyawan_id, $sertakan_bor)
    {
        $totalBOR = 0;
        if ($sertakan_bor) {
            $dataKaryawan = DB::table('data_karyawans')
                ->join('kompetensis', 'data_karyawans.kompetensi_id', '=', 'kompetensis.id')
                ->select('kompetensis.nilai_bor')
                ->where('data_karyawans.id', $data_karyawan_id)
                ->first();
            if (!$dataKaryawan) {
                Log::warning("Data karyawan dengan ID {$data_karyawan_id} tidak ditemukan.");
                return 0;
            }

            $totalBOR = $dataKaryawan->nilai_bor;
        }

        return $totalBOR;
    }

    private function calculatedRewardPresensi($data_karyawan_id)
    {
        // Ambil user_id yang sesuai dengan data_karyawan_id
        $userId = DB::table('data_karyawans')
            ->where('id', $data_karyawan_id)
            ->value('user_id');

        // Tentukan tanggal awal dan akhir bulan sebelumnya
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();

        // Ambil ID untuk kategori 'Tepat Waktu'
        $kategoriTepatWaktuId = DB::table('kategori_presensis')->where('label', 'Tepat Waktu')->value('id');

        // Hitung jumlah presensi 'Tepat Waktu' dari tanggal 1 sampai akhir bulan sebelumnya
        $presensiCount = DB::table('presensis')
            ->where('data_karyawan_id', $data_karyawan_id)
            ->where('kategori_presensi_id', $kategoriTepatWaktuId)
            ->whereBetween('jam_masuk', [$startDate, $endDate])
            ->count();
        Log::info("Presensi tepat waktu terhitung: {$presensiCount}");

        // Hitung jumlah hari dalam bulan sebelumnya
        $totalDays = $startDate->daysInMonth;

        // Memeriksa selain 'Cuti Tahunan' dan 'Cuti Besar'
        $invalidCutiCount = DB::table('cutis')
            ->join('tipe_cutis', 'cutis.tipe_cuti_id', '=', 'tipe_cutis.id')
            ->where('cutis.user_id', $userId)
            ->where('tipe_cutis.cuti_administratif', false)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tgl_from', [$startDate, $endDate])
                    ->orWhereBetween('tgl_to', [$startDate, $endDate]);
            })
            ->count();
        Log::info("Cuti selain 'Cuti Tahunan' dan 'Cuti Besar' terhitung: {$invalidCutiCount}");

        // Jika tidak ada cuti selain 'Cuti Tahunan' dan 'Cuti Besar' dan presensi tepat waktu sama dengan jumlah hari dalam bulan sebelumnya, berikan bonus presensi
        $bonusPresensi = 0;
        if ($invalidCutiCount == 0 && $presensiCount == $totalDays) {
            $bonusPresensi = 300000;
        }

        return $bonusPresensi;
    }

    private function calculatedPenghasilanBruto($dataKaryawan, $reward, $penghasilanTHR)
    {
        return $dataKaryawan->gaji_pokok
            + $reward
            + $penghasilanTHR
            + $dataKaryawan->tunjangan_jabatan
            + $dataKaryawan->tunjangan_fungsional
            + $dataKaryawan->tunjangan_khusus
            + $dataKaryawan->tunjangan_lainnya
            + $dataKaryawan->uang_makan
            + $dataKaryawan->uang_lembur;
    }

    private function calculatedTotalTunjangan($dataKaryawan)
    {
        return $dataKaryawan->tunjangan_jabatan
            + $dataKaryawan->tunjangan_fungsional
            + $dataKaryawan->tunjangan_khusus
            + $dataKaryawan->tunjangan_lainnya;
    }

    private function calculatedPenghasilanKenaPajak($penghasilanKenaPajak)
    {
        $pph21 = 0;
        if ($penghasilanKenaPajak > 0) {
            if ($penghasilanKenaPajak > 5000000000) {
                $pph21 += 0.35 * $penghasilanKenaPajak;
            } elseif ($penghasilanKenaPajak > 500000000) {
                $pph21 += 0.30 * $penghasilanKenaPajak;
            } elseif ($penghasilanKenaPajak > 250000000) {
                $pph21 += 0.25 * $penghasilanKenaPajak;
            } elseif ($penghasilanKenaPajak > 60000000) {
                $pph21 += 0.15 * $penghasilanKenaPajak;
            } else {
                $pph21 += 0.05 * $penghasilanKenaPajak;
            }
        }
        return $pph21;
    }

    private function calculatedPenyesuaianPenambah($kategori_penambah, $penggajian_id, &$takeHomePay)
    {
        $details = [];

        // Ambil data penyesuaian gaji penambah berdasarkan penggajian_id
        $penyesuaianGajis = DB::table('penyesuaian_gajis')
            ->where('penggajian_id', $penggajian_id)
            ->where('kategori_gaji_id', $kategori_penambah)
            ->get();

        // Iterasi setiap penyesuaian gaji untuk validasi dan perhitungan
        foreach ($penyesuaianGajis as $penyesuaianGaji) {
            // $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse($penyesuaianGaji->bulan_mulai) : null;
            // $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse($penyesuaianGaji->bulan_selesai) : null;
            $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_mulai)) : null;
            $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_selesai)) : null;
            $currentDate = Carbon::now();

            // Cek apakah saat ini berada pada rentang bulan mulai dan selesai atau jika null
            if (
                ($bulanMulai && $bulanSelesai && $currentDate->between($bulanMulai, $bulanSelesai)) ||
                (!$bulanMulai && !$bulanSelesai) ||
                ($bulanMulai && !$bulanSelesai && $currentDate->greaterThanOrEqualTo($bulanMulai)) ||
                (!$bulanMulai && $bulanSelesai && $currentDate->lessThanOrEqualTo($bulanSelesai))
            ) {
                // Tambahkan take_home_pay dengan besaran penyesuaian gaji
                $takeHomePay += $penyesuaianGaji->besaran;

                // Tambahkan detail penyesuaian gaji ke array details
                $details[] = [
                    'penggajian_id' => $penggajian_id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => $penyesuaianGaji->nama_detail,
                    'besaran' => $penyesuaianGaji->besaran
                ];
            }
        }

        return $details;
    }

    private function calculatedPenyesuaianPengurang($kategori_pengurang, $penggajian_id, &$takeHomePay)
    {
        $details = [];

        // Ambil data penyesuaian gaji pengurang berdasarkan penggajian_id
        $penyesuaianGajis = DB::table('penyesuaian_gajis')
            ->where('penggajian_id', $penggajian_id)
            ->where('kategori_gaji_id', $kategori_pengurang)
            ->get();

        // Iterasi setiap penyesuaian gaji untuk validasi dan perhitungan
        foreach ($penyesuaianGajis as $penyesuaianGaji) {
            // $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse($penyesuaianGaji->bulan_mulai) : null;
            // $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse($penyesuaianGaji->bulan_selesai) : null;
            $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_mulai)) : null;
            $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_selesai)) : null;
            $currentDate = Carbon::now();

            // Cek apakah saat ini berada pada rentang bulan mulai dan selesai atau jika null
            if (
                ($bulanMulai && $bulanSelesai && $currentDate->between($bulanMulai, $bulanSelesai)) ||
                (!$bulanMulai && !$bulanSelesai) ||
                ($bulanMulai && !$bulanSelesai && $currentDate->greaterThanOrEqualTo($bulanMulai)) ||
                (!$bulanMulai && $bulanSelesai && $currentDate->lessThanOrEqualTo($bulanSelesai))
            ) {
                // Kurangi take_home_pay dengan besaran penyesuaian gaji
                $takeHomePay -= $penyesuaianGaji->besaran;

                // Tambahkan detail penyesuaian gaji ke array details
                $details[] = [
                    'penggajian_id' => $penggajian_id,
                    'kategori_gaji_id' => $kategori_pengurang,
                    'nama_detail' => $penyesuaianGaji->nama_detail,
                    'besaran' => $penyesuaianGaji->besaran
                ];
            }
        }

        return $details;
    }
}
