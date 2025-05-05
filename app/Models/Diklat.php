<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Diklat extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'gambar' => 'integer',
        'dokumen_eksternal' => 'integer',
        'dokumen_diklat_1' => 'integer',
        'dokumen_diklat_2' => 'integer',
        'dokumen_diklat_3' => 'integer',
        'dokumen_diklat_4' => 'integer',
        'dokumen_diklat_5' => 'integer',
        'kategori_diklat_id' => 'integer',
        'status_diklat_id' => 'integer',
        'total_peserta' => 'integer',
        'kuota' => 'integer',
        'durasi' => 'integer',
        'verifikator_1' => 'integer',
        'verifikator_2' => 'integer',
        'certificate_published' => 'integer',
        'certificate_verified_by' => 'integer',
    ];

    /**
     * Get the kategori_diklats that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_diklats(): BelongsTo
    {
        return $this->belongsTo(KategoriDiklat::class, 'kategori_diklat_id', 'id');
    }

    /**
     * Get the status_diklats that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status_diklats(): BelongsTo
    {
        return $this->belongsTo(StatusDiklat::class, 'status_diklat_id', 'id');
    }

    /**
     * Get all of the peserta_diklat for the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peserta_diklat(): HasMany
    {
        return $this->hasMany(PesertaDiklat::class, 'diklat_id', 'id');
    }

    /**
     * Get the dokumen_eksternals that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_dokumen_eksternals(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_eksternal', 'id');
    }

    /**
     * Get the berkas_gambar that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_gambars(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'gambar', 'id');
    }

    /**
     * Get the berkas_internal_1 that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_internal_1(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_diklat_1', 'id');
    }

        /**
     * Get the berkas_internal_2 that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_internal_2(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_diklat_2', 'id');
    }

        /**
     * Get the berkas_internal_3 that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_internal_3(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_diklat_3', 'id');
    }

        /**
     * Get the berkas_internal_4 that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_internal_4(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_diklat_4', 'id');
    }

        /**
     * Get the berkas_internal_5 that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function berkas_internal_5(): BelongsTo
    {
        return $this->belongsTo(Berkas::class, 'dokumen_diklat_5', 'id');
    }

    /**
     * Get the verifikator_1_diklats that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_1_diklats(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_1', 'id');
    }

    /**
     * Get the verifikator_2_diklats that owns the Cuti
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifikator_2_diklats(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_2', 'id');
    }

    /**
     * Get the certificate_diklats that owns the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function certificate_diklats(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certificate_verified_by', 'id');
    }

    /**
     * Get the internal_diklat_templates associated with the Diklat
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function internal_diklat_templates(): HasOne
    {
        return $this->hasOne(TemplateCertificate::class, 'diklat_id', 'id');
    }
}
