<?php

namespace App\Helpers;

use App\Models\DetailGaji;
use Illuminate\Support\Facades\DB;

class DetailGajiHelper
{
	/**
	 * Get the sum of 'besaran' for a specific 'nama_detail' and 'penggajian_id'
	 *
	 * @param int $penggajianId
	 * @param string $namaDetail
	 * @return int
	 */
	public static function getDetailGajiByNamaDetail($penggajianId, $namaDetail)
	{
		return DetailGaji::where('penggajian_id', $penggajianId)
			->where('nama_detail', $namaDetail)
			->sum('besaran') ?: 0;
	}

	public static function getKeluargaTerkenaPotonganBPJS($data_karyawan_id)
	{
		return DB::table('data_keluargas')
			->where('data_karyawan_id', $data_karyawan_id)
			->where('is_bpjs', 1)
			->whereIn('hubungan', ['Anak Ke-4', 'Anak Ke-5', 'Bapak', 'Ibu', 'Bapak Mertua', 'Ibu Mertua'])
			->pluck('hubungan')->toArray();
	}
}
