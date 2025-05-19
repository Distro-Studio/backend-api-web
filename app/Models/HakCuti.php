<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HakCuti extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'data_karyawan_id' => 'integer',
        'tipe_cuti_id' => 'integer',
        'kuota' => 'integer',
        'used_kuota' => 'integer',
    ];

    /**
     * Get the data_karyawans that owns the HakCuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the tipe_cutis that owns the HakCuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tipe_cutis(): BelongsTo
    {
        return $this->belongsTo(TipeCuti::class, 'tipe_cuti_id', 'id');
    }
}
