<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenyesuaianGaji extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // Status constants
    const STATUS_PENGURANG = 1;
    const STATUS_PENAMBAH = 2;

    public function getStatusDescriptionAttribute()
    {
        switch ($this->kategori) {
            case self::STATUS_PENGURANG:
                return 'Pengurang';
            case self::STATUS_PENAMBAH:
                return 'Penambah';
            default:
                return 'N/A';
        }
    }

    /**
     * Get the penggajians that owns the PenyesuaianGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function penggajians(): BelongsTo
    {
        return $this->belongsTo(Penggajian::class, 'penggajian_id', 'id');
    }

    /**
     * Get the kategori_gajis  that owns the PenyesuaianGaji
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_gajis(): BelongsTo
    {
        return $this->belongsTo(KategoriGaji::class, 'kategori_gaji_id', 'id');
    }
}
