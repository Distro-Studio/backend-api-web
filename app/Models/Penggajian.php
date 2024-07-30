<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penggajian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Status constants
    const STATUS_CREATED = 1;
    const STATUS_PUBLISHED = 2;

    public function getStatusDescriptionAttribute()
    {
        switch ($this->status_penggajian) {
            case self::STATUS_CREATED:
                return 'Butuh Verifikasi';
            case self::STATUS_PUBLISHED:
                return 'Berhasil Dipublikasi';
            default:
                return 'N/A';
        }
    }

    /**
     * Get the riwayat_penggajians that owns the Penggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function riwayat_penggajians(): BelongsTo
    {
        return $this->belongsTo(RiwayatPenggajian::class, 'riwayat_penggajian_id', 'id');
    }

    /**
     * Get all of the detail_gajis for the Penggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detail_gajis(): HasMany
    {
        return $this->hasMany(DetailGaji::class, 'penggajian_id', 'id');
    }

    /**
     * Get the data_karyawan that owns the Penggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_karyawans(): BelongsTo
    {
        return $this->belongsTo(DataKaryawan::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get all of the penyesuaian_gaji for the Penggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penyesuaian_gaji(): HasMany
    {
        return $this->hasMany(PenyesuaianGaji::class, 'penggajian_id', 'id');
    }

    /**
     * Get the status_gajis that owns the RiwayatPenggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_gajis(): BelongsTo
    {
        return $this->belongsTo(StatusGaji::class, 'status_gaji_id', 'id');
    }
}
