<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RunThr extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'riwayat_thr_id' => 'integer',
        'data_karyawan_id' => 'integer'
    ];

    /**
     * Get the thr that owns the RunThr
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thr(): BelongsTo
    {
        return $this->belongsTo(Thr::class, 'thr_id', 'id');
    }

    /**
     * Get the riwayat_thrs that owns the RunThr
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function riwayat_thrs(): BelongsTo
    {
        return $this->belongsTo(RiwayatThr::class, 'riwayat_thr_id', 'id');
    }

    /**
     * Get the data_karyawans that owns the RunThr
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }
}
