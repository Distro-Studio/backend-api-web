<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitKerja extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

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
     * Get all of the unit_kerja_dinilai for the UnitKerja
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unit_kerja_dinilai(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'unit_kerja_dinilai', 'id');
    }

    /**
     * Get all of the unit_kerja_penilai for the UnitKerja
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unit_kerja_penilai(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'unit_kerja_penilai', 'id');
    }
}
