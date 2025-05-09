<?php

namespace App\Jobs\Penggajian;

use Carbon\Carbon;
use App\Models\Lembur;
use App\Models\DetailGaji;
use App\Models\Penggajian;
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
        $currentDate = Carbon::now('Asia/Jakarta');
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
        Log::info("THR Karyawan IDs: " . implode(', ', $thrKaryawanIds));

        $query = DB::table('data_karyawans')
            ->join('kelompok_gajis', 'data_karyawans.kelompok_gaji_id', '=', 'kelompok_gajis.id')
            ->leftJoin('penggajians', 'data_karyawans.id', '=', 'penggajians.data_karyawan_id')
            ->join('status_karyawans', 'data_karyawans.status_karyawan_id', '=', 'status_karyawans.id')
            ->leftJoin('kompetensis', DB::raw('COALESCE(data_karyawans.kompetensi_id, 0)'), '=', 'kompetensis.id')
            ->select(
                'data_karyawans.id as data_karyawan_id',
                'data_karyawans.status_karyawan_id',
                DB::raw('COALESCE(kelompok_gajis.besaran_gaji, 0) as gaji_pokok'),

                // TUNJANGAN JABATAN DIAMBIL DARI TABEL JABATAN
                DB::raw('COALESCE(data_karyawans.tunjangan_jabatan, 0) as tunjangan_jabatan'),
                DB::raw('COALESCE(data_karyawans.tunjangan_fungsional, 0) as tunjangan_fungsional'),
                DB::raw('COALESCE(data_karyawans.tunjangan_khusus, 0) as tunjangan_khusus'),
                DB::raw('COALESCE(data_karyawans.tunjangan_lainnya, 0) as tunjangan_lainnya'),
                DB::raw('COALESCE(data_karyawans.uang_makan, 0) as uang_makan'),
                DB::raw('COALESCE(data_karyawans.uang_lembur, 0) as uang_lembur'),
                'data_karyawans.ptkp_id as ptkp_id',
                'status_karyawans.label as status_karyawan',
                'data_karyawans.tgl_masuk as tgl_masuk',
                'data_karyawans.user_id as user_id'
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

            // Hitung reward (BOR, Bonus Presensi dan Lembur)
            $rewardBOR = $this->calculatedRewardBOR($data_karyawan_id, $this->sertakan_bor);
            $rewardBonusPresensi = $this->calculatedRewardPresensi($data_karyawan_id);
            $rewardLembur = $this->calculatedLembur($dataKaryawan);
            $totalReward = $rewardBOR + $rewardBonusPresensi + $rewardLembur;

            // uang makan sebulan
            // $uangMakanSebulan = $this->calculatedUangMakanSebulan($dataKaryawan);

            // Potongan tagihan
            $potonganTagihan = $this->calculatedTagihanPotongan($dataKaryawan);
            $totalPotonganPerBulan = $potonganTagihan['total_potongan_per_bulan'];
            $potonganDetails = $potonganTagihan['potongan_detail_gaji'];

            // Tentukan apakah THR perlu dihitung
            $penghasilanTHR = in_array($data_karyawan_id, $thrKaryawanIds) ? $this->calculatedTHR($dataKaryawan) : 0;
            Log::info("THR: " . $penghasilanTHR);

            // Hitung penghasilan THR, bruto, total tunjangan, dan total premi
            $totalTunjangan = $this->calculatedTotalTunjangan($dataKaryawan);
            $penghasilanBruto = $this->calculatedPenghasilanBruto($dataKaryawan, $totalTunjangan);
            $penghasilanBrutoTotal = $this->calculatedPenghasilanBrutoTotal($dataKaryawan, $totalReward, $penghasilanTHR);
            // $penghasilanBrutoTotal = $this->calculatedPenghasilanBrutoTotal($dataKaryawan, $totalReward, $penghasilanTHR, $uangMakanSebulan);
            $totalPremi = $this->calculatedPremi($data_karyawan_id, $penghasilanBruto, $penghasilanBrutoTotal, $dataKaryawan->gaji_pokok);

            // Tentukan status penggajian
            $status_penggajian = $currentDate->greaterThanOrEqualTo($tgl_mulai) ? $statusSudahDipublikasi : $statusBelumDipublikasi;

            // Hitung PPh 21 bulanan dan PPh 21 Desember
            $currentMonth = Carbon::now('Asia/Jakarta')->month;
            $penggajianData = [
                'riwayat_penggajian_id' => $this->riwayat_penggajian_id,
                'data_karyawan_id' => $data_karyawan_id,
                'tgl_penggajian' => Carbon::now('Asia/Jakarta'),
                'gaji_pokok' => $dataKaryawan->gaji_pokok,
                'total_tunjangan' => $totalTunjangan,
                'reward' => $totalReward,
                'gaji_bruto' => $penghasilanBrutoTotal,
                'total_premi' => $totalPremi,
                'status_gaji_id' => $status_penggajian
            ];

            if ($currentMonth >= 1 && $currentMonth <= 11) {
                // Januari - November
                $pph21Bulanan = $this->calculatedPPH21ForMonths($penghasilanBrutoTotal, $dataKaryawan->ptkp_id);
                $takeHomePay = $penghasilanBrutoTotal - $totalPremi - $pph21Bulanan - $totalPotonganPerBulan;
                $penggajianData['pph_21'] = $pph21Bulanan;
                $penggajianData['take_home_pay'] = $takeHomePay;

                $penggajian = Penggajian::updateOrCreate(
                    [
                        'data_karyawan_id' => $data_karyawan_id,
                        'tgl_penggajian' => Carbon::now('Asia/Jakarta'),
                    ],
                    $penggajianData
                );

                Log::info("| TAKE HOME PAY | Karyawan ID {$data_karyawan_id} bulan [{$currentMonth}] adalah {$takeHomePay}.");
            } elseif ($currentMonth == 12) {
                // Desember
                $pph21Desember = $this->calculatedPPH21ForDecember($dataKaryawan, $potonganTagihan['total_potongan_per_bulan'], $penghasilanBrutoTotal, $totalPremi);
                $takeHomePayDesember = $penghasilanBrutoTotal - $totalPremi - $pph21Desember;
                $penggajianData['pph_21'] = $pph21Desember;
                $penggajianData['take_home_pay'] = $takeHomePayDesember;

                $penggajian = Penggajian::updateOrCreate(
                    [
                        'data_karyawan_id' => $data_karyawan_id,
                        'tgl_penggajian' => Carbon::now('Asia/Jakarta'),
                    ],
                    $penggajianData
                );

                Log::info("| TAKE HOME PAY DESEMBER | Karyawan ID {$data_karyawan_id} bulan Desember adalah {$takeHomePayDesember}.");
            } else {
                Log::error("Perhitungan tidak valid untuk karyawan ID {$data_karyawan_id}.");
            }

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
                    'besaran' => $rewardLembur == 0 ? null : $rewardLembur
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
                    'nama_detail' => 'Reward BOR',
                    'besaran' => $rewardBOR == 0 ? null : $rewardBOR
                ],
                [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_penambah,
                    'nama_detail' => 'Reward Absensi',
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
                    'nama_detail' => 'PPh21',
                    'besaran' => $currentMonth == 12 ? ($pph21Desember == 0 ? null : $pph21Desember) : ($pph21Bulanan == 0 ? null : $pph21Bulanan)
                ]
            ];

            // detail premi
            $premis = DB::table('pengurang_gajis')
                ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
                ->where('pengurang_gajis.data_karyawan_id', $data_karyawan_id)
                ->whereNull('pengurang_gajis.deleted_at')
                ->select('premis.*')
                ->get();

            foreach ($premis as $premi) {
                $premiAmount = $this->calculatedPremiDetail($premi, $penghasilanBruto, $penghasilanBrutoTotal, $dataKaryawan->gaji_pokok, $data_karyawan_id);
                $details[] = [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_pengurang,
                    'nama_detail' => $premi->nama_premi,
                    'besaran' => $premiAmount == 0 ? null : $premiAmount
                ];
            }

            foreach ($potonganDetails as $detail) {
                $details[] = [
                    'penggajian_id' => $penggajian->id,
                    'kategori_gaji_id' => $kategori_pengurang,
                    'nama_detail' => $detail['nama_detail'],
                    'besaran' => $detail['besaran'],
                ];
            }

            foreach ($details as $detail) {
                DetailGaji::create($detail);
            }
        }
    }

    /* =========================== Calculated ============================= */
    private function calculatedTHR($dataKaryawan)
    {
        $thr = 0;
        $tglMulaiKerja = Carbon::createFromFormat('d-m-Y', $dataKaryawan->tgl_masuk);
        $masaKerja = $tglMulaiKerja->diffInMonths(Carbon::now('Asia/Jakarta'));

        if ($dataKaryawan->status_karyawan_id == 1) {
            if ($masaKerja <= 12) {
                $thr = ($masaKerja / 12) * $dataKaryawan->gaji_pokok;
            } else {
                $thr = $dataKaryawan->gaji_pokok;
            }
        }
        Log::info("Karyawan dengan masa kerja: $masaKerja dari {$tglMulaiKerja}, dengan thr $thr");

        return $thr;
    }

    private function calculatedPremi($data_karyawan_id, $penghasilanBruto, $penghasilanBrutoTotal, $gajiPokok)
    {
        // Ambil data premi yang dipilih untuk karyawan ini dari tabel pengurang_gajis
        $premis = DB::table('pengurang_gajis')
            ->join('premis', 'pengurang_gajis.premi_id', '=', 'premis.id')
            ->where('pengurang_gajis.data_karyawan_id', $data_karyawan_id)
            ->whereNull('pengurang_gajis.deleted_at')
            ->select('premis.*')
            ->get();

        $totalPremi = 0;

        foreach ($premis as $premi) {
            $premiAmount = 0;
            if ($premi->kategori_potongan_id == 3) { // gaji bruto total
                $basisPengkali = $penghasilanBrutoTotal;
            } else if ($premi->kategori_potongan_id == 2) { // gaji pokok
                $basisPengkali = $gajiPokok;
            } else {
                $basisPengkali = $penghasilanBruto;
            }

            $basisPengkaliRate = $basisPengkali;

            // Terapkan minimal maksimal rate jika ada
            if (!is_null($premi->minimal_rate)) {
                $basisPengkaliRate = max($basisPengkali, $premi->minimal_rate);
                Log::info("Minimal rate: {$premi->minimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
            }
            if (!is_null($premi->maksimal_rate)) {
                $basisPengkaliRate = min($basisPengkali, $premi->maksimal_rate);
                Log::info("Maksimal rate: {$premi->maksimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
            }

            if ($premi->id == 1) { // BPJS Kesehatan
                $dataKeluargas = DB::table('data_keluargas')
                    ->where('data_karyawan_id', $data_karyawan_id)
                    ->where('is_bpjs', 1)
                    ->where('status_hidup', 1)
                    ->whereNotNull('verifikator_1')
                    ->get();

                // Validasi bahwa semua anggota keluarga memiliki status_keluarga_id = 2
                $allVerified = $dataKeluargas->every(function ($keluarga) {
                    return $keluarga->status_keluarga_id == 2;
                });

                // Jika tidak semua anggota keluarga terverifikasi, kosongkan $dataKeluargas
                if (!$allVerified) {
                    $dataKeluargas = collect(); // Membuatnya kosong
                }

                // Keluarga Lainnya
                $keluargaLainnya = $dataKeluargas->whereIn('hubungan', ['Anak Ke-4', 'Anak Ke-5', 'Bapak', 'Ibu', 'Bapak Mertua', 'Ibu Mertua']);

                // Hitung premi untuk karyawan
                $premi_bpjs_kes = ($premi->besaran_premi / 100) * $basisPengkaliRate;

                // Hitung premi untuk keluarga lainnya (1% untuk setiap anggota keluarga lainnya)
                $totalPremiKeluargaLainnya = $premi_bpjs_kes * $keluargaLainnya->count();

                // Jika karyawan tidak memiliki keluarga BPJS, hanya hitung premi untuk karyawan saja
                if ($dataKeluargas->isEmpty()) {
                    $premiAmount = $premi_bpjs_kes;
                    Log::info("Calculated BPJS Kesehatan premi: {$premiAmount} untuk karyawan ID: {$data_karyawan_id} tanpa keluarga BPJS.");
                } else {
                    $premiAmount = $premi_bpjs_kes + $totalPremiKeluargaLainnya;
                    Log::info("Calculated BPJS Kesehatan premi: {$premiAmount} untuk karyawan ID: {$data_karyawan_id}, Total premi keluarga lainnya: {$premi_bpjs_kes}, Total premi keluarga lainnya: {$totalPremiKeluargaLainnya}");
                }
            } else {
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
            }
            $totalPremi += $premiAmount;
        }

        return $totalPremi;
    }

    private function calculatedTagihanPotongan($dataKaryawan)
    {
        $totalPotonganPerBulan = 0;
        $potonganDetails = [];

        $tagihanPotongans = DB::table('tagihan_potongans')
            ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
            ->where(function ($query) {
                $query->where('sisa_tagihan', '>', 0)
                    ->orWhereNull('sisa_tagihan');
            })
            ->where('status_tagihan_id', '!=', 3)
            ->get();

        // Loop untuk menghitung potongan per bulan dan sisa tagihan
        foreach ($tagihanPotongans as $tagihan) {
            $sisaTenor = $tagihan->sisa_tenor ?? $tagihan->tenor;

            // Case untuk tagihan dengan sisa_tagihan
            if ($tagihan->sisa_tagihan > 0) {
                // Jika sisa tenor lebih dari 1, bagi sisa tagihan dengan sisa tenor
                if ($sisaTenor > 1) {
                    $potonganPerBulan = round($tagihan->sisa_tagihan / $sisaTenor);
                    $totalPotonganPerBulan += $potonganPerBulan;

                    Log::info("Potongan per bulan untuk tagihan ID {$tagihan->id} (dari sisa tagihan): {$potonganPerBulan}");

                    // Hitung sisa tagihan setelah pengurangan potongan per bulan
                    $sisaTagihan = round($tagihan->sisa_tagihan - $potonganPerBulan);

                    // Jika sisa tagihan adalah 0, simpan null
                    if ($sisaTagihan <= 0) {
                        $sisaTagihan = null;
                    }

                    $sisaTenor--;
                    if ($sisaTenor <= 0) {
                        $sisaTenor = null;
                    }

                    // Update sisa_tagihan dan sisa_tenor ke database
                    DB::table('tagihan_potongans')
                        ->where('id', $tagihan->id)
                        ->update([
                            'sisa_tagihan' => $sisaTagihan,
                            'sisa_tenor' => $sisaTenor,
                            'status_tagihan_id' => 2
                        ]);

                    Log::info("Sisa tagihan untuk tagihan ID {$tagihan->id} setelah update: " . ($sisaTagihan ?? 'null'));
                } else {
                    // Jika tenor kurang dari atau sama dengan 1, ambil langsung nilai sisa_tagihan
                    $potonganPerBulan = $tagihan->sisa_tagihan;
                    $totalPotonganPerBulan += $potonganPerBulan;

                    // Set sisa_tagihan ke null jika tagihan lunas
                    DB::table('tagihan_potongans')
                        ->where('id', $tagihan->id)
                        ->update([
                            'sisa_tagihan' => null,
                            'sisa_tenor' => null,
                            'status_tagihan_id' => 3
                        ]);

                    Log::info("Tagihan ID {$tagihan->id} telah lunas, sisa_tagihan diset menjadi null.");
                }
            }
            // Case untuk tagihan baru (tanpa sisa_tagihan)
            else {
                // Jika tenor lebih dari 1, bagi besaran dengan tenor
                if ($sisaTenor > 1) {
                    $potonganPerBulan = round($tagihan->besaran / $tagihan->tenor);
                    $totalPotonganPerBulan += $potonganPerBulan;

                    // Hitung sisa tagihan setelah pengurangan potongan per bulan
                    $sisaTagihan = round($tagihan->besaran - $potonganPerBulan);

                    // Jika sisa tagihan adalah 0, simpan null
                    if ($sisaTagihan <= 0) {
                        $sisaTagihan = null;
                        Log::info("Tagihan ID {$tagihan->id} telah lunas, sisa_tagihan diset menjadi null.");
                    }

                    $sisaTenor--;
                    if ($sisaTenor <= 0) {
                        $sisaTenor = null;
                    }

                    // Update sisa_tagihan dan sisa_tenor ke database
                    DB::table('tagihan_potongans')
                        ->where('id', $tagihan->id)
                        ->update([
                            'sisa_tagihan' => $sisaTagihan,
                            'sisa_tenor' => $sisaTenor,
                            'status_tagihan_id' => 2
                        ]);

                    Log::info("Sisa tagihan untuk tagihan ID {$tagihan->id} setelah update: " . ($sisaTagihan ?? 'null'));
                } else {
                    // Jika tenor kurang dari atau sama dengan 1, ambil langsung nilai besaran
                    $potonganPerBulan = $tagihan->besaran;
                    $totalPotonganPerBulan += $potonganPerBulan;

                    // Set sisa_tagihan ke null jika tagihan lunas
                    DB::table('tagihan_potongans')
                        ->where('id', $tagihan->id)
                        ->update([
                            'sisa_tagihan' => null,
                            'sisa_tenor' => null,
                            'status_tagihan_id' => 3
                        ]);

                    Log::info("Tagihan ID {$tagihan->id} telah lunas, sisa_tagihan diset menjadi null.");
                }
            }

            Log::info("Total potongan per bulan untuk karyawan ID {$dataKaryawan->data_karyawan_id}: {$totalPotonganPerBulan}");

            // Simpan detail potongan untuk setiap tagihan
            if ($tagihan->kategori_tagihan_id == 1 || $tagihan->kategori_tagihan_id == 2) {
                $namaDetail = $tagihan->kategori_tagihan_id == 1 ? 'Obat/Perawatan' : 'Koperasi';
                $potonganDetails[] = [
                    'nama_detail' => $namaDetail,
                    'besaran' => $potonganPerBulan
                ];
            }
        }

        return [
            'total_potongan_per_bulan' => $totalPotonganPerBulan,
            'potongan_detail_gaji' => $potonganDetails
        ];
    }

    // buat itung detail gajis
    private function calculatedPremiDetail($premi, $penghasilanBruto, $penghasilanBrutoTotal, $gajiPokok, $data_karyawan_id)
    {
        $premiAmount = 0;
        if ($premi->kategori_potongan_id == 3) { // gaji bruto total
            $basisPengkali = $penghasilanBrutoTotal;
        } else if ($premi->kategori_potongan_id == 2) { // gaji pokok
            $basisPengkali = $gajiPokok;
        } else {
            $basisPengkali = $penghasilanBruto;
        }

        $basisPengkaliRate = $basisPengkali;

        // Terapkan minimal maksimal rate jika ada
        if (!is_null($premi->minimal_rate)) {
            $basisPengkaliRate = max($basisPengkali, $premi->minimal_rate);
            Log::info("Minimal rate: {$premi->minimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
        }
        if (!is_null($premi->maksimal_rate)) {
            $basisPengkaliRate = min($basisPengkali, $premi->maksimal_rate);
            Log::info("Maksimal rate: {$premi->maksimal_rate} premi ID: {$premi->id} sumber potongan: {$basisPengkali}");
        }

        if ($premi->id == 1) { // BPJS Kesehatan
            $dataKeluargas = DB::table('data_keluargas')
                ->where('data_karyawan_id', $data_karyawan_id)
                ->where('is_bpjs', 1)
                ->where('status_hidup', 1)
                ->whereNotNull('verifikator_1')
                ->get();

            // Validasi bahwa semua anggota keluarga memiliki status_keluarga_id = 2
            $allVerified = $dataKeluargas->every(function ($keluarga) {
                return $keluarga->status_keluarga_id == 2;
            });

            // Jika tidak semua anggota keluarga terverifikasi, kosongkan $dataKeluargas
            if (!$allVerified) {
                $dataKeluargas = collect(); // Membuatnya kosong
            }

            // Keluarga Inti
            $keluargaLainnya = $dataKeluargas->whereIn('hubungan', ['Anak Ke-4', 'Anak Ke-5', 'Bapak', 'Ibu', 'Bapak Mertua', 'Ibu Mertua']);

            // Hitung premi untuk karyawan
            $premi_bpjs_kes = ($premi->besaran_premi / 100) * $basisPengkaliRate;

            // Hitung premi untuk keluarga lainnya (1% untuk setiap anggota keluarga lainnya)
            $totalPremiKeluargaLainnya = $premi_bpjs_kes * $keluargaLainnya->count();

            // Jika karyawan tidak memiliki keluarga BPJS, hanya hitung premi untuk karyawan saja
            if ($dataKeluargas->isEmpty()) {
                $premiAmount = $premi_bpjs_kes;
                Log::info("Calculated BPJS Kesehatan premi: {$premiAmount} untuk karyawan ID: {$data_karyawan_id} tanpa keluarga BPJS.");
            } else {
                $premiAmount = $premi_bpjs_kes + $totalPremiKeluargaLainnya;
                Log::info("Calculated BPJS Kesehatan premi: {$premiAmount} untuk karyawan ID: {$data_karyawan_id}, Total premi keluarga lainnya: {$premi_bpjs_kes}, Total premi keluarga lainnya: {$totalPremiKeluargaLainnya}");
            }
        } else {
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
        }

        return $premiAmount;
    }

    private function calculatedPPH21ForMonths($penghasilanBrutoTotal, $ptkp_id)
    {
        // Langkah 1: Ambil data PTKP dari data_karyawans
        $ptkp = DB::table('ptkps')->where('id', $ptkp_id)->first();

        // Langkah 2: Cocokkan kategori_ter_id pada tabel ptkps dengan id kategori ter pada tabel kategori_ters
        $kategoriTer = DB::table('kategori_ters')->where('id', $ptkp->kategori_ter_id)->first();

        // Langkah 3: Ambil nilai percentage pada tabel ters dengan syarat kategori_ter_id dan gaji bruto antara from_ter dan to_ter
        $ters = DB::table('ters')
            ->select('percentage')
            ->where('kategori_ter_id', $kategoriTer->id)
            ->where('from_ter', '<=', $penghasilanBrutoTotal)
            ->where('to_ter', '>=', $penghasilanBrutoTotal)
            ->first();

        $pph21Bulanan = ($ters->percentage / 100) * $penghasilanBrutoTotal;
        return ceil($pph21Bulanan);
    }

    private function calculatedPPH21ForDecember($dataKaryawan, $potonganTagihan, $penghasilanBrutoTotal, $totalPremis)
    {
        // 1. Hitung bruto dan premi Desember
        $penghasilanBrutoTotalDesember = $penghasilanBrutoTotal;
        $totalPremiDesember = $totalPremis;
        // $penghasilanBrutoTotalDesember = $this->calculatedPenghasilanBrutoTotal($dataKaryawan, $reward, $penghasilanTHR, $uangMakanSebulan);
        // $totalPremiDesember = $this->calculatedPremi($dataKaryawan->data_karyawan_id, $penghasilanBruto, $penghasilanBrutoTotalDesember, $dataKaryawan->gaji_pokok);
        // $totalPotonganTagihanDesember = $this->calculatedTagihanPotongan($dataKaryawan);
        // $totalPotonganTagihanDesember = $this->calculatedTagihanPotongan($dataKaryawan);
        $totalPotonganTagihan = $potonganTagihan;
        $currentYear = Carbon::now('Asia/Jakarta')->year;

        // 2. Jumlahkan bruto dan premi dari Januari hingga Desember
        $totalBruto = DB::table('penggajians')
            ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
            ->whereYear('tgl_penggajian', $currentYear)
            ->sum('gaji_bruto') + $penghasilanBrutoTotalDesember;

        $totalPremi = DB::table('penggajians')
            ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
            ->whereYear('tgl_penggajian', $currentYear)
            ->sum('total_premi') + $totalPremiDesember + $totalPotonganTagihan;

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
        return ceil($pph21Desember);
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

    // private function calculatedUangMakanSebulan($dataKaryawan)
    // {
    //     // Get current month and year
    //     $currentMonth = Carbon::now('Asia/Jakarta')->month;
    //     $currentYear = Carbon::now('Asia/Jakarta')->year;
    //     $daysInMonth = Carbon::createFromDate($currentYear, $currentMonth)->daysInMonth;

    //     $rate_uang_makan = $dataKaryawan->uang_makan;

    //     if (!$rate_uang_makan || $rate_uang_makan == 0) {
    //         return 0;
    //     }

    //     return $rate_uang_makan * $daysInMonth;
    // }

    // ini yg pake scheduller
    private function calculatedRewardPresensi($data_karyawan_id)
    {
        // $statusRewardPresensi = DB::table('data_karyawans')
        //     ->where('id', $data_karyawan_id)
        //     ->value('status_reward_presensi');
        $statusRewardPresensi = DB::table('reward_bulan_lalus')->where('data_karyawan_id', $data_karyawan_id)
            ->value('status_reward');

        $bonusPresensi = 0;

        // Jika status_reward_presensi adalah true, karyawan mendapatkan reward
        if ($statusRewardPresensi) {
            $bonusPresensi = 300000;
        }

        return $bonusPresensi;
    }

    private function calculatedPenghasilanBrutoTotal($dataKaryawan, $reward, $penghasilanTHR)
    {
        return $dataKaryawan->gaji_pokok
            + $reward
            + $penghasilanTHR
            + $dataKaryawan->tunjangan_jabatan
            + $dataKaryawan->tunjangan_fungsional
            + $dataKaryawan->tunjangan_khusus
            + $dataKaryawan->tunjangan_lainnya
            + $dataKaryawan->uang_makan;
    }

    private function calculatedPenghasilanBruto($dataKaryawan, $totalTunjangan)
    {
        return $dataKaryawan->gaji_pokok
            + $totalTunjangan;
    }

    private function calculatedLembur($dataKaryawan)
    {
        $rate_lembur = $dataKaryawan->uang_lembur;

        // Ambil semua record lembur untuk karyawan ini
        $lemburRecords = Lembur::where('user_id', $dataKaryawan->user_id)->get();

        // Inisialisasi total bonus lembur
        $totalBonusLembur = 0;

        // Loop melalui setiap record lembur
        foreach ($lemburRecords as $lembur) {
            if (!is_null($lembur->durasi)) {
                $durasiMenit = $lembur->durasi / 60;

                // Hitung bonus lembur untuk lembur ini dan tambahkan ke total
                $bonusLembur = ($rate_lembur / 60) * $durasiMenit;
                $totalBonusLembur += $bonusLembur;
                Log::info("Total bonus lembur: $totalBonusLembur, dari karyawan {$dataKaryawan->user_id}");
            }
        }

        return $totalBonusLembur;
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
}
