<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerubahanKeluarga extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'riwayat_perubahan_id' => 'integer',
        'jenis_perubahan' => 'boolean',
        'data_keluarga_id' => 'integer',
        'status_hidup' => 'boolean'
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

    /**
     * Get the data_keluargas that owns the PerubahanKeluarga
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function data_keluargas(): BelongsTo
    {
        return $this->belongsTo(DataKeluarga::class, 'data_keluarga_id', 'id');
    }
}
