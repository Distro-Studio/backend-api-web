<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berkas extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the users that owns the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get all of the presensi_foto_masuk for the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presensi_foto_masuk(): HasMany
    {
        return $this->hasMany(Presensi::class, 'foto_masuk', 'local_key');
    }

    /**
     * Get all of the presensi_foto_masuk for the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presensi_foto_keluar(): HasMany
    {
        return $this->hasMany(Presensi::class, 'foto_keluar', 'local_key');
    }

    /**
     * Get the kategori_berkas that owns the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_berkas(): BelongsTo
    {
        return $this->belongsTo(KategoriBerkas::class, 'kategori_berkas_id', 'id');
    }
}
