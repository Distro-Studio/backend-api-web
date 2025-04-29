<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitKerja extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'jenis_karyawan' => 'integer',
        'kategori_unit_id' => 'integer',
    ];

    /**
     * Get all of the data_karyawan for the UnitKerja
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawan(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'unit_kerja_id', 'id');
    }

    /**
     * Get all of the jenis_penilaians for the UnitKerja
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jenis_penilaians(): HasMany
    {
        return $this->hasMany(JenisPenilaian::class, 'unit_kerja_id', 'id');
    }

    /**
     * Get the kategori_unit that owns the UnitKerja
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategori_unit(): BelongsTo
    {
        return $this->belongsTo(KategoriUnitKerja::class, 'kategori_unit_id', 'id');
    }
}
