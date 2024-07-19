<?php

namespace App\Models;

use App\Models\Penggajian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiwayatPenggajian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Status constants
    const STATUS_CREATED = 1;
    const STATUS_PUBLISHED = 2;

    public function getStatusDescriptionAttribute()
    {
        switch ($this->status_riwayat_gaji) {
            case self::STATUS_CREATED:
                return 'Butuh Verifikasi';
            case self::STATUS_PUBLISHED:
                return 'Berhasil Dipublikasi';
            default:
                return 'N/A';
        }
    }

    /**
     * Get all of the penggajians for the RiwayatPenggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penggajians(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'riwayat_penggajian_id', 'id');
    }

    /**
     * Get the verifikator_1 that owns the RiwayatPenggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get the verifikator_2 that owns the RiwayatPenggajian
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_2', 'id');
    }
}
