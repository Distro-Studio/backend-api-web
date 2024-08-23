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

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'kategori_berkas_id' => 'integer',
        'status_berkas_id' => 'integer',
        'verifikator_1' => 'integer',
    ];

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
        return $this->hasMany(Presensi::class, 'foto_masuk', 'id');
    }

    /**
     * Get all of the presensi_foto_masuk for the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function presensi_foto_keluar(): HasMany
    {
        return $this->hasMany(Presensi::class, 'foto_keluar', 'id');
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

    /**
     * Get the status_berkas that owns the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_berkas(): BelongsTo
    {
        return $this->belongsTo(StatusBerkas::class, 'status_berkas_id', 'id');
    }

    /**
     * Get the verifikator_1 that owns the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_berkas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get all of the perubahan_berkas for the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perubahan_berkas(): HasMany
    {
        return $this->hasMany(PerubahanBerkas::class, 'berkas_id', 'id');
    }

    /**
     * Get all of the diklats for the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function berkas_dokumen_eksternal(): HasMany
    {
        return $this->hasMany(Diklat::class, 'dokumen_eksternal', 'id');
    }

    /**
     * Get all of the dokumen_gambar for the Berkas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function berkas_gambar(): HasMany
    {
        return $this->hasMany(Diklat::class, 'gambar', 'id');
    }
}
