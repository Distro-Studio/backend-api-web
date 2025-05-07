<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnulirPresensi extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'data_karyawan_id' => 'integer',
        'presensi_id' => 'integer',
        'dokumen_anulir_id' => 'integer',
    ];

    /**
     * Get the data_karyawans that owns the AnulirPresensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the presensis that owns the AnulirPresensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function presensis(): BelongsTo
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'id');
    }

    /**
     * Get the dokumen_anulir that owns the AnulirPresensi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dokumen_anulir(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_anulir', 'id');
    }
}
