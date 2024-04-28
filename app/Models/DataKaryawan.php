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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the unit_kerja that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit_kerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id', 'id');
    }

    /**
     * Get the jabatan that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }

    /**
     * Get the kompetensi that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kompetensi(): BelongsTo
    {
        return $this->belongsTo(Kompetensi::class, 'kompetensi_id', 'id');
    }

    /**
     * Get the kelompok_gaji that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelompok_gaji(): BelongsTo
    {
        return $this->belongsTo(KelompokGaji::class, 'kelompok_gaji_id', 'id');
    }

    /**
     * Get the ptkp that owns the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ptkp(): BelongsTo
    {
        return $this->belongsTo(Ptkp::class, 'ptkp_id', 'id');
    }

    /**
     * Get all of the penggajian for the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'data_karyawan_id', 'id');
    }

    /**
     * Get the data_keluarga associated with the DataKaryawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function data_keluarga(): HasOne
    {
        return $this->hasOne(DataKeluarga::class, 'data_karyawan_id', 'id');
    }
}
