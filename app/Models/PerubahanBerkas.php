<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerubahanBerkas extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'riwayat_perubahan_id' => 'integer',
        'berkas_id' => 'integer'
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
     * Get the berkas that owns the PerubahanBerkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'berkas_id', 'id');
    }
}
