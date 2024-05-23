<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataKaryawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the user that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the unit_kerja that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerjas(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id', 'id');
    }

    /**
     * Get the jabatan that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatans(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }

    /**
     * Get the kompetensi that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kompetensis(): BelongsTo
    {
        return $this->belongsTo(Kompetensi::class, 'kompetensi_id', 'id');
    }

    /**
     * Get the kelompok_gaji that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelompok_gajis(): BelongsTo
    {
        return $this->belongsTo(KelompokGaji::class, 'kelompok_gaji_id', 'id');
    }

    /**
     * Get the ptkp that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ptkps(): BelongsTo
    {
        return $this->belongsTo(Ptkp::class, 'ptkp_id', 'id');
    }

    /**
     * Get all of the penggajian for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penggajians(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the data_keluarga associated with the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_keluargas(): HasMany
    {
        return $this->hasMany(DataKeluarga::class, 'data_karyawan_id', 'id');
    }
}
