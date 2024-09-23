<?php

namespace App\Helpers;

use App\Models\DetailGaji;

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
}
