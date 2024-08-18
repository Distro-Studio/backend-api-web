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

    public static function calculatedPenyesuaianPenambah($kategori_penambah, $penggajian_id, $penghasilanBruto)
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
                $penghasilanBruto += $penyesuaianGaji->besaran;

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
}
