<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CalculateHelper
{
    // public static function calculatedPPH21ForMonths($penghasilanBruto, $ptkp_id)
    // {
    //     // Langkah 1: Ambil data PTKP dari data_karyawans
    //     $ptkp = DB::table('ptkps')->where('id', $ptkp_id)->first();

    //     // Langkah 2: Cocokkan kategori_ter_id pada tabel ptkps dengan id kategori ter pada tabel kategori_ters
    //     $kategoriTer = DB::table('kategori_ters')->where('id', $ptkp->kategori_ter_id)->first();

    //     // Langkah 3: Ambil nilai percentage pada tabel ters dengan syarat kategori_ter_id dan gaji bruto antara from_ter dan to_ter
    //     $ters = DB::table('ters')
    //         ->select('percentage')
    //         ->where('kategori_ter_id', $kategoriTer->id)
    //         ->where('from_ter', '<=', $penghasilanBruto)
    //         ->where('to_ter', '>=', $penghasilanBruto)
    //         ->first();

    //     $pph21Bulanan = ($ters->percentage / 100) * $penghasilanBruto;
    //     return $pph21Bulanan;
    // }

    // public static function calculatedPenyesuaianPenambah($kategori_penambah, $penggajian_id, $penghasilanBruto)
    // {
    //     $details = [];

    //     // Ambil data penyesuaian gaji penambah berdasarkan penggajian_id
    //     $penyesuaianGajis = DB::table('penyesuaian_gajis')
    //         ->where('penggajian_id', $penggajian_id)
    //         ->where('kategori_gaji_id', $kategori_penambah)
    //         ->get();

    //     // Iterasi setiap penyesuaian gaji untuk validasi dan perhitungan
    //     foreach ($penyesuaianGajis as $penyesuaianGaji) {
    //         // $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse($penyesuaianGaji->bulan_mulai) : null;
    //         // $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse($penyesuaianGaji->bulan_selesai) : null;
    //         $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_mulai)) : null;
    //         $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_selesai)) : null;
    //         $currentDate = Carbon::now();

    //         // Cek apakah saat ini berada pada rentang bulan mulai dan selesai atau jika null
    //         if (
    //             ($bulanMulai && $bulanSelesai && $currentDate->between($bulanMulai, $bulanSelesai)) ||
    //             (!$bulanMulai && !$bulanSelesai) ||
    //             ($bulanMulai && !$bulanSelesai && $currentDate->greaterThanOrEqualTo($bulanMulai)) ||
    //             (!$bulanMulai && $bulanSelesai && $currentDate->lessThanOrEqualTo($bulanSelesai))
    //         ) {
    //             // Tambahkan take_home_pay dengan besaran penyesuaian gaji
    //             $penghasilanBruto += $penyesuaianGaji->besaran;

    //             // Tambahkan detail penyesuaian gaji ke array details
    //             $details[] = [
    //                 'penggajian_id' => $penggajian_id,
    //                 'kategori_gaji_id' => $kategori_penambah,
    //                 'nama_detail' => $penyesuaianGaji->nama_detail,
    //                 'besaran' => $penyesuaianGaji->besaran
    //             ];
    //         }
    //     }

    //     return $details;
    // }

    // public static function calculatedPenyesuaianPengurang($kategori_pengurang, $penggajian_id, $takeHomePay)
    // {
    //     $details = [];

    //     // Ambil data penyesuaian gaji pengurang berdasarkan penggajian_id
    //     $penyesuaianGajis = DB::table('penyesuaian_gajis')
    //         ->where('penggajian_id', $penggajian_id)
    //         ->where('kategori_gaji_id', $kategori_pengurang)
    //         ->get();

    //     // Iterasi setiap penyesuaian gaji untuk validasi dan perhitungan
    //     foreach ($penyesuaianGajis as $penyesuaianGaji) {
    //         // $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse($penyesuaianGaji->bulan_mulai) : null;
    //         // $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse($penyesuaianGaji->bulan_selesai) : null;
    //         $bulanMulai = $penyesuaianGaji->bulan_mulai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_mulai)) : null;
    //         $bulanSelesai = $penyesuaianGaji->bulan_selesai ? Carbon::parse(RandomHelper::convertToDateString($penyesuaianGaji->bulan_selesai)) : null;
    //         $currentDate = Carbon::now();

    //         // Cek apakah saat ini berada pada rentang bulan mulai dan selesai atau jika null
    //         if (
    //             ($bulanMulai && $bulanSelesai && $currentDate->between($bulanMulai, $bulanSelesai)) ||
    //             (!$bulanMulai && !$bulanSelesai) ||
    //             ($bulanMulai && !$bulanSelesai && $currentDate->greaterThanOrEqualTo($bulanMulai)) ||
    //             (!$bulanMulai && $bulanSelesai && $currentDate->lessThanOrEqualTo($bulanSelesai))
    //         ) {
    //             // Kurangi take_home_pay dengan besaran penyesuaian gaji
    //             $takeHomePay -= $penyesuaianGaji->besaran;

    //             // Tambahkan detail penyesuaian gaji ke array details
    //             $details[] = [
    //                 'penggajian_id' => $penggajian_id,
    //                 'kategori_gaji_id' => $kategori_pengurang,
    //                 'nama_detail' => $penyesuaianGaji->nama_detail,
    //                 'besaran' => $penyesuaianGaji->besaran
    //             ];
    //         }
    //     }

    //     return $details;
    // }

    // public static function calculatedPPH21ForDecember($dataKaryawan, $reward)
    // {
    //     // 1. Hitung bruto dan premi Desember
    //     $penghasilanBrutoDesember = $this->calculatedPenghasilanBruto($dataKaryawan, $reward);
    //     $totalPremiDesember = $this->calculatedPremi($dataKaryawan->data_karyawan_id, $penghasilanBrutoDesember, $dataKaryawan->gaji_pokok);
    //     $currentYear = Carbon::now()->year;

    //     // 2. Jumlahkan bruto dan premi dari Januari hingga Desember
    //     $totalBruto = DB::table('penggajians')
    //         ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
    //         ->whereYear('tgl_penggajian', $currentYear)
    //         ->sum('gaji_bruto') + $penghasilanBrutoDesember;

    //     $totalPremi = DB::table('penggajians')
    //         ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
    //         ->whereYear('tgl_penggajian', $currentYear)
    //         ->sum('total_premi') + $totalPremiDesember;

    //     // 3. Kurangi total bruto dengan total premi
    //     $penghasilanNeto = $totalBruto - $totalPremi;

    //     // 4. Kurangi dengan biaya jabatan (5% dari total bruto)
    //     $biayaJabatan = 0.05 * $totalBruto;
    //     $biayaJabatan = min($biayaJabatan, 500000); // Batas maksimum Rp500.000
    //     $penghasilanNetoSetelahBiayaJabatan = $penghasilanNeto - $biayaJabatan;

    //     // 5. Kurangi dengan nilai PTKP
    //     $nilaiPTKP = DB::table('ptkps')
    //         ->where('id', $dataKaryawan->ptkp_id)
    //         ->value('nilai');
    //     $penghasilanKenaPajak = $penghasilanNetoSetelahBiayaJabatan - $nilaiPTKP;

    //     // 6. Kalikan dengan tarif pajak 2021
    //     $pph21Tahunan = $this->calculatedPenghasilanKenaPajak($penghasilanKenaPajak);

    //     // 7. Kurangi dengan jumlah PPh bulanan dari Januari hingga November
    //     $pph21BulananTotal = DB::table('penggajians')
    //         ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
    //         ->whereBetween('tgl_penggajian', [Carbon::create($currentYear, 1, 1), Carbon::create($currentYear, 11, 30)])
    //         ->sum('pph_21');
    //     $pph21Desember = $pph21Tahunan - $pph21BulananTotal;
    //     return $pph21Desember;
    // }

    // public function calculatedPenghasilanBruto($dataKaryawan, $reward, $penghasilanTHR)
    // {
    //     return $dataKaryawan->gaji_pokok
    //         + $reward
    //         + $penghasilanTHR
    //         + $dataKaryawan->tunjangan_jabatan
    //         + $dataKaryawan->tunjangan_fungsional
    //         + $dataKaryawan->tunjangan_khusus
    //         + $dataKaryawan->tunjangan_lainnya
    //         + $dataKaryawan->uang_makan
    //         + $dataKaryawan->uang_lembur;
    // }

    // public function calculatedPenghasilanKenaPajak($penghasilanKenaPajak)
    // {
    //     $pph21 = 0;
    //     if ($penghasilanKenaPajak > 0) {
    //         if ($penghasilanKenaPajak > 5000000000) {
    //             $pph21 += 0.35 * $penghasilanKenaPajak;
    //         } elseif ($penghasilanKenaPajak > 500000000) {
    //             $pph21 += 0.30 * $penghasilanKenaPajak;
    //         } elseif ($penghasilanKenaPajak > 250000000) {
    //             $pph21 += 0.25 * $penghasilanKenaPajak;
    //         } elseif ($penghasilanKenaPajak > 60000000) {
    //             $pph21 += 0.15 * $penghasilanKenaPajak;
    //         } else {
    //             $pph21 += 0.05 * $penghasilanKenaPajak;
    //         }
    //     }
    //     return $pph21;
    // }

    public static function calculatedTHR($dataKaryawan)
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

    public static function calculatedPremi($data_karyawan_id, $penghasilanBruto, $gajiPokok)
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
    public static function calculatedPremiDetail($premi, $penghasilanBruto, $gajiPokok)
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

    public static function calculatedPPH21ForMonths($penghasilanBruto, $ptkp_id)
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

    public static function calculatedPPH21ForDecember($dataKaryawan, $reward)
    {
        // 1. Hitung bruto dan premi Desember
        $penghasilanBrutoDesember = self::calculatedPenghasilanBruto($dataKaryawan, $reward);
        $totalPremiDesember = self::calculatedPremi($dataKaryawan->data_karyawan_id, $penghasilanBrutoDesember, $dataKaryawan->gaji_pokok);
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
        $pph21Tahunan = self::calculatedPenghasilanKenaPajak($penghasilanKenaPajak);

        // 7. Kurangi dengan jumlah PPh bulanan dari Januari hingga November
        $pph21BulananTotal = DB::table('penggajians')
            ->where('data_karyawan_id', $dataKaryawan->data_karyawan_id)
            ->whereBetween('tgl_penggajian', [Carbon::create($currentYear, 1, 1), Carbon::create($currentYear, 11, 30)])
            ->sum('pph_21');
        $pph21Desember = $pph21Tahunan - $pph21BulananTotal;
        return $pph21Desember;
    }

    public static function calculatedRewardBOR($data_karyawan_id, $sertakan_bor)
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

    public function calculatedRewardPresensi($data_karyawan_id)
    {
        $statusRewardPresensi = DB::table('data_karyawans')
            ->where('id', $data_karyawan_id)
            ->value('status_reward_presensi');

        $bonusPresensi = 0;

        // Jika status_reward_presensi adalah true, karyawan mendapatkan reward
        if ($statusRewardPresensi) {
            $bonusPresensi = 300000;
        }

        return $bonusPresensi;
    }

    public static function calculatedPenghasilanBruto($dataKaryawan, $reward, $penghasilanTHR = 0)
    {
        return $dataKaryawan->gaji_pokok
            + $reward
            + $penghasilanTHR
            + $dataKaryawan->tunjangan_jabatan
            + $dataKaryawan->tunjangan_kompetensi
            + $dataKaryawan->tunjangan_fungsional
            + $dataKaryawan->tunjangan_khusus
            + $dataKaryawan->tunjangan_lainnya
            + $dataKaryawan->uang_makan
            + $dataKaryawan->uang_lembur;
    }

    public static function calculatedTotalTunjangan($dataKaryawan)
    {
        return $dataKaryawan->tunjangan_jabatan
            + $dataKaryawan->tunjangan_kompetensi
            + $dataKaryawan->tunjangan_fungsional
            + $dataKaryawan->tunjangan_khusus
            + $dataKaryawan->tunjangan_lainnya;
    }

    public static function calculatedPenghasilanKenaPajak($penghasilanKenaPajak)
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

    public static function calculatedPenyesuaianPenambah($kategori_penambah, $penggajian_id, &$takeHomePay)
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

    public static function calculatedPenyesuaianPengurang($kategori_pengurang, $penggajian_id, &$takeHomePay)
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
