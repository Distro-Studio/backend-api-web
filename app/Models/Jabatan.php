<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jabatan extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    /**
     * Get all of the data_karyawan for the Jabatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data_karyawan(): HasMany
    {
        return $this->hasMany(DataKaryawan::class, 'jabatan_id', 'id');
    }

    /**
     * Get all of the pertanyaans for the Jabatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pertanyaans(): HasMany
    {
        return $this->hasMany(Pertanyaan::class, 'jabatan_id', 'id');
    }

    /**
     * Get all of the jabatan_dinilai for the Jabatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jabatan_dinilai(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'jabatan_dinilai', 'id');
    }

    /**
     * Get all of the jabatan_penilai for the Jabatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jabatan_penilai(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'jabatan_penilai', 'id');
    }
}
