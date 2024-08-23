<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerubahanPersonal extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'riwayat_perubahan_id' => 'integer'
    ];

    /**
     * Get the riwayat_perubahan that owns the PerubahanKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function riwayat_perubahans(): BelongsTo
    {
        return $this->belongsTo(RiwayatPerubahan::class, 'riwayat_perubahan_id', 'id');
    }
}
