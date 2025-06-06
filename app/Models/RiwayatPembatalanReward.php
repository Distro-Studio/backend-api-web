<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiwayatPembatalanReward extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'data_karyawan_id' => 'integer',
        'cuti_id' => 'integer',
        'presensi_id' => 'integer',
        'riwayat_izin_id' => 'integer',
        'verifikator_1' => 'integer',
        'is_anulir_presensi' => 'integer',
    ];

    /**
     * Get the data_karyawans that owns the RiwayatPembatalanReward
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    public function cuti()
    {
        return $this->belongsTo(Cuti::class, 'cuti_id', 'id');
    }

    public function presensi()
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'id');
    }

    public function riwayat_izin()
    {
        return $this->belongsTo(RiwayatIzin::class, 'riwayat_izin_id', 'id');
    }

    /**
     * Get the verifikators that owns the RiwayatPembatalanReward
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikators(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }
}
